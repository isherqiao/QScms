<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         backup_lib.php
 * @CreatedTime  20/11/26 12:00:00
 * @LastEditTime 20/11/26 12:00:00
 * @Description  系统备份核心库：打包（系统 zip / ZipArchive 自动选择）、FTP 上传、保留策略、定时调度与运行状态记录。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('QS_BACKUP_LIB')) {
    define('QS_BACKUP_LIB', '1.0');
}

/* ============================================================
 * 一、配置
 * ============================================================ */

/**
 * 备份相关配置项的完整默认值
 */
function qs_backup_defaults(): array
{
    return [
        // 既有打包参数
        'tar_name'              => 'qscms',
        'tar_directory'         => '/',
        'des_directory'         => '/backups/',
        'tar_exclude_directory' => '/backups/',
        'tar_passwd'            => '',
        'remote_bak01'          => 'email',
        'tar_email'             => '',
        'tar_other'             => '',
        // 计划任务
        'backup_cron_on'        => 'off',
        'backup_cron_freq'      => 'daily',
        'backup_cron_time'      => '03:00',
        'backup_cron_week'      => '1',
        'backup_cron_day'       => '1',
        'backup_keep'           => '7',
        // FTP 自动上传
        'backup_ftp_on'         => 'off',
        'backup_ftp_host'       => '',
        'backup_ftp_port'       => '21',
        'backup_ftp_user'       => '',
        'backup_ftp_pass'       => '',
        'backup_ftp_dir'        => '',
        'backup_ftp_ssl'        => 'none',
        'backup_ftp_pasv'       => 'on',
    ];
}

/**
 * 载入站点配置（CLI / 独立入口场景下 $qs_config 可能尚未存在）
 *
 * 注意：在函数内 require core.php，其中定义的 $qs_config 会落在函数作用域，
 * 必须在 require 之后手工搬回 $GLOBALS，否则配置读不到（会全用默认值）。
 */
function qs_backup_bootstrap(): void
{
    if (isset($GLOBALS['qs_config']) && is_array($GLOBALS['qs_config'])) {
        return;
    }
    $core = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'control' . DIRECTORY_SEPARATOR . 'core.php';
    if (!is_file($core)) {
        return;
    }

    require $core;

    foreach (['qs_config', 'sp_config'] as $qsVar) {
        if (isset($$qsVar) && is_array($$qsVar)) {
            $GLOBALS[$qsVar] = $$qsVar;
        }
    }
}

/**
 * 取备份配置（缺省项回落默认值）
 */
function qs_backup_cfg(): array
{
    qs_backup_bootstrap();

    $defaults = qs_backup_defaults();
    $cfg = $defaults;

    if (isset($GLOBALS['qs_config']) && is_array($GLOBALS['qs_config'])) {
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $GLOBALS['qs_config'])) {
                continue;
            }
            $val = $GLOBALS['qs_config'][$k];
            if (is_string($val) && $val === '') {
                continue; // 空值不覆盖默认，避免把打包目录等关键项清空
            }
            $cfg[$k] = $val;
        }
    }

    return $cfg;
}

/* ============================================================
 * 二、路径
 * ============================================================ */

/**
 * 站点根目录（绝对，正斜杠）
 */
function qs_backup_root(): string
{
    return rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
}

/**
 * 站点根相对路径 → 绝对路径
 */
function qs_backup_join(string $rel): string
{
    $rel = str_replace('\\', '/', $rel);
    $root = qs_backup_root();
    if ($rel === '') {
        return $root;
    }
    if (strpos($rel, $root) === 0) {
        return rtrim($rel, '/');
    }
    return $root . '/' . ltrim($rel, '/');
}

/**
 * 备份存放目录（绝对，末尾带斜杠）
 */
function qs_backup_des_dir(array $cfg): string
{
    return rtrim(qs_backup_join((string)$cfg['des_directory']), '/') . '/';
}

/**
 * 打包源目录（绝对，末尾不带斜杠）
 */
function qs_backup_src_dir(array $cfg): string
{
    return rtrim(qs_backup_join((string)$cfg['tar_directory']), '/');
}

/**
 * 排除清单（相对打包根目录，无首尾斜杠）
 * 内置强制排除：备份目录自身、SSG 产物、.git、admin/tmp
 */
function qs_backup_excludes(array $cfg): array
{
    $root = qs_backup_root();
    $src  = qs_backup_src_dir($cfg);
    $list = [];

    $raw = (string)($cfg['tar_exclude_directory'] ?? '');
    foreach (preg_split('/[，,]+/u', $raw) as $item) {
        $item = trim(str_replace('\\', '/', (string)$item));
        if ($item === '') {
            continue;
        }
        if (strpos($item, $root) === 0) {
            $item = substr($item, strlen($root));
        }
        if (strpos($item, $src) === 0) {
            $item = substr($item, strlen($src));
        }
        $item = trim($item, '/');
        if ($item !== '' && strpos($item, '..') === false) {
            $list[] = $item;
        }
    }

    // 备份存放目录自身必须排除，否则打包会把自己卷进去
    $des = qs_backup_des_dir($cfg);
    if (strpos($des, $src . '/') === 0) {
        $list[] = trim(substr($des, strlen($src)), '/');
    }

    // 内置强制排除
    foreach (['backups', 'static_site', '.git', basename(__DIR__) . '/tmp'] as $hard) {
        $list[] = $hard;
    }

    return array_values(array_unique(array_filter($list, static function ($v) {
        return $v !== '' && $v !== '.';
    })));
}

/* ============================================================
 * 三、运行状态与日志
 * ============================================================ */

function qs_backup_state_path(): string
{
    return qs_backup_root() . '/control/data/backup_state.php';
}

function qs_backup_state_get(): array
{
    $default = [
        'last_run'      => 0,
        'last_name'     => '',
        'last_file'     => '',
        'last_size'     => 0,
        'last_result'   => '',
        'last_msg'      => '',
        'last_trigger'  => '',
        'last_method'   => '',
        'ftp_result'    => '',
        'run_count'     => 0,
        'running_since' => 0,
    ];

    $f = qs_backup_state_path();
    if (is_file($f)) {
        $data = @include $f;
        if (is_array($data)) {
            $default = array_merge($default, $data);
        }
    }
    return $default;
}

function qs_backup_state_set(array $patch): array
{
    $state = array_merge(qs_backup_state_get(), $patch);
    $code = "<?php\n// QS Cms 系统备份运行状态（程序自动维护，请勿手工编辑）\nreturn " . var_export($state, true) . ";\n";
    @file_put_contents(qs_backup_state_path(), $code, LOCK_EX);
    return $state;
}

function qs_backup_log_path(): string
{
    return qs_backup_root() . '/control/data/backup_run.log';
}

function qs_backup_runlog(string $msg): void
{
    @file_put_contents(
        qs_backup_log_path(),
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

/**
 * 读取运行日志尾部
 */
function qs_backup_tail(int $lines = 20): array
{
    $f = qs_backup_log_path();
    if (!is_file($f)) {
        return [];
    }
    $arr = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$arr) {
        return [];
    }
    return array_slice($arr, -max(1, $lines));
}

/* ============================================================
 * 三之二、外部触发令牌
 * ============================================================ */

function qs_backup_token_path(): string
{
    return qs_backup_root() . '/control/data/backup_token.php';
}

function qs_backup_token_get(): string
{
    $f = qs_backup_token_path();
    if (is_file($f)) {
        $t = @include $f;
        if (is_string($t) && $t !== '') {
            return $t;
        }
    }
    $t = bin2hex(random_bytes(16));
    @file_put_contents($f, "<?php\n// 系统备份外部触发令牌（程序自动生成）\nreturn " . var_export($t, true) . ";\n", LOCK_EX);
    return $t;
}

function qs_backup_token_check(string $token): bool
{
    if ($token === '') {
        return false;
    }
    return hash_equals(qs_backup_token_get(), $token);
}

/* ============================================================
 * 四、打包
 * ============================================================ */

/**
 * 系统 zip 命令是否可用（优先使用，可加密）
 */
function qs_backup_zip_bin(): ?string
{
    static $found = null;
    if ($found !== null) {
        return $found === '' ? null : $found;
    }

    $found = '';
    if (!function_exists('shell_exec')) {
        return null;
    }
    $out = @shell_exec('zip -v 2>&1');
    if (is_string($out) && (stripos($out, 'Info-ZIP') !== false || stripos($out, 'zip') !== false && stripos($out, 'not found') === false)) {
        $found = 'zip';
    }
    return $found === '' ? null : $found;
}

/**
 * 生成备份包文件名
 */
function qs_backup_pack_name(array $cfg): string
{
    $base = preg_replace('/[^A-Za-z0-9._-]/', '', (string)$cfg['tar_name']);
    if ($base === '') {
        $base = 'qscms';
    }
    return $base . '_' . date('Ymd_His') . '.zip';
}

/**
 * 执行打包
 *
 * @param array $cfg  备份配置
 * @param array $info 打包结果信息（引用返回）
 * @return bool
 */
function qs_backup_pack(array $cfg, array &$info = []): bool
{
    @set_time_limit(0);
    @ignore_user_abort(true);

    $info = [
        'file'      => '',
        'name'      => '',
        'size'      => 0,
        'elapsed'   => 0,
        'method'    => '',
        'encrypted' => false,
        'warn'      => '',
        'error'     => '',
        'files'     => 0,
    ];

    $src    = qs_backup_src_dir($cfg);
    $desDir = qs_backup_des_dir($cfg);

    if (!is_dir($src)) {
        $info['error'] = '打包源目录不存在：' . qs_backup_src_dir($cfg);
        return false;
    }

    if (!is_dir($desDir)) {
        if (!@mkdir($desDir, 0777, true) && !is_dir($desDir)) {
            $info['error'] = '无法创建备份目录：' . $desDir;
            return false;
        }
    }
    if (!is_writable($desDir)) {
        $info['error'] = '备份目录不可写：' . $desDir;
        return false;
    }

    $name = qs_backup_pack_name($cfg);
    $abs  = $desDir . $name;
    $info['name'] = $name;
    $info['file'] = $abs;

    $excl = qs_backup_excludes($cfg);
    $pwd  = trim((string)$cfg['tar_passwd']);
    if ($pwd === 'nopass') {
        $pwd = '';
    }

    $t0 = microtime(true);

    // ---- 方式一：系统 zip 命令（可加密） ----
    if (qs_backup_zip_bin() !== null && function_exists('exec')) {
        $cmd = 'cd ' . escapeshellarg($src) . ' && zip -r -q';
        if ($pwd !== '') {
            $cmd .= ' -P ' . escapeshellarg($pwd);
        }
        $cmd .= ' ' . escapeshellarg($abs) . ' .';
        foreach ($excl as $e) {
            $cmd .= ' -x ' . escapeshellarg($e . '/*');
        }
        $out = [];
        $code = 0;
        @exec($cmd . ' 2>&1', $out, $code);

        if ($code === 0 && is_file($abs) && filesize($abs) > 0) {
            $info['method'] = 'zip';
            $info['encrypted'] = ($pwd !== '');
            $info['size'] = (int)filesize($abs);
            $info['elapsed'] = round(microtime(true) - $t0, 2);
            return true;
        }

        @unlink($abs);
        qs_backup_runlog('系统 zip 打包失败，回退 ZipArchive（exit=' . $code . '）');
    }

    // ---- 方式二：ZipArchive（跨平台兜底） ----
    if (!class_exists('ZipArchive')) {
        $info['error'] = '系统缺少 zip 命令，且 PHP 未启用 zip 扩展，无法打包';
        return false;
    }

    $zip = new ZipArchive();
    $open = $zip->open($abs, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($open !== true) {
        $info['error'] = '创建压缩包失败（代码 ' . $open . '）';
        return false;
    }

    $srcReal = str_replace('\\', '/', realpath($src) ?: $src);
    $prefix  = strlen($srcReal) + 1;
    $count   = 0;

    try {
        $dirIt = new RecursiveDirectoryIterator(
            $srcReal,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
        );
        $it = new RecursiveIteratorIterator($dirIt, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($it as $path => $item) {
            $p = str_replace('\\', '/', $path);
            $rel = substr($p, $prefix);

            if ($rel === false || $rel === '') {
                continue;
            }
            foreach ($excl as $e) {
                if ($e === '') {
                    continue;
                }
                if ($rel === $e || strpos($rel, $e . '/') === 0) {
                    continue 2;
                }
            }

            if ($item->isDir()) {
                $zip->addEmptyDir($rel);
            } elseif ($item->isFile()) {
                if (@$zip->addFile($p, $rel)) {
                    $count++;
                }
            }
        }
    } catch (Throwable $e) {
        qs_backup_runlog('打包遍历异常：' . $e->getMessage());
    }

    $zip->close();

    if (!is_file($abs) || filesize($abs) === 0) {
        $info['error'] = '压缩包为空，打包失败';
        return false;
    }

    $info['method'] = 'ziparchive';
    $info['encrypted'] = false;
    $info['files'] = $count;
    $info['size'] = (int)filesize($abs);
    $info['elapsed'] = round(microtime(true) - $t0, 2);
    if ($pwd !== '') {
        $info['warn'] = '当前环境没有 zip 命令，压缩包未能加密（如需加密请安装 Info-ZIP）';
    }

    return true;
}

/* ============================================================
 * 五、FTP 自动上传
 * ============================================================ */

/**
 * 上传备份包到 FTP
 *
 * @param array       $cfg
 * @param string      $localFile 本地备份包绝对路径
 * @param string|null $msg       结果描述
 * @return bool
 */
function qs_backup_ftp_upload(array $cfg, string $localFile, ?string &$msg = null): bool
{
    $msg = '';

    if (!function_exists('ftp_connect')) {
        $msg = 'PHP 未启用 FTP 扩展';
        return false;
    }
    $host = trim((string)$cfg['backup_ftp_host']);
    if ($host === '') {
        $msg = '未配置 FTP 主机';
        return false;
    }
    if (!is_file($localFile)) {
        $msg = '本地备份文件不存在';
        return false;
    }

    $port   = (int)($cfg['backup_ftp_port'] ?: 21);
    $ssl    = ($cfg['backup_ftp_ssl'] ?? 'none') === 'ftps';
    $conn   = $ssl ? @ftp_ssl_connect($host, $port, 15) : @ftp_connect($host, $port, 15);

    if (!$conn) {
        $msg = '连接失败：' . $host . ':' . $port;
        return false;
    }

    $user = trim((string)$cfg['backup_ftp_user']);
    if ($user === '') {
        $user = 'anonymous';
    }

    if (!@ftp_login($conn, $user, (string)$cfg['backup_ftp_pass'])) {
        @ftp_close($conn);
        $msg = 'FTP 登录失败：账号或密码不正确';
        return false;
    }

    @ftp_pasv($conn, ($cfg['backup_ftp_pasv'] ?? 'on') !== 'off');

    $dir = trim(str_replace('\\', '/', (string)$cfg['backup_ftp_dir']), '/');
    if ($dir !== '') {
        if (!@ftp_chdir($conn, $dir)) {
            $cur = '';
            foreach (explode('/', $dir) as $part) {
                if ($part === '') {
                    continue;
                }
                $cur = ($cur === '' ? $part : $cur . '/' . $part);
                @ftp_mkdir($conn, $cur);
            }
            if (!@ftp_chdir($conn, $dir)) {
                @ftp_close($conn);
                $msg = '远程目录不可用：/' . $dir;
                return false;
            }
        }
    }

    $remote = basename($localFile);
    $ok = @ftp_put($conn, $remote, $localFile, FTP_BINARY);
    $err = $ok ? '' : (string)(error_get_last()['message'] ?? '');
    @ftp_close($conn);

    if (!$ok) {
        $msg = '上传失败' . ($err !== '' ? '：' . $err : '');
        return false;
    }

    $msg = '已上传至 /' . ($dir === '' ? '' : $dir . '/') . $remote;
    return true;
}

/* ============================================================
 * 六、保留策略
 * ============================================================ */

/**
 * 按保留份数清理旧备份
 *
 * @param array $cfg
 * @param array $removed 被删除的文件名列表
 * @param string|null $msg
 * @return int 删除数量
 */
function qs_backup_prune(array $cfg, array &$removed = [], ?string &$msg = null): int
{
    $removed = [];
    $msg = '';

    $keep = (int)($cfg['backup_keep'] ?? 7);
    if ($keep < 1) {
        return 0;
    }

    $desDir = qs_backup_des_dir($cfg);
    if (!is_dir($desDir)) {
        return 0;
    }

    $base = preg_replace('/[^A-Za-z0-9._-]/', '', (string)$cfg['tar_name']);
    if ($base === '') {
        $base = 'qscms';
    }

    $files = glob($desDir . $base . '_*.zip') ?: [];
    if (count($files) <= $keep) {
        return 0;
    }

    usort($files, static function ($a, $b) {
        return strcmp(basename($b), basename($a));
    });

    $delete = array_slice($files, $keep);
    $n = 0;
    foreach ($delete as $f) {
        if (@unlink($f)) {
            $removed[] = basename($f);
            $n++;
        }
    }

    if ($n > 0) {
        $msg = '已清理 ' . $n . ' 个超出保留份数的旧备份';
    }
    return $n;
}

/* ============================================================
 * 七、定时调度
 * ============================================================ */

/**
 * 计算下次执行时间（以 $from 为基准）
 */
function qs_backup_next_run(array $cfg, int $from = 0): int
{
    $from = $from > 0 ? $from : time();

    $time = trim((string)$cfg['backup_cron_time']);
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
        $m = [1 => '3', 2 => '00'];
    }
    $hh = str_pad((string)min(23, max(0, (int)$m[1])), 2, '0', STR_PAD_LEFT);
    $mm = str_pad((string)min(59, max(0, (int)$m[2])), 2, '0', STR_PAD_LEFT);

    $freq = (string)$cfg['backup_cron_freq'];

    if ($freq === 'weekly') {
        $w = (int)$cfg['backup_cron_week'];
        if ($w < 0 || $w > 6) {
            $w = 1;
        }
        $base = strtotime(date('Y-m-d', $from) . ' ' . $hh . ':' . $mm . ':00');
        $curW = (int)date('w', $base);
        $delta = ($w - $curW + 7) % 7;
        $ts = strtotime('+' . $delta . ' day', $base);
        if ($ts <= $from) {
            $ts = strtotime('+7 day', $ts);
        }
        return $ts;
    }

    if ($freq === 'monthly') {
        $d = (int)$cfg['backup_cron_day'];
        $d = $d < 1 ? 1 : ($d > 28 ? 28 : $d);
        $dm = str_pad((string)$d, 2, '0', STR_PAD_LEFT);
        $ts = strtotime(date('Y-m', $from) . '-' . $dm . ' ' . $hh . ':' . $mm . ':00');
        if ($ts === false || $ts <= $from) {
            $nextMonth = strtotime('first day of +1 month', $from);
            $ts = strtotime(date('Y-m', $nextMonth) . '-' . $dm . ' ' . $hh . ':' . $mm . ':00');
        }
        return (int)$ts;
    }

    // 默认：每天
    $ts = strtotime(date('Y-m-d', $from) . ' ' . $hh . ':' . $mm . ':00');
    if ($ts === false) {
        $ts = $from + 86400;
    }
    if ($ts <= $from) {
        $ts = strtotime('+1 day', $ts);
    }
    return (int)$ts;
}

/**
 * 判断当前是否到了执行时间
 */
function qs_backup_is_due(array $cfg): bool
{
    if (($cfg['backup_cron_on'] ?? 'off') !== 'on') {
        return false;
    }
    $state = qs_backup_state_get();
    $last = (int)$state['last_run'];

    if ($last <= 0) {
        return true; // 从未执行过，立即执行一次
    }
    return time() >= qs_backup_next_run($cfg, $last);
}

/**
 * 找到可用的 PHP CLI 可执行文件
 */
function qs_backup_php_bin(): string
{
    $cands = [];

    if (defined('PHP_BINDIR') && PHP_BINDIR) {
        $cands[] = rtrim(PHP_BINDIR, '/\\') . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php');
    }
    if (defined('PHP_BINARY') && PHP_BINARY) {
        $b = strtolower(basename(PHP_BINARY));
        if (strpos($b, 'httpd') === false && strpos($b, 'apache') === false && strpos($b, 'php') !== false) {
            $cands[] = PHP_BINARY;
        }
    }

    foreach ($cands as $c) {
        if (is_file($c)) {
            return $c;
        }
    }

    return PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php';
}

/**
 * 通过 HTTP 自调用触发一次备份（不等待响应，适合 Web 环境）
 */
function qs_backup_spawn_http(string $hostport, string $scriptName, bool $https = false): bool
{
    $host = $hostport;
    $port = $https ? 443 : 80;

    if (strpos($hostport, ':') !== false) {
        list($host, $port) = explode(':', $hostport, 2);
        $port = (int)$port;
    }
    if ($host === '' || $port <= 0) {
        return false;
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($dir === '' || $dir === '.') {
        $dir = '';
    }
    $uri = $dir . '/backup_cron.php?action=run&token=' . urlencode(qs_backup_token_get());

    $fp = @fsockopen(($port === 443 ? 'ssl://' : 'tcp://') . $host, $port, $errno, $errstr, 2);
    if (!$fp) {
        qs_backup_runlog('HTTP 自调用连接失败：' . $errstr . ' (' . $host . ':' . $port . ')');
        return false;
    }

    @fwrite($fp, "GET {$uri} HTTP/1.1\r\nHost: {$hostport}\r\nUser-Agent: QScms-Backup-Cron\r\nConnection: Close\r\n\r\n");
    @fclose($fp);

    qs_backup_runlog('已通过 HTTP 自调用触发备份（' . $host . ':' . $port . '）');
    return true;
}

/**
 * 后台拉起定时备份（不阻塞当前请求）
 */
function qs_backup_spawn(array $args = []): bool
{
    // Web 环境：服务端 HTTP 自调用，独立请求处理，不受父进程退出影响
    if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        return qs_backup_spawn_http(
            (string)$_SERVER['HTTP_HOST'],
            (string)($_SERVER['SCRIPT_NAME'] ?? '/' . basename(__DIR__) . '/set.php'),
            $https
        );
    }

    // CLI 兜底：直接拉起子进程（系统计划任务一般直接调用 backup_cron.php）
    $php = qs_backup_php_bin();
    $script = __DIR__ . DIRECTORY_SEPARATOR . 'backup_cron.php';

    $argStr = ' --auto';
    foreach ($args as $a) {
        $argStr .= ' ' . escapeshellarg((string)$a);
    }

    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = 'start /B "" "' . $php . '" "' . $script . '"' . $argStr;
        @pclose(@popen($cmd, 'r'));
        return true;
    }

    $cmd = 'nohup ' . escapeshellarg($php) . ' ' . escapeshellarg($script) . $argStr . ' > /dev/null 2>&1 &';
    if (function_exists('exec')) {
        @exec($cmd);
        return true;
    }
    return false;
}

/**
 * 懒触发：后台任意页面访问时调用，到点则拉起一次备份
 */
function qs_backup_cron_tick(): void
{
    $cfg = qs_backup_cfg();
    if (($cfg['backup_cron_on'] ?? 'off') !== 'on') {
        return;
    }

    $state = qs_backup_state_get();

    // 有任务在跑（2 小时内）则不重复触发
    if (!empty($state['running_since']) && (time() - (int)$state['running_since']) < 7200) {
        return;
    }

    if (!qs_backup_is_due($cfg)) {
        return;
    }

    // 抢占标记，防止多个请求同时拉起
    qs_backup_state_set(['running_since' => time()]);
    qs_backup_spawn();
}

/* ============================================================
 * 八、主流程
 * ============================================================ */

/**
 * 执行一次完整备份：打包 → FTP 上传 → 清理 → 记录
 *
 * @param string $trigger manual|cron|cli
 * @param array  $opts    ['skip_ftp'=>bool, 'skip_prune'=>bool]
 * @return array
 */
function qs_backup_run(string $trigger = 'manual', array $opts = []): array
{
    @set_time_limit(0);
    @ignore_user_abort(true);

    $cfg = qs_backup_cfg();

    $result = [
        'ok'       => false,
        'trigger'  => $trigger,
        'time'     => date('Y-m-d H:i:s'),
        'name'     => '',
        'file'     => '',
        'size'     => 0,
        'elapsed'  => 0,
        'method'   => '',
        'encrypted' => false,
        'warn'     => '',
        'msg'      => '',
        'ftp'      => null,
        'ftp_msg'  => '',
        'removed'  => 0,
        'removed_files' => [],
    ];

    // 进程锁（放在 admin/tmp 下，避免被锁住的文件同时被打进备份包）
    $lockDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
    if (!is_dir($lockDir)) {
        @mkdir($lockDir, 0777, true);
    }
    $lockFile = $lockDir . DIRECTORY_SEPARATOR . 'backup.lock';
    $fp = @fopen($lockFile, 'c');
    if (!$fp) {
        $result['msg'] = '无法创建锁文件：' . $lockFile;
        return $result;
    }
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        $result['msg'] = '已有备份任务正在执行，本次跳过';
        return $result;
    }

    qs_backup_state_set(['running_since' => time()]);
    qs_backup_runlog('开始备份（来源：' . $trigger . '）');

    $info = [];
    $ok = qs_backup_pack($cfg, $info);

    $result['name']    = $info['name'] ?? '';
    $result['file']    = $info['file'] ?? '';
    $result['size']    = (int)($info['size'] ?? 0);
    $result['elapsed'] = $info['elapsed'] ?? 0;
    $result['method']  = $info['method'] ?? '';
    $result['encrypted'] = !empty($info['encrypted']);
    $result['warn']    = $info['warn'] ?? '';

    if (!$ok) {
        $msg = $info['error'] ?? '打包失败';
        $result['msg'] = $msg;
        qs_backup_runlog('备份失败：' . $msg);

        qs_backup_state_set([
            'last_run'      => time(),
            'last_result'   => 'fail',
            'last_msg'      => $msg,
            'last_trigger'  => $trigger,
            'running_since' => 0,
        ]);

        flock($fp, LOCK_UN);
        fclose($fp);
        return $result;
    }

    // FTP 上传
    if (($cfg['backup_ftp_on'] ?? 'off') === 'on' && empty($opts['skip_ftp'])) {
        $ftpMsg = '';
        $ftpOk = qs_backup_ftp_upload($cfg, $result['file'], $ftpMsg);
        $result['ftp'] = $ftpOk;
        $result['ftp_msg'] = $ftpMsg;
        qs_backup_runlog(($ftpOk ? 'FTP 上传成功：' : 'FTP 上传失败：') . $ftpMsg);
    }

    // 保留策略
    if (empty($opts['skip_prune'])) {
        $removedList = [];
        $pruneMsg = '';
        $result['removed'] = qs_backup_prune($cfg, $removedList, $pruneMsg);
        $result['removed_files'] = $removedList;
        if ($result['removed'] > 0) {
            qs_backup_runlog($pruneMsg);
        }
    }

    // 追加 pack_log.txt（兼容原备份列表页）
    $desDir = qs_backup_des_dir($cfg);
    $desRel = '/' . trim((string)$cfg['des_directory'], '/') . '/';
    if ($desRel === '//') {
        $desRel = '/';
    }
    $logEntry = $result['name'] . ' - ' . date('Y-m-d H:i:s') . ' - ' . rtrim($desDir, '/') . ' - ' . $desRel . $result['name'] . PHP_EOL;
    @file_put_contents($desDir . 'pack_log.txt', $logEntry, FILE_APPEND);

    $sizeMb = round($result['size'] / 1048576, 2);
    $result['ok'] = true;
    $result['msg'] = '备份完成：' . $result['name'] . '（' . $sizeMb . ' MB，耗时 ' . $result['elapsed'] . ' 秒）'
        . ($result['warn'] !== '' ? '；' . $result['warn'] : '')
        . ($result['ftp_msg'] !== '' ? '；FTP：' . $result['ftp_msg'] : '');

    qs_backup_runlog($result['msg']);

    $state = qs_backup_state_get();
    qs_backup_state_set([
        'last_run'      => time(),
        'last_name'     => $result['name'],
        'last_file'     => $result['file'],
        'last_size'     => $result['size'],
        'last_result'   => 'ok',
        'last_msg'      => $result['msg'],
        'last_trigger'  => $trigger,
        'last_method'   => $result['method'],
        'ftp_result'    => $result['ftp'] === null ? '' : ($result['ftp'] ? 'ok' : 'fail'),
        'run_count'     => (int)$state['run_count'] + 1,
        'running_since' => 0,
    ]);

    flock($fp, LOCK_UN);
    fclose($fp);

    return $result;
}
