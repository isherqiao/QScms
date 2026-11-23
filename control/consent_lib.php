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
 * @File         consent_lib.php
 * @CreatedTime  15/11/26 03:20:00
 * @LastEditTime 15/11/26 03:20:00
 * @Description  Cookie consent core: configuration, language fallback, consent audit trail.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie 同意核心库
// 职责：配置读写、语言模板合并、组件渲染所需数据、同意记录（审计轨迹）的写入与读取。
// 组件本体在 files/cookie-consent/ 下，本文件不含任何 HTML。

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/') . '/');
}

if (!defined('QS_CONSENT_COOKIE')) {
    define('QS_CONSENT_COOKIE', 'qs_consent');
}

/**
 * 默认配置
 */
function qs_consent_defaults()
{
    return array(
        'enabled'         => '1',                        // 总开关
        'default_lang'    => 'zh-CN',                    // 兜底语言
        'languages'       => array('zh-CN', 'en'),       // 启用语言（可增删）
        'record'          => '1',                        // 是否服务端留证
        'retention_months' => 36,                        // 记录保留月数
        'cookie_days'     => 365,                        // 前端 cookie 有效期（天）
        'policy_version'  => 'v1.0',                     // 隐私政策版本（改文案必须升）
        'privacy_url'     => '',                         // 隐私政策地址，留空自动推导
        'record_endpoint' => '',                         // 记录接口地址；纯静态站可填有 PHP 的站点完整地址
        'allowed_origins' => '',                         // 允许跨域写入记录的来源域名，每行一个
        'texts'           => array(),                    // 后台覆盖的文案
    );
}

/**
 * 配置文件位置（不计入版本库）
 */
function qs_consent_config_file()
{
    return __DIR__ . '/data/cookie_consent.php';
}

/**
 * 递归合并，后者覆盖前者
 */
function qs_consent_merge($base, $override)
{
    if (!is_array($base)) {
        return $override;
    }
    if (!is_array($override)) {
        return $base;
    }
    $out = $base;
    foreach ($override as $k => $v) {
        if (is_array($v) && isset($out[$k]) && is_array($out[$k])) {
            $out[$k] = qs_consent_merge($out[$k], $v);
        } else {
            $out[$k] = $v;
        }
    }
    return $out;
}

/**
 * 读取配置（静态缓存）
 */
function qs_consent_config($reload = false)
{
    static $cfg = null;
    if ($reload) {
        $cfg = null;
    }
    if (is_array($cfg)) {
        return $cfg;
    }
    $cfg  = qs_consent_defaults();
    $file = qs_consent_config_file();
    if (is_file($file)) {
        $qs_cookie_consent = null;
        include $file;
        if (isset($qs_cookie_consent) && is_array($qs_cookie_consent)) {
            $cfg = qs_consent_merge($cfg, $qs_cookie_consent);
        }
    }
    return $cfg;
}

/**
 * 保存配置：只接受已知键，先吃盘上旧值再合并
 */
function qs_consent_save($input)
{
    if (!is_array($input)) {
        return false;
    }
    $cfg = qs_consent_config();

    $scalarKeys = array('enabled', 'default_lang', 'record', 'policy_version', 'privacy_url', 'record_endpoint', 'allowed_origins');
    foreach ($scalarKeys as $k) {
        if (array_key_exists($k, $input) && !is_array($input[$k])) {
            $cfg[$k] = trim((string)$input[$k]);
        }
    }
    $intKeys = array('retention_months', 'cookie_days');
    foreach ($intKeys as $k) {
        if (array_key_exists($k, $input) && !is_array($input[$k])) {
            $cfg[$k] = max(1, (int)$input[$k]);
        }
    }

    // 允许来源：统一成“域名”或“*.域名”，去协议、去尾斜杠、去空行
    if (isset($cfg['allowed_origins'])) {
        $lines = preg_split('/[\r\n,]+/', (string)$cfg['allowed_origins']);
        $clean = array();
        foreach ($lines as $line) {
            $line = strtolower(trim((string)$line));
            if ($line === '') {
                continue;
            }
            $line = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $line);
            $line = rtrim((string)$line, '/');
            if ($line !== '' && !in_array($line, $clean, true)) {
                $clean[] = $line;
            }
        }
        $cfg['allowed_origins'] = implode("\n", $clean);
    }

    // 启用语言：只保留模板目录里真实存在的语言
    if (isset($input['languages']) && is_array($input['languages'])) {
        $available = qs_consent_lang_list();
        $clean     = array();
        foreach ($input['languages'] as $code) {
            $code = (string)$code;
            if (isset($available[$code]) && !in_array($code, $clean, true)) {
                $clean[] = $code;
            }
        }
        if (!empty($clean)) {
            $cfg['languages'] = $clean;
        }
    }
    if (empty($cfg['languages']) || !is_array($cfg['languages'])) {
        $cfg['languages'] = array('zh-CN');
    }

    // 默认语言必须落在启用语言里
    if (!in_array($cfg['default_lang'], $cfg['languages'], true)) {
        $cfg['default_lang'] = $cfg['languages'][0];
    }

    // 文案覆盖：只收启用语言
    if (isset($input['texts']) && is_array($input['texts'])) {
        $allowed = array_keys(qs_consent_template($cfg['default_lang']));
        if (empty($allowed)) {
            $allowed = qs_consent_text_keys();
        }
        $texts = array();
        foreach ($input['texts'] as $code => $fields) {
            $code = (string)$code;
            if (!in_array($code, $cfg['languages'], true) || !is_array($fields)) {
                continue;
            }
            $set = array();
            foreach ($fields as $key => $val) {
                $key = (string)$key;
                if (substr($key, 0, 1) === '_') {
                    continue;
                }
                if ($allowed && !in_array($key, $allowed, true)) {
                    continue;
                }
                if (is_array($val)) {
                    continue;
                }
                $set[$key] = (string)$val;
            }
            if (!empty($set)) {
                $texts[$code] = $set;
            }
        }
        $cfg['texts'] = $texts;
    }

    $file = qs_consent_config_file();
    $dir  = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $code = "<?php\n// Cookie 同意配置（自动生成，不入版本库）\n\$qs_cookie_consent = "
          . var_export($cfg, true) . ";\n";
    $ok = @file_put_contents($file, $code, LOCK_EX);
    if ($ok !== false) {
        @chmod($file, 0640);
        qs_consent_config(true); // 让同一请求内后续读取拿到新值
    }
    return $ok !== false;
}

/**
 * 语言模板目录
 */
function qs_consent_lang_dir()
{
    return dirname(__DIR__) . '/files/cookie-consent/lang/';
}

/**
 * 模板里可编辑的文案键（取中文模板作为基准）
 */
function qs_consent_text_keys()
{
    static $keys = null;
    if (is_array($keys)) {
        return $keys;
    }
    $probe = qs_consent_template('zh-CN');
    if (empty($probe)) {
        $probe = qs_consent_template('en');
    }
    $keys = array();
    foreach ($probe as $k => $v) {
        if (substr((string)$k, 0, 1) === '_') {
            continue;
        }
        if (is_string($v)) {
            $keys[] = $k;
        }
    }
    return $keys;
}

/**
 * 读取单个语言模板
 */
function qs_consent_template($code)
{
    static $cache = array();
    $code = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$code);
    if ($code === '') {
        return array();
    }
    if (isset($cache[$code])) {
        return $cache[$code];
    }
    $file = qs_consent_lang_dir() . $code . '.php';
    $out  = array();
    if (is_file($file)) {
        $data = include $file;
        if (is_array($data)) {
            $out = $data;
        }
    }
    return $cache[$code] = $out;
}

/**
 * 可用语言清单：code => 显示名
 */
function qs_consent_lang_list()
{
    static $list = null;
    if (is_array($list)) {
        return $list;
    }
    $list = array();
    $dir  = qs_consent_lang_dir();
    if (is_dir($dir)) {
        $files = glob($dir . '*.php');
        if (is_array($files)) {
            foreach ($files as $f) {
                $code = basename($f, '.php');
                $tpl  = qs_consent_template($code);
                $list[$code] = isset($tpl['name']) && $tpl['name'] !== '' ? $tpl['name'] : $code;
            }
        }
    }
    return $list;
}

/**
 * 取某语言的最终文案：模板 + 后台覆盖
 */
function qs_consent_texts($code)
{
    $cfg    = qs_consent_config();
    $tpl    = qs_consent_template($code);
    $merged = array();
    foreach ($tpl as $k => $v) {
        $merged[$k] = $v;
    }
    if ($code === '') {
        $code = $cfg['default_lang'];
    }
    if (isset($cfg['texts'][$code]) && is_array($cfg['texts'][$code])) {
        foreach ($cfg['texts'][$code] as $k => $v) {
            if (trim((string)$v) !== '') {
                $merged[$k] = (string)$v;
            }
        }
    }
    return $merged;
}

/**
 * 组装前端要用的语言包（全部启用语言）
 */
function qs_consent_payload()
{
    global $qs_config;

    $cfg   = qs_consent_config();
    $langs = array();
    foreach ((array)$cfg['languages'] as $code) {
        $t = qs_consent_texts($code);
        if (empty($t)) {
            continue;
        }
        $langs[$code] = $t;
    }
    if (empty($langs)) {
        $t = qs_consent_texts($cfg['default_lang']);
        if (!empty($t)) {
            $langs[$cfg['default_lang']] = $t;
        }
    }
    // 静态站本身没有后端：没配外部记录端点时干脆不发请求，避免一堆 404
    $recordOn = ($cfg['record'] === '1');
    if ($recordOn && defined('QS_CONSENT_STATIC') && QS_CONSENT_STATIC
        && trim((string)($cfg['record_endpoint'] ?? '')) === '') {
        $recordOn = false;
    }

    return array(
        'languages'      => $langs,
        'defaultLang'    => $cfg['default_lang'],
        'policyVersion'  => $cfg['policy_version'],
        'privacyUrl'     => qs_consent_privacy_url(),
        'cookieDays'     => (int)$cfg['cookie_days'],
        'record'         => $recordOn,
        'cookieName'     => QS_CONSENT_COOKIE,
        'siteName'       => isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : '',
    );
}

/**
 * 是否启用组件
 */
function qs_consent_enabled()
{
    $cfg = qs_consent_config();
    return $cfg['enabled'] === '1';
}

/**
 * 隐私政策页地址
 */
function qs_consent_privacy_url()
{
    global $qs_config;

    $cfg = qs_consent_config();
    $url = trim((string)$cfg['privacy_url']);
    if ($url !== '') {
        return $url;
    }

    $slug = 'privacy';
    if (isset($qs_config['privacy_slug']) && trim((string)$qs_config['privacy_slug']) !== '') {
        $slug = trim((string)$qs_config['privacy_slug']);
    }

    // 相对路径：按当前访问地址解析，换域名或 IP 访问都指向本站
    $dir = isset($qs_config['site_directory']) ? (string)$qs_config['site_directory'] : '/';
    $dir = '/' . trim($dir, '/');
    $dir = $dir === '/' ? '/' : $dir . '/';

    // 静态站没有路由：隐私页是真实目录 /privacy/，没生成就不要给死链
    if (defined('QS_CONSENT_STATIC') && QS_CONSENT_STATIC) {
        if (trim((string)($qs_config['privacy_body'] ?? '')) === '') {
            return '';
        }
        return $dir . 'privacy/';
    }

    return $dir . '?' . rawurlencode($slug) . '/';
}

/**
 * 记录目录
 */
function qs_consent_data_dir()
{
    return __DIR__ . '/data/consent/';
}

/**
 * 记录文件路径：按月分文件
 */
function qs_consent_log_file($ts = null)
{
    $dir = qs_consent_data_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . 'consent_' . date('Ym', $ts ?: time()) . '.log';
}

/**
 * 生成同意标识
 */
function qs_consent_new_id()
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    $h = bin2hex($b);
    return substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-' . substr($h, 12, 4)
         . '-' . substr($h, 16, 4) . '-' . substr($h, 20, 12);
}

/**
 * 追加一条同意记录
 */
function qs_consent_append($row)
{
    if (!is_array($row)) {
        return false;
    }
    $line = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($line === false) {
        return false;
    }
    $file = qs_consent_log_file();

    // 记录目录首次创建时顺带挡住直接访问（control/data 不入库，靠这里兜底）
    $dir = qs_consent_data_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $ht = rtrim(str_replace('\\', '/', $dir), '/') . '/.htaccess';
    if (!is_file($ht)) {
        @file_put_contents($ht, "# 同意记录属于服务端专有数据，禁止直接访问\nRequire all denied\n");
    }

    $fp   = @fopen($file, 'ab');
    if (!$fp) {
        return false;
    }
    $ok = flock($fp, LOCK_EX);
    fwrite($fp, $line . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($file, 0640);
    return $ok !== false;
}

/**
 * 读取记录（倒序）
 *
 * @param int    $limit  条数
 * @param string $action 过滤动作
 * @param string $month  过滤月份 YYYYMM
 */
function qs_consent_read($limit = 50, $action = '', $month = '')
{
    $dir = qs_consent_data_dir();
    if (!is_dir($dir)) {
        return array();
    }
    $files = glob($dir . 'consent_*.log');
    if (empty($files)) {
        return array();
    }
    if ($month !== '' && preg_match('/^[0-9]{6}$/', $month)) {
        $files = array($dir . 'consent_' . $month . '.log');
    }
    sort($files);
    $files = array_reverse($files);

    $rows  = array();
    $limit = max(1, (int)$limit);
    foreach ($files as $f) {
        if (!is_file($f)) {
            continue;
        }
        $lines = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            continue;
        }
        foreach (array_reverse($lines) as $line) {
            $row = json_decode($line, true);
            if (!is_array($row)) {
                continue;
            }
            if ($action !== '' && ($row['action'] ?? '') !== $action) {
                continue;
            }
            $rows[] = $row;
            if (count($rows) >= $limit) {
                return $rows;
            }
        }
    }
    return $rows;
}

/**
 * 统计：总条数、动作分布、语言分布
 */
function qs_consent_stats()
{
    $dir = qs_consent_data_dir();
    $out = array('total' => 0, 'actions' => array(), 'langs' => array());
    if (!is_dir($dir)) {
        return $out;
    }
    $files = glob($dir . 'consent_*.log');
    if (empty($files)) {
        return $out;
    }
    foreach ($files as $f) {
        $lines = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            continue;
        }
        foreach ($lines as $line) {
            $row = json_decode($line, true);
            if (!is_array($row)) {
                continue;
            }
            $out['total']++;
            $a = (string)($row['action'] ?? '');
            $l = (string)($row['lang'] ?? '');
            if ($a !== '') {
                $out['actions'][$a] = ($out['actions'][$a] ?? 0) + 1;
            }
            if ($l !== '') {
                $out['langs'][$l] = ($out['langs'][$l] ?? 0) + 1;
            }
        }
    }
    return $out;
}

/**
 * 按保留期限清理旧记录
 */
function qs_consent_cleanup($months = null)
{
    $cfg    = qs_consent_config();
    $months = $months === null ? (int)$cfg['retention_months'] : (int)$months;
    if ($months < 1) {
        return 0;
    }
    $dir = qs_consent_data_dir();
    if (!is_dir($dir)) {
        return 0;
    }
    $cutoff = date('Ymd', strtotime('-' . $months . ' months')) . '01';
    $cutoffMonth = substr($cutoff, 0, 6);
    $files  = glob($dir . 'consent_*.log');
    $removed = 0;
    foreach ((array)$files as $f) {
        if (!preg_match('/consent_([0-9]{6})\.log$/', $f, $m)) {
            continue;
        }
        if ($m[1] < $cutoffMonth) {
            if (@unlink($f)) {
                $removed++;
            }
        }
    }
    return $removed;
}

/**
 * 简单限流：同一 key 在窗口内最多 N 次
 */
function qs_consent_rate_limit($key, $limit = 20, $window = 3600)
{
    $dir = qs_consent_data_dir() . '.rate/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . preg_replace('/[^A-Za-z0-9]/', '', (string)$key) . '.cnt';
    $now  = time();
    $data = array('t' => $now, 'n' => 0);
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $tmp = json_decode((string)$raw, true);
        if (is_array($tmp) && isset($tmp['t'], $tmp['n']) && ($now - (int)$tmp['t']) < $window) {
            $data = array('t' => (int)$tmp['t'], 'n' => (int)$tmp['n']);
        }
    }
    $data['n']++;
    if ($data['n'] > $limit) {
        return false;
    }
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

/**
 * 判断某个 Origin 是否被允许写入记录
 *
 * 同一站点的 Origin 由 record.php 自行判断，这里只管“跨域白名单”：
 * 纯静态站（或 file:// 打开）部署到别的域名时，把它写进后台白名单即可。
 *
 * @param string $origin 形如 https://example.com，或字符串 null
 * @return bool
 */
function qs_consent_origin_allowed($origin)
{
    $origin = strtolower(trim((string)$origin));
    if ($origin === '') {
        return false;
    }

    if ($origin === 'null' || $origin === 'file://') {
        $host = 'null';
    } else {
        $host = parse_url($origin, PHP_URL_HOST);
        $host = is_string($host) ? strtolower($host) : '';
    }
    if ($host === '') {
        return false;
    }

    $cfg = qs_consent_config();
    $raw = (string)($cfg['allowed_origins'] ?? '');
    if (trim($raw) === '') {
        return false;
    }

    foreach (preg_split('/[\r\n,]+/', $raw) as $item) {
        $item = strtolower(trim((string)$item));
        if ($item === '') {
            continue;
        }
        $item = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $item);
        $item = rtrim((string)$item, '/');
        if ($item === '') {
            continue;
        }
        if ($item === $host) {
            return true;
        }
        // 支持 *.example.com 形式（含所有子域）
        if (strpos($item, '*.') === 0) {
            $suffix = substr($item, 1);
            if ($suffix !== '' && substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }
    }

    return false;
}

