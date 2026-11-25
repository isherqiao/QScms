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

/*
 * ============================================================================
 *  接口契约（给调用方 / AI 看的说明，只对本机文件可见，HTTP 访问只会执行、不会输出源码）
 * ============================================================================
 *  入口：POST  /admin/AI/publish.php
 *  鉴权：请求头 X-QSIS-AI-KEY: <密钥>（形如 QsisAI-xxxx）
 *        也兼容 Authorization: Bearer <密钥>，但 Apache 下常取不到，以 X-QSIS-AI-KEY 为准
 *  编码：Content-Type: application/json; charset=utf-8，请求体 JSON（表单也可，字段名一致）
 *
 *  请求字段（键名 → 说明）
 *    action       选填  create（默认，新建一篇）/ update（按 id 覆盖已有文章）
 *    title        新建必填  标题；同时用于生成文章 id（shorturl()）
 *    content      新建必填  正文，可含 HTML
 *    summary      选填  摘要
 *    tags         选填  关键词；JSON 数组 ["a","b"] 或逗号/空格分隔字符串
 *    state        选填  publish（默认，前台可见）/ draft（草稿）
 *    date         选填  YYYY-MM-DD，默认当天
 *    time         选填  HH:MM 或 HH:MM:SS，默认当前时间
 *    id           选填  新建时可自定义文章 id（仅字母数字 - _），已存在返回 409
 *                 必填  action=update 时，指要改的那篇文章；不存在返回 404
 *    author       选填  作者【登录名 username】，不是显示名；默认取本站超级管理员登录名（本机为 admin）
 *                      传显示名（如「管理员」）会 422；落库存登录名，前台由 qs_the_user() 映射显示名
 *    can_comment  选填  1（默认）/ 0
 *
 *  action=update 的覆盖语义
 *    只覆盖请求里出现过的字段，未出现的字段保留原值；
 *    title / content 若出现则不能为空字符串；state 变化会同步在两个状态索引间迁移。
 *
 *  成功返回（code=200）
 *    { "code":200, "msg":"文章已写入" | "文章已更新",
 *      "data":{ "id", "state", "title", "author", "date", "time", "tags":[], "url" } }
 *    url = site_link + '/?' + sitemode + '/' + id + url_ends（本机例：http://127.0.0.1:90/?article/<id>.qshtml）
 *
 *  失败返回（code 与 HTTP 状态码一致，body 为 {code,msg}）
 *    400  请求体为空或不是 JSON/表单
 *    401  密钥校验失败
 *    403  接口未开启，或密钥尚未生成（ai_publish_key 为空）
 *    404  action=update 指定的 id 不存在
 *    409  新建时指定的 id 已存在
 *    422  标题/正文为空、action 非法、author 非登录名、state 非法、日期时间格式错、命中屏蔽词
 *    500  文章目录不可用、索引或正文写入失败
 * ============================================================================
 */

require_once __DIR__ . '/../../control/core.php';


date_default_timezone_set('Asia/Shanghai');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

/**
 * 输出 JSON 并结束请求
 *
 * @param int        $code 业务状态码（同时作为 HTTP 状态码）
 * @param string     $msg  说明
 * @param array|null $data 数据
 * @return void
 */
function ai_publish_out(int $code, string $msg, ?array $data = null): void
{
    http_response_code($code >= 400 ? $code : 200);
    $body = ['code' => $code, 'msg' => $msg];
    if ($data !== null) {
        $body['data'] = $data;
    }
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * 读取一篇已存在文章的完整记录
 *
 * 正文落盘格式为 "<?php //" . serialize($data) . "?>"。
 *
 * @param string $post_root 文章根目录（含 data/ 与 index/）
 * @param string $post_id   文章 id
 * @return array|null 读不到返回 null
 */
function ai_load_post(string $post_root, string $post_id): ?array
{
    $file = $post_root . 'data/' . $post_id . '.php';
    if (!is_file($file)) {
        return null;
    }
    $raw = (string) file_get_contents($file);
    $prefix = '<?php //';
    if (strncmp($raw, $prefix, strlen($prefix)) !== 0) {
        return null;
    }
    $raw = substr($raw, strlen($prefix));
    if (substr($raw, -2) === '?>') {
        $raw = substr($raw, 0, -2);
    }
    $data = @unserialize($raw);
    return is_array($data) ? $data : null;
}

// ---- 1. 接口开关与密钥 ----
$ai_mode = $qs_config['ai_publish'] ?? 'close';
$ai_key_hash = $qs_config['ai_publish_key'] ?? '';
if ($ai_mode !== 'open' || $ai_key_hash === '') {
    ai_publish_out(403, 'AI 发文接口未启用，或密钥尚未生成');
}

$client_key = $_SERVER['HTTP_X_QSIS_AI_KEY'] ?? '';
if ($client_key === '') {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (is_string($auth) && preg_match('/^Bearer\s+(.+)$/i', trim($auth), $m)) {
        $client_key = trim($m[1]);
    }
}
if ($client_key === '' || !hash_equals($ai_key_hash, hash('sha256', $client_key))) {
    ai_publish_out(401, '密钥校验失败');
}

// ---- 2. 读取请求体（JSON 或表单） ----
$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}
if (!is_array($payload) || $payload === []) {
    ai_publish_out(400, '请求体为空，需为 JSON 或表单');
}

// ---- 3. 字段整理 ----
$action = strtolower(trim((string) ($payload['action'] ?? 'create')));
if (!in_array($action, ['create', 'update'], true)) {
    ai_publish_out(422, 'action 只支持 create 或 update');
}

$title = trim((string) ($payload['title'] ?? ''));
$summary = trim((string) ($payload['summary'] ?? ''));
$content = trim((string) ($payload['content'] ?? ''));

$post_root = rtrim(SITE_ROOT . '/' . ($qs_config['qs_post_dir'] ?? 'data/posts/'), '/') . '/';
if (!is_dir($post_root . 'data') || !is_dir($post_root . 'index')) {
    ai_publish_out(500, '文章目录不可用：' . $post_root);
}

$requested_id = trim((string) ($payload['id'] ?? ''));
$requested_id = preg_replace('/[^A-Za-z0-9_-]/', '', $requested_id);

// 更新模式：先确认文章存在，再谈改什么
$existing = null;
if ($action === 'update') {
    if ($requested_id === '') {
        ai_publish_out(422, 'action=update 时必须提供 id');
    }
    $existing = ai_load_post($post_root, $requested_id);
    if ($existing === null) {
        ai_publish_out(404, '文章不存在：' . $requested_id);
    }
}

// 必填校验：新建要求标题与正文都有；更新只要求"传了就不许空"
if ($title === '' && ($action === 'create' || array_key_exists('title', $payload))) {
    ai_publish_out(422, '标题不能为空');
}
if ($content === '' && ($action === 'create' || array_key_exists('content', $payload))) {
    ai_publish_out(422, '正文内容不能为空');
}

$raw_tags = $payload['tags'] ?? [];
$tags_given = array_key_exists('tags', $payload);
if (is_string($raw_tags)) {
    $raw_tags = preg_split('/[,#\x{ff0c}\s]+/u', trim($raw_tags), -1, PREG_SPLIT_NO_EMPTY);
}
$tags = [];
if (is_array($raw_tags)) {
    foreach ($raw_tags as $t) {
        $t = trim((string) $t);
        if ($t !== '' && !in_array($t, $tags, true)) {
            $tags[] = $t;
        }
    }
}

$state_given = array_key_exists('state', $payload);
if ($state_given) {
    $state = strtolower(trim((string) ($payload['state'] ?? '')));
    if (!in_array($state, ['publish', 'draft'], true)) {
        ai_publish_out(422, 'state 只支持 publish 或 draft');
    }
} else {
    $state = $action === 'update'
        ? (string) ($existing['state'] ?? 'publish')
        : 'publish';
    if (!in_array($state, ['publish', 'draft'], true)) {
        $state = 'publish';
    }
}

$date_given = array_key_exists('date', $payload);
$date = trim((string) ($payload['date'] ?? ''));
if ($date_given) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        ai_publish_out(422, 'date 格式应为 YYYY-MM-DD');
    }
} else {
    $date = $action === 'update'
        ? (string) ($existing['date'] ?? date('Y-m-d'))
        : date('Y-m-d');
}

$time_given = array_key_exists('time', $payload);
$time = trim((string) ($payload['time'] ?? ''));
if ($time_given) {
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
        ai_publish_out(422, 'time 格式应为 HH:MM 或 HH:MM:SS');
    }
    if (strlen($time) === 5) {
        $time .= ':00';
    }
} else {
    $time = $action === 'update'
        ? (string) ($existing['time'] ?? date('H:i:s'))
        : date('H:i:s');
}

// 作者只认登录名（username），不是显示名：文章记录存登录名，前台由 qs_the_user() 映射成显示名
$admin_username = trim((string) ($qs_config['user_name'] ?? ''));
if ($admin_username === '') {
    $admin_username = 'admin';
}
global $sp_config_users;
$valid_users = [$admin_username];
if (isset($sp_config_users) && is_array($sp_config_users)) {
    foreach (array_keys($sp_config_users) as $one_user) {
        $one_user = trim((string) $one_user);
        if ($one_user !== '') {
            $valid_users[] = $one_user;
        }
    }
}
$valid_users = array_values(array_unique($valid_users));

$author_given = array_key_exists('author', $payload);
$author = trim((string) ($payload['author'] ?? ''));
if ($author === '') {
    $author_given = false;
    $author = $action === 'update'
        ? (string) ($existing['user'] ?? $admin_username)
        : $admin_username;
} elseif (!in_array($author, $valid_users, true)) {
    ai_publish_out(422, 'author 必须是登录名，可选：' . implode('、', $valid_users));
}

$can_comment_given = array_key_exists('can_comment', $payload);
$can_comment = (string) ($payload['can_comment'] ?? '1');
$can_comment = in_array($can_comment, ['0', '1'], true) ? $can_comment : '1';

// ---- 4. 生成 id（新建）或复用 id（更新） ----
if ($action === 'update') {
    $post_id = $requested_id;
} elseif ($requested_id !== '') {
    if (is_file($post_root . 'data/' . $requested_id . '.php')) {
        ai_publish_out(409, '指定 id 已存在：' . $requested_id);
    }
    $post_id = $requested_id;
} else {
    $candidates = function_exists('shorturl') ? shorturl($title) : [];
    $post_id = '';
    foreach ((array) $candidates as $candidate) {
        $candidate = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $candidate);
        if ($candidate !== '' && !is_file($post_root . 'data/' . $candidate . '.php')) {
            $post_id = $candidate;
            break;
        }
    }
    if ($post_id === '') {
        ai_publish_out(500, '未能生成可用的文章 id');
    }
}

// ---- 5. 组装记录（更新时以原记录为底，只覆盖请求里出现的字段） ----
if ($action === 'update') {
    $data = $existing;
    if (array_key_exists('title', $payload)) {
        $data['title'] = $title;
    }
    if (array_key_exists('summary', $payload)) {
        $data['summary'] = $summary;
    }
    if (array_key_exists('content', $payload)) {
        $data['content'] = $content;
    }
    if ($tags_given) {
        $data['tags'] = $tags;
    }
    $data['state'] = $state;
    if ($date_given) {
        $data['date'] = $date;
    }
    if ($time_given) {
        $data['time'] = $time;
    }
    if ($author_given) {
        $data['user'] = $author;
    }
    if ($can_comment_given) {
        $data['can_comment'] = $can_comment;
    }
    $data['id'] = $post_id;
} else {
    $data = [
        'id' => $post_id,
        'state' => $state,
        'title' => $title,
        'title_2nd' => '',
        'tags' => $tags,
        'tags_2nd' => [],
        'date' => $date,
        'time' => $time,
        'user' => $author,
        'can_comment' => $can_comment,
        'video' => '',
        'summary' => $summary,
        'content' => $content,
        'content_2nd' => '',
        'download_size' => '',
        'download' => '',
        'post_passwd' => '',
    ];
}

// 屏蔽词校验，与后台保存口径一致（用最终生效的标题与正文）
if (function_exists('qs_keyword_check')) {
    $hits = qs_keyword_check((string) ($data['title'] ?? ''), (string) ($data['content'] ?? ''));
    if (!empty($hits)) {
        ai_publish_out(422, '内容命中屏蔽词：' . implode('、', array_keys((array) $hits)));
    }
}

// ---- 6. 写入索引与正文（结构对齐 admin/post-edit.php） ----
/**
 * 读取某个状态的索引数组（索引文件内部以 $qs_posts 变量返回）
 *
 * @param string $file 索引文件路径
 * @return array
 */
function ai_load_index(string $file): array
{
    $qs_posts = [];
    if (is_file($file)) {
        require $file;
    }
    return is_array($qs_posts) ? $qs_posts : [];
}

$index_file = $post_root . 'index/' . $state . '.php';
$qs_posts = ai_load_index($index_file);

// 同一 id 若已存在于其它状态索引，先移除，避免两处重复
foreach (['publish', 'draft'] as $other_state) {
    if ($other_state === $state) {
        continue;
    }
    $other_file = $post_root . 'index/' . $other_state . '.php';
    if (!is_file($other_file)) {
        continue;
    }
    $other_posts = ai_load_index($other_file);
    if (isset($other_posts[$post_id])) {
        unset($other_posts[$post_id]);
        file_put_contents(
            $other_file,
            "<?php\n\$qs_posts=" . var_export($other_posts, true) . "\n?>",
            LOCK_EX
        );
    }
}

$qs_posts[$post_id] = $data;
if (function_exists('post_sort')) {
    uasort($qs_posts, 'post_sort');
}
$index_written = file_put_contents(
    $index_file,
    "<?php\n\$qs_posts=" . var_export($qs_posts, true) . "\n?>",
    LOCK_EX
);
if ($index_written === false) {
    ai_publish_out(500, '索引写入失败');
}

$content_file = $post_root . 'data/' . $post_id . '.php';
$data_written = file_put_contents($content_file, "<?php //" . serialize($data) . "?>", LOCK_EX);
if ($data_written === false) {
    ai_publish_out(500, '正文写入失败');
}

// ---- 7. 重新生成 feeds，与后台保存行为一致 ----
if (is_file(__DIR__ . '/../qs_feeds.php')) {
    require_once __DIR__ . '/../qs_feeds.php';
    if (function_exists('qs_feeds_regenerate')) {
        qs_feeds_regenerate();
    }
}

// ---- 8. 返回 ----
$site_link = rtrim((string) ($qs_config['site_link'] ?? ''), '/');
$sitemode = (string) ($qs_config['sitemode'] ?? 'article');
$url_ends = (string) ($qs_config['url_ends'] ?? '');
ai_publish_out(200, $action === 'update' ? '文章已更新' : '文章已写入', [
    'id' => $post_id,
    'state' => $state,
    'title' => (string) ($data['title'] ?? ''),
    'author' => (string) ($data['user'] ?? ''),
    'date' => (string) ($data['date'] ?? ''),
    'time' => (string) ($data['time'] ?? ''),
    'tags' => (array) ($data['tags'] ?? []),
    'url' => $site_link . '/' . '?' . $sitemode . '/' . $post_id . $url_ends,
]);
