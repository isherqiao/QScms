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
 * @File         Client.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 14/11/26
 * @Description  Authorization status reader and on-demand verify client. Reads the local authorization file only; nothing is sent to the verify server unless the site owner explicitly clicks a button (pure pull/active mode).
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 本地授权文件
$localFilePath = $datapaths . 'authorization.php';

/**
 * 解析授权文件里的 //key: value 行，按出现顺序返回数组
 * 同一 key 可能出现多次（历史约定：最后一行 //to_time 存的是 SN）
 *
 * @param string $path
 * @return array 形如 [[key, value], ...]
 */
function qs_auth_file_parse($path)
{
    $pairs = [];
    if ($path === '' || !is_file($path)) {
        return $pairs;
    }
    $raw = (string)@file_get_contents($path);
    if ($raw !== '' && preg_match_all('#//([a-zA-Z_][a-zA-Z0-9_]*):[ \t]*([^\r\n]*)#', $raw, $m, PREG_SET_ORDER)) {
        foreach ($m as $one) {
            $pairs[] = [strtolower($one[1]), trim($one[2])];
        }
    }
    return $pairs;
}

/**
 * 从解析结果取某个 key 的值
 *
 * @param array  $pairs
 * @param string $key
 * @param bool   $last true 取最后一次出现，false 取第一次
 * @return string
 */
function qs_auth_file_get(array $pairs, $key, $last = true)
{
    $hit = null;
    foreach ($pairs as $p) {
        if ($p[0] === $key) {
            $hit = $p[1];
            if (!$last) {
                break;
            }
        }
    }
    return ($hit === null) ? '' : $hit;
}

/**
 * 回写本地授权文件，保持既有格式（最后一行的 //to_time 存 SN）
 *
 * @param string $path
 * @param array  $fields status / version_number / authorization_name / to_time / sn
 * @return bool
 */
function qs_auth_file_write($path, array $fields)
{
    $content  = "<?php \n";
    $content .= "//status: " . (string)($fields['status'] ?? 'unauthorized') . "\n";
    $content .= "//version_number: " . (string)($fields['version_number'] ?? '') . "\n";
    $content .= "//authorization_name: " . (string)($fields['authorization_name'] ?? '') . "\n";
    $content .= "//authorization_nature: " . (string)($fields['authorization_nature'] ?? '') . "\n";
    $content .= "//company_name: " . (string)($fields['company_name'] ?? '') . "\n";
    $content .= "//to_time: " . (string)($fields['to_time'] ?? '') . "\n";
    $content .= "//to_time: " . (string)($fields['sn'] ?? '') . "\n";
    $content .= "?>";
    return (bool)@file_put_contents($path, $content, LOCK_EX);
}

/**
 * 主动请求 verify 服务器：只在站长点了「登录 / 绑定」时调用，平时一次请求都不发。
 *
 * @param string $url     接口地址
 * @param array  $payload 提交字段（以 JSON 发送）
 * @param int    $timeout 超时秒数
 * @return array ['ok'=>bool,'code'=>int,'body'=>string,'data'=>array|null,'error'=>string]
 */
function qs_verify_post($url, array $payload, $timeout = 15)
{
    $out = ['ok' => false, 'code' => 0, 'body' => '', 'data' => null, 'error' => ''];
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $out['error'] = '接口地址不合法';
        return $out;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => (int)$timeout,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
    ]);
    $resp  = curl_exec($ch);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    if (PHP_VERSION_ID < 80000) {
        curl_close($ch);
    }

    $out['code'] = $code;
    if ($resp === false || $errno !== 0) {
        $out['error'] = $err !== '' ? $err : '网络错误';
        return $out;
    }

    $out['body'] = (string)$resp;
    $out['data'] = json_decode($out['body'], true);
    $out['ok']   = ($code >= 200 && $code < 300) && is_array($out['data']);
    return $out;
}

/**
 * 接口防重放：给 payload 补 ts / nonce / sign，密钥 = 授权码 SN
 * 规则必须与 verify 侧 api/_auth.php 逐字一致：
 *   sign = hash_hmac('sha256', "api|email|ts|nonce|bodyHash", SN)
 *   bodyHash = md5(把 $signKeys 里的字段按 ksort 排好后拼成 k=v&k=v)
 * $api 是接口名，如 'install_auth' / 'bug_report'
 */
function qs_api_sign(array $payload, $secret, $api, array $signKeys)
{
    $payload['ts']    = time();
    $payload['nonce'] = bin2hex(random_bytes(16));

    $fields = [];
    foreach ($signKeys as $k) {
        $v = isset($payload[$k]) ? $payload[$k] : '';
        $fields[$k] = is_array($v) ? '' : (string)$v;
    }
    ksort($fields);
    $parts = [];
    foreach ($fields as $k => $v) {
        $parts[] = $k . '=' . $v;
    }
    $bodyHash = md5(implode('&', $parts));

    $email = isset($payload['email']) ? (string)$payload['email'] : '';
    $msg   = $api . '|' . $email . '|' . (int)$payload['ts'] . '|' . $payload['nonce'] . '|' . $bodyHash;
    $payload['sign'] = hash_hmac('sha256', $msg, (string)$secret);
    return $payload;
}

/**
 * 版权页后台静默刷新：页面渲染全程不发请求、不阻塞；
 * 输出完响应后，最多每 $ttl 秒（默认 24 小时）向认证服务器同步一次授权信息。
 * 失败静默，等下一个周期再试。
 *
 * @param array  $site     verify_site.php 里的绑定信息（sn / email / api_url / site_domain / site_alias）
 * @param string $authFile 本地授权文件路径
 * @param int    $ttl      最小同步间隔（秒）
 * @return bool 是否已排入本次刷新
 */
function qs_auth_refresh_schedule(array $site, $authFile, $ttl = 86400)
{
    if (empty($site['sn']) || empty($site['email']) || empty($site['api_url'])) {
        return false;
    }
    if (!filter_var($site['api_url'], FILTER_VALIDATE_URL)) {
        return false;
    }

    $stampFile = dirname($authFile) . '/.auth_refresh_at';
    $last      = is_file($stampFile) ? (int)@file_get_contents($stampFile) : 0;
    if ($last > 0 && (time() - $last) < $ttl) {
        return false;
    }
    // 先落时间戳，避免并发访问重复触发
    @file_put_contents($stampFile, (string)time(), LOCK_EX);

    $versionNumber = isset($GLOBALS['versionNumber']) ? (string)$GLOBALS['versionNumber'] : '';

    register_shutdown_function(function () use ($site, $authFile, $versionNumber) {
        @ignore_user_abort(true);
        @set_time_limit(20);
        // 尽量把响应交给浏览器之后再做同步
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @flush();
        }

        $sn     = (string)$site['sn'];
        $email  = (string)$site['email'];
        $domain = isset($site['site_domain']) ? (string)$site['site_domain'] : '';

        $payload = array(
            'email'       => $email,
            'password'    => $sn,
            'sn'          => $sn,
            'site_domain' => $domain,
            'cms_url'     => ($domain !== '' ? 'http://' . $domain : ''),
            'site_alias'  => isset($site['site_alias']) ? (string)$site['site_alias'] : '',
        );
        $payload = qs_api_sign($payload, $sn, 'install_auth', array('password', 'sn'));

        // 毫秒级超时：同步必须短，宁可这次拿不到也不能让页面等
        $ch = curl_init((string)$site['api_url']);
        curl_setopt_array($ch, array(
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => json_encode($payload),
            CURLOPT_HTTPHEADER        => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_CONNECTTIMEOUT_MS => 400,
            CURLOPT_TIMEOUT_MS        => 900,
        ));
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        if ($resp === false || $code < 200 || $code >= 300) {
            return;
        }
        $dec = json_decode((string)$resp, true);
        if (!is_array($dec) || empty($dec['ok'])) {
            return;
        }
        $name    = isset($dec['authorization']) ? (string)$dec['authorization'] : '';
        $nature  = isset($dec['nature']) ? trim((string)$dec['nature']) : '';
        $company = isset($dec['company_name']) ? trim((string)$dec['company_name']) : '';
        $expire  = isset($dec['expire_at']) ? trim((string)$dec['expire_at']) : '';
        $toTime  = preg_match('/^\d{4}-\d{2}-\d{2}$/', $expire) ? $expire : '';

        qs_auth_file_write($authFile, array(
            'status'               => 'authorized',
            'version_number'       => $versionNumber,
            'authorization_name'   => $name,
            'authorization_nature' => $nature,
            'company_name'         => $company,
            'to_time'              => $toTime,
            'sn'                   => isset($dec['sn']) ? (string)$dec['sn'] : $sn,
        ));
    });

    return true;
}

/**
 * 带文件的 multipart POST（Bug 上报用；qs_verify_post 只发 JSON）
 */
function qs_verify_post_multipart($url, array $fields, $fileField, $filePath, $timeout = 30)
{
    $out = ['ok' => false, 'code' => 0, 'body' => '', 'data' => null, 'error' => ''];
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $out['error'] = '接口地址不合法';
        return $out;
    }
    $post = $fields;
    if ($filePath !== '' && is_file($filePath)) {
        $post[$fileField] = new CURLFile($filePath, mime_content_type($filePath) ?: 'application/octet-stream', basename($filePath));
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => (int)$timeout,
    ]);
    $resp  = curl_exec($ch);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    if (PHP_VERSION_ID < 80000) {
        curl_close($ch);
    }
    $out['code'] = $code;
    if ($resp === false || $errno !== 0) {
        $out['error'] = $err !== '' ? $err : '网络错误';
        return $out;
    }
    $out['body'] = (string)$resp;
    $out['data'] = json_decode($out['body'], true);
    $out['ok']   = ($code >= 200 && $code < 300) && is_array($out['data']);
    return $out;
}

$authPairs    = qs_auth_file_parse($localFilePath);
$localStatus  = (qs_auth_file_get($authPairs, 'status') === 'authorized');
$localVersion = qs_auth_file_get($authPairs, 'version_number');
$localName    = qs_auth_file_get($authPairs, 'authorization_name');
$localNature  = qs_auth_file_get($authPairs, 'authorization_nature');
$localCompany = qs_auth_file_get($authPairs, 'company_name');
$localToTime  = qs_auth_file_get($authPairs, 'to_time', false);
$localSn      = qs_auth_file_get($authPairs, 'to_time');   // 约定：最后一个 //to_time 行是 SN

// 页面只读本地授权文件：不发请求、不联网、不阻断
$responseData = [
    'authorized'         => ($localStatus ? 'true' : 'false'),
    'version_number'     => $localVersion,
    'authorization_name' => $localName,
    'authorization_nature' => $localNature,
    'company_name'       => $localCompany,
    'to_time'            => $localToTime,
    'new_version'        => '',
    'sn'                 => $localSn,
];

// 供 version.php 使用的变量
$versionNumber     = isset($responseData['version_number']) ? $responseData['version_number'] : '';
$authorizationName = isset($responseData['authorization_name']) ? $responseData['authorization_name'] : '';
$authorizationNature = isset($responseData['authorization_nature']) ? $responseData['authorization_nature'] : '';
$companyName         = isset($responseData['company_name']) ? $responseData['company_name'] : '';
$toTime            = isset($responseData['to_time']) ? $responseData['to_time'] : '';
$newVersionLink    = isset($responseData['new_version']) ? $responseData['new_version'] : '';
$sn                = isset($responseData['sn']) ? $responseData['sn'] : '';
?>
