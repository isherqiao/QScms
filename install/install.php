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
 * @File         install.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 06/11/26 02:18:13
 * @Description  Installation wizard: environment checks, cloud or manual configuration restore, and completion that writes install.lock.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * QScms 安装向导
 * 步骤：1 账号验证 → 2 环境检测（并上报 verify）→ 3 初始化数据（云端恢复 / 手动填写）→ 4 完成
 *
 * 说明：
 *  - 本文件不加载 control/core.php，避免安装阶段触发整站初始化
 *  - 配置写入 control/data（不再写 install/data，也不整包覆盖）
 *  - 检测结果通过 verify 的 api/install_report.php 上报，后台「安装检测报告」里按账号查看
 */

date_default_timezone_set('Asia/Shanghai');
ini_set('display_errors', 'On');
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* ------------------------------------------------------------------ 基础配置 */

$ROOT       = dirname(__DIR__);                       // QScms 根目录
$DATA_DIR   = defined('QS_DATA_DIR') ? QS_DATA_DIR : $ROOT . '/control/data';
$PRESET_DIR = __DIR__ . '/preset';

// 完成页上的“重新开始安装”：允许刚装完那一次自行删锁重来（其它时候有锁一律跳站）
if (isset($_POST['action']) && $_POST['action'] === 'finish' && !empty($_SESSION['qs_reinstall'])) {
    unset($_SESSION['qs_reinstall']);
    if (is_file(__DIR__ . '/install.lock')) { @unlink(__DIR__ . '/install.lock'); }
    $_SESSION['qs_install'] = array();
    $_SESSION['qs_checks'] = array();
    header('Location: install.php');
    exit;
}

// 已安装就把安装器关掉：锁是“是否已安装”的唯一判据（conf.php 可能写坏，不能拿来当凭证）
if (is_file(__DIR__ . '/install.lock')) {
    header('Location: ../index.php');
    exit;
}

// 连接 verify 的候选地址：优先自定义，其次官方，最后本机测试
$VERIFY_CANDIDATES = array(
    'https://verify.qsis.cn:88',
    'https://verify.qsis.cn',
    'http://127.0.0.1:91',
);

/* ------------------------------------------------------------------ 工具函数 */

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// 后台目录名：安装器不加载 core.php，这里自行识别（先读记录，再扫描根目录里含 head.php + set.php 的目录）
function install_admin_dir() {
    static $dir = null;
    if ($dir !== null) { return $dir; }
    $root = dirname(__DIR__);
    $rec  = $root . '/control/data/admin_dir.php';
    if (is_file($rec)) {
        $raw = (string)@file_get_contents($rec);
        if (preg_match("/return\\s*'([A-Za-z0-9_-]{1,64})'/", $raw, $m) && is_file($root . '/' . $m[1] . '/head.php')) {
            return $dir = $m[1];
        }
    }
    foreach ((array)@scandir($root) as $d) {
        if ($d === '.' || $d === '..') { continue; }
        if (is_file($root . '/' . $d . '/head.php') && is_file($root . '/' . $d . '/set.php')) { return $dir = $d; }
    }
    return $dir = 'admin';
}

// 安装完成锁：index.php 见到 install/install.lock 就不再跳安装向导
function install_lock_file() {
    return __DIR__ . '/install.lock';
}

function write_install_lock() {
    $file = install_lock_file();
    if (is_file($file)) { return; }
    $ver = function_exists('qs_version') ? qs_version() : '';
    @file_put_contents($file, "QScms install lock\r\nversion: " . $ver . "\r\ninstalled_at: " . date('Y-m-d H:i:s') . "\r\n", LOCK_EX);
}

function http_post_form($url, array $data, $timeout = 15) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array('code' => $code, 'body' => $body, 'error' => $err);
}

function http_get_quick($url, $timeout = 4) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code === 200 ? (string)$body : false;
}

function find_verify_base() {
    global $VERIFY_CANDIDATES;
    foreach ($VERIFY_CANDIDATES as $base) {
        $ip = http_get_quick(rtrim($base, '/') . '/ip.php');
        if ($ip !== false && preg_match('/^[0-9a-fA-F:.]+$/', trim($ip)) !== false) {
            return array('base' => rtrim($base, '/'), 'ip' => trim($ip));
        }
    }
    return false;
}

// 命令是否存在（不拼接用户输入，参数固定）
function cmd_exists($cmd) {
    $os = DIRECTORY_SEPARATOR === '\\' ? 'win' : 'nix';
    if ($os === 'win') {
        $out = @shell_exec('where ' . $cmd . ' 2>NUL');
    } else {
        $out = @shell_exec('command -v ' . $cmd . ' 2>/dev/null');
    }
    return trim((string)$out) !== '';
}

function cmd_first_line($cmd) {
    $out = @shell_exec($cmd . ' 2>&1');
    if (!$out) {
        return '';
    }
    $first = strtok(trim((string)$out), "\n");
    return trim((string)$first);
}

/* ------------------------------------------------------------------ 环境检测 */

function php_short_ver() {
    return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;      // 例：8.2
}

// 生成 apt 包名提示：php8.5-mbstring（同时给通用名 php-mbstring）
function apt_hint($suffix, $generic = true) {
    $a = 'php' . php_short_ver() . '-' . $suffix;
    return '缺失时安装：sudo apt-get install ' . $a . ($generic ? '（或 php-' . $suffix . '）' : '');
}

// 取扩展自身版本号，取不到就说“已安装”
function ext_ver_text($ext) {
    $v = @phpversion($ext);
    return ($v !== false && $v !== '') ? (string)$v : '已安装';
}

// 跑一条命令并按正则从输出里抠版本号
function bin_version($bin, $arg, $pattern) {
    $out = @shell_exec($bin . ' ' . $arg . ' 2>&1');
    if ($out && preg_match($pattern, (string)$out, $m)) {
        return trim($m[1]);
    }
    return '';
}

// Windows 下常见压缩工具的安装路径（zip：WinRAR 的 Rar/WinRAR、WinZip 的 WZZIP；unzip：Rar、WZUNZIP）
function win_zip_tools($which) {
    $sd   = DIRECTORY_SEPARATOR;
    $pf   = getenv('ProgramFiles') ?: 'C:' . $sd . 'Program Files';
    $pfx  = getenv('ProgramFiles(x86)') ?: 'C:' . $sd . 'Program Files (x86)';
    $base = array($pf, $pfx);
    if ($which === 'zip') {
        $list = array('WinRAR' . $sd . 'Rar.exe', 'WinRAR' . $sd . 'WinRAR.exe', 'WinZip' . $sd . 'WZZIP.EXE');
    } else {
        $list = array('WinRAR' . $sd . 'Rar.exe', 'WinZip' . $sd . 'WZUNZIP.EXE', 'WinRAR' . $sd . 'WinRAR.exe');
    }
    foreach ($base as $b) {
        foreach ($list as $rel) {
            $p = $b . $sd . $rel;
            if (@is_file($p)) {
                return $p;
            }
        }
    }
    return '';
}

// 从工具路径尽可能取版本号（仅对控制台工具 Rar.exe 执行取号，GUI 工具只返回名称）
function win_zip_version($path) {
    if (stripos($path, 'rar.exe') !== false) {
        $v = bin_version('"' . $path . '"', '', '/RAR\s+([0-9.]+)/i');
        return $v !== '' ? $v : '';
    }
    return '';
}

/* ------------------------------------------------------------------ 运行时目录权限 */

/**
 * 运行时目录清单。core=true：不写就用不了（装不上或发不了文章）
 */
function install_runtime_dirs() {
    $admin = install_admin_dir();
    return array(
        array('p' => 'control/data',     'core' => true,  'desc' => '配置与运行时数据'),
        array('p' => 'data/posts/data',  'core' => true,  'desc' => '文章正文'),
        array('p' => 'data/posts/index', 'core' => true,  'desc' => '文章索引'),
        array('p' => 'data/pages/data',  'core' => true,  'desc' => '页面正文'),
        array('p' => 'data/pages/index', 'core' => true,  'desc' => '页面索引'),
        array('p' => 'upload',           'core' => true,  'desc' => '上传文件'),
        array('p' => 'install',          'core' => true,  'desc' => '安装锁 install.lock'),
        array('p' => 'data/ads',         'core' => false, 'desc' => '广告素材'),
        array('p' => 'static_site',      'core' => false, 'desc' => '静态站输出'),
        array('p' => 'backups',          'core' => false, 'desc' => '站点备份'),
        array('p' => 'files/theme',      'core' => false, 'desc' => '主题（后台代码编辑器）'),
        array('p' => 'files/language',   'core' => false, 'desc' => '语言包编辑'),
        array('p' => 'files/plugins',    'core' => false, 'desc' => '插件安装'),
        array('p' => 'temp_md_import',   'core' => false, 'desc' => 'Markdown 导入'),
        array('p' => $admin . '/tmp',    'core' => false, 'desc' => '后台临时文件'),
        array('p' => $admin . '/logs',   'core' => false, 'desc' => '后台日志'),
        array('p' => $admin . '/data',   'core' => false, 'desc' => '后台数据'),
    );
}

/**
 * 缺失的运行时目录在检测阶段就建出来，不等写配置那一步
 * @param array $fail 引用返回：建不出来的目录
 * @return array 本次新建的目录
 */
function install_ensure_dirs(&$fail) {
    $root = dirname(__DIR__);
    $made = array();
    $fail = array();
    foreach (install_runtime_dirs() as $d) {
        $abs = $root . '/' . $d['p'];
        if (is_dir($abs)) { continue; }
        if (@mkdir($abs, 0755, true) || is_dir($abs)) { $made[] = $d['p']; }
        else { $fail[] = $d['p']; }
    }
    return $made;
}

/**
 * 实写探测：能建出并删掉探测文件才算真的可写（比 is_writable 可靠）
 */
function install_dir_probe($abs) {
    if (!is_dir($abs)) { return false; }
    $probe = rtrim($abs, '/\\') . '/.qs_install_probe.tmp';
    $ok = (@file_put_contents($probe, 'ok') !== false);
    if ($ok) { @unlink($probe); }
    return $ok;
}

/**
 * 目录权限检测项：必需一组 + 可选一组 + 隐藏明细（明细随安装报告上报 verify）
 */
function install_dir_checks() {
    $root = dirname(__DIR__);
    $made = install_ensure_dirs($mkFail);

    $coreTotal = 0; $coreBad = array();
    $extraTotal = 0; $extraBad = array();
    $detail = array();

    foreach (install_runtime_dirs() as $d) {
        $abs = $root . '/' . $d['p'];
        $exists = is_dir($abs);
        $w = $exists && install_dir_probe($abs);
        if ($d['core']) { $coreTotal++; if (!$w) { $coreBad[] = $d['p']; } }
        else { $extraTotal++; if (!$w) { $extraBad[] = $d['p']; } }
        $detail[] = array(
            'path' => $d['p'], 'desc' => $d['desc'], 'core' => (bool)$d['core'],
            'exists' => $exists, 'writable' => $w,
        );
    }

    $fix = 'Linux：sudo bash ' . $root . '/scripts/fix-permissions.sh；Windows：scripts\fix-permissions.bat';

    $items = array();
    $items[] = array(
        'key'    => 'dirs_core',
        'label'  => '运行时目录可写（必需 ' . $coreTotal . ' 个）',
        'status' => $coreBad ? 'fail' : 'ok',
        'value'  => ($coreTotal - count($coreBad)) . ' / ' . $coreTotal . ' 可写'
                    . ($made ? '（本次新建 ' . count($made) . ' 个）' : ''),
        'note'   => $coreBad
            ? '不可写：' . implode('、', $coreBad) . '　修复：' . $fix
            : 'control/data、data/posts|pages（data+index）、upload、install 全部可写',
    );
    $items[] = array(
        'key'    => 'dirs_extra',
        'label'  => '可选功能目录（' . $extraTotal . ' 个）',
        'status' => $extraBad ? 'fail' : 'ok',
        'value'  => ($extraTotal - count($extraBad)) . ' / ' . $extraTotal . ' 可写',
        'note'   => $extraBad
            ? '不可写：' . implode('、', $extraBad) . '（站点照样能跑，对应功能会报错；修复：' . $fix . '）'
            : '静态化、备份、主题、语言、插件、后台临时目录均可写',
    );
    $items[] = array(
        'key'    => 'dirs_detail',
        'label'  => '目录权限明细',
        'status' => 'info',
        'value'  => json_encode($detail, JSON_UNESCAPED_UNICODE),
        'note'   => '',
        'hidden' => true,
    );

    return $items;
}

function env_checks() {
    $items = array();

    // 1. PHP 版本：7.0 及以上可运行，8.x 为推荐版本
    $canRun = version_compare(PHP_VERSION, '7.0.0', '>=');
    $recommend = version_compare(PHP_VERSION, '8.0.0', '>=');
    $items[] = array(
        'key'    => 'php_version',
        'label'  => 'PHP 版本（≥ 7.0 可运行）',
        'status' => $canRun ? 'ok' : 'fail',
        'value'  => PHP_VERSION . '（' . PHP_SAPI . '）',
        'note'   => $canRun
            ? ($recommend ? '满足推荐版本（8.x）' : '老版本可用，程序可运行；建议后续择机升级到 8.x')
            : '低于 7.0，程序无法运行，请升级到 7.0 及以上（推荐 8.x）',
    );

    // 2. 必需扩展（zip 以系统命令形式单列，不在此处判定）
    $exts = array(
        'mbstring' => '多字节字符串（中文处理）',
        'gd'       => '图片处理（验证码、缩略图）',
        'curl'     => '远程请求（联网校验、下载）',
        'bcmath'   => '高精度计算',
        'exif'     => '图片元信息',
    );
    foreach ($exts as $ext => $desc) {
        $loaded = extension_loaded($ext);
        $items[] = array(
            'key'    => 'ext_' . $ext,
            'label'  => 'PHP 扩展 ' . $ext,
            'status' => $loaded ? 'ok' : 'fail',
            'value'  => $loaded ? ext_ver_text($ext) : '未安装',
            'note'   => $loaded ? $desc : apt_hint($ext),
        );
    }

    // 3. zip / unzip（Linux 用系统命令；Windows 优先手动指定路径，其次自动探测 WinRAR/WinZip）
    $cmds = array(
        'zip'   => array('label' => 'zip 打包命令',   'pattern' => '/Zip\s+([0-9][0-9.]*)/i'),
        'unzip' => array('label' => 'unzip 解包命令', 'pattern' => '/UnZip\s+([0-9][0-9.]*)/i'),
    );
    foreach ($cmds as $cmd => $cfg) {
        if (DIRECTORY_SEPARATOR === '\\') {
            $override = isset($_SESSION['qs_install'][$cmd . '_bin']) ? trim((string)$_SESSION['qs_install'][$cmd . '_bin']) : '';
            $tool = '';
            if ($override !== '' && (@is_file($override) || cmd_exists($override))) {
                $tool = $override;
            }
            if ($tool === '') {
                $tool = win_zip_tools($cmd);
            }
            if ($tool !== '') {
                $ver = win_zip_version($tool);
                $items[] = array(
                    'key'    => 'cmd_' . $cmd,
                    'label'  => $cfg['label'],
                    'status' => 'ok',
                    'value'  => '已找到 ' . basename($tool) . ($ver !== '' ? ' ' . $ver : ''),
                    'note'   => $tool,
                );
                continue;
            }
        }
        $exists = cmd_exists($cmd);
        $ver = $exists ? bin_version($cmd, '-v', $cfg['pattern']) : '';
        $items[] = array(
            'key'    => 'cmd_' . $cmd,
            'label'  => $cfg['label'],
            'status' => $exists ? 'ok' : 'fail',
            'value'  => $exists ? ($ver !== '' ? $ver : '已安装') : '未安装',
            'note'   => $exists ? '' : '缺失时安装：sudo apt-get install ' . $cmd,
        );
    }

    // 4. php-fpm（Linux 部署项，任意版本均可）
    $isWin = DIRECTORY_SEPARATOR === '\\';
    if ($isWin) {
        $items[] = array(
            'key'    => 'php_fpm',
            'label'  => 'php-fpm（PHP-FPM 服务）',
            'status' => 'na',
            'value'  => 'Windows 环境不适用',
            'note'   => '当前是 Windows（Apache 模块 / php-cgi 形态），此项不计入判定',
        );
    } else {
        $fpmVer = '';
        $fpmBin = '';
        $cands = array('php-fpm');
        foreach (array('8.5', '8.4', '8.3', '8.2', '8.1', '8.0', '7.4', '7.3', '7.2', '7.1', '7.0') as $v) {
            $cands[] = 'php-fpm' . $v;
        }
        foreach ($cands as $bin) {
            if (cmd_exists($bin)) {
                $v = bin_version($bin, '-v', '/PHP\s+([0-9.]+)/i');
                $fpmVer = $v !== '' ? $v : '已安装';
                $fpmBin = $bin;
                break;
            }
        }
        if ($fpmVer === '') {
            foreach (array('/usr/sbin/php-fpm', '/usr/local/sbin/php-fpm') as $p) {
                if (@is_executable($p)) { $fpmVer = '已安装'; $fpmBin = $p; break; }
            }
        }
        $items[] = array(
            'key'    => 'php_fpm',
            'label'  => 'php-fpm（PHP-FPM 服务）',
            'status' => $fpmVer !== '' ? 'ok' : 'fail',
            'value'  => $fpmVer !== '' ? ($fpmVer . '（' . $fpmBin . '）') : '未检测到',
            'note'   => $fpmVer !== '' ? '' : '缺失时安装：sudo apt-get install php' . php_short_ver() . '-fpm（或 php-fpm）',
        );
    }

    // 5. php-zend：只检测、不判定，界面隐藏（仍随报告上报）
    $zendEngine = function_exists('zend_version') ? zend_version() : '';
    $opcacheOn = extension_loaded('Zend OPcache') || function_exists('opcache_get_status');
    $opVer = @phpversion('Zend OPcache');
    $loaderOn = extension_loaded('Zend Guard Loader') || extension_loaded('Zend Loader')
                || (string)ini_get('zend_loader.enabled') === '1';
    $zendVal = 'Zend Engine ' . ($zendEngine !== '' ? $zendEngine : '未知');
    $zendVal .= '；OPcache ' . ($opcacheOn ? '已启用' . ($opVer !== false && $opVer !== '' ? ' ' . $opVer : '') : '未启用');
    $zendVal .= '；Loader ' . ($loaderOn ? '已加载' : '未加载');
    $items[] = array(
        'key'    => 'php_zend',
        'label'  => 'php-zend（Zend 组件）',
        'status' => 'info',
        'value'  => $zendVal,
        'note'   => '仅检测显示，不参与安装判定',
        'hidden' => true,
    );

    // 7. 运行时目录权限：缺失目录先建出来，再逐个实写探测（必需项不过不许继续）
    foreach (install_dir_checks() as $dirItem) {
        $items[] = $dirItem;
    }

    // 8. exec / shell_exec
    $execOk = function_exists('exec') || function_exists('shell_exec');
    $items[] = array(
        'key'    => 'exec',
        'label'  => 'exec / shell_exec 可用',
        'status' => $execOk ? 'ok' : 'fail',
        'value'  => $execOk ? '可用' : '已被 disable_functions 禁用',
        'note'   => $execOk ? '' : 'zip/unzip 等命令检测需要它',
    );

    return $items;
}

function check_summary(array $items) {
    $ok = 0; $fail = 0;
    foreach ($items as $it) {
        if ($it['status'] === 'ok') { $ok++; }
        if ($it['status'] === 'fail') { $fail++; }
    }
    return array($ok, $fail);
}

/* ------------------------------------------------------------------ 配置写入 */

function preset_files() {
    global $PRESET_DIR;
    $files = array();
    if (!is_dir($PRESET_DIR)) {
        return $files;
    }
    foreach (glob($PRESET_DIR . '/*.php') as $p) {
        $files[basename($p)] = file_get_contents($p);
    }
    return $files;
}

function load_conf_array($path) {
    $qs_config = array();
    if (is_file($path)) {
        include $path;    // 只取 $qs_config，函数作用域内不会污染全局
    }
    return is_array($qs_config) ? $qs_config : array();
}

function write_target($name, $content) {
    global $DATA_DIR;
    if (!preg_match('/^[A-Za-z0-9._-]+\.php$/', $name)) {
        return '文件名不合法：' . $name;
    }
    $target = $DATA_DIR . '/' . $name;
    if (@file_put_contents($target, $content) === false) {
        return '写入失败：' . $target;
    }
    return '';
}

function qs_version() {
    global $ROOT;
    $core = $ROOT . '/control/core.php';
    if (is_file($core) && preg_match("/\\\$version\s*=\s*'([^']+)'/", file_get_contents($core), $m)) {
        return $m[1];
    }
    return '';
}

/**
 * 把预设文件铺到 control/data，并按向导填写的内容改写关键项
 */
function apply_manual_config(array $form) {
    global $DATA_DIR;
    $errors = array();

    if (!is_dir($DATA_DIR) && !@mkdir($DATA_DIR, 0755, true)) {
        return array('control/data 目录不存在且无法创建');
    }
    if (!is_writable($DATA_DIR)) {
        return array('control/data 目录不可写');
    }

    // 1. 预设铺底
    foreach (preset_files() as $name => $content) {
        $err = write_target($name, $content);
        if ($err !== '') { $errors[] = $err; }
    }

    // 2. 改写 conf.php
    $qs_config = load_conf_array($DATA_DIR . '/conf.php');
    if (count($qs_config) === 0) {
        return array('预设 conf.php 读取失败');
    }

    $qs_config['site_name']        = $form['site_name'];
    $qs_config['site_desc']        = $form['site_desc'];
    $qs_config['site_link']        = $form['site_link'];
    $qs_config['footer_code']      = $form['footer_code'];
    $qs_config['liuyan_data_name'] = $form['liuyan_data_name'];
    $qs_config['sitemode']         = $form['sitemode'];
    $qs_config['language_file']    = $form['language_file'];
    $qs_config['language_name']    = $form['language_name'];
    $qs_config['verify_email']     = $form['verify_email'];
    $qs_config['user_nick']        = $form['user_nick'];
    $qs_config['sitelogo']         = '';

    $sp_config = array(
        'user_name'         => $form['admin_user'],
        'user_pass'         => password_hash($form['admin_pass'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT),
        'admin_displayname' => '管理员',
    );

    $code = "<?php\n\$qs_config = " . var_export($qs_config, true) . ";\n"
          . "\$sp_config = " . var_export($sp_config, true) . ";\n?>";
    $err = write_target('conf.php', $code);
    if ($err !== '') { $errors[] = $err; }

    // 3. 授权文件：版本号跟 core.php 对齐
    $ver = qs_version();
    $auth = "<?php \n//status: authorized\n//version_number: " . $ver . "\n"
          . "//authorization_name: QScms 自主部署\n//to_time: 2099-12-31\n//to_time: \n?>";
    $err = write_target('authorization.php', $auth);
    if ($err !== '') { $errors[] = $err; }

    // 4. 留言数据文件
    $liuyan = $form['liuyan_data_name'];
    if (preg_match('/^[A-Za-z0-9._-]+\.php$/', $liuyan)) {
        $err = write_target($liuyan, '');
        if ($err !== '') { $errors[] = $err; }
    }

    return $errors;
}

/**
 * 云端恢复：把 verify 下发的文件写进 control/data，缺的用预设补
 */
function apply_cloud_config(array $files, $adminUser, $adminPass) {
    global $DATA_DIR;
    $errors = array();

    if (!is_dir($DATA_DIR) && !@mkdir($DATA_DIR, 0755, true)) {
        return array('control/data 目录不存在且无法创建');
    }
    if (!is_writable($DATA_DIR)) {
        return array('control/data 目录不可写');
    }

    $preset = preset_files();

    // 预设铺底，云端内容覆盖
    foreach ($preset as $name => $content) {
        $err = write_target($name, $content);
        if ($err !== '') { $errors[] = $err; }
    }
    $written = array();
    foreach ($files as $name => $content) {
        if (!is_string($content) || $content === '') { continue; }
        if (!preg_match('/^[A-Za-z0-9._-]+\.php$/', $name)) { continue; }
        $err = write_target($name, $content);
        if ($err !== '') { $errors[] = $err; } else { $written[] = $name; }
    }

    // 云端 conf.php 里可能没有后台账号（旧版本只存站点配置），补上
    $confPath = $DATA_DIR . '/conf.php';
    $qs_config = load_conf_array($confPath);
    if (count($qs_config) > 0) {
        $content = file_get_contents($confPath);
        if (strpos($content, 'user_name') === false) {
            $sp_config = array(
                'user_name'         => $adminUser,
                'user_pass'         => password_hash($adminPass, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT),
                'admin_displayname' => '管理员',
            );
            $code = "<?php\n\$qs_config = " . var_export($qs_config, true) . ";\n"
                  . "\$sp_config = " . var_export($sp_config, true) . ";\n?>";
            $err = write_target('conf.php', $code);
            if ($err !== '') { $errors[] = $err; } else { $written[] = 'conf.php（补写后台账号）'; }
        }
    }

    return array($errors, $written);
}

/* ------------------------------------------------------------------ 路由 */

$state = isset($_SESSION['qs_install']) ? $_SESSION['qs_install'] : array();
$step  = isset($_GET['step']) ? (int)$_GET['step'] : (isset($state['step']) ? (int)$state['step'] : 1);
if ($step < 1 || $step > 4) { $step = 1; }

// 会话与磁盘对账：会话说“已完成”，但磁盘上没有 conf.php，说明是历史残留（比如同一主机旧安装留下的会话）
if ((int)$step >= 4 && !is_file($DATA_DIR . '/conf.php')) {
    $_SESSION['qs_install'] = array();
    $_SESSION['qs_checks'] = array();
    $state = array();
    $step  = 1;
}

$notice = '';
$errors = array();

$action = isset($_POST['action']) ? (string)$_POST['action'] : '';

/* ---- 第一步：账号验证 ---- */
if ($action === 'login') {
    $email    = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    if ($email === '' || $password === '') {
        $errors[] = '请填写注册邮箱和密码';
    } else {
        // 允许手填 verify 地址（自建 verify 服务或本地联调时用）
        $baseOverride = isset($_POST['verify_base']) ? trim((string)$_POST['verify_base']) : '';
        if ($baseOverride !== '') {
            $GLOBALS['VERIFY_CANDIDATES'] = array(rtrim($baseOverride, '/'));
        }
        $found = find_verify_base();
        if ($found === false) {
            $errors[] = '连不上 verify 服务（试过 ' . implode('、', $VERIFY_CANDIDATES) . '），请检查网络或服务器的 HTTPS 访问';
        } else {
            $resp = http_post_form($found['base'] . '/api/install_auth.php', array('email' => $email, 'password' => $password));
            $json = json_decode((string)$resp['body'], true);
            if (!is_array($json)) {
                $errors[] = 'verify 返回异常（HTTP ' . $resp['code'] . '）：' . h(mb_substr((string)$resp['body'], 0, 200));
            } elseif (empty($json['ok'])) {
                $errors[] = '账号验证未通过：' . (isset($json['error']) ? $json['error'] : '未知原因');
            } else {
                $state = array(
                    'step'       => 2,
                    'base'       => $found['base'],
                    'ip'         => $found['ip'],
                    'email'      => $json['email'],
                    'sn'         => $json['sn'],
                    'password'   => $password,
                    'sites'      => isset($json['sites']) ? $json['sites'] : array(),
                    'has_backup' => !empty($json['has_backup']),
                );
                $_SESSION['qs_install'] = $state;
                header('Location: install.php?step=2');
                exit;
            }
        }
    }
    $step = 1;
}

/* ---- 第二步：环境检测 + 上报 ---- */
$checks = null;
if ($step === 2 && empty($state['sn']) && $action !== 'rescan') {
    $step = 1;
}

if (($action === '' || $action === 'rescan') && $step === 2 && !isset($_GET['done']) && !empty($state['sn'])) {
    if (!empty($state['sn'])) {
        // Windows zip/unzip 工具路径覆盖（手动指定后随 rescan 一并保存）
        if ($action === 'rescan') {
            if (isset($_POST['zip_bin']))   { $state['zip_bin']   = trim((string)$_POST['zip_bin']); }
            if (isset($_POST['unzip_bin'])) { $state['unzip_bin'] = trim((string)$_POST['unzip_bin']); }
            $_SESSION['qs_install'] = $state;
        }
        $checks = env_checks();
        list($okCount, $failCount) = check_summary($checks);

        $resp = http_post_form($state['base'] . '/api/install_report.php', array(
            'email'       => $state['email'],
            'password'    => $state['password'],
            'sn'          => $state['sn'],
            'site_domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
            'cms_url'     => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
                             . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')
                            . preg_replace('#/install/.*$#', '', dirname($_SERVER['SCRIPT_NAME'])),
            'php_version' => PHP_VERSION,
            'php_sapi'    => PHP_SAPI,
            'os_info'     => PHP_OS . ' / ' . (function_exists('php_uname') ? php_uname('m') : ''),
            'results'     => json_encode($checks, JSON_UNESCAPED_UNICODE),
        ));
        $json = json_decode((string)$resp['body'], true);
        if (is_array($json) && !empty($json['ok'])) {
            $notice = '检测结果已上报 verify（记录编号 ' . (int)$json['id'] . '），后台「安装检测报告」里可以按账号查看。';
            $state['step'] = 2;
            $_SESSION['qs_install'] = $state;
            $_SESSION['qs_checks'] = $checks;
            header('Location: install.php?step=2&done=1');
            exit;
        } else {
            $notice = '检测完成，但上报 verify 失败（HTTP ' . $resp['code'] . '）：' . mb_substr((string)$resp['body'], 0, 200) . '，本地结果仍然可用。';
            $_SESSION['qs_install'] = $state;
            $_SESSION['qs_checks'] = $checks;
        }
    }
}

if ($step === 2 && $checks === null) {
    $checks = isset($_SESSION['qs_checks']) ? $_SESSION['qs_checks'] : null;
}

/* ---- 进第三步前再验一次必需目录（防止直接访问 ?step=3 跳过检测） ---- */
if ($step === 3) {
    $root3 = dirname(__DIR__);
    install_ensure_dirs($mkFail3);
    $badCore = array();
    foreach (install_runtime_dirs() as $d) {
        if (!$d['core']) { continue; }
        if (!install_dir_probe($root3 . '/' . $d['p'])) { $badCore[] = $d['p']; }
    }
    if (count($badCore) > 0) {
        $errors[] = '必需目录还不可写：' . implode('、', $badCore)
            . '。按下面第 2 步的说明修好权限后，点「重新检测」再继续。';
        $checks = env_checks();
        $_SESSION['qs_checks'] = $checks;
        $step = 2;
    }
}

/* ---- 第三步：初始化数据 ---- */
if ($action === 'restore' && !empty($state['sn'])) {
    $adminUser = isset($_POST['admin_user']) ? trim((string)$_POST['admin_user']) : 'admin';
    $adminPass = isset($_POST['admin_pass']) ? (string)$_POST['admin_pass'] : '';
    if ($adminUser === '' || $adminPass === '') {
        $errors[] = '请填写后台管理员账号和密码（云端备份恢复后要用它登录）';
        $step = 3;
    } else {
        $resp = http_post_form($state['base'] . '/api/config_fetch.php', array(
            'email' => $state['email'], 'password' => $state['password'], 'sn' => $state['sn'],
        ));
        $json = json_decode((string)$resp['body'], true);
        if (!is_array($json) || empty($json['ok'])) {
            $errors[] = '取回云端配置失败（HTTP ' . $resp['code'] . '）：' . (isset($json['error']) ? $json['error'] : mb_substr((string)$resp['body'], 0, 200));
            $step = 3;
        } else {
            list($errs, $written) = apply_cloud_config($json['files'], $adminUser, $adminPass);
            $errors = $errs;
            $state['step'] = 4;
            $state['mode'] = '云端恢复（站点 ' . (isset($json['site']) ? $json['site'] : '') . '，写入 ' . count($written) . ' 个文件）';
            $state['admin_user'] = $adminUser;
            $_SESSION['qs_install'] = $state;
            if (count($errors) === 0) {
                header('Location: install.php?step=4');
                exit;
            }
            $step = 3;
        }
    }
}

if ($action === 'manual' && !empty($state['sn'])) {
    $form = array(
        'site_name'        => trim((string)($_POST['site_name'] ?? '')),
        'site_desc'        => trim((string)($_POST['site_desc'] ?? '')),
        'site_link'        => rtrim(trim((string)($_POST['site_link'] ?? ''))),
        'footer_code'      => (string)($_POST['footer_code'] ?? ''),
        'liuyan_data_name' => trim((string)($_POST['liuyan_data_name'] ?? '43wangzhanliuyan.php')),
        'sitemode'         => (string)($_POST['sitemode'] ?? 'article'),
        'language_file'    => (string)($_POST['language_file'] ?? 'lang_zh-CN.php'),
        'language_name'    => (string)($_POST['language_name'] ?? '简体中文'),
        'verify_email'     => trim((string)($_POST['verify_email'] ?? $state['email'])),
        'user_nick'        => trim((string)($_POST['user_nick'] ?? 'admin')),
        'admin_user'       => trim((string)($_POST['admin_user'] ?? 'admin')),
        'admin_pass'       => (string)($_POST['admin_pass'] ?? ''),
    );
    $need = array('site_name' => '站点名称', 'site_desc' => '站点描述', 'site_link' => '站点地址',
                  'admin_user' => '后台账号', 'admin_pass' => '后台密码');
    foreach ($need as $k => $label) {
        if ($form[$k] === '') { $errors[] = '请填写' . $label; }
    }
    if (count($errors) === 0) {
        $errors = apply_manual_config($form);
        $state['step'] = 4;
        $state['mode'] = '手动填写（' . $form['site_name'] . '）';
        $state['admin_user'] = $form['admin_user'];
        $_SESSION['qs_install'] = $state;
        if (count($errors) === 0) {
            header('Location: install.php?step=4');
            exit;
        }
        $step = 3;
    } else {
        $step = 3;
    }
}

if ($step === 4 && empty($state['mode'])) {
    $step = 1;
}

$okAdmin = isset($state['admin_user']) ? $state['admin_user'] : 'admin';
$liuyanDefault = isset($state['sites']) ? '43wangzhanliuyan.php' : '43wangzhanliuyan.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>QScms 安装向导</title>
<style>
  * { box-sizing:border-box; }
  body { margin:0; padding:36px 18px 64px; background:#f6f7f9; color:#222;
         font-family:"Microsoft YaHei","PingFang SC",Arial,sans-serif; }
  .wrap { max-width:820px; margin:0 auto; }
  h1 { font-size:22px; margin:0 0 4px; }
  .sub { color:#777; font-size:13px; margin-bottom:22px; }
  .steps { display:flex; gap:8px; margin-bottom:20px; }
  .steps div { flex:1; text-align:center; padding:9px 6px; border-radius:6px; font-size:13px;
               background:#eef0f3; color:#888; }
  .steps div.on { background:#2563eb; color:#fff; }
  .steps div.done { background:#e8f5ec; color:#166534; }
  .card { background:#fff; border-radius:10px; padding:20px 22px; box-shadow:0 1px 3px rgba(0,0,0,.07); }
  .row { margin-bottom:14px; }
  .row label { display:block; font-size:13px; color:#555; margin-bottom:5px; }
  .row input[type=text], .row input[type=password], .row input[type=email], .row select, .row textarea {
     width:100%; padding:9px 11px; border:1px solid #d8dbe0; border-radius:6px; font-size:14px; font-family:inherit; }
  .row .tip { color:#999; font-size:12px; margin-top:4px; }
  .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; }
  .btn { display:inline-block; padding:9px 20px; border:none; border-radius:6px; background:#2563eb;
         color:#fff; font-size:14px; cursor:pointer; }
  .btn:hover { background:#1d4ed8; }
  .btn.ghost { background:#fff; color:#2563eb; border:1px solid #c7d7f7; }
  .btn.gray { background:#eef0f3; color:#555; }
  .msg { padding:10px 14px; border-radius:6px; font-size:13px; margin-bottom:16px; }
  .msg-err { background:#fdecec; color:#b42318; }
  .msg-ok { background:#e8f5ec; color:#166534; }
  table { width:100%; border-collapse:collapse; }
  th, td { padding:9px 10px; font-size:13px; text-align:left; border-bottom:1px solid #f0f1f3; }
  th { color:#444; background:#fafbfc; font-weight:600; }
  td.val { color:#555; word-break:break-all; }
  .pill { display:inline-block; padding:2px 9px; border-radius:10px; font-size:12px; white-space:nowrap; }
  .pill-ok{background:#e8f5ec;color:#166534;} .pill-fail{background:#fdecec;color:#b42318;}
  .pill-na{background:#f1f2f4;color:#666;} .pill-info{background:#eef4ff;color:#1d4ed8;}
  .foot { margin-top:18px; color:#999; font-size:12px; line-height:1.8; }
  .actions { margin-top:18px; display:flex; gap:10px; align-items:center; }
  .tabs { display:flex; gap:10px; margin-bottom:16px; }
  .tabs a { padding:8px 16px; border-radius:6px; text-decoration:none; font-size:13px;
            background:#eef0f3; color:#555; }
  .tabs a.on { background:#2563eb; color:#fff; }
  @media (max-width:640px){ .grid2{grid-template-columns:1fr;} }
</style>
</head>
<body>
<div class="wrap">
  <h1>QScms 安装向导</h1>
  <div class="sub">账号验证 → 环境检测 → 初始化数据 → 完成</div>

  <div class="steps">
    <div class="<?= $step === 1 ? 'on' : ($step > 1 ? 'done' : '') ?>">1 账号验证</div>
    <div class="<?= $step === 2 ? 'on' : ($step > 2 ? 'done' : '') ?>">2 环境检测</div>
    <div class="<?= $step === 3 ? 'on' : ($step > 3 ? 'done' : '') ?>">3 初始化数据</div>
    <div class="<?= $step === 4 ? 'on' : '' ?>">4 完成</div>
  </div>

  <?php foreach ($errors as $e): ?>
    <div class="msg msg-err"><?= h($e) ?></div>
  <?php endforeach; ?>
  <?php if ($notice !== ''): ?>
    <div class="msg msg-ok"><?= $notice ?></div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
  <div class="card">
    <form method="post" action="install.php">
      <input type="hidden" name="action" value="login">
      <div class="row">
        <label>注册邮箱（verify 上注册时使用的邮箱）</label>
        <input type="email" name="email" value="<?= h(isset($_POST['email']) ? $_POST['email'] : '') ?>" required>
      </div>
      <div class="row">
        <label>注册密码</label>
        <input type="password" name="password" required>
      </div>
      <div class="row">
        <label>verify 地址（可选，留空自动探测）</label>
        <input type="text" name="verify_base" placeholder="<?= h($VERIFY_CANDIDATES[0]) ?>">
        <div class="tip">自建 verify 服务或本地联调时填写，例如 http://127.0.0.1:91</div>
      </div>
      <div class="actions">
        <button class="btn" type="submit">验证并进入环境检测</button>
        <a class="btn ghost" href="https://verify.qsis.cn:88/reg/register.php" target="_blank">还没注册？去注册</a>
      </div>
      <div class="foot">
        verify 连接地址会按顺序自动试探：<?= h(implode('、', $VERIFY_CANDIDATES)) ?>。
        验证通过后才会开始检测环境，检测结果会上报到你的账号下，可在 verify 后台「安装检测报告」查看。
        环境检测会把缺少的运行时目录（control/data、data/posts|pages、upload 等）先建出来，再逐个实写测试权限，必需目录不可写时不允许进入下一步。
      </div>
    </form>
  </div>

  <?php elseif ($step === 2): ?>
  <div class="card">
    <p style="margin:0 0 12px;font-size:13px;color:#555;">
      账号 <strong><?= h($state['email']) ?></strong>　SN <strong><?= h($state['sn']) ?></strong>
      　verify：<?= h($state['base']) ?>
    </p>
    <?php if (!$checks): ?>
      <p style="color:#999;">检测尚未运行。</p>
      <form method="post" action="install.php"><input type="hidden" name="action" value="rescan">
        <button class="btn" type="submit">开始检测</button></form>
    <?php else: ?>
      <?php
      list($okCount, $failCount) = check_summary($checks);
      $coreFail = false; $dirDetail = array();
      foreach ($checks as $it) {
          if ($it['key'] === 'dirs_core' && $it['status'] === 'fail') { $coreFail = true; }
          if ($it['key'] === 'dirs_detail') {
              $dirDetail = json_decode((string)$it['value'], true);
              if (!is_array($dirDetail)) { $dirDetail = array(); }
          }
      }
      ?>
      <p style="font-size:13px;margin:0 0 12px;">
        通过 <strong style="color:#166534"><?= $okCount ?></strong> 项，
        未通过 <strong style="color:#b42318"><?= $failCount ?></strong> 项
        <?php if ($failCount > 0): ?>　<span style="color:#b42318">建议先按说明补齐再继续安装</span><?php endif; ?>
      </p>
      <table>
        <tr><th>检测项</th><th>状态</th><th>检测到的值</th><th>说明</th></tr>
        <?php $map = array('ok' => array('✓ 已通过','pill-ok'), 'fail' => array('✗ 未通过','pill-fail'),
                           'na' => array('– 不适用','pill-na'), 'info' => array('i 仅显示','pill-info'));
              foreach ($checks as $it): if (!empty($it['hidden'])) continue; $st = $map[$it['status']]; ?>
          <tr>
            <td><?= h($it['label']) ?></td>
            <td><span class="pill <?= $st[1] ?>"><?= $st[0] ?></span></td>
            <td class="val"><?= h($it['value']) ?></td>
            <td class="val"><?= h($it['note']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <?php if (count($dirDetail) > 0): ?>
      <details style="margin:14px 0 4px;font-size:13px;">
        <summary style="cursor:pointer;color:#555;">目录权限明细（共 <?= count($dirDetail) ?> 个）</summary>
        <table style="margin-top:8px;">
          <tr><th>目录</th><th>用途</th><th>级别</th><th>状态</th></tr>
          <?php foreach ($dirDetail as $dd): ?>
          <tr>
            <td class="val"><?= h($dd['path']) ?></td>
            <td class="val"><?= h($dd['desc']) ?></td>
            <td class="val"><?= !empty($dd['core']) ? '必需' : '可选' ?></td>
            <td><span class="pill <?= !empty($dd['writable']) ? 'pill-ok' : 'pill-fail' ?>"><?= !empty($dd['writable']) ? '可写' : (!empty($dd['exists']) ? '不可写' : '不存在') ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </details>
      <?php endif; ?>
      <details style="margin:14px 0 4px;font-size:13px;">
        <summary style="cursor:pointer;color:#555;">Windows 压缩工具路径（可选，装了 WinRAR/WinZip 一般自动识别）</summary>
        <form method="post" action="install.php" style="margin-top:10px;">
          <input type="hidden" name="action" value="rescan">
          <div class="grid2">
            <div class="row"><label>zip 工具路径</label>
              <input type="text" name="zip_bin" placeholder="例：C:\Program Files\WinRAR\Rar.exe" value="<?= h($state['zip_bin'] ?? '') ?>"></div>
            <div class="row"><label>unzip 工具路径</label>
              <input type="text" name="unzip_bin" placeholder="例：C:\Program Files\WinRAR\Rar.exe" value="<?= h($state['unzip_bin'] ?? '') ?>"></div>
          </div>
          <div class="tip">识别不到时在此手填压缩工具（如 WinRAR 的 Rar.exe、WinZip 的 WZZIP.EXE / WZUNZIP.EXE）完整路径，点下方按钮重新检测。</div>
          <button class="btn gray" type="submit">指定并重新检测</button>
        </form>
      </details>
      <div class="actions">
        <?php if ($coreFail): ?>
          <span class="pill pill-fail">必需目录还不可写</span>
          <span style="font-size:13px;color:#555;margin-left:6px;">按上表「修复」那列给的命令修好权限，再点重新检测</span>
          <form method="post" action="install.php" style="display:inline;margin-left:8px"><input type="hidden" name="action" value="rescan">
            <button class="btn" type="submit">重新检测</button></form>
        <?php else: ?>
          <a class="btn" href="install.php?step=3">环境没问题，下一步</a>
          <form method="post" action="install.php" style="display:inline"><input type="hidden" name="action" value="rescan">
            <button class="btn gray" type="submit">重新检测</button></form>
        <?php endif; ?>
      </div>
      <div class="foot">php-fpm 是 Linux 部署项，Windows 下显示「不适用」不计入判定。检测项不限定具体 PHP 版本，7.0 以上均可运行，8.x 为推荐。</div>
    <?php endif; ?>
  </div>

  <?php elseif ($step === 3): ?>
  <div class="card">
    <div class="tabs">
      <a class="<?= empty($_GET['mode']) || $_GET['mode'] === 'cloud' ? 'on' : '' ?>" href="install.php?step=3&mode=cloud">从云端恢复</a>
      <a class="<?= isset($_GET['mode']) && $_GET['mode'] === 'manual' ? 'on' : '' ?>" href="install.php?step=3&mode=manual">手动填写</a>
    </div>

    <?php if (!isset($_GET['mode']) || $_GET['mode'] === 'cloud'): ?>
      <?php if (empty($state['has_backup'])): ?>
        <p style="font-size:13px;color:#b42318;margin:0 0 10px;">该 SN 在 verify 上还没有云端配置备份。</p>
        <p style="font-size:13px;color:#555;">可以先用「手动填写」装起来，之后在后台「用户配置备份」里把配置推上去，下次重装就能一键恢复。</p>
      <?php else: ?>
        <p style="font-size:13px;color:#555;margin:0 0 14px;">
          该 SN 在 verify 上有配置备份，可以把上次的站点配置直接拉回来，不用重新填一遍。
        </p>
        <form method="post" action="install.php?step=3">
          <input type="hidden" name="action" value="restore">
          <div class="grid2">
            <div class="row"><label>后台管理员账号</label><input type="text" name="admin_user" value="admin"></div>
            <div class="row"><label>后台管理员密码</label><input type="password" name="admin_pass" required></div>
          </div>
          <div class="row tip" style="color:#999;font-size:12px;">恢复后用它登录 /<?= install_admin_dir() ?>/set.php（云端若已带账号，这里只用于补写缺失的情况）。</div>
          <div class="actions"><button class="btn" type="submit">从云端恢复并写入 control/data</button></div>
        </form>
      <?php endif; ?>

    <?php else: ?>
      <form method="post" action="install.php?step=3">
        <input type="hidden" name="action" value="manual">
        <div class="row"><label>站点名称</label><input type="text" name="site_name" required placeholder="例如：我的博客"></div>
        <div class="row"><label>站点描述</label><textarea name="site_desc" rows="2" required placeholder="一句话介绍这个站点"></textarea></div>
        <div class="row"><label>站点地址</label><input type="text" name="site_link" required placeholder="https://example.com/">
          <div class="tip">结尾带 / 或不带都行，程序会按需处理</div></div>
        <div class="grid2">
          <div class="row"><label>后台管理员账号</label><input type="text" name="admin_user" value="admin" required></div>
          <div class="row"><label>后台管理员密码</label><input type="password" name="admin_pass" required></div>
        </div>
        <div class="grid2">
          <div class="row"><label>站点模式</label>
            <select name="sitemode">
              <option value="article">article（文章）</option>
              <option value="page">page（页面）</option>
            </select></div>
          <div class="row"><label>语言</label>
            <select name="language_file">
              <option value="lang_zh-CN.php">简体中文</option>
              <option value="lang_zh-TW.php">繁體中文</option>
              <option value="lang_en.php">English</option>
            </select></div>
        </div>
        <div class="grid2">
          <div class="row"><label>留言数据文件名</label><input type="text" name="liuyan_data_name" value="<?= h($liuyanDefault) ?>"></div>
          <div class="row"><label>验证邮箱（默认取当前账号）</label><input type="email" name="verify_email" value="<?= h($state['email']) ?>"></div>
        </div>
        <div class="row"><label>页脚版权内容</label><input type="text" name="footer_code" value="Powered by QScms."></div>
        <div class="actions"><button class="btn" type="submit">写入 control/data</button></div>
        <div class="foot">手动模式会以 install/preset 下的通用预设为基底，只改写站点身份、后台账号等关键项，不再整包覆盖 control/data。</div>
      </form>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <?php
  // 完成页只取这两项信息渲染；渲染时写锁（此后安装器就对所有访问关闭）、清会话
  $doneMode  = isset($state['mode']) ? $state['mode'] : '';
  $doneAdmin = isset($state['admin_user']) ? $state['admin_user'] : 'admin';
  write_install_lock();
  $_SESSION['qs_reinstall'] = 1;   // 仅让本页的“重新开始安装”按钮可用
  $_SESSION['qs_install'] = array();
  $_SESSION['qs_checks'] = array();
  ?>
  <div class="card">
    <p style="font-size:15px;margin:0 0 6px;"><strong>安装完成</strong></p>
    <p style="font-size:13px;color:#555;margin:0 0 4px;">方式：<?= h($doneMode) ?></p>
    <p style="font-size:13px;color:#555;margin:0 0 14px;">后台账号：<?= h($doneAdmin) ?>，登录入口 <code>/<?= install_admin_dir() ?>/set.php</code></p>
    <table>
      <tr><th style="width:150px;">下一步</th><th>说明</th></tr>
      <tr><td>安装锁</td><td>已生成 <code>install/install.lock</code>。它是“已安装”的唯一凭证：锁在，安装器就自动跳站点首页；要重装，删掉它再访问 <code>install/install.php</code> 即可</td></tr>
      <tr><td>安装文件</td><td>可以把 install/ 下的安装文件删掉，但<strong>请保留 <code>install.lock</code></strong>，否则站点会以为还没装过</td></tr>
      <tr><td>改后台目录名</td><td><strong>强烈建议装完第一件事就做</strong>：把当前的 <code><?= h(install_admin_dir()) ?>/</code> 改成无规律的名字（例如 <code>manage-9f3k</code>）。扫描器与自动化工具最常探测 <code>/admin/</code>、<code>/scc/</code>、<code>/manage/</code> 这类常见路径，换成无规律名等于直接把它们挡在门外。<strong>只需重命名文件夹、访问一次新入口</strong>，代码与配置都不用改（详见使用手册 5.3）</td></tr>
      <tr><td>登录后台</td><td>进入 /<?= install_admin_dir() ?>/set.php 检查站点名称、主题、友链等</td></tr>
      <tr><td>配置备份</td><td>后台「用户配置备份」可把 control/data 推到 verify，下次重装即可一键恢复</td></tr>
    </table>
    <div class="actions">
      <a class="btn" href="../<?= install_admin_dir() ?>/set.php">去后台</a>
      <form method="post" action="install.php" style="display:inline"><input type="hidden" name="action" value="finish">
        <button class="btn gray" type="submit">重新开始安装</button></form>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
