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
 * @File         record.php
 * @CreatedTime  15/11/26 03:45:00
 * @LastEditTime 15/11/26 03:45:00
 * @Description  Anonymous consent-record endpoint: appends one audit line per user decision.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie 同意记录端点（匿名，不需登录）
// 只做一件事：把一次“同意 / 拒绝 / 自定义”的选择落成一条可举证的记录。

require_once __DIR__ . '/../../control/consent_lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/**
 * 统一输出并结束
 */
function qs_consent_out($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$cfg = qs_consent_config();

// 来源判定：本站 或 后台白名单。静态站常部署在别的域名，file:// 打开时 Origin 为 null
$selfHost  = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$originRaw = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
$referRaw  = (string)($_SERVER['HTTP_REFERER'] ?? '');

$originSame    = true;    // 没带 Origin 视为本站
$originAllowed = false;

if ($originRaw !== '') {
    if ($originRaw === 'null') {
        $originSame    = false;
        $originAllowed = qs_consent_origin_allowed('null');
    } else {
        $oHost = strtolower((string)parse_url($originRaw, PHP_URL_HOST));
        $oPort = parse_url($originRaw, PHP_URL_PORT);
        $oFull = $oHost . ($oPort ? ':' . $oPort : '');
        $originSame    = ($oFull === $selfHost || $oHost === $selfHost);
        $originAllowed = qs_consent_origin_allowed($originRaw);
    }
}

// 跨域响应头：只对本站与白名单回显，避免变成任意站点的写入通道
if ($originRaw !== '' && ($originSame || $originAllowed)) {
    header('Access-Control-Allow-Origin: ' . $originRaw);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
}

// CORS 预检
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    if ($originRaw !== '' && !$originSame && !$originAllowed) {
        http_response_code(403);
        qs_consent_out(array('ok' => 0, 'err' => 'bad_origin'));
    }
    http_response_code(204);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    qs_consent_out(array('ok' => 0, 'err' => 'method_not_allowed'));
}

if (($cfg['enabled'] ?? '') !== '1') {
    qs_consent_out(array('ok' => 0, 'err' => 'disabled'));
}
if (($cfg['record'] ?? '') !== '1') {
    qs_consent_out(array('ok' => 1, 'recorded' => 0));
}

// 来源校验：本站 or 白名单，其余拒绝
if ($originRaw !== '' && !$originSame && !$originAllowed) {
    qs_consent_out(array('ok' => 0, 'err' => 'bad_origin'));
}
if ($originRaw === '' && $referRaw !== '') {
    $rHost = strtolower((string)parse_url($referRaw, PHP_URL_HOST));
    if ($rHost !== '' && $rHost !== $selfHost && !qs_consent_origin_allowed($referRaw)) {
        qs_consent_out(array('ok' => 0, 'err' => 'bad_origin'));
    }
}

// 限流：同 IP 每小时 30 次，超了直接拒绝
$ip    = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$ipKey = substr(hash('sha256', $ip . '|qs-consent'), 0, 24);
if (!qs_consent_rate_limit($ipKey, 30, 3600)) {
    qs_consent_out(array('ok' => 0, 'err' => 'rate_limited'));
}

// 解析请求体：JSON 优先，兼容表单
$raw  = file_get_contents('php://input');
$body = json_decode((string)$raw, true);
if (!is_array($body)) {
    $body = $_POST;
}
if (!is_array($body)) {
    $body = array();
}

$action = strtolower(trim((string)($body['action'] ?? '')));
$allowedActions = array('accept_all', 'reject_all', 'custom', 'withdraw');
if (!in_array($action, $allowedActions, true)) {
    qs_consent_out(array('ok' => 0, 'err' => 'bad_action'));
}

$switches = $body['switches'] ?? array();
if (!is_array($switches)) {
    $switches = array();
}
$cleanSwitches = array();
foreach (array('storage', 'ads', 'measure', 'geo', 'analytics', 'social') as $key) {
    $cleanSwitches[$key] = !empty($switches[$key]);
}
$cleanSwitches['storage'] = true; // 必要项固定开启

$lang = trim((string)($body['lang'] ?? ''));
$langFiles = qs_consent_lang_list();
if (!isset($langFiles[$lang])) {
    $lang = $cfg['default_lang'];
}

$page = (string)($body['page'] ?? '');
if (strlen($page) > 200) {
    $page = substr($page, 0, 200);
}

$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

// 标识：跨域时浏览器不会给本站种 cookie，以客户端自带的标识为准（同一设备始终复用）
$id       = preg_replace('/[^A-Za-z0-9\-]/', '', (string)($_COOKIE[QS_CONSENT_COOKIE] ?? ''));
$clientId = preg_replace('/[^A-Za-z0-9\-]/', '', (string)($body['id'] ?? ''));
if (strlen($clientId) >= 16) {
    $id = $clientId;
}
if (strlen($id) < 16) {
    $id = qs_consent_new_id();
}

// 告知内容快照哈希：证明当时给用户看的是哪一版文案
$texts       = qs_consent_texts($lang);
$noticeParts = array();
foreach (array('modal_title', 'modal_intro', 'cat_storage_name', 'cat_storage_desc',
               'cat_ads_name', 'cat_ads_desc', 'cat_measure_name', 'cat_measure_desc',
               'cat_geo_name', 'cat_geo_desc', 'cat_analytics_name', 'cat_analytics_desc',
               'cat_social_name', 'cat_social_desc') as $k) {
    $noticeParts[] = isset($texts[$k]) ? (string)$texts[$k] : '';
}
$noticeHash = substr(hash('sha256', implode('|', $noticeParts)), 0, 16);

$row = array(
    'id'       => $id,
    't'        => gmdate('Y-m-d\TH:i:s\Z'),
    'ts'       => time(),
    'action'   => $action,
    'switches' => $cleanSwitches,
    'lang'     => $lang,
    'version'  => (string)$cfg['policy_version'],
    'page'     => $page,
    'origin'   => substr($originRaw, 0, 100),                    // 只记来源域，不记完整地址
    'ip'       => $ipKey,                                        // 哈希，不存明文
    'ua'       => substr(hash('sha256', $ua . '|qs-consent'), 0, 16),
    'notice'   => $noticeHash,
);

$ok = qs_consent_append($row);
if (!$ok) {
    qs_consent_out(array('ok' => 0, 'err' => 'write_failed'));
}

// 下发标识 cookie：以后同一台设备的变更都归到同一个 id
$cookieDays = (int)$cfg['cookie_days'];
if ($cookieDays < 1) {
    $cookieDays = 365;
}
setcookie(QS_CONSENT_COOKIE, $id, array(
    'expires'  => time() + $cookieDays * 86400,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => false,   // 前端需要读它来展示“我的选择”
    'samesite' => 'Lax',
));

qs_consent_out(array('ok' => 1, 'id' => $id, 'version' => (string)$cfg['policy_version']));
