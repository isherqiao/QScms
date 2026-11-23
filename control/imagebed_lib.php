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
 * @File         imagebed_lib.php
 * @CreatedTime  15/11/26 10:20:00
 * @LastEditTime 15/11/26 10:20:00
 * @Description  外部图床模块：统一上传接口 + 可插拔驱动（S3 兼容 / 阿里 OSS / 七牛 / 又拍 / GitHub / Chevereto / 兰空 / 自定义 HTTP）。
 *               配置独立存放于 control/data/imagebed.php（该目录已被 .gitignore 忽略，密钥不入库）。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('QS_IMAGEBED_LIB')) {
    define('QS_IMAGEBED_LIB', 1);
}

/**
 * 默认配置骨架。driver 为空表示"只用本地"，与旧版行为完全一致。
 */
function qs_imagebed_defaults() {
    return [
        'driver'    => 'local',
        'on_fail'   => 'local',   // local=图床失败回落本地；error=直接报错
        'keep_local'=> '0',       // 1=上传成功后仍保留本地副本
        'url_prefix'=> '',        // 旧本地图片外链前缀（复活原来的「图片床地址」）
        'naming'    => 'time',    // time|md5|orig
        'path'      => 'images/{Y}/{m}/',
        's3' => [
            'endpoint'      => '',
            'region'        => 'auto',
            'bucket'        => '',
            'access_key'    => '',
            'secret_key'    => '',
            'public_url'    => '',
            'path_style'    => '1',
            'acl'           => 'public-read',
        ],
        'oss' => [
            'endpoint'      => '',
            'bucket'        => '',
            'access_key'    => '',
            'secret_key'    => '',
            'public_url'    => '',
        ],
        'qiniu' => [
            'bucket'        => '',
            'access_key'    => '',
            'secret_key'    => '',
            'public_url'    => '',
            'upload_host'   => 'https://upload.qiniup.com',
        ],
        'upyun' => [
            'bucket'        => '',
            'operator'      => '',
            'password'      => '',
            'public_url'    => '',
            'api_host'      => 'https://v0.api.upyun.com',
        ],
        'github' => [
            'owner'         => '',
            'repo'          => '',
            'branch'        => 'main',
            'token'         => '',
            'public_url'    => '',
            'api_host'      => 'https://api.github.com',
        ],
        'chevereto' => [
            'base_url'      => '',
            'key'           => '',
        ],
        'lsky' => [
            'base_url'      => '',
            'token'         => '',
        ],
        'custom' => [
            'url'           => '',
            'method'        => 'POST',
            'file_field'    => 'file',
            'extra_fields'  => '',
            'extra_headers' => '',
            'json_path'     => 'url',
            'public_url'    => '',
        ],
    ];
}

/**
 * 配置文件路径
 */
function qs_imagebed_config_file() {
    return __DIR__ . '/data/imagebed.php';
}

/**
 * 读取配置（与默认值深合并）。文件不存在时返回默认值，不写盘。
 */
function qs_imagebed_config() {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $cfg = qs_imagebed_defaults();
    $file = qs_imagebed_config_file();
    if (is_file($file)) {
        $loaded = null;
        include $file;
        if (isset($qs_imagebed) && is_array($qs_imagebed)) {
            $loaded = $qs_imagebed;
        }
        if (is_array($loaded)) {
            foreach ($loaded as $k => $v) {
                if (isset($cfg[$k]) && is_array($cfg[$k]) && is_array($v)) {
                    $cfg[$k] = array_merge($cfg[$k], $v);
                } else {
                    $cfg[$k] = $v;
                }
            }
        }
    }
    $cached = $cfg;
    return $cfg;
}

/**
 * 保存配置。只接受已知键，避免把任意 POST 写进文件。
 */
function qs_imagebed_save($input) {
    if (!is_array($input)) {
        return false;
    }
    $cfg  = qs_imagebed_defaults();
    $file = qs_imagebed_config_file();
    // 先吃掉磁盘上的旧值，避免没提交的分组被清空
    if (is_file($file)) {
        include $file;
        if (isset($qs_imagebed) && is_array($qs_imagebed)) {
            foreach ($qs_imagebed as $k => $v) {
                if (isset($cfg[$k]) && is_array($cfg[$k]) && is_array($v)) {
                    $cfg[$k] = array_merge($cfg[$k], $v);
                }
            }
        }
    }

    $scalarKeys = ['driver', 'on_fail', 'keep_local', 'url_prefix', 'naming', 'path'];
    foreach ($scalarKeys as $k) {
        if (isset($input[$k])) {
            $cfg[$k] = trim((string)$input[$k]);
        }
    }
    foreach ($cfg as $group => $v) {
        if (!is_array($v) || !isset($input[$group]) || !is_array($input[$group])) {
            continue;
        }
        foreach ($v as $key => $old) {
            if (isset($input[$group][$key])) {
                $cfg[$group][$key] = trim((string)$input[$group][$key]);
            }
        }
    }

    $code = "<?php\n"
          . "// 图床配置：由「设置 → 图床」页签维护，control/data/ 不入库，密钥不会进版本库\n"
          . '$qs_imagebed = ' . var_export($cfg, true) . ";\n"
          . "?>\n";
    if (!is_dir(dirname($file))) {
        @mkdir(dirname($file), 0777, true);
    }
    return @file_put_contents($file, $code) !== false;
}

/**
 * 把表单提交值合并成一份配置（不落盘），供「测试上传」用。
 */
function qs_imagebed_config_from($input, $base = null) {
    if (!is_array($input)) {
        return qs_imagebed_config();
    }
    if ($base === null) {
        $base = qs_imagebed_config();
    }
    $cfg = $base;
    $scalarKeys = ['driver', 'on_fail', 'keep_local', 'url_prefix', 'naming', 'path'];
    foreach ($scalarKeys as $k) {
        if (isset($input[$k])) {
            $cfg[$k] = trim((string)$input[$k]);
        }
    }
    foreach ($base as $group => $v) {
        if (!is_array($v) || !isset($input[$group]) || !is_array($input[$group])) {
            continue;
        }
        foreach ($v as $key => $old) {
            if (isset($input[$group][$key])) {
                $cfg[$group][$key] = trim((string)$input[$group][$key]);
            }
        }
    }
    if (!isset($input['keep_local'])) {
        $cfg['keep_local'] = '0';
    }
    return $cfg;
}

/**
 * 驱动清单：name => [标题, 需要的字段]
 * 后台表单据此渲染，新增驱动只要往这里加一项 + 加一个 qs_imagebed_do_xxx()。
 */
function qs_imagebed_drivers() {
    return [
        'local' => ['title' => '仅本地存储（不接外部图床）', 'fields' => []],
        's3' => [
            'title'  => 'S3 兼容（AWS S3 / Cloudflare R2 / 阿里 OSS / 腾讯 COS / MinIO / 七牛 Kodo / B2）',
            'fields' => [
                'endpoint'   => ['终端地址', 'https://xxx.r2.cloudflarestorage.com 或 https://s3.us-east-1.amazonaws.com', 'text'],
                'region'     => ['区域', 'AWS 填 us-east-1 等；R2 填 auto', 'text'],
                'bucket'     => ['存储桶', 'bucket 名称', 'text'],
                'access_key' => ['Access Key', '', 'text'],
                'secret_key' => ['Secret Key', '', 'password'],
                'public_url' => ['公开访问前缀', 'https://cdn.example.com（不含桶名，路径风格时也可含桶名）', 'text'],
                'path_style' => ['路径风格', '1=path style（多数兼容服务需要），0=virtual host', 'select'],
                'acl'        => ['ACL', 'public-read 或留空（R2 不支持该头）', 'text'],
            ],
        ],
        'oss' => [
            'title'  => '阿里云 OSS（原生签名）',
            'fields' => [
                'endpoint'   => ['Endpoint', 'https://oss-cn-beijing.aliyuncs.com', 'text'],
                'bucket'     => ['Bucket', '', 'text'],
                'access_key' => ['AccessKeyId', '', 'text'],
                'secret_key' => ['AccessKeySecret', '', 'password'],
                'public_url' => ['公开访问前缀', 'https://bucket.oss-cn-beijing.aliyuncs.com', 'text'],
            ],
        ],
        'qiniu' => [
            'title'  => '七牛云 Kodo',
            'fields' => [
                'bucket'      => ['空间名', '', 'text'],
                'access_key'  => ['AccessKey', '', 'text'],
                'secret_key'  => ['SecretKey', '', 'password'],
                'public_url'  => ['公开访问前缀', 'https://cdn.example.com', 'text'],
                'upload_host' => ['上传域名', '默认 https://upload.qiniup.com', 'text'],
            ],
        ],
        'upyun' => [
            'title'  => '又拍云',
            'fields' => [
                'bucket'     => ['空间名', '', 'text'],
                'operator'   => ['操作员', '', 'text'],
                'password'   => ['操作员密码', '', 'password'],
                'public_url' => ['公开访问前缀', 'https://xxx.b0.upaiyun.com', 'text'],
                'api_host'   => ['API 域名', '默认 https://v0.api.upyun.com', 'text'],
            ],
        ],
        'github' => [
            'title'  => 'GitHub 仓库（免费图床，适合个人站）',
            'fields' => [
                'owner'      => ['用户名 / 组织', '', 'text'],
                'repo'       => ['仓库名', '', 'text'],
                'branch'     => ['分支', '默认 main', 'text'],
                'token'      => ['Token', '需要 contents:write 权限', 'password'],
                'public_url' => ['公开访问前缀', 'https://cdn.jsdelivr.net/gh/owner/repo@main（不含末尾斜杠）', 'text'],
                'api_host'   => ['API 域名', '默认 https://api.github.com', 'text'],
            ],
        ],
        'chevereto' => [
            'title'  => 'Chevereto（自建图床）',
            'fields' => [
                'base_url' => ['站点地址', 'https://img.example.com', 'text'],
                'key'      => ['API Key', '后台 API 密钥', 'password'],
            ],
        ],
        'lsky' => [
            'title'  => '兰空图床 Lsky Pro',
            'fields' => [
                'base_url' => ['站点地址', 'https://img.example.com', 'text'],
                'token'    => ['Token', '个人 Token（Bearer）', 'password'],
            ],
        ],
        'custom' => [
            'title'  => '自定义 HTTP 接口（任何 JSON 图床都能接）',
            'fields' => [
                'url'           => ['接口地址', 'https://api.example.com/upload', 'text'],
                'method'        => ['请求方法', 'POST 或 PUT', 'select'],
                'file_field'    => ['文件字段名', '默认 file', 'text'],
                'extra_fields'  => ['附加表单字段', '每行 key=value', 'textarea'],
                'extra_headers' => ['附加请求头', '每行 Key: Value', 'textarea'],
                'json_path'     => ['返回取值路径', 'url 或 data.links.url，用点号逐层取', 'text'],
                'public_url'    => ['相对地址前缀', '若接口只返回相对路径，在这里补域名', 'text'],
            ],
        ],
    ];
}

/**
 * 统一 HTTP 请求：优先 curl，没有 curl 时退化为 stream context。
 * 返回 ['ok'=>bool, 'code'=>int, 'body'=>string, 'err'=>string]
 */
function qs_imagebed_http($method, $url, $body = null, $headers = [], $timeout = 20) {
    $method  = strtoupper($method);
    $headers = (array)$headers;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($resp === false) {
            return ['ok' => false, 'code' => $code, 'body' => '', 'err' => $err !== '' ? $err : 'curl failed'];
        }
        return ['ok' => ($code >= 200 && $code < 300), 'code' => $code, 'body' => (string)$resp, 'err' => ''];
    }

    $opts = [
        'method'        => $method,
        'timeout'       => $timeout,
        'ignore_errors' => true,
        'header'        => implode("\r\n", $headers),
    ];
    if ($body !== null) {
        $opts['content'] = $body;
    }
    $resp = @file_get_contents($url, false, stream_context_create(['http' => $opts]));
    if ($resp === false) {
        return ['ok' => false, 'code' => 0, 'body' => '', 'err' => 'request failed (no curl extension)'];
    }
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    return ['ok' => ($code >= 200 && $code < 300), 'code' => $code, 'body' => (string)$resp, 'err' => ''];
}

/**
 * 生成远端对象路径，如 images/2026/11/20261115123045_4821.jpg
 */
function qs_imagebed_object_key($filename, $cfg = null) {
    if ($cfg === null) {
        $cfg = qs_imagebed_config();
    }
    $ext   = strtolower((string)pathinfo($filename, PATHINFO_EXTENSION));
    $base  = (string)pathinfo($filename, PATHINFO_FILENAME);
    $mimeSafe = preg_replace('/[^a-z0-9\-_\.]/i', '', $base);
    if ($mimeSafe === '') {
        $mimeSafe = 'img';
    }

    $naming = (string)($cfg['naming'] ?? 'time');
    if ($naming === 'orig') {
        $name = $mimeSafe;
    } elseif ($naming === 'md5') {
        $name = md5($filename . microtime(true) . mt_rand());
    } else {
        $name = date('YmdHis') . '_' . mt_rand(1000, 9999);
    }
    if ($ext !== '') {
        $name .= '.' . $ext;
    }

    $tpl = (string)($cfg['path'] ?? 'images/{Y}/{m}/');
    if ($tpl === '') {
        $tpl = 'images/{Y}/{m}/';
    }
    $tpl = str_replace(
        ['{Y}', '{m}', '{d}', '{name}', '{md5}', '{ext}'],
        [date('Y'), date('m'), date('d'), pathinfo($name, PATHINFO_FILENAME), md5($name), $ext],
        $tpl
    );
    return trim($tpl, '/') . '/' . $name;
}

/**
 * 拼接公开访问地址
 */
function qs_imagebed_public_url($base, $key) {
    $base = rtrim((string)$base, '/');
    if ($base === '') {
        return '';
    }
    return $base . '/' . ltrim((string)$key, '/');
}

/**
 * 从 JSON 按点号路径取值：url / data.links.url
 */
function qs_imagebed_json_pick($data, $path) {
    $path = trim((string)$path);
    if ($path === '') {
        return null;
    }
    foreach (explode('.', $path) as $seg) {
        if (is_array($data) && array_key_exists($seg, $data)) {
            $data = $data[$seg];
        } elseif (is_object($data) && isset($data->$seg)) {
            $data = $data->$seg;
        } else {
            return null;
        }
    }
    return is_scalar($data) ? (string)$data : null;
}

/**
 * S3 Signature V4：PUT 单对象
 */
function qs_imagebed_s3_put($cfg, $key, $filePath, $mime) {
    $endpoint = rtrim((string)$cfg['endpoint'], '/');
    $bucket   = (string)$cfg['bucket'];
    $ak       = (string)$cfg['access_key'];
    $sk       = (string)$cfg['secret_key'];
    $region   = (string)$cfg['region'] !== '' ? (string)$cfg['region'] : 'auto';
    $pathStyle = (string)($cfg['path_style'] ?? '1') === '1';

    if ($endpoint === '' || $bucket === '' || $ak === '' || $sk === '') {
        return ['ok' => false, 'err' => 'S3 参数不完整'];
    }

    $host = parse_url($endpoint, PHP_URL_HOST);
    $port = parse_url($endpoint, PHP_URL_PORT);
    $scheme = parse_url($endpoint, PHP_URL_SCHEME) ?: 'https';
    if (!$host) {
        return ['ok' => false, 'err' => 'S3 endpoint 无效'];
    }
    $hostHeader = $host . ($port ? ':' . $port : '');

    $encodedKey = implode('/', array_map('rawurlencode', explode('/', trim($key, '/'))));    if ($pathStyle) {
        $url = $scheme . '://' . $hostHeader . '/' . $bucket . '/' . $encodedKey;
        $canonicalUri = '/' . $bucket . '/' . $encodedKey;
    } else {
        $url = $scheme . '://' . $bucket . '.' . $hostHeader . '/' . $encodedKey;
        $canonicalUri = '/' . $encodedKey;
    }

    $payloadHash = hash_file('sha256', $filePath);
    $amzDate = gmdate('Ymd\THis\Z');
    $dateYmd = gmdate('Ymd');
    $regionForSign = ($region === 'auto' || $region === '') ? 'us-east-1' : $region;

    $canonicalHeaders = "host:" . $hostHeader . "\n"
                      . "x-amz-content-sha256:" . $payloadHash . "\n"
                      . "x-amz-date:" . $amzDate . "\n";
    $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
    $canonicalRequest = "PUT\n" . $canonicalUri . "\n\n" . $canonicalHeaders . "\n" . $signedHeaders . "\n" . $payloadHash;

    $scope = $dateYmd . '/' . $regionForSign . '/s3/aws4_request';
    $stringToSign = "AWS4-HMAC-SHA256\n" . $amzDate . "\n" . $scope . "\n" . hash('sha256', $canonicalRequest);

    $kDate    = hash_hmac('sha256', $dateYmd, 'AWS4' . $sk, true);
    $kRegion  = hash_hmac('sha256', $regionForSign, $kDate, true);
    $kService = hash_hmac('sha256', 's3', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    $auth = 'AWS4-HMAC-SHA256 Credential=' . $ak . '/' . $scope
          . ', SignedHeaders=' . $signedHeaders
          . ', Signature=' . $signature;

    $headers = [
        'Host: ' . $hostHeader,
        'x-amz-date: ' . $amzDate,
        'x-amz-content-sha256: ' . $payloadHash,
        'Authorization: ' . $auth,
        'Content-Type: ' . ($mime !== '' ? $mime : 'application/octet-stream'),
        'Content-Length: ' . filesize($filePath),
        'Expect:',
    ];
    $acl = trim((string)($cfg['acl'] ?? ''));
    if ($acl !== '') {
        $headers[] = 'x-amz-acl: ' . $acl;
    }

    $resp = qs_imagebed_http('PUT', $url, file_get_contents($filePath), $headers, 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => 'S3 上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    return ['ok' => true, 'url' => qs_imagebed_public_url($cfg['public_url'], $key)];
}

/**
 * 阿里云 OSS 原生签名 PUT
 */
function qs_imagebed_oss_put($cfg, $key, $filePath, $mime) {
    $endpoint = rtrim((string)$cfg['endpoint'], '/');
    $bucket   = (string)$cfg['bucket'];
    $ak       = (string)$cfg['access_key'];
    $sk       = (string)$cfg['secret_key'];
    if ($endpoint === '' || $bucket === '' || $ak === '' || $sk === '') {
        return ['ok' => false, 'err' => 'OSS 参数不完整'];
    }

    $object   = '/' . $bucket . '/' . implode('/', array_map('rawurlencode', explode('/', trim($key, '/'))));
    $date     = gmdate('D, d M Y H:i:s') . ' GMT';
    $ossMime  = $mime !== '' ? $mime : 'application/octet-stream';
    $signStr  = "PUT\n\n" . $ossMime . "\n" . $date . "\n" . $object;
    $sign     = base64_encode(hash_hmac('sha1', $signStr, $sk, true));

    $url = $endpoint . $object;
    $headers = [
        'Date: ' . $date,
        'Content-Type: ' . $ossMime,
        'Content-Length: ' . filesize($filePath),
        'Authorization: OSS ' . $ak . ':' . $sign,
        'Expect:',
    ];
    $resp = qs_imagebed_http('PUT', $url, file_get_contents($filePath), $headers, 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => 'OSS 上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    return ['ok' => true, 'url' => qs_imagebed_public_url($cfg['public_url'], $key)];
}

/**
 * 七牛云表单上传
 */
function qs_imagebed_qiniu_put($cfg, $key, $filePath, $mime) {
    $bucket = (string)$cfg['bucket'];
    $ak     = (string)$cfg['access_key'];
    $sk     = (string)$cfg['secret_key'];
    if ($bucket === '' || $ak === '' || $sk === '') {
        return ['ok' => false, 'err' => '七牛参数不完整'];
    }
    $policy = json_encode(['scope' => $bucket . ':' . $key, 'deadline' => time() + 3600]);
    $encodedPolicy = rtrim(strtr(base64_encode($policy), '+/', '-_'), '=');
    $sign = rtrim(strtr(base64_encode(hash_hmac('sha1', $encodedPolicy, $sk, true)), '+/', '-_'), '=');
    $token = $ak . ':' . $sign . ':' . $encodedPolicy;

    $post = [
        'token' => $token,
        'key'   => $key,
        'file'  => new CURLFile($filePath, $mime !== '' ? $mime : 'application/octet-stream', basename($filePath)),
    ];
    $host = rtrim((string)$cfg['upload_host'], '/');
    if ($host === '') {
        $host = 'https://upload.qiniup.com';
    }
    $resp = qs_imagebed_http('POST', $host . '/', $post, [], 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => '七牛上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    $json = json_decode($resp['body'], true);
    if (!is_array($json) || !empty($json['error'])) {
        return ['ok' => false, 'err' => '七牛返回错误：' . substr($resp['body'], 0, 200)];
    }
    $finalKey = isset($json['key']) && $json['key'] !== '' ? (string)$json['key'] : $key;
    return ['ok' => true, 'url' => qs_imagebed_public_url($cfg['public_url'], $finalKey)];
}

/**
 * 又拍云表单上传
 */
function qs_imagebed_upyun_put($cfg, $key, $filePath, $mime) {
    $bucket   = (string)$cfg['bucket'];
    $operator = (string)$cfg['operator'];
    $password = (string)$cfg['password'];
    if ($bucket === '' || $operator === '' || $password === '') {
        return ['ok' => false, 'err' => '又拍云参数不完整'];
    }
    $policyArr = ['bucket' => $bucket, 'save-key' => '/' . ltrim($key, '/'), 'expiration' => time() + 3600];
    $policy    = base64_encode(json_encode($policyArr));
    $signature = md5($policy . '&' . $password);
    $auth      = $operator . ':' . $signature;

    $post = [
        'policy'        => $policy,
        'authorization' => $auth,
        'file'          => new CURLFile($filePath, $mime !== '' ? $mime : 'application/octet-stream', basename($filePath)),
    ];
    $host = rtrim((string)$cfg['api_host'], '/');
    if ($host === '') {
        $host = 'https://v0.api.upyun.com';
    }
    $resp = qs_imagebed_http('POST', $host . '/' . $bucket, $post, [], 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => '又拍云上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    $json = json_decode($resp['body'], true);
    $okCode = isset($json['code']) ? (int)$json['code'] : 0;
    if ($okCode !== 200) {
        return ['ok' => false, 'err' => '又拍云返回码 ' . $okCode . ' ' . substr($resp['body'], 0, 200)];
    }
    return ['ok' => true, 'url' => qs_imagebed_public_url($cfg['public_url'], $key)];
}

/**
 * GitHub Contents API 上传
 */
function qs_imagebed_github_put($cfg, $key, $filePath, $mime) {
    $owner = (string)$cfg['owner'];
    $repo  = (string)$cfg['repo'];
    $token = (string)$cfg['token'];
    $branch = (string)$cfg['branch'] !== '' ? (string)$cfg['branch'] : 'main';
    if ($owner === '' || $repo === '' || $token === '') {
        return ['ok' => false, 'err' => 'GitHub 参数不完整'];
    }
    $apiHost = rtrim((string)$cfg['api_host'], '/');
    if ($apiHost === '') {
        $apiHost = 'https://api.github.com';
    }
    $url = $apiHost . '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo)
         . '/contents/' . implode('/', array_map('rawurlencode', explode('/', trim($key, '/'))));

    $body = json_encode([
        'message' => 'upload ' . basename($key),
        'content' => base64_encode(file_get_contents($filePath)),
        'branch'  => $branch,
    ]);
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/vnd.github+json',
        'Content-Type: application/json',
        'User-Agent: QScms-ImageBed',
    ];
    $resp = qs_imagebed_http('PUT', $url, $body, $headers, 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => 'GitHub 上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    $publicBase = trim((string)$cfg['public_url']);
    if ($publicBase !== '') {
        return ['ok' => true, 'url' => qs_imagebed_public_url($publicBase, $key)];
    }
    $json = json_decode($resp['body'], true);
    $dl = qs_imagebed_json_pick($json, 'content.download_url');
    return ['ok' => $dl !== null && $dl !== '', 'url' => (string)$dl, 'err' => '返回中取不到 download_url'];
}

/**
 * Chevereto API v1
 */
function qs_imagebed_chevereto_put($cfg, $key, $filePath, $mime) {
    $base = rtrim((string)$cfg['base_url'], '/');
    $key_ = (string)$cfg['key'];
    if ($base === '') {
        return ['ok' => false, 'err' => 'Chevereto 站点地址为空'];
    }
    $url = $base . '/api/1/upload?format=json';
    if ($key_ !== '') {
        $url .= '&key=' . rawurlencode($key_);
    }
    $post = ['source' => new CURLFile($filePath, $mime !== '' ? $mime : 'image/jpeg', basename($filePath))];
    $resp = qs_imagebed_http('POST', $url, $post, [], 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => 'Chevereto 上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    $json = json_decode($resp['body'], true);
    $out  = qs_imagebed_json_pick($json, 'image.url');
    if ($out === null) {
        $out = qs_imagebed_json_pick($json, 'image.display_url');
    }
    return ['ok' => $out !== null && $out !== '', 'url' => (string)$out, 'err' => 'Chevereto 返回中取不到图片地址：' . substr($resp['body'], 0, 200)];
}

/**
 * 兰空图床 Lsky Pro v1
 */
function qs_imagebed_lsky_put($cfg, $key, $filePath, $mime) {
    $base  = rtrim((string)$cfg['base_url'], '/');
    $token = (string)$cfg['token'];
    if ($base === '' || $token === '') {
        return ['ok' => false, 'err' => '兰空参数不完整'];
    }
    $post = ['file' => new CURLFile($filePath, $mime !== '' ? $mime : 'image/jpeg', basename($filePath))];
    $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    $resp = qs_imagebed_http('POST', $base . '/api/v1/upload', $post, $headers, 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => '兰空上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }
    $json = json_decode($resp['body'], true);
    $out = qs_imagebed_json_pick($json, 'data.links.url');
    if ($out === null) {
        $out = qs_imagebed_json_pick($json, 'data.url');
    }
    return ['ok' => $out !== null && $out !== '', 'url' => (string)$out, 'err' => '兰空返回中取不到图片地址：' . substr($resp['body'], 0, 200)];
}

/**
 * 自定义 HTTP 接口
 */
function qs_imagebed_custom_put($cfg, $key, $filePath, $mime) {
    $url = trim((string)$cfg['url']);
    if ($url === '') {
        return ['ok' => false, 'err' => '自定义接口地址为空'];
    }
    $method = strtoupper((string)$cfg['method']);
    if ($method !== 'PUT' && $method !== 'POST') {
        $method = 'POST';
    }
    $fileField = trim((string)$cfg['file_field']);
    if ($fileField === '') {
        $fileField = 'file';
    }

    $headers = [];
    $rawHeaders = (string)$cfg['extra_headers'];
    foreach (preg_split('/\r?\n/', $rawHeaders) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, ':') === false) {
            continue;
        }
        $headers[] = $line;
    }

    $fields = [];
    $rawFields = (string)$cfg['extra_fields'];
    foreach (preg_split('/\r?\n/', $rawFields) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '=') === false) {
            continue;
        }
        list($k, $v) = explode('=', $line, 2);
        $fields[trim($k)] = trim($v);
    }
    $fields[$fileField] = new CURLFile($filePath, $mime !== '' ? $mime : 'application/octet-stream', basename($filePath));

    $resp = qs_imagebed_http($method, $url, $fields, $headers, 40);
    if (!$resp['ok']) {
        return ['ok' => false, 'err' => '自定义接口上传失败 HTTP ' . $resp['code'] . ' ' . substr($resp['body'], 0, 200)];
    }

    $json = json_decode($resp['body'], true);
    if (!is_array($json) && !is_object($json)) {
        // 纯文本返回：整段当作 URL
        $plain = trim($resp['body']);
        if (preg_match('#^https?://[^\s"<>]+$#i', $plain)) {
            return ['ok' => true, 'url' => $plain];
        }
        return ['ok' => false, 'err' => '自定义接口返回不是 JSON，也无法当作地址：' . substr($resp['body'], 0, 200)];
    }
    $out = qs_imagebed_json_pick($json, (string)$cfg['json_path']);
    if ($out === null || $out === '') {
        return ['ok' => false, 'err' => '按路径 ' . $cfg['json_path'] . ' 取不到地址：' . substr($resp['body'], 0, 200)];
    }
    if (strpos($out, 'http://') !== 0 && strpos($out, 'https://') !== 0) {
        $prefix = trim((string)$cfg['public_url']);
        $out = $prefix !== '' ? qs_imagebed_public_url($prefix, $out) : $out;
    }
    return ['ok' => true, 'url' => $out];
}

/**
 * 统一入口：把本地文件传到当前配置的图床。
 * 返回 ['ok'=>bool, 'url'=>string, 'err'=>string, 'driver'=>string, 'key'=>string]
 */
function qs_imagebed_upload($filePath, $filename, $mime = '', $cfg = null) {
    if ($cfg === null) {
        $cfg = qs_imagebed_config();
    }
    $driver = (string)($cfg['driver'] ?? 'local');

    if ($driver === '' || $driver === 'local' || !is_file($filePath)) {
        return ['ok' => false, 'url' => '', 'err' => 'driver=local', 'driver' => 'local', 'key' => ''];
    }

    $key  = qs_imagebed_object_key($filename, $cfg);
    $args = [$cfg[$driver] ?? [], $key, $filePath, $mime];

    switch ($driver) {
        case 's3':       $res = qs_imagebed_s3_put(...$args); break;
        case 'oss':      $res = qs_imagebed_oss_put(...$args); break;
        case 'qiniu':    $res = qs_imagebed_qiniu_put(...$args); break;
        case 'upyun':    $res = qs_imagebed_upyun_put(...$args); break;
        case 'github':   $res = qs_imagebed_github_put(...$args); break;
        case 'chevereto':$res = qs_imagebed_chevereto_put(...$args); break;
        case 'lsky':     $res = qs_imagebed_lsky_put(...$args); break;
        case 'custom':   $res = qs_imagebed_custom_put(...$args); break;
        default:         $res = ['ok' => false, 'err' => '未知驱动：' . $driver];
    }

    return [
        'ok'     => (bool)($res['ok'] ?? false),
        'url'    => (string)($res['url'] ?? ''),
        'err'    => (string)($res['err'] ?? ''),
        'driver' => $driver,
        'key'    => $key,
    ];
}

/**
 * 旧图外链：把形如 /upload/month_2611/xxx.jpg 的本地路径换成配置的图床前缀。
 * 后台「图片床地址」为空时原样返回。
 */
function qs_imagebed_rewrite($src) {
    $src = (string)$src;
    if ($src === '') {
        return $src;
    }
    if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0 || strpos($src, '//') === 0) {
        return $src;
    }
    $prefix = qs_imagebed_url_prefix();
    if ($prefix === '') {
        return $src;
    }
    $path = preg_replace('#^(?:\.\./)+#', '', $src);
    $path = ltrim($path, '/');
    if ($path === '') {
        return $src;
    }
    return $prefix . '/' . $path;
}

/**
 * 取旧图外链前缀（空表示不改写）
 */
function qs_imagebed_url_prefix() {
    $cfg = qs_imagebed_config();
    return rtrim(trim((string)($cfg['url_prefix'] ?? '')), '/');
}

/**
 * 整段 HTML 改写：把正文里 src/href 指向本地上传目录的地址换成图床前缀。
 * 未配置前缀时原样返回，零开销。
 */
function qs_imagebed_rewrite_html($html) {
    $html = (string)$html;
    if ($html === '') {
        return $html;
    }
    $prefix = qs_imagebed_url_prefix();
    if ($prefix === '') {
        return $html;
    }
    return preg_replace_callback(
        '#(src|href)\s*=\s*(["\'])((?:\.\./)*/?upload/[^"\'<>]+)\2#i',
        function ($m) use ($prefix) {
            $path = preg_replace('#^(?:\.\./)+#', '', $m[3]);
            return $m[1] . '=' . $m[2] . $prefix . '/' . ltrim($path, '/') . $m[2];
        },
        $html
    );
}

/**
 * 是否真的接了外部图床
 */
function qs_imagebed_enabled() {
    $cfg = qs_imagebed_config();
    $driver = (string)($cfg['driver'] ?? 'local');
    return $driver !== '' && $driver !== 'local';
}
