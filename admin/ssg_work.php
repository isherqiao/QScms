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
 * @File         ssg_work.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 15/10/22 16:39:45
 * @Description  Static site generator: renders pages and posts through the static theme, using the SSG menu and language settings, and writes the output tree.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

session_status() === PHP_SESSION_NONE && session_start();
date_default_timezone_set('Asia/Shanghai');
ini_set("display_errors", "On");
error_reporting(E_ALL);
require_once '../control/verify.php';
require_once '../control/data/conf.php';
require_once '../control/data/static_config.php';
// 页脚/隐私渲染库（与动态站共用同一份实现）
require_once __DIR__ . '/../control/footer_privacy_lib.php';
// 收款打赏渲染库（与动态站共用同一份实现）
require_once __DIR__ . '/../control/payment_lib.php';
// 投票数据层（静态站渲染文章内嵌投票卡片时读取投票数据，与动态站同一份实现）
require_once __DIR__ . '/../control/vote_lib.php';
// Cookie 同意组件（静态站与动态站共用同一份渲染器）
require_once __DIR__ . '/../files/cookie-consent/consent.php';
// 静态站没有任何后端：组件只按静态规则输出（隐私页用真实目录，不给路由式链接）
if (!defined('QS_CONSENT_STATIC')) {
    define('QS_CONSENT_STATIC', true);
}
// 静态站菜单配置（与动态站 menu_conf.php 完全独立，仅覆盖 $qs_menu / $qs_side_menu）
if (file_exists('../control/data/ssg_menu_conf.php')) {
    require_once '../control/data/ssg_menu_conf.php';
}
// ========= SSG单独复制L() UI()，不再引入完整functions.php =========
if (!isset($language_dir)) {
    $language_dir = dirname(__DIR__) . '/files/language/admin/';
}
if (!isset($UI_language_dir)) {
    $UI_language_dir = dirname(__DIR__) . '/files/language/UI/';
}

/**
 * 加载语言包（核心函数 L()） 从functions.php复制，用于SSG独立运行
 * @param string $key 语言键名
 * @return string 翻译后的文本
 */
function L($key) {
    global $qs_config, $language_dir;
    $lang_file = '';
    $language_name = '';
    // 1. 优先使用配置文件指定的语言
    if (isset($qs_config['language_name']) && $qs_config['language_name'] !== '') {
        $lang_file = $language_dir . $qs_config['language_file'];
        $language_name = $qs_config['language_name'];
    } else {
        // 2. SSG脚本环境，无浏览器HTTP_ACCEPT_LANGUAGE，强制回落到zh_CN
        $user_lang = 'zh_CN';
    }
    // 5. 加载语言文件（不存在则 fallback 到英文）
    if (file_exists($lang_file)) {
        $translations = include($lang_file);
    } else {
        $lang_file = $language_dir . 'lang_en.php';
        $translations = file_exists($lang_file) ? include($lang_file) : [];
    }
    return isset($translations[$key]) ? $translations[$key] : $key;
}

/**
 * 加载UI语言包（UI()函数，从functions.php复制，用于SSG独立运行）
 */
function UI($key) {
    global $qs_config, $language_dir, $UI_language_dir;
    $lang_file = '';
    // SSG无浏览器请求头，强制简体中文
    $user_lang = 'zh_CN';
    $lang_file = $UI_language_dir . 'lang_' . $user_lang . '.php';

    if (file_exists($lang_file)) {
        $translations = include($lang_file);
    } else {
        $lang_file = $UI_language_dir . 'lang_en.php';
        $translations = file_exists($lang_file) ? include($lang_file) : [];
    }
    return isset($translations[$key]) ? $translations[$key] : $key;
}
// ======================================================

//=====【新增：加载广告数据】=====
$adsFile = '../control/data/ads.php';
$adsConfigFile = '../control/data/ads_config.php';
$ads = [];
if (file_exists($adsFile)) {
    $ads = require $adsFile;
}
$adsConfig = [];
if (file_exists($adsConfigFile)) {
    $adsConfig = require $adsConfigFile;
}
if (!qs_Pass()) {
    header("Location:/?logout/");
    exit;
}
$logspath = '../control/data/logs/';
define('DATA_LOG_PATH', $logspath);

// ========== 核心改造：定义站点域名 ==========
define('SITE_DOMAIN', isset($static_config['static_root_url']) ? rtrim($static_config['static_root_url'], '/') : 'shturl.cc/nIT1WGH4E8Woa');
$static_output = isset($static_config['static_output']) ? $static_config['static_output'] : '';
define('STATIC_ROOT_URL', SITE_DOMAIN);
// 主题目录兜底：静态主题与动态主题已合并为同一套（files/theme）
// 配置里若仍指向旧的 files/static_theme，或目录不存在，一律回落到 files/theme
if (!isset($static_config['static_theme_dir']) || !is_dir(dirname(__DIR__) . '/' . ltrim(str_replace(['../', '\\'], ['', '/'], (string)$static_config['static_theme_dir']), '/'))) {
    $static_config['static_theme_dir'] = dirname(__DIR__) . '/files/theme/';
}
// ========== 增量构建哈希缓存 新增函数 ==========
/**
 * 计算字符串SHA256哈希
 */
function str_sha256(string $str): string
{
    return hash('sha256', $str);
}
/**
 * 读取整个文件内容计算哈希，文件不存在返回空字符串
 */
function file_sha256(string $filePath): string
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return '';
    }
    return str_sha256(file_get_contents($filePath));
}
/**
 * 加载构建缓存 build_cache.json
 */
function load_build_cache(string $cacheFile): array
{
    if (!file_exists($cacheFile)) {
        return [
            'posts_file_hash' => [],
            'pages_file_hash' => [],
            'template_hash' => [],
            'refresh_categories' => [],
            'refresh_tags' => []
        ];
    }
    $raw = file_get_contents($cacheFile);
    return json_decode($raw, true) ?: [
        'posts_file_hash' => [],
        'pages_file_hash' => [],
        'template_hash' => [],
        'refresh_categories' => [],
        'refresh_tags' => []
    ];
}
/**
 * 保存构建缓存
 */
function save_build_cache(string $cacheFile, array $data): void
{
    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
/**
 * 安全写入文件：内容完全一致则不写入，避免mtime变动
 */
function safe_file_put_contents(string $file, string $content): bool
{
    // 目标目录不存在时先建，避免 "Failed to open stream" 之类的写入失败
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        error_log('SSG: 目录创建失败 → ' . $dir);
        return false;
    }
    // 文件不存在直接写入
    if (!file_exists($file)) {
        $ok = @file_put_contents($file, $content);
        if ($ok === false) { error_log('SSG: 写入失败 → ' . $file); }
        return $ok !== false;
    }
    // 内容一模一样，跳过写入
    $oldContent = @file_get_contents($file);
    if ($oldContent === $content) {
        return false;
    }
    // 内容不同，正常覆盖
    $ok = @file_put_contents($file, $content);
    if ($ok === false) { error_log('SSG: 写入失败 → ' . $file); return false; }
    return true;
}
// ========== 增量函数结束 ==========
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= L('Ssg_Page_Title'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Microsoft Yahei', Arial, sans-serif;
        }
        body {
            background-color: #f5f7fa;
            padding: 20px 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 8px;
        }
        .header .version {
            color: #666;
            font-size: 14px;
        }
        .log-item {
            margin: 8px 0;
            padding: 6px 10px;
            border-radius: 4px;
            line-height: 1.6;
        }
        .log-info {
            color: #3498db;
            background-color: #f0f7ff;
        }
        .log-success {
            color: #27ae60;
            background-color: #f0fff4;
        }
        .log-warning {
            color: #f39c12;
            background-color: #fffbf0;
        }
        .log-error {
            color: #e74c3c;
            background-color: #fff0f0;
        }
        .stats {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        .stats h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .stats ul {
            list-style: none;
            padding-left: 10px;
        }
        .stats li {
            margin: 5px 0;
            color: #555;
        }
        .final-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f4fd;
            border-radius: 6px;
            text-align: center;
            color: #2980b9;
            font-size: 18px;
            font-weight: bold;
        }
        .menu-main {
            margin: 15px 0;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
        }
        .menu-main > ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .menu-main li {
            position: relative;
        }
        .menu-main li a {
            text-decoration: none;
            color: #2c3e50;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .menu-main li a:hover {
            background: #e8f4fd;
            color: #2980b9;
        }
        .menu-main .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 10px;
            min-width: 150px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 999;
        }
        .menu-main .submenu li {
            padding: 5px 0;
            white-space: nowrap;
        }
        .menu-main li:hover .submenu {
            display: block;
        }
        .friend-links {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .friend-links h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
        }
        .friend-links ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .friend-links li a {
            text-decoration: none;
            color: #3498db;
            padding: 4px 8px;
            background: #f0f7ff;
            border-radius: 4px;
        }
        .friend-links li a:hover {
            background: #e8f4fd;
        }
        .side-menu {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .side-menu h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 16px;
        }
        .side-menu ul {
            list-style: none;
            padding-left: 10px;
        }
        .side-menu li {
            margin: 8px 0;
        }
        .side-menu li a {
            text-decoration: none;
            color: #2c3e50;
        }
        .side-menu li a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        .side-menu .submenu {
            padding-left: 15px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= L('Ssg_Builder_Title'); ?></h1>
            <div class="version">v7.0 Final Fixed | <?= L('Absolute_Path_Mode'); ?><?php echo STATIC_ROOT_URL; ?></div>
        </div>
        <div class="logs">
<?php
function logMessage($message, $type = 'info') {
    $typeClass = 'log-' . $type;
    $message = htmlspecialchars($message);
    $message = str_replace("\n", "<br>", $message);
    echo "<div class='log-item $typeClass'>$message</div>";
}
function qs_switch_on($key) {
    global $qs_config, $static_config;
    // 后台「静态站点设置」的开关保存在主配置 switch（文件版 conf.php / MySQL 版 qs_config 表）
    if (isset($qs_config['switch']) && is_array($qs_config['switch']) && array_key_exists($key, $qs_config['switch'])) {
        return (bool)$qs_config['switch'][$key];
    }
    if (!isset($static_config['switch'][$key])) return true;
    return (bool)$static_config['switch'][$key];
}





logMessage(L('Ssg_Engine_Start'), 'info');
logMessage(sprintf(L('Using_Absolute_Domain'), STATIC_ROOT_URL), 'info');
set_time_limit(0);
ini_set('memory_limit', '512M');
$static_upload_dir = isset($static_config['static_upload_dir']) ? $static_config['static_upload_dir'] : '/upload';
define('STATIC_UPLOAD_URL', $static_upload_dir);
$static_config['static_data_pages'] = realpath(__DIR__ . '/' . $static_config['static_data_pages']) ?: $static_config['static_data_pages'];
$static_config['static_publish_pages'] = realpath(__DIR__ . '/' . $static_config['static_publish_pages']) ?: $static_config['static_publish_pages'];
$static_config['local_upload_dir'] = realpath(__DIR__ . '/' . $static_config['local_upload_dir']) ?: $static_config['local_upload_dir'];
logMessage(sprintf(L('Debug_Pages_Data_Dir'), $static_config['static_data_pages']), 'info');
logMessage(sprintf(L('Debug_Pages_Publish_File'), $static_config['static_publish_pages']), 'info');
$siteMode = isset($qs_config['sitemode']) ? $qs_config['sitemode'] : 'novel';
logMessage(sprintf(L('Current_Site_Mode'), $siteMode), 'info');
if (!is_dir($static_config['static_output']) && !@mkdir($static_config['static_output'], 0755, true)) {
    die(sprintf(L('Create_Dir_Failed_No_Permission'), $static_config['static_output']));
}
$themePath = $static_config['static_theme_dir'] . $static_config['static_theme_name'];
if (!is_dir($themePath)) {
    logMessage(sprintf(L('Theme_Dir_Not_Exist'), $themePath), 'error');
    die("</div></div></body></html>");
}
logMessage(sprintf(L('Theme_Dir_Confirmed'), $themePath), 'info');
$requiredTemplates = ['index.php', 'post.php', 'header.php', 'footer.php'];
foreach ($requiredTemplates as $tpl) {
    if (!file_exists($themePath . "/{$tpl}")) {
        logMessage(sprintf(L('Missing_Core_Template'), $themePath, $tpl), 'error');
        die("</div></div></body></html>");
    }
}
function loadPost($file) {
    if (!file_exists($file)) return null;
    $content = file_get_contents($file);
    $content = preg_replace('/^<\?php\s*\/\/\s*/', '', $content);
    $content = preg_replace('/\?>$/', '', $content);

    $data = @unserialize($content);
    if (!$data || !isset($data['title'])) return null;

    $defaults = array(
        'id' => '',
        'title' => '',
        'content' => '',
        'intro' => isset($data['summary']) ? $data['summary'] : '',
        'author' => isset($data['user']) ? $data['user'] : '',
        'bookname' => '',
        'tags' => array(),
        'date' => isset($data['date']) ? strtotime($data['date']) : time(),
        'state' => 'draft',
        'time' => '',
        'can_comment' => '1',
        'bookpic' => '',
        'path' => '',
        'file' => ''
    );
    $data = array_merge($defaults, $data);

    if (!is_array($data['tags'])) {
        $data['tags'] = empty($data['tags']) ? array() : explode(',', $data['tags']);
    } else {
        $data['tags'] = array_filter($data['tags'], function($tag) {
            return !empty(trim($tag));
        });
        $data['tags'] = array_values($data['tags']);
    }

    $data['id'] = pathinfo($file, PATHINFO_FILENAME);
    // file 统一存"文件名去扩展名"，供 sitemap 的 lastmod 匹配使用；
    // 早先这里没有该键，导致 sitemap 阶段读取 $pageData['file'] 报 Undefined array key
    if (!isset($data['file']) || $data['file'] === '') {
        $data['file'] = $data['id'];
    }
    return $data;
}
function getPublishedPages() {
    global $static_config;
    $pages = array();
    $publishFile = $static_config['static_publish_pages'];

    if (!file_exists($publishFile)) {
        logMessage(sprintf(L('Pages_Publish_File_Not_Exist'), $publishFile), 'error');
        return $pages;
    }

    $qs_pages = array();
    require $publishFile;

    if (empty($qs_pages) || !is_array($qs_pages)) {
        logMessage(L('No_Valid_Qs_Pages_Array'), 'error');
        return $pages;
    }

    foreach ($qs_pages as $path => $pageData) {
        if (isset($pageData['state']) && $pageData['state'] !== 'publish') {
            continue;
        }

        $file = $pageData['file'] ?? '';
        if (empty($file)) {
            logMessage(sprintf(L('Pages_Missing_File_Skip'), $path), 'warning');
            continue;
        }

        $dataFile = $static_config['static_data_pages'] . '/' . $file . '.php';
        if (!file_exists($dataFile)) {
            logMessage(sprintf(L('Pages_Data_File_Not_Exist'), $path, $dataFile), 'error');
            continue;
        }

        $pageContent = loadPost($dataFile);
        if (!$pageContent) {
            logMessage(sprintf(L('Cannot_Load_Pages_Content'), $path, $dataFile), 'error');
            continue;
        }

        $pageContent['path'] = $path;
        $pages[$path] = $pageContent;
        logMessage(sprintf(L('Pages_Loaded_Success'), $path, $pageContent['title']), 'success');
    }

    return $pages;
}
function getPublishedIds($publishFile) {
    $ids = array();
    $bookpicMap = array();
    if (!file_exists($publishFile)) {
        logMessage(sprintf(L('Publish_Config_File_Not_Exist'), $publishFile), 'warning');
        return array($ids, $bookpicMap);
    }

    $qs_posts = array();
    require $publishFile;

    if (is_array($qs_posts)) {
        foreach ($qs_posts as $item) {
            if (isset($item['state']) && $item['state'] === 'publish' && isset($item['id']) && !empty($item['id'])) {
                $ids[] = trim($item['id']);
                if (isset($item['bookpic']) && !empty($item['bookpic'])) {
                    $bookpicMap[trim($item['id'])] = $item['bookpic'];
                }
            }
        }
    }

    return array(array_unique($ids), $bookpicMap);
}
$allPosts = array();
if (isset($static_config['static_publish_posts']) && file_exists($static_config['static_publish_posts'])) {
    list($postIds, $bookpicMap) = getPublishedIds($static_config['static_publish_posts']);
    foreach ($postIds as $id) {
        $file = $static_config['static_data_posts'] . '/' . $id . '.php';
        $p = loadPost($file);
        if ($p) {
            if (isset($bookpicMap[$id])) {
                $bookpic = $bookpicMap[$id];
                if (strpos($bookpic, 'http') !== 0) {
                    $bookpic = preg_replace('/^\.\.\//', '', $bookpic);
                    $bookpic = '/' . ltrim($bookpic, '/');
                }
                $p['bookpic'] = $bookpic;
            }
            $allPosts[$p['id']] = $p;
        }
    }
}
logMessage(sprintf(L('Load_Published_Posts'), count($allPosts)), 'info');
$allPages = getPublishedPages();
logMessage(sprintf(L('Load_Published_Pages'), count($allPages)), 'info');
// ========== 增量缓存初始化 ==========
$cacheFilePath = rtrim($static_config['static_output'], '/') . '/build_cache.json';
$oldCache = load_build_cache($cacheFilePath);
$newCache = [
    'posts_file_hash' => [],
    'pages_file_hash' => [],
    'template_hash' => [],
    'ads_file_hash' => '',
    'ads_config_file_hash' => '',
    'qs_config_file_hash' => '',
    'static_config_file_hash' => '',
    'ssg_work_self_hash' => '',
    'refresh_categories' => [],
    'refresh_tags' => []
];
$themePath = $static_config['static_theme_dir'] . $static_config['static_theme_name'];
// 需要监控哈希的核心模板列表，新增模板在这里追加
$watchTemplates = [
    'index' => $themePath . '/index.php',
    'privacy' => $themePath . '/privacy.php',
    'post' => $themePath . '/post.php',
    'header' => $themePath . '/header.php',
    'footer' => $themePath . '/footer.php',
    'list' => $themePath . '/list.php',
    'tags' => $themePath . '/tags.php',
    '404' => $themePath . '/404.php'
];
// 计算当前所有模板哈希，对比旧缓存判断是否需要全站重绘
$fullRebuild = false;
foreach ($watchTemplates as $tplKey => $tplPath) {
    $currentHash = file_sha256($tplPath);
    $newCache['template_hash'][$tplKey] = $currentHash;
    if (($oldCache['template_hash'][$tplKey] ?? '') !== $currentHash) {
        $fullRebuild = true;
    }
}
//=====新增：监控广告、站点配置文件哈希 start =====
$monitorFiles = [
    'ads_file'          => __DIR__ . '/../control/data/ads.php',
    'ads_config_file'   => __DIR__ . '/../control/data/ads_config.php',
    'qs_config_file'    => __DIR__ . '/../control/data/conf.php',
    'static_config_file'=> __DIR__ . '/../control/data/static_config.php',
    'ssg_work_self'     => __FILE__,
];
$newCache['ads_file_hash']           = file_sha256($monitorFiles['ads_file']);
$newCache['ads_config_file_hash']    = file_sha256($monitorFiles['ads_config_file']);
$newCache['qs_config_file_hash']     = file_sha256($monitorFiles['qs_config_file']);
$newCache['static_config_file_hash'] = file_sha256($monitorFiles['static_config_file']);
$newCache['ssg_work_self_hash'] = file_sha256($monitorFiles['ssg_work_self']);

if(
    ($oldCache['ads_file_hash'] ?? '')          !== $newCache['ads_file_hash']
    || ($oldCache['ads_config_file_hash'] ?? '') !== $newCache['ads_config_file_hash']
    || ($oldCache['qs_config_file_hash'] ?? '')    !== $newCache['qs_config_file_hash']
    || ($oldCache['static_config_file_hash'] ?? '')!== $newCache['static_config_file_hash']
    || ($oldCache['ssg_work_self_hash'] ?? '') !== $newCache['ssg_work_self_hash']
){
    $fullRebuild = true;
    logMessage(L('Ssg_Work_Modified_Full_Redraw'), 'warning');
}
//=====新增 end =====
if ($fullRebuild) {
    logMessage(L('Config_Modified_Full_Redraw'), 'warning');
}
// ========== 缓存初始化结束 ==========
$books = array();
$categories = array();
$tags = array();
foreach ($allPosts as $p) {
    // 找到当前文章原始php源文件路径
    $postSourceFile = $static_config['static_data_posts'] . '/' . $p['id'] . '.php';
    $currentPostHash = file_sha256($postSourceFile);
    $newCache['posts_file_hash'][$p['id']] = $currentPostHash;
    // 判断文章是否变动：模板全量重绘 或 源文件哈希变化
    $postChanged = $fullRebuild || (($oldCache['posts_file_hash'][$p['id']] ?? '') !== $currentPostHash);
    if ($postChanged) {
        // 标记该文章所属分类需要刷新列表页、首页
        if ($siteMode === 'novel') {
            $bn = trim($p['bookname']) ?: 'Uncategorized';
            $safeCat = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $bn);
            $newCache['refresh_categories'][] = $safeCat;
        } else {
            $catName = $siteMode;
            $safeCat = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $catName);
            $newCache['refresh_categories'][] = $safeCat;
        }
        // 标记所有关联标签需要刷新标签页
        foreach ($p['tags'] as $t) {
            $tagTrim = trim($t);
            if ($tagTrim) {
                $newCache['refresh_tags'][] = $tagTrim;
            }
        }
    }
    // 原有分类标签组装逻辑完全保留，不动
    if ($siteMode === 'novel') {
        $bn = trim($p['bookname']) ?: 'Uncategorized';
        $safeCat = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $bn);
        if (!isset($books[$safeCat])) {
            $books[$safeCat] = array('orig' => $bn, 'list' => array());
        }
        $books[$safeCat]['list'][] = $p;
        $categories = $books;
    } else {
        $catName = $siteMode;
        $safeCat = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $catName);
        if (!isset($categories[$safeCat])) {
            $categories[$safeCat] = array('orig' => $catName, 'list' => array());
        }
        $categories[$safeCat]['list'][] = $p;
    }
    foreach ($p['tags'] as $t) {
        $t = trim($t);
        if (empty($t)) continue;
        if (!isset($tags[$t])) $tags[$t] = array();
        $tags[$t][] = $p;
    }
}
// 去重需要刷新的分类、标签
$newCache['refresh_categories'] = array_unique($newCache['refresh_categories']);
$newCache['refresh_tags'] = array_unique($newCache['refresh_tags']);
foreach ($categories as &$cat) {
    usort($cat['list'], function($a, $b) {
        $t1 = is_numeric($a['date']) ? (int)$a['date'] : strtotime($a['date']);
        $t2 = is_numeric($b['date']) ? (int)$b['date'] : strtotime($b['date']);
        return $t2 - $t1;
    });
    $cat['list'] = array_values($cat['list']);
}
unset($cat);
foreach ($tags as &$t) {
    usort($t, function($a, $b) {
        $timeA = is_numeric($a['date']) ? $a['date'] : strtotime($a['date']);
        $timeB = is_numeric($b['date']) ? $b['date'] : strtotime($b['date']);
        return $timeB - $timeA;
    });
    $t = array_values($t);
}
unset($t);
$sortedPosts = array_values($allPosts);
// 首页全部文章按发布日期date降序排序（新文章靠前）
usort($sortedPosts, function($a, $b){
    $t1 = is_numeric($a['date']) ? (int)$a['date'] : strtotime($a['date']);
    $t2 = is_numeric($b['date']) ? (int)$b['date'] : strtotime($b['date']);
    return $t2 - $t1;
});

// ============================
// 【SSG内部函数：the_*全部只return，无内部echo；模板调用必须echo】
// ============================
function qs_is_index() { global $static_config; return $static_config['page_type'] === 'home'; }
function qs_is_post() { global $static_config; return $static_config['page_type'] === 'post'; }
function qs_is_page() { global $static_config; return $static_config['page_type'] === 'page'; }
function qs_is_tag() { global $static_config; return $static_config['page_type'] === 'tag'; }
function qs_is_bookname() { global $qs_config; return $qs_config['sitemode'] === 'novel' && $static_config['page_type'] === 'book'; }
function qs_is_category() { global $qs_config; return $qs_config['sitemode'] !== 'novel' && $static_config['page_type'] === 'category'; }
function qs_can_comment() { return false; }
function qs_speakArticle($qs_config_arg = null, $rate = '读速:')
{
    global $qs_config;
    // 与动态站签名对齐（$qs_config, $rate）；兼容旧式单参数调用
    if (is_string($qs_config_arg) && $qs_config_arg !== '') {
        $rate = $qs_config_arg;
    }
    if (!isset($qs_config['openspeak']) || $qs_config['openspeak'] !== 'open') {
        return '';
    }
    // 解析当前生效的 TTS（口径与动态站 qs_tts_active_provider 一致：选中项 → 第一个启用 → 内置 web）
    $sel = $qs_config['speakArticle'] ?? '';
    $provider = null;
    $list = $qs_config['speak_tts_list'] ?? [];
    if (is_array($list)) {
        foreach ($list as $pp) {
            if (is_array($pp) && ($pp['key'] ?? '') === $sel && ($pp['enabled'] ?? '1') !== '0') {
                $provider = $pp;
                break;
            }
        }
        if ($provider === null) {
            foreach ($list as $pp) {
                if (is_array($pp) && ($pp['enabled'] ?? '1') !== '0') {
                    $provider = $pp;
                    break;
                }
            }
        }
    }
    if ($provider === null) {
        $provider = [
            'key' => 'web', 'name' => '浏览器原生朗读', 'handler' => 'web', 'enabled' => '1',
            'voice' => '', 'lang' => 'zh-CN', 'rate' => '1', 'method' => 'POST',
            'api_url' => '', 'api_key' => '', 'api_secret' => '', 'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
        ];
    }
    $handler = $provider['handler'] ?? 'web';
    $isWeb = ($handler === 'web');
    $isWebJs = $isWeb ? 'true' : 'false';
    $lang = htmlspecialchars((string)($provider['lang'] ?? 'zh-CN'), ENT_QUOTES);
    $defaultRate = htmlspecialchars((string)($provider['rate'] ?? '1'), ENT_QUOTES);
    $pkey = htmlspecialchars((string)($provider['key'] ?? ''), ENT_QUOTES);
    $rateLabel = htmlspecialchars((string)$rate, ENT_QUOTES);
    // 代理端点：指向动态站 PHP 控制端（web 型用浏览器原生，不依赖它；非 web 型需后端可达）
    $siteLink = isset($qs_config['site_link']) ? rtrim($qs_config['site_link'], '/') : '';
    $proxy = $siteLink . '/control/tts_api.php';

    $html = '<div class="qstts-box" style="display:flex;align-items:center;gap:8px;margin:8px 0;flex-wrap:wrap;">';
    $html .= '<button type="button" id="qstts-toggle" style="cursor:pointer;border:1px solid #ccc;background:#fff;border-radius:4px;padding:2px 8px;font-size:14px;">▶️</button>';
    $html .= '<label for="qstts-rate">' . $rateLabel . '</label>';
    $html .= '<input type="range" id="qstts-rate" min="0.1" max="2" step="0.1" value="1">';
    $html .= '<span id="qstts-rate-val">1</span>';
    $html .= '</div>';
    $html .= <<<QSTTS_JS
<script>
(function () {
    function qsInit() {
    var btn = document.getElementById('qstts-toggle');
    if (!btn) return;
    var art = document.getElementById('article-content');
    if (!art) { var box = btn.closest('.qstts-box'); if (box) box.style.display = 'none'; return; }
    var playing = false, idx = 0, sentences = [];
    var rateControl = parseFloat('{$defaultRate}') || 1;
    var rateEl = document.getElementById('qstts-rate');
    var rateVal = document.getElementById('qstts-rate-val');
    var isWeb = {$isWebJs};
    var audio = isWeb ? null : new Audio();
    var proxy = '{$proxy}';
    var providerKey = '{$pkey}';
    var lang = '{$lang}';
    function split(){ var t = art.innerText || ''; sentences = t.split(/([。！？!?；;\n])/).filter(function (s) { return s.trim() !== ''; }); idx = 0; }
    function stop(){
        playing = false;
        if (isWeb) { if ('speechSynthesis' in window) speechSynthesis.cancel(); }
        else { audio.pause(); audio.removeAttribute('src'); }
        if (btn) btn.textContent = '▶️';
    }
    function playNext(){
        if (idx >= sentences.length) { stop(); return; }
        var text = sentences[idx++];
        if (isWeb) {
            if (!('speechSynthesis' in window)) { stop(); return; }
            var u = new SpeechSynthesisUtterance(text);
            u.lang = lang; u.rate = rateControl;
            u.onend = function () { playNext(); };
            u.onerror = function () { stop(); };
            speechSynthesis.speak(u);
            return;
        }
        var fd = new FormData();
        fd.append('action', 'synth'); fd.append('key', providerKey); fd.append('text', text); fd.append('rate', String(rateControl));
        var xhr = new XMLHttpRequest();
        xhr.open('POST', proxy, true); xhr.responseType = 'blob';
        xhr.onload = function () {
            if (xhr.status === 200 && xhr.response && xhr.response.size > 0) {
                var url = URL.createObjectURL(xhr.response);
                audio.src = url;
                audio.onended = function () { URL.revokeObjectURL(url); playNext(); };
                audio.play();
            } else {
                var r = new FileReader();
                r.onload = function () { try { var j = JSON.parse(r.result); if (j && j.error) alert('朗读失败：' + j.error); } catch (e) {} stop(); };
                r.readAsText(xhr.response);
            }
        };
        xhr.onerror = function () { stop(); };
        xhr.send(fd);
    }
    if (btn) {
        btn.addEventListener('click', function () {
            if (playing) { stop(); }
            else { split(); playing = true; btn.textContent = '⏸️'; playNext(); }
        });
    }
    if (rateEl) {
        rateEl.addEventListener(isWeb ? 'input' : 'change', function () {
            rateControl = parseFloat(rateEl.value) || 1;
            if (rateVal) rateVal.textContent = rateControl;
            if (playing && !isWeb) { stop(); split(); playing = true; btn.textContent = '⏸️'; playNext(); }
        });
    }
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', qsInit); }
    else { qsInit(); }
})();
</script>
QSTTS_JS;
    return $html;
}

function replace_upload_url($content) {
    global $static_config;
    $web_upload_url = STATIC_ROOT_URL . '/upload/';
    $content = str_replace('../upload/', $web_upload_url, $content);
    $content = str_replace('src="/upload/', 'src="'.$web_upload_url, $content);
    return $content;
}
function parseAdTags(string $content, string $context): string
{
    return preg_replace_callback('/\{\{ad:(.+?)\}\}/', function ($m) use ($context) {
        $aid = trim($m[1]);
        $src = '/ads/' . htmlspecialchars($aid) . '.js';
        return '<div class="ad-slot" data-ad-id="'.htmlspecialchars($aid).'" data-ad-context="'.htmlspecialchars($context).'"></div>'."\n".'<script src="'.$src.'"></script>';
    }, $content);
}

/**
 * @param mixed $post null使用全局current_data
 * @return string 渲染后的文章内容HTML
 */
function qs_the_content($operation = '') {
    global $static_config, $ads, $adsConfig;
    $val = isset($static_config['current_data']['content']) ? $static_config['current_data']['content'] : '';
    $val = replace_upload_url($val);
    $val = parseAdTags($val, 'post');
    // 静态站同样展开投票短标签：只渲染结果快照，投票动作指向动态站
    if (is_file(__DIR__ . '/../control/vote_embed_lib.php')) {
        require_once __DIR__ . '/../control/vote_embed_lib.php';
        $val = qs_vote_shortcodes((string)$val, true);
    }
    return $val;
}

/**
 * @param int $length 截取摘要字数
 * @return string 纯文本摘要
 */
function qs_the_summary($length = 80) {
    global $static_config;
    $content = isset($static_config['current_data']['summary']) ? $static_config['current_data']['summary'] : '';
    if (empty($content)) {
        $content = isset($static_config['current_data']['content']) ? $static_config['current_data']['content'] : '';
    }
    $text = strip_tags($content);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    $excerpt = mb_substr($text, 0, $length, 'UTF-8');
    if (mb_strlen($text) > $length) {
        $excerpt .= '...';
    }
    return $excerpt;
}

/**
 * @return string 转义后的intro
 */
function qs_the_intro() {
    global $static_config;
    $val = isset($static_config['current_data']['intro']) ? $static_config['current_data']['intro'] : '';
    return htmlspecialchars($val);
}

/**
 * @return string 转义后的作者
 */
function qs_the_author() {
    global $static_config;
    $val = isset($static_config['current_data']['author']) ? $static_config['current_data']['author'] : '';
    return htmlspecialchars($val);
}

/**
 * @param mixed $post null取全局current_data
 * @return string 转义后的标题
 */
function qs_the_title($post = null) {
    global $static_config;
    if ($post === null) {
        $post = $static_config['current_data'];
    }
    if (empty($post)) return '';
    return htmlspecialchars($post['title'] ?? '');
}

/**
 * 重命名：原qs_tag_name → qs_the_tag_name
 * @return string 转义后的当前标签名
 */
function qs_the_tag_name() {
    global $static_config;
    return htmlspecialchars($static_config['current_tag'] ?? '');
}

/**
 * @param string $type year/month/day/all
 * @return string 格式化日期字符串
 */
function qs_the_date($type = 'all') {
    global $static_config;
    $d = isset($static_config['current_data']['date']) ? $static_config['current_data']['date'] : time();
    switch ($type) {
        case 'year': $format = 'Y'; break;
        case 'month': $format = 'm'; break;
        case 'day': $format = 'd'; break;
        default: $format = 'Y-m-d';
    }
    return is_numeric($d) ? date($format, $d) : $d;
}

/**
 * @return string 转义后的封面图地址
 */
function qs_the_book_pic() {
    global $static_config;
    $bookpic = $static_config['current_data']['bookpic'] ?? '';
    return htmlspecialchars($bookpic);
}

/**
 * 重命名：原qs_post_link → qs_the_post_link
 * @return string 完整文章URL
 */
function qs_the_post_link() {
    return qs_the_link_url();
}

function qs_next_post() {
    global $static_config;
    // 取出当前要循环的文章列表
    $posts = $static_config['loop_posts'] ?? [];
    $posts = is_array($posts) ? $posts : [];
    // 取出当前指针（不存在默认为 -1）
    $index = $static_config['current_loop_index'] ?? -1;
    $index++;
    // 超出范围，结束
    if ($index >= count($posts)) {
        $static_config['current_loop_index'] = -1;
        $static_config['current_data'] = null;
        return false;
    }
    // 正常赋值
    $static_config['current_loop_index'] = $index;
    $static_config['current_data'] = $posts[$index];
    return true;
}

function qs_rewind_posts() {
    global $static_config;
    // 与 qs_next_post() 使用同一个指针变量，避免模板循环状态串页
    $static_config['current_loop_index'] = -1;
    $static_config['current_data'] = null;
}

function qs_search_name() {
    global $static_config;
    echo htmlspecialchars($static_config['search_query'] ?? '');
}

/**
 * 返回标签组完整HTML
 * @param string $before 前缀html
 * @param string $after 后缀html
 * @param string $sep 分隔符
 * @return string
 */
function qs_the_tags($item_begin = '', $item_gap = ', ', $item_end = '') {
    global $static_config, $qs_config;
    $tags = isset($static_config['current_data']['tags']) ? $static_config['current_data']['tags'] : array();
    $staticRoot = $static_config['static_root_url'];
    $links = array();
    foreach ($tags as $t) {
        $t = trim($t);
        if (empty($t)) continue;
        $safeTag = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $t);
        $links[] = $item_begin . '<a href="' . $staticRoot . '/tag/' . $safeTag . '/index.html">' . htmlspecialchars($t) . '</a>' . $item_end;
    }
    return implode($item_gap, $links);
}

/**
 * 返回标签云完整HTML
 * @param string $before
 * @param string $after
 * @return string
 */
function qs_the_render_all_tags($before='<div class="all-tags" style="margin:20px 0; padding:10px; background:#f8f9fa; border-radius:6px;">', $after='</div>') {
    global $static_config,$qs_config;
    $tags = $static_config['all_tags'] ?? array();
    $staticRoot = $static_config['static_root_url'];
    if (empty($tags)) return '';
    $html = '<div style="font-weight:bold; margin-bottom:8px; color:#2c3e50;">' . L('All_Tags') . '</div>';
    foreach ($tags as $tagName => $posts) {
        $safeTag = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $tagName);
        $count = count($posts);
        $html .= '<a href="' . $staticRoot . '/tag/' . $safeTag . '/index.html" style="margin:0 4px 8px; display:inline-block; padding:4px 10px; background:#e8f4fd; color:#2980b9; border-radius:4px; text-decoration:none;">' . htmlspecialchars($tagName) . ' (' . $count . ')</a>';
    }
    return $before . $html . $after;
}

/**
 * @return string 文章内第一张图片src，无图片返回空字符串
 */
function qs_the_content_pic() {
    global $static_config,$qs_config;
    $content = isset($static_config['current_data']['content']) ? $static_config['current_data']['content'] : '';
    if (preg_match('/<img\s+[^>]*?src\s*=\s*(["\']?)(.*?)\1[^>]*>/i', $content, $m)) {
        $src = $m[2];
        $src = str_replace('../upload/', STATIC_ROOT_URL . '/upload/', $src);
        if (strpos($src, '/upload/') === 0) {
            $src = STATIC_ROOT_URL . $src;
        }
        return $src;
    }
    return '';
}
// ============================ end the_系列 ============================

function get_posts_by_tag($tagName) {
    global $tags;
    return isset($tags[trim($tagName)]) ? $tags[trim($tagName)] : [];
}
function get_posts_by_tag_limit($tagName, $num = 6) {
    return array_slice(get_posts_by_tag($tagName), 0, $num);
}
// 只处理普通文章，剔除无用page分支
function custom_get_post_link($post = null) {
    if(empty($post)) return '';
    global $static_config, $qs_config;
    $staticRoot = $static_config['static_root_url'];
    $siteMode = $qs_config['sitemode'];
    if($siteMode === 'novel'){
        $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? 'Other');
        return "{$staticRoot}/{$siteMode}/{$safeBook}/{$post['id']}.html";
    }
    return "{$staticRoot}/{$siteMode}/{$post['id']}.html";
}
function custom_get_post_title($post = null) {
    return empty($post) ? '' : htmlspecialchars($post['title']);
}

function qs_the_link()
{
    $url = qs_the_link_url();
    if ($url === '') {
        return '';
    }
    return '<a href="' . htmlspecialchars($url) . '" target="_blank">' . qs_the_title() . '</a>';
}

function qs_get_field($key, $default = '') {
    global $static_config, $qs_config;
    $data = $static_config['current_data'] ?? array();
    $value = $data[$key] ?? $default;

    if (in_array($key, ['bookpic', 'pic']) && !empty($value)) {
        $staticRoot = $static_config['static_root_url'] ?? '';
        if (strpos($value, 'http') !== 0) {
            $value = preg_replace('/^\.\.\//', '', $value);
            if (strpos($value, '/') !== 0) {
                $value = '/' . ltrim($value, '/');
            }
            $value = $staticRoot . $value;
        }
    }

    if ($key === 'full_date' && isset($data['date']) && isset($data['time'])) {
        $value = $data['date'] . ' ' . $data['time'];
    }

    if (in_array($key, ['content', 'summary', 'intro'])) {
        return $value;
    }

    return htmlspecialchars($value);
}
function qs_the_field($key, $default = '') {
    echo qs_get_field($key, $default);
}
/**
 * favicon图标（兼容SSG静态域名替换）
 */
function static_ico(): string
{
    global $qs_config;
    $iconPath = trim($qs_config['ico'] ?? '');
    if ($iconPath === '') {
        return '';
    }
    // SSG路径处理：非http开头的相对路径，拼接静态站点根域名STATIC_ROOT_URL
    if (defined('STATIC_ROOT_URL') && strpos($iconPath, 'http') !== 0) {
        $iconPath = rtrim(STATIC_ROOT_URL, '/') . '/' . ltrim($iconPath, '/');
    }
    $ext = strtolower(pathinfo($iconPath, PATHINFO_EXTENSION));
    $mimeMap = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'avif' => 'image/avif',
        'svg'  => 'image/svg+xml',
        'ico'  => null
    ];
    if (!array_key_exists($ext, $mimeMap)) {
        return '';
    }
    $hrefEscaped = htmlspecialchars($iconPath, ENT_QUOTES);
    $mimeVal = $mimeMap[$ext];
    if ($mimeVal === null) {
        return '<link rel="icon" href="' . $hrefEscaped . '">';
    }
    $mimeEscaped = htmlspecialchars($mimeVal, ENT_QUOTES);
    return '<link rel="icon" type="' . $mimeEscaped . '" href="' . $hrefEscaped . '">';
}
/**
 * google图标快捷图标（兼容SSG静态域名替换）
 */
function qs_google_icon(): string
{
    global $qs_config;
    $googleIconPath = trim($qs_config['google_icon'] ?? '');
    if ($googleIconPath === '') {
        return '';
    }
    // SSG路径处理：非http开头的相对路径，拼接静态站点根域名STATIC_ROOT_URL
    if (defined('STATIC_ROOT_URL') && strpos($googleIconPath, 'http') !== 0) {
        $googleIconPath = rtrim(STATIC_ROOT_URL, '/') . '/' . ltrim($googleIconPath, '/');
    }
    $hrefEscaped = htmlspecialchars($googleIconPath, ENT_QUOTES);
    return '<link rel="icon" href="' . $hrefEscaped . '">';
}
function parseMenuTree($menuList, $parentId = 0) {
    $tree = [];
    foreach ($menuList as $item) {
        if ($item['parent_id'] == $parentId) {
            $item['children'] = parseMenuTree($menuList, $item['self_id']);
            $tree[] = $item;
        }
    }
    return $tree;
}
// 渲染友情链接（修复：变量名 + global 声明）
function qs_links() {
    global $qs_links, $static_config;
    $links = $qs_links;
    if (empty($links)) return;

    foreach ($links as $link) {
        $name = htmlspecialchars($link['name'] ?? '');
        $href = htmlspecialchars($link['link'] ?? '#');
        $target = htmlspecialchars($link['tar'] ?? '_blank');

        if (strpos($href, 'http') !== 0 && strpos($href, '/') === 0 && isset($static_config['static_root_url'])) {
            $href = $static_config['static_root_url'] . $href;
        }

        echo "<a href=\"{$href}\" target=\"{$target}\">{$name}</a>";
    }
}
// 渲染主菜单（修复：global 声明 + 变量名）
function qs_menu($cssClassOrBefore = '<ul>', $afterMarkup = '</ul>') {
    global $qs_menu;
    $menu = $qs_menu;
    if (empty($menu)) return;

    $cssClass = '';
    $before = '<ul>';
    if (is_string($cssClassOrBefore) && $cssClassOrBefore !== '' && strpos(ltrim($cssClassOrBefore), '<') !== 0) {
        $cssClass = $cssClassOrBefore;
    } else {
        $before = (string)$cssClassOrBefore;
    }
    $cls = $cssClass !== '' ? ' class="' . htmlspecialchars($cssClass) . '"' : '';

    $menuTree = parseMenuTree($menu);
    echo $before;

    foreach ($menuTree as $item) {
        $name = htmlspecialchars($item['name'] ?? '');
        // 菜单链接保持站内相对路径，不拼 static_root_url，避免静态站绑定单一域名
        $href = htmlspecialchars($item['link'] ?? '#');
        $target = htmlspecialchars($item['tar'] ?? '_self');
        $children = $item['children'] ?? array();

        echo "<li>";
        if (!empty($href)) {
            echo "<a href=\"{$href}\" target=\"{$target}\"{$cls}>{$name}</a>";
        } else {
            echo "<span>{$name}</span>";
        }

        if (!empty($children)) {
            echo "<ul class=\"submenu\">";
            foreach ($children as $child) {
                $childName = htmlspecialchars($child['name'] ?? '');
                $childHref = htmlspecialchars($child['link'] ?? '#');
                $childTarget = htmlspecialchars($child['tar'] ?? '_blank');
                echo "<li><a href=\"{$childHref}\" target=\"{$childTarget}\"{$cls}>{$childName}</a></li>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }

    echo $afterMarkup;
}
// 渲染侧边菜单（修复：global 声明 + 变量名）
function qs_sidemenu($cssClassOrBefore = '<ul>', $afterMarkup = '</ul>') {
    global $qs_side_menu;
    $menu = $qs_side_menu;
    if (empty($menu)) return;

    $cssClass = '';
    $before = '<ul>';
    if (is_string($cssClassOrBefore) && $cssClassOrBefore !== '' && strpos(ltrim($cssClassOrBefore), '<') !== 0) {
        $cssClass = $cssClassOrBefore;
    } else {
        $before = (string)$cssClassOrBefore;
    }
    $cls = $cssClass !== '' ? ' class="' . htmlspecialchars($cssClass) . '"' : '';

    $menuTree = parseMenuTree($menu);
    echo $before;

    foreach ($menuTree as $item) {
        $name = htmlspecialchars($item['name'] ?? '');
        // 保持站内相对路径，不拼 static_root_url
        $href = htmlspecialchars($item['link'] ?? '#');
        $target = htmlspecialchars($item['tar'] ?? '_self');
        $children = $item['children'] ?? array();

        echo "<li>";
        if (!empty($href)) {
            echo "<a href=\"{$href}\" target=\"{$target}\"{$cls}>{$name}</a>";
        } else {
            echo "<span>{$name}</span>";
        }

        if (!empty($children)) {
            echo "<ul class=\"submenu\">";
            foreach ($children as $child) {
                $childName = htmlspecialchars($child['name'] ?? '');
                $childHref = htmlspecialchars($child['link'] ?? '#');
                $childTarget = htmlspecialchars($child['tar'] ?? '_blank');
                echo "<li><a href=\"{$childHref}\" target=\"{$childTarget}\"{$cls}>{$childName}</a></li>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }
    echo $afterMarkup;
}

function qs_pagination() {
    global $static_config, $qs_config;
    $current_page = $static_config['page'] ?? 1;
    $total_pages = $static_config['total_pages'] ?? 1;
    $staticRoot = $static_config['static_root_url'];
    $pageType = $static_config['page_type'];
    $siteMode = $qs_config['sitemode'];
    $currentBook = $static_config['current_book'] ?? '';
    $safeBook = $currentBook ? preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $currentBook) : '';
    
    if ($total_pages <= 1) return;
    // 生成基础URL（完全保留你的原有规则）
    $baseUrl = '';
    if ($pageType === 'home') {
        $baseUrl = $staticRoot;
    } elseif ($pageType === 'book' && $siteMode === 'novel') {
        $baseUrl = "{$staticRoot}/{$siteMode}/{$safeBook}";
    } elseif ($pageType === 'category') {
        $baseUrl = "{$staticRoot}/{$siteMode}";
    } elseif ($pageType === 'tag') {
        $safeTag = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $static_config['current_tag']);
        $baseUrl = "{$staticRoot}/tag/{$safeTag}";
    }
    // ========== 核心修改：智能分页逻辑，最多显示10个按钮 ==========
    $visible_pages = 8; // 最多显示页码数量
    $start = max(1, $current_page - intval($visible_pages / 2));
    $end = min($total_pages, $start + $visible_pages - 1);
    // 如果末尾不够，往前补
    if ($end - $start + 1 < $visible_pages) {
        $start = max(1, $end - $visible_pages + 1);
    }
    $pagination = '<div class="pagination" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">';
    $pagination .= '<div class="pagination-numbers" style="display:flex;gap:6px;">';
    // 首页按钮
    if ($current_page > 1) {
        $pagination .= '<a href="' . getPageUrl('', 1) . '" class="page-item">' . L('Home') . '</a>';
    }
    // 上一页
    if ($current_page > 1) {
        $prev = $current_page - 1;
        $pagination .= '<a href="' . getPageUrl($baseUrl, $prev) . '" class="page-item">' . L('Previous_Page') . '</a>';
    }
    // 智能显示中间页码（最多10个）
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $pagination .= '<span class="pagination-active" style="padding:4px 10px;border-radius:4px;">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . getPageUrl($baseUrl, $i) . '" class="page-item" style="padding:4px 10px;border:1px solid #ddd;border-radius:4px;text-decoration:none;">' . $i . '</a>';
        }
    }
    // 下一页
    if ($current_page < $total_pages) {
        $next = $current_page + 1;
        $pagination .= '<a href="' . getPageUrl($baseUrl, $next) . '" class="page-item">' . L('Next_Page') . '</a>';
    }
    // 尾页
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . getPageUrl($baseUrl, $total_pages) . '" class="page-item">' . L('Last_Page') . '</a>';
    }
    $pagination .= '</div>';
    // 保留你的跳转功能（优化了样式）
    $pagination .= '<form method="get" onsubmit="return jumpToPage(this)" style="margin:0;display:flex;gap:4px;">';
    $pagination .= '<input type="number" name="page" min="1" max="' . $total_pages . '" value="' . $current_page . '" class="pagination-input" style="width:60px;padding:4px;">';
    $pagination .= '<button type="submit" class="pagination-btn">' . L('Jump') . '</button>';
    $pagination .= '
    <script>
    function jumpToPage(form) {
        var page = parseInt(form.page.value);
        var max = parseInt(form.page.max);
        if (page < 1 || page > max) { alert(' . json_encode(L('Page_Number_OutOf_Range'), JSON_UNESCAPED_UNICODE) . '); return false; }
        var baseUrl = "' . $baseUrl . '";
        var url = page === 1 ? baseUrl + "/index.html" : baseUrl + "/page/" + page + "/index.html";
        window.location.href = url;
    }
    </script>';
    $pagination .= '</form>';
    $pagination .= '</div>';
    echo $pagination;
}

// 封装URL生成函数（复用代码，更干净）
function getPageUrl($baseUrl, $page) {
     return $page == 1 ? '/index.html' : $baseUrl . '/page/' . $page . '/index.html';
}

// 模板渲染函数
function renderTemplate($tplFile, $context) {
    global $static_config, $qs_config;
    if (!is_array($static_config)) $static_config = [];
    if (!file_exists($tplFile)) {
        return "<!-- Template Missing: " . $tplFile . " -->";
    }
    $static_config = array_merge($static_config, $context);
    $static_config['current_loop_index'] = -1;
    $static_config['current_data'] = $context['current_data'] ?? null;
    // 主题通用的"共 N 篇文章"计数（动态站对应变量为 $qs_post_count）
    if (!isset($static_config['qs_post_count'])) {
        $qs_all_for_count = $GLOBALS['allPosts'] ?? null;
        $static_config['qs_post_count'] = is_array($qs_all_for_count) ? count($qs_all_for_count) : 0;
    }
    $bufferLevel = ob_get_level();
    ob_start();
    try {
        extract($static_config);
        extract($qs_config);
        // 部分主题的 post/list 模板只写正文片段（header/footer 独立成文件），
        // 这里自动补上外壳，避免产出没有 <html>/<head> 的碎片页
        $qs_tpl_dir = dirname($tplFile);
        $qs_tpl_raw = (string)@file_get_contents($tplFile);
        $qs_self_wrapped = ($qs_tpl_raw !== ''
            && (preg_match('/header\.php/i', $qs_tpl_raw) || stripos($qs_tpl_raw, '<!DOCTYPE') !== false));
        $qs_auto_wrap = !$qs_self_wrapped
            && is_file($qs_tpl_dir . '/header.php')
            && is_file($qs_tpl_dir . '/footer.php');
        if ($qs_auto_wrap) {
            include $qs_tpl_dir . '/header.php';
        }
        include $tplFile;
        if ($qs_auto_wrap) {
            include $qs_tpl_dir . '/footer.php';
        }
        $html = ob_get_contents();
    } catch (Throwable $e) {
        $html = ob_get_contents();
        if ($html !== '') {
            $html .= "\n<!-- Template render error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . " -->";
        } else {
            $html = "<!-- Template render error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . " -->";
        }
        logMessage(sprintf(L('Template_Render_Failed'), basename($tplFile), $e->getMessage()), 'error');
    } finally {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
    }
    $html = parseAdTags($html, 'theme');

    // Cookie 同意组件：静态页也注入同一套 DOM/样式/脚本，纯静态即可显示
    if (function_exists('qs_consent_inject')) {
        $html = qs_consent_inject($html);
    }

    return $html;
}
// 链接重写
function rewriteLinks($html) {
    $staticRoot = defined('STATIC_ROOT_URL') ? STATIC_ROOT_URL : '';
    $html = str_replace('href="/', 'href="' . $staticRoot . '/', $html);
    $html = str_replace('src="/', 'src="' . $staticRoot . '/', $html);
    return $html;
}
function copyAssets($src, $dst) {
    if (!is_dir($src)) {
        logMessage(sprintf(L('Source_Dir_Not_Exist'), $src), 'warning');
        return false;
    }

    if (!is_dir($dst)) {
        if (!@mkdir($dst, 0755, true)) {
            logMessage(sprintf(L('Cannot_Create_Target_Dir'), $dst), 'error');
            return false;
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $targetPath = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

        if ($item->isDir()) {
            if (!is_dir($targetPath)) {
                @mkdir($targetPath, 0755, true);
            }
        } else {
            $ext = strtolower(pathinfo($item->getPathname(), PATHINFO_EXTENSION));
            if (in_array($ext, array('php', 'phtml'))) continue;
            @copy($item->getPathname(), $targetPath);
        }
    }
    return true;
}
$stats = array('home'=>0, 'category_list'=>0, 'chapter'=>0, 'tag'=>0, 'page'=>0);
logMessage(L('Copying_Static_Assets'), 'info');
copyAssets($themePath, $static_config['static_output'] . "/files/theme/" . $static_config['static_theme_name']);
// 组件静态资源（consent.css / consent.js）一并复制到静态输出，纯静态站才能加载
copyAssets(dirname(__DIR__) . '/files/cookie-consent/assets', $static_config['static_output'] . '/files/cookie-consent/assets');
// 静态站自己没有任何后端，用户的选择必须发到有 PHP 的站点才能留证
if (trim((string)(qs_consent_config()['record_endpoint'] ?? '')) === '') {
    logMessage('Cookie 同意：未设置记录接口地址，静态站的用户选择不会落库；请在后台「Cookie 同意」填写有 PHP 站点的 record.php 完整地址', 'warning');
}
if (is_dir($static_config['local_upload_dir'])) {
    $uploadFolderName = basename(rtrim($static_config['local_upload_dir'], '/'));
    $uploadTargetDir = $static_config['static_output'] . '/' . $uploadFolderName;
    copyAssets($static_config['local_upload_dir'], $uploadTargetDir);
    logMessage(sprintf(L('Upload_Folder_Copied'), $uploadTargetDir), 'success');
} else {
    logMessage(sprintf(L('Upload_Dir_Not_Exist'), $static_config['local_upload_dir']), 'warning');
}
// 聚合页面总开关：模板改动 或者 有文章变更，才渲染首页/分类/标签聚合页
$needRenderAggregate = $fullRebuild || !empty($newCache['refresh_categories']) || !empty($newCache['refresh_tags']);
if (!$needRenderAggregate) {
    logMessage(L('No_Change_Skip_Aggregate_Render'), 'success');
}
logMessage(L('Assets_Copy_Done'), 'success');
logMessage(L('Generating_Home_Page'), 'info');
$total = count($sortedPosts);
$pages = max(1, ceil($total / $static_config['static_per_page']));
// 判断是否存在列表模板
$hasListTemplate = file_exists($themePath . "/list.php");
if ($needRenderAggregate) {
foreach ($categories as $safeName => $category) {
    $catRootDir = $static_config['static_output'] . "/" . $siteMode;
    $catDir = $siteMode === 'novel' ? $catRootDir . "/" . $safeName : $catRootDir;
    @mkdir($catDir, 0755, true);

    $catList = $category['list'];
    $catTotal = count($catList);
    $catPages = max(1, ceil($catTotal / $static_config['static_per_page']));
    $homeRootDir = $static_config['static_output'];
    for ($p = 1; $p <= $catPages; $p++) {
        $offset = ($p - 1) * $static_config['static_per_page'];
        $list = array_slice($catList, $offset, $static_config['static_per_page']);

        $ctx = array(
            'page_type' => $siteMode === 'novel' ? 'book' : 'category',
            'current_book' => $siteMode === 'novel' ? $category['orig'] : '',
            'current_category' => $siteMode !== 'novel' ? $category['orig'] : '',
            'page' => $p,
            'total_pages' => $catPages,
            'loop_posts' => $list
        );

    if ($hasListTemplate && $p >= 2) {
        $tpl = $themePath . "/list.php";
    } else {
        $tpl = $themePath . "/index.php";
    }

        $html = renderTemplate($tpl, $ctx);
        $html = rewriteLinks($html);

        if ($p === 1) {
        $dir = $homeRootDir;
        } else {
        $dir = $homeRootDir . "/page/" . $p;
        }
        @mkdir($dir, 0755, true);
        safe_file_put_contents($dir . "/index.html", $html);
        $stats['category_list']++;
    }

foreach ($catList as $post) {
    if (isset($post['is_page']) && $post['is_page'] === true) {
        continue;
    }
    $ctx = array(
        'page_type' => 'post',
        'current_data' => $post,
        'page' => 1,
        'total_pages' => 1,
        'loop_posts' => array($post)
    );
    $tpl = $themePath . "/post.php";
    $html = renderTemplate($tpl, $ctx);
    $html = rewriteLinks($html);
    if ($siteMode === 'novel') {
        $bookName = trim($post['bookname']) ?: L('Untitled_Novel');
        $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $bookName);
        $bookRealDir = $static_config['static_output'] . '/' . $siteMode . '/' . $safeBook;
        @mkdir($bookRealDir, 0755, true);
        $saveFile = $bookRealDir . '/' . $post['id'] . '.html';
    } else {
        $saveFile = $catDir . '/' . $post['id'] . '.html';
    }
    safe_file_put_contents($saveFile, $html);
    $stats['chapter']++;
}

    $logMsg = $siteMode === 'novel'
        ? '   ' . sprintf(L('Category_Done_Chapters'), $category['orig'], $catTotal)
        : '   ' . sprintf(L('Category_Done_Posts'), $category['orig'], $catTotal);
    logMessage($logMsg, 'success');
}
if ($hasListTemplate) {
        logMessage('   ' . sprintf(L('First_Page_Index_Rest_List'), $pages), 'success');
    } else {
        logMessage('   ' . sprintf(L('All_Use_Index_Template'), $pages), 'success');
    }
} else {
    logMessage('   ' . L('No_Change_Skip_Home_Pagination'), 'info');
}
logMessage(L('Generating_Category_Content'), 'info');
if ($needRenderAggregate) {
foreach ($categories as $safeName => $category) {
    $catRootDir = $static_config['static_output'] . "/" . $siteMode;
    $catDir = $siteMode === 'novel' ? $catRootDir . "/" . $safeName : $catRootDir;
    @mkdir($catDir, 0755, true);

    $catList = $category['list'];
    $catTotal = count($catList);
    $catPages = max(1, ceil($catTotal / $static_config['static_per_page']));

    for ($p = 1; $p <= $catPages; $p++) {
        $offset = ($p - 1) * $static_config['static_per_page'];
        $list = array_slice($catList, $offset, $static_config['static_per_page']);

        $ctx = array(
            'page_type' => $siteMode === 'novel' ? 'book' : 'category',
            'current_book' => $siteMode === 'novel' ? $category['orig'] : '',
            'current_category' => $siteMode !== 'novel' ? $category['orig'] : '',
            'page' => $p,
            'total_pages' => $catPages,
            'loop_posts' => $list
        );

    if ($hasListTemplate && $p >= 2) {
        $tpl = $themePath . "/list.php";
    } else {
        $tpl = $themePath . "/index.php";
    }

        $html = renderTemplate($tpl, $ctx);
        $html = rewriteLinks($html);

        if ($p !== 1) {
        	$dir = $catDir . "/page/" . $p;
        	}
        @mkdir($dir, 0755, true);
        safe_file_put_contents($dir . "/index.html", $html);
        $stats['category_list']++;
    }

foreach ($catList as $post) {
    if (isset($post['is_page']) && $post['is_page'] === true) {
        continue;
    }
    $ctx = array(
        'page_type' => 'post',
        'current_data' => $post,
        'page' => 1,
        'total_pages' => 1,
        'loop_posts' => array($post)
    );
    $tpl = $themePath . "/post.php";
    $html = renderTemplate($tpl, $ctx);
    $html = rewriteLinks($html);
    if ($siteMode === 'novel') {
        $bookName = trim($post['bookname']) ?: L('Untitled_Novel');
        $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $bookName);
        $bookRealDir = $static_config['static_output'] . '/' . $siteMode . '/' . $safeBook;
        @mkdir($bookRealDir, 0755, true);
        $saveFile = $bookRealDir . '/' . $post['id'] . '.html';
    } else {
        $saveFile = $catDir . '/' . $post['id'] . '.html';
    }
    safe_file_put_contents($saveFile, $html);
    $stats['chapter']++;
}

    $logMsg = $siteMode === 'novel'
        ? '   ' . sprintf(L('Category_Done_Chapters'), $category['orig'], $catTotal)
        : '   ' . sprintf(L('Category_Done_Posts'), $category['orig'], $catTotal);
    logMessage($logMsg, 'success');
}
if ($hasListTemplate) {
        logMessage('   ' . sprintf(L('First_Page_Index_Rest_List'), $pages), 'success');
    } else {
        logMessage('   ' . sprintf(L('All_Use_Index_Template'), $pages), 'success');
    }
} else {
    logMessage('   ' . L('No_Change_Skip_Home_Pagination'), 'info');
}
if ($needRenderAggregate) {
logMessage(sprintf(L('Tag_Page_Start'), count($tags)), 'info');
foreach ($tags as $tagName => $tPosts) {
    $safeTag = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $tagName);
    $tagDir = $static_config['static_output'] . "/tag/" . $safeTag;
    @mkdir($tagDir, 0755, true);

    $tagTotal = count($tPosts);
    $tagPages = max(1, ceil($tagTotal / $static_config['static_per_page']));

    for ($p = 1; $p <= $tagPages; $p++) {
        $offset = ($p - 1) * $static_config['static_per_page'];
        $list = array_slice($tPosts, $offset, $static_config['static_per_page']);

        $ctx = array(
            'page_type' => 'tag',
            'current_tag' => $tagName,
            'page' => $p,
            'total_pages' => $tagPages,
            'loop_posts' => $list
        );

        $tpl = $themePath . "/index.php";
        $html = renderTemplate($tpl, $ctx);
        $html = rewriteLinks($html);

        $dir = ($p === 1) ? $tagDir : $tagDir . "/page/" . $p;
        @mkdir($dir, 0755, true);
        safe_file_put_contents($dir . "/index.html", $html);
    }

    $stats['tag']++;
}
logMessage('   ' . sprintf(L('Tag_Page_Done'), count($tags)), 'success');
} else {
    logMessage(L('No_Change_Skip_Tag_Pages'), 'info');
}
logMessage(L('Generating_Pages'), 'info');
$pagesSourceDir = $static_config['static_data_pages'];
if (empty($allPages)) {
    logMessage(L('No_Pages_Data_Skip'), 'warning');
} else {

foreach ($allPages as $path => $data) {
    $pageSourceFile = $pagesSourceDir . '/' . $data['file'] . '.php';
    $pageHash = file_sha256($pageSourceFile);
    $newCache['pages_file_hash'][$path] = $pageHash;
    if (!$fullRebuild && ($oldCache['pages_file_hash'][$path] ?? '') === $pageHash) {
        continue;
    }
$newCache['refresh_categories'][] = 'page';
$needRenderAggregate = true;
    $data['is_page'] = true;
    $pageFileName = $path;
    $pageFilePath = $static_config['static_output'] . "/{$pageFileName}.html";
    $ctx = array(
        'page_type' => 'page',
        'current_data' => $data,
        'page' => 1,
        'total_pages' => 1,
        'loop_posts' => []
    );
    $tpl = $themePath . "/post.php";
    $html = renderTemplate($tpl, $ctx);
    $html = rewriteLinks($html);
    safe_file_put_contents($pageFilePath, $html);
    logMessage('   ' . sprintf(L('Pages_Redrawn'), $pageFileName), 'success');
    $stats['page']++;
}
}
logMessage(L('Generating_Tags_Overview'), 'info');
$tagsPageDir = $static_config['static_output'] . "/tags";
@mkdir($tagsPageDir, 0755, true);
$tagsCtx = array(
    'page_type' => 'tags',
    'page' => 1,
    'total_pages' => 1,
    'loop_posts' => array(),
    'all_tags' => $tags,
    'allPosts' => $allPosts
);
$tagsTpl = $themePath . "/tags.php";
if (file_exists($tagsTpl)) {
    $tagsHtml = renderTemplate($tagsTpl, $tagsCtx);
    $tagsHtml = rewriteLinks($tagsHtml);
    safe_file_put_contents($tagsPageDir . "/index.html", $tagsHtml);
    logMessage('   ' . L('Tags_Overview_Done'), 'success');
    $stats['tags_page'] = 1;
} else {
    logMessage(L('Missing_Tags_Template_Skip'), 'warning');
}
// ==========================================
// 自动生成 rss.xml（全量显示）
// ==========================================
if (qs_switch_on('rss')) {
logMessage(L('Generating_Rss'), 'info');
$rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>'.htmlspecialchars($qs_config['site_name']).'</title>
<link>'.STATIC_ROOT_URL.'/</link>
<description>'.htmlspecialchars($qs_config['site_desc']).'</description>
<language>zh-cn</language>
<lastBuildDate>'.date('r').'</lastBuildDate>
<atom:link href="'.STATIC_ROOT_URL.'/rss-all.xml" rel="self" type="application/rss+xml" />';
$max_rss = 30;
$count = 0;
foreach ($allPosts as $post) {
    if ($count >= $max_rss) break;
    $title = qs_the_title($post);
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');
    if ($siteMode === 'novel') {
        $url = STATIC_ROOT_URL . "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = STATIC_ROOT_URL . "/{$siteMode}/{$post['id']}.html";
    }
    $desc = htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 280, 'UTF-8') . '...');

    $timestamp = is_numeric($post['date']) ? $post['date'] : strtotime($post['date']);
    $date_str = date('r', $timestamp);
    $rssContent .= "
<item>
<title>$title</title>
<link>$url</link>
<guid>$url</guid>
<pubDate>$date_str</pubDate>
<description>$desc</description>
</item>";
    $count++;
}
$rssContent .= '
</channel>
</rss>';
safe_file_put_contents($static_config['static_output'] . '/rss-all.xml', $rssContent);
logMessage('   ' . L('Rss_All_Generated'), 'success');
// ==========================================
// 自动生成 rss.xml（150字分段显示）
// ==========================================
logMessage(L('Generating_Rss'), 'info');
$rssContent = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>'.htmlspecialchars($qs_config['site_name']).'</title>
<link>'.STATIC_ROOT_URL.'/</link>
<description>'.htmlspecialchars($qs_config['site_desc']).'</description>
<language>zh-cn</language>
<lastBuildDate>'.date('r').'</lastBuildDate>
<atom:link href="'.STATIC_ROOT_URL.'/rss.xml" rel="self" type="application/rss+xml" />';
$max_rss = 30;
$count = 0;
foreach ($allPosts as $post) {
    if ($count >= $max_rss) break;
    $title = qs_the_title($post);
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');
    if ($siteMode === 'novel') {
        $url = STATIC_ROOT_URL . "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = STATIC_ROOT_URL . "/{$siteMode}/{$post['id']}.html";
    }
    // ======================
    // 🔥 终极修复：保留段落 + 精准150字
    // ======================
    $content = $post['content'];
    // 1. 保留段落标签 <p>，其他标签全部清理
    $cleanContent = strip_tags($content, '<p>');
    // 2. 拿到纯文本（只用来统计字数，不算HTML）
    $pureText = strip_tags($content);
    $pureText = preg_replace('/\s+/', ' ', $pureText);
    $pureText = trim($pureText);
    // 3. 截取 150 个纯汉字
    $excerptText = mb_substr($pureText, 0, 150, 'UTF-8');
    $needsMore = mb_strlen($pureText, 'UTF-8') > 150;
    // 4. 从带段落的内容里，匹配到 150 字位置 → 保留段落！
    $pos = 0;
    $output = '';
    $remaining = 150;
    preg_match_all('#<p>(.*?)</p>#is', $cleanContent, $matches);
    foreach ($matches[1] as $p) {
        $t = trim(strip_tags($p));
        if ($remaining <= 0) break;
        $len = mb_strlen($t, 'UTF-8');
        if ($len <= $remaining) {
            $output .= '<p>' . htmlspecialchars($t) . '</p>';
            $remaining -= $len;
        } else {
            $part = mb_substr($t, 0, $remaining, 'UTF-8');
            $output .= '<p>' . htmlspecialchars($part) . '...</p>';
            $remaining = 0;
        }
    }
    // 最终描述
    $desc = $output;
    if ($needsMore) {
        $desc .= '<p><strong><a href="' . htmlspecialchars($url) . '" target="_blank">' . sprintf(L('Read_Full_Text_Link'), htmlspecialchars($qs_config['site_name'])) . '</a></strong></p>';
    }
    // 时间
    $timestamp = is_numeric($post['date']) ? $post['date'] : strtotime($post['date']);
    $date_str = date('r', $timestamp);
    $rssContent .= "
<item>
<title>$title</title>
<link>$url</link>
<guid>$url</guid>
<pubDate>$date_str</pubDate>
<description>$desc</description>
</item>";
    $count++;
}
$rssContent .= '
</channel>
</rss>';
safe_file_put_contents($static_config['static_output'] . '/rss.xml', $rssContent);
logMessage('   ' . L('Rss_Generated_150_Chars'), 'success');
}
// --------------------------
// 2. 生成 search-index.json（全量文章，用于搜索）
// --------------------------
if (qs_switch_on('search_index')) {
$searchItems = [];
foreach ($allPosts as $post) {
    $title = qs_the_title($post);
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');

    if ($siteMode === 'novel') {
        $url = "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = "/{$siteMode}/{$post['id']}.html";
    }
    // 搜索用：标题 + URL + 摘要（可选）
    $searchItems[] = [
        'title'   => $title,
        'url'     => $url,
        'content' => mb_substr(strip_tags($post['content']), 0, 200) // 简短内容
    ];
}
// 生成 JSON
$jsonContent = json_encode($searchItems, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
safe_file_put_contents($static_config['static_output'] . '/search-index.json', $jsonContent);
logMessage('   ' . sprintf(L('Search_Index_Generated'), count($searchItems)), 'success');
} else {
    logMessage(L('Search_Index_Disabled'), 'warning');
}
//===== SSG全部页面生成完成，复制广告js到static_out/ads =====
$adSourceDir = '../data/ads/';
$adDestDir = $static_output.'/ads/';
if(is_dir($adSourceDir))
{
    if(!is_dir($adDestDir))
    {
        mkdir($adDestDir, 0755, true);
    }
    //清空输出目录旧js
    $oldFiles = glob($adDestDir . '*.js');
    if($oldFiles)
    {
        foreach ($oldFiles as $f)
        {
            if(is_file($f))
            {
                unlink($f);
            }
        }
    }
    //复制源目录所有ad_*.js
    $srcFileList = glob($adSourceDir . 'ad_*.js');
    if($srcFileList)
    {
        foreach ($srcFileList as $filePath)
        {
            $baseName = basename($filePath);
            copy($filePath, $adDestDir . $baseName);
        }
    }
}
// ==========================================
// 自动生成 sitemap.xml（标准SEO格式）
// ==========================================
/*
if (qs_switch_on('sitemap')) {
logMessage("🗺️ 生成 sitemap.xml 搜索引擎地图...", 'info');
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
// 加入首页
$xml .= '
<url>
<loc>'.STATIC_ROOT_URL.'/</loc>
<lastmod>'.date('Y-m-d').'</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
// 加入所有文章
foreach ($allPosts as $post) {
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');
    if ($siteMode === 'novel') {
        $url = STATIC_ROOT_URL . "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = STATIC_ROOT_URL . "/{$siteMode}/{$post['id']}.html";
    }
    // 🔥 修复：自动识别字符串时间 / 时间戳
    $date_str = is_numeric($post['date']) ? date('Y-m-d', $post['date']) : date('Y-m-d', strtotime($post['date']));
    $xml .= "
<url>
<loc>$url</loc>
<lastmod>$date_str</lastmod>
<changefreq>monthly</changefreq>
<priority>0.8</priority>
</url>";
}
// 加入所有单页 page
foreach ($allPages as $path => $page) {
    $url = STATIC_ROOT_URL . "/{$path}.html";
    $pagetimestamp = is_numeric($page['date']) ? $page['date'] : strtotime($page['date']);
    $page_date_str = date('Y-m-d',$pagetimestamp);
    $xml .= "
<url>
<loc>$url</loc>
<lastmod>$page_date_str</lastmod>
<changefreq>monthly</changefreq>
<priority>0.7</priority>
</url>";
}
$xml .= '
</urlset>';
safe_file_put_contents($static_config['static_output'] . '/sitemap.xml', $xml);
logMessage("   ✅ sitemap.xml 已生成到网站根目录", 'success');
// =====================【在此插入生成 sitemap.txt 的代码】=====================
logMessage("🗺️ 同步生成 sitemap.txt 纯文本站点地图...", 'info');
$txtLines = [];
// 首页
$txtLines[] = STATIC_ROOT_URL . '/';
// 文章链接
foreach ($allPosts as $post) {
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');
    if ($siteMode === 'novel') {
        $url = STATIC_ROOT_URL . "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = STATIC_ROOT_URL . "/{$siteMode}/{$post['id']}.html";
    }
    $txtLines[] = $url;
}
// 单页链接
foreach ($allPages as $path => $page) {
    $url = STATIC_ROOT_URL . "/{$path}.html";
    $txtLines[] = $url;
}
// 写入sitemap.txt，一行一条URL
safe_file_put_contents($static_config['static_output'] . '/sitemap.txt', implode("\n", $txtLines));
logMessage("   ✅ sitemap.txt 已生成到网站根目录", 'success');
// ===========================================================================
} else {
    logMessage("🗺️ 已关闭生成 sitemap.xml", 'warning');
}
*/
// ==========================================
// 新增：生成 404 页面（输出目录根目录 404.html）
// ==========================================
if (qs_switch_on('page_404')) {
logMessage(L('Generating_404_Page'), 'info');
$fortyFourTpl = $themePath . '/404.php';
if (file_exists($fortyFourTpl)) {
    $ctx404 = [
        'page_type' => '404',
        'page'      => 1,
        'total_pages' => 1,
        'loop_posts'  => []
    ];
    $html404 = renderTemplate($fortyFourTpl, $ctx404);
    $html404 = rewriteLinks($html404);
    $save404 = $static_config['static_output'] . '/404.html';
    safe_file_put_contents($save404, $html404);
    logMessage('   ' . L('404_Generated'), 'success');
    $stats['404_page'] = 1;
} else {
    logMessage(L('Missing_404_Template_Skip'), 'warning');
    $stats['404_page'] = 0;
}
} else {
    logMessage(L('404_Disabled'), 'warning');
}
// ==========================================
// 新增：生成隐私政策页（输出 privacy/index.html）
// 只有后台「页脚与隐私」里填写了正文才生成，避免覆盖站点已有的隐私页
// ==========================================
if (trim((string)($qs_config['privacy_body'] ?? '')) !== '') {
    logMessage(L('Generating_Privacy_Page'), 'info');
    $privacyHtml = qs_privacy_html();
    // 隐私页同样注入 Cookie 同意组件，保证静态站每页都有入口
    if (function_exists('qs_consent_inject')) {
        $privacyHtml = qs_consent_inject($privacyHtml);
    }
    $privacyHtml = rewriteLinks($privacyHtml);
    $privacyDir  = rtrim($static_config['static_output'], '/') . '/privacy';
    if (!is_dir($privacyDir)) {
        @mkdir($privacyDir, 0755, true);
    }
    safe_file_put_contents($privacyDir . '/index.html', $privacyHtml);
    logMessage('   ' . L('Privacy_Generated'), 'success');
} else {
    logMessage(L('Privacy_Empty_Skip'), 'warning');
}
// ==========================================
// 生成收款打赏页（输出 donate/index.html）
// 只有开启且配置了至少一种收款方式才生成
// ==========================================
if (function_exists('qs_payment_enabled') && qs_payment_enabled()) {
    $paySlug = trim((string)($qs_config['payment_slug'] ?? ''));
    if ($paySlug === '') $paySlug = 'donate';
    logMessage('Generating payment page: ' . $paySlug, 'info');
    $payHtml = qs_payment_html();
    if (function_exists('qs_consent_inject')) {
        $payHtml = qs_consent_inject($payHtml);
    }
    $payHtml = rewriteLinks($payHtml);
    $payDir  = rtrim($static_config['static_output'], '/') . '/' . $paySlug;
    if (!is_dir($payDir)) {
        @mkdir($payDir, 0755, true);
    }
    safe_file_put_contents($payDir . '/index.html', $payHtml);
    logMessage('   Payment page generated: ' . $paySlug . '/index.html', 'success');
} else {
    logMessage('Payment page skipped (not configured)', 'warning');
}
// ==========================================
// 生成全站 URL 列表 urls.txt
// ==========================================
if (qs_switch_on('urls_txt')) {
logMessage(L('Generating_Urls_Txt'), 'info');
$urlList = [];
// 加入首页
$urlList[] = STATIC_ROOT_URL . '/';
// 加入所有文章
foreach ($allPosts as $post) {
    $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $post['bookname'] ?? '');
    if ($siteMode === 'novel') {
        $url = STATIC_ROOT_URL . "/novel/{$safeBook}/{$post['id']}.html";
    } else {
        $url = STATIC_ROOT_URL . "/{$siteMode}/{$post['id']}.html";
    }
    $urlList[] = $url;
}
// 加入所有单页
foreach ($allPages as $path => $page) {
    $url = STATIC_ROOT_URL . "/{$path}.html";
    $urlList[] = $url;
}
// 写入文件
safe_file_put_contents($static_config['static_output'] . '/urls.txt', implode("\n", $urlList));
logMessage('   ' . L('Urls_Txt_Generated'), 'success');
} else {
    logMessage(L('Urls_Txt_Disabled'), 'warning');
}
// ==========================================
// 自动生成 CNAME 文件（GitHub 域名解析）
// ==========================================
if (qs_switch_on('cname')) {
logMessage(L('Generating_Cname'), 'info');
$cnameContent = trim($static_config['static_root_url']);
// 去掉 http:// https:// 只保留纯域名
$cnameContent = preg_replace('#^https?://#i', '', $cnameContent);
// 写入到静态输出根目录
safe_file_put_contents($static_config['static_output'] . '/CNAME', $cnameContent);
logMessage('   ' . sprintf(L('Cname_Generated'), $cnameContent), 'success');
} else {
    logMessage(L('Cname_Disabled'), 'warning');
}
// ==========================================
// 自动生成 robots.txt（根据 $static_config['robots'] 生成）
// ==========================================
if (qs_switch_on('robots')) {
    logMessage(L('Generating_Robots'), 'info');
    // 优先使用 static_robots 配置，为空则走默认模板
    $robots_content = isset($static_config['static_robots']) && trim($static_config['static_robots']) !== ''
        ? trim($static_config['static_robots'])
        : "User-agent: *\nAllow: /\nSitemap: " . STATIC_ROOT_URL . "/sitemap.xml";
    safe_file_put_contents($static_config['static_output'] . '/robots.txt', $robots_content);
    logMessage('   ' . L('Robots_Generated'), 'success');
} else {
    logMessage(L('Robots_Disabled'), 'warning');
}
// ========== 新增：自动生成google ads.txt ==========
$adsTxtContent = trim($static_config['google_ads'] ?? '');
if (!empty($adsTxtContent)) {
    $staticRoot = rtrim($static_config['static_output'], '/');
    $adsSavePath = $staticRoot . '/ads.txt';
    // 写入ads.txt，校验写入结果
    $writeResult = safe_file_put_contents($adsSavePath, $adsTxtContent);
    if ($writeResult !== false) {
        logMessage(L('Ads_Txt_Generated'), 'success');
    } else {
        logMessage(sprintf(L('Ads_Txt_Write_Failed_Permission'), $adsSavePath), 'warning');
    }
} else {
    logMessage(L('Ads_Txt_Empty_Skip'), 'warning');
}
// ========== 新增：自动生成IndexNow验证txt文件到静态站点根目录 ==========
$key = trim($static_config['IndexNow']);
if (!empty($key)) {
    $indexnowFileName = $key . '.txt';
    $staticRoot = rtrim($static_config['static_output'], '/');
    $indexnowSavePath = $staticRoot . '/' . $indexnowFileName;
    if (!is_dir($staticRoot)) {
        mkdir($staticRoot, 0755, true);
    }
    safe_file_put_contents($indexnowSavePath, $key);
    logMessage(sprintf(L('IndexNow_File_Generated'), $indexnowFileName), 'success');
} else {
    logMessage(L('IndexNow_Key_Empty_Skip'), 'warning');
}
// ======================================================================
// ========== 工具函数：扫描html文件（改为匿名函数，防止重复定义报错） ==========
    while (ob_get_level()) ob_end_clean();
    ob_implicit_flush(true);
   
    $hostFullUrl = rtrim($static_config['static_root_url'] ?? '');
    $staticRoot = rtrim($static_config['static_output'], '/');
    // 输出目录统一转成绝对路径：cwd 不同（网页方式与 CLI 方式）时相对路径会落到别处或写入失败
    if (!preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\|\/)/', $staticRoot)) {
        // 相对路径按脚本所在目录（admin/）解析；不能用 SITE_ROOT，否则 ../static_site 会落到项目上级
        $staticRootAbs = rtrim(__DIR__ . DIRECTORY_SEPARATOR
            . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($staticRoot, '/\\')), '/\\');
    } else {
        $staticRootAbs = $staticRoot;
    }
    if(!is_dir($staticRoot)){
        $log(sprintf(L('Static_Output_Dir_Not_Exist'), $staticRoot),false);
        exit(1);
    }
function scanHtmlFiles(string $dir, string $baseLocal, int $depth = 0): array
    {
        $results = [];
        // 递归深度保护：防止目录层级异常（或隐藏的链接环）导致无限递归
        if ($depth > 40) { return $results; }
        if (!is_dir($dir)) { return $results; }
        $files = scandir($dir);
        if ($files === false) { return $results; }
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $fullPath = $dir . DIRECTORY_SEPARATOR . $f;
            // 不跟随符号链接 / 目录联接，避免指向父级的链接造成死循环
            if (is_link($fullPath)) { continue; }
            if(is_dir($fullPath)){
                $results = array_merge($results, scanHtmlFiles($fullPath,$baseLocal,$depth + 1));
            }else{
                $ext = strtolower(pathinfo($f,PATHINFO_EXTENSION));
                if($ext === 'html'){
                    $rel = substr($fullPath,strlen($baseLocal));
                    $rel = str_replace(DIRECTORY_SEPARATOR,'/',$rel);
                    $results[] = $rel;
                }
            }
        }
        return $results;
    }
    $htmlRelPaths = scanHtmlFiles($staticRoot,$staticRoot);
    $urlList = [];
    foreach ($htmlRelPaths as $rel){
        $urlList[] = $hostFullUrl.$rel;
    }
    $urlList = array_values(array_unique($urlList));
    $apiUrl = 'https://api.indexnow.org/indexnow';
    //解析裸host，协议要求host字段只填域名，不带http
    $hostPure = parse_url($hostFullUrl,PHP_URL_HOST);
    if($hostPure === null){
        $hostPure = parse_url('https://'.$hostFullUrl,PHP_URL_HOST);
    }
    $batchSize = 10000; //官方上限10000，取保守值
    $batches = array_chunk($urlList,$batchSize);
    $allOk = true;
foreach ($batches as $idx=>$batchUrls){
    $payloadArr = [
        "host" => $hostPure,
        "key" => $key,
        "urlList" => $batchUrls
    ];
    if(!empty($key)){
        $keyLocation = rtrim($hostFullUrl,'/') . '/' . $key . '.txt';
        $payloadArr['keyLocation'] = $keyLocation;
     
    }
$payload = json_encode($payloadArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    // ==========日志写入 control/data/indexnow_log 绝对路径==========
    if (!defined('SITE_ROOT')) {
        define('SITE_ROOT', dirname(__DIR__));
    }
$datapaths = SITE_ROOT . '/control/data/';  // 管理用数据文件所在路径
    $logDir = $datapaths . 'indexnow_log';
    $dirExists = is_dir($logDir);
    //尝试递归创建目录
    if(!$dirExists){
        $mkdirRet = mkdir($logDir,0755,true);
        if(!$mkdirRet){
        }
    }
    $fileName = 'batch_'.date("Ymd_His")."_no".($idx+1).".json";
    $saveFile = $logDir . '/' . $fileName;
    $writeRet = false;
    if(is_dir($logDir)){
        $writeRet = safe_file_put_contents($saveFile,$payload);
    }
// sitemap.txt 的写入已移到 IndexNow 批次循环之外（见循环结束后），此处不再重复写入
    if($writeRet === false){
        $viewUrl = '';
    }else{
        $viewUrl = htmlspecialchars("show_indexnow_batch.php?f=".$fileName);
    }
    // =============================================================
 
    $ch = curl_init($apiUrl);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
    curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json; charset=utf-8']);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,15);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
// PHP < 8.0 才执行 curl_close
if (PHP_VERSION_ID < 80000) {
    curl_close($ch);
}

    if($resp === false || !empty($curlErr)){
        $allOk = false;
    }else{
   }
    sleep(1);
}
// ========== sitemap.txt：只在批次循环之外写一次 ==========
// 原先把这段放在 foreach ($batches ...) 循环里，批次多时会反复覆盖写入；
// 而且 safe_file_put_contents 在“内容与上次相同”时返回 false（表示跳过写入），
// 被当成写入失败报了 warning。这里改为以“文件内容是否到位”判断成败。
// 与后台「生成 sitemap」开关联动：关闭时不产出 sitemap.txt / sitemap.xml
if (qs_switch_on('sitemap')) {
$sitemapTxtContent = implode("\n", $urlList) . "\n";
$sitemapOutPathtxt = $staticRootAbs . '/sitemap.txt';
safe_file_put_contents($sitemapOutPathtxt, $sitemapTxtContent);
$qs_txt_written = is_file($sitemapOutPathtxt) && ((string)@file_get_contents($sitemapOutPathtxt) === $sitemapTxtContent);
if ($qs_txt_written) {
    logMessage(sprintf(L('Sitemap_Txt_Generated_Count'), count($urlList)), 'success');
} else {
    logMessage(L('Sitemap_Txt_Write_Failed') . ' → ' . $sitemapOutPathtxt, 'warning');
}
// ========== 3、推送完毕后生成标准 sitemap.xml（全盘扫描产出，包含分类/标签等页面） ==========
$fileItems = scanHtmlFiles($staticRoot,$staticRoot);
$xmlLines = [];
foreach ($fileItems as $rel){
    $loc = $hostFullUrl . $rel;
    $lastmodIso = null;
    $basename = basename($rel);
    $aid = pathinfo($basename,PATHINFO_FILENAME);
    // 匹配普通文章：读取publish.php的date+time拼接完整时间
    if(isset($allPosts[$aid])){
        $art = $allPosts[$aid];
        // publish.php date + time 拼接完整日期时间字符串
        $datetimeStr = $art['date'] . ' ' . $art['time'];
        $ts = strtotime($datetimeStr);
        if ($ts !== false) {
            $dt = new DateTime('@'.$ts);
            $dt->setTimezone(new DateTimeZone('Asia/Shanghai'));
            $lastmodIso = $dt->format('c');
        }
    }
    // 匹配pages单页
    if($lastmodIso === null){
        foreach ($allPages as $pageData){
            if($pageData['file'] === $aid){
                $ts = $pageData['date'];
                if(is_numeric($ts)){
                    $dt = new DateTime('@'.$ts);
                    $dt->setTimezone(new DateTimeZone('Asia/Shanghai'));
                    $lastmodIso = $dt->format('c');
                }
                break;
            }
        }
    }
    // 分类、标签、列表页：依旧降级使用html文件mtime(SSG构建时间)
    if ($lastmodIso === null) {
        $fullHtmlPath = $staticRoot . str_replace('/',DIRECTORY_SEPARATOR,$rel);
        $tsHtml = filemtime($fullHtmlPath);
        $dt = new DateTime('@' . $tsHtml);
        $dt->setTimezone(new DateTimeZone('Asia/Shanghai'));
        $lastmodIso = $dt->format('c');
    }
    $xmlLines[] = [
        'loc'=>$loc,
        'lastmod'=>$lastmodIso
    ];
}
$xmlLines = array_values(array_unique($xmlLines,SORT_REGULAR));
$sitemapXml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
foreach ($xmlLines as $item){
    $locEsc = htmlspecialchars($item['loc'], ENT_XML1);
    $lmEsc = htmlspecialchars($item['lastmod'], ENT_XML1);
    $sitemapXml .= "  <url>\n    <loc>{$locEsc}</loc>\n    <lastmod>{$lmEsc}</lastmod>\n  </url>\n";
}
$sitemapXml .= "</urlset>\n";
$sitemapOutPath = rtrim($static_config['static_output'] ?? '', '/') . '/sitemap.xml';
$writeRet = safe_file_put_contents($sitemapOutPath,$sitemapXml);
if($writeRet){
    logMessage(sprintf(L('Sitemap_Xml_Generated_Count'), count($xmlLines)),'success');
}else{
    logMessage(L('Sitemap_Xml_Write_Failed'),'warning');
}
}
// ======================================================================
save_build_cache($cacheFilePath, $newCache);
logMessage(L('Build_Cache_Saved'), 'info');
write_operate_log(qs_get_current_admin_name(), L('Ssg_Publish'), 'ssg_work', 'ssg_work.php');
?>
        </div>
        
        <div class="final-message">
            <?php echo sprintf(L('Site_Build_Success'), $siteMode, '<a href="'.STATIC_ROOT_URL.'" target="_blank">'.STATIC_ROOT_URL.'</a>'); ?>
        </div>
        <div class="final-message">
            <?= L('Local_Preview_Path'); ?> 
            <?php
            $previewUrl = STATIC_ROOT_URL;
            if (!empty($static_output)) {
                $currentDir = dirname($_SERVER['REQUEST_URI']);
                $trimOutput = trim($static_output, '/');
                $levelCount = substr_count($trimOutput, '../');
                $dirParts = explode('/', trim($currentDir, '/'));
                if ($levelCount > 0) {
                    $dirParts = array_slice($dirParts, 0, -$levelCount);
                }
                $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
                $basePath = implode('/', $dirParts);
                $targetFolder = basename($trimOutput);
                $previewUrl = $host . '/' . $basePath . ($basePath ? '/' : '') . $targetFolder . '/';
            }
            echo "<a href=\"$previewUrl\" target=\"_blank\" style=\"color:inherit; text-decoration:underline;\">$previewUrl</a>";
            ?>
        </div>
        <div class="stats">
            <h3><?= L('Generation_Stats'); ?></h3>
            <ul>
                <li>• <?= L('Stats_Home_Pages'); ?> <?php echo $stats['home']; ?></li>
                <li>• <?= L('Stats_Category_List'); ?> <?php echo $stats['category_list']; ?></li>
                <li>• <?= L('Stats_Content_Pages'); ?> <?php echo $stats['chapter']; ?></li>
                <li>• <?= L('Stats_Tag_Pages'); ?> <?php echo $stats['tag']; ?></li>
                <li>• <?= L('Stats_Tags_Page'); ?> <?php echo $stats['tags_page'] ?? 0; ?></li>
                <li>• <?= L('Stats_Pages'); ?> <?php echo $stats['page']; ?></li>
                <li>• <?= L('Stats_404_Page'); ?> <?php echo $stats['404_page'] ?? 0; ?></li>
                <li>• <?= L('Stats_Site_Mode'); ?> <?php echo $siteMode; ?></li>
                <li>• <?= L('Stats_Output_Dir'); ?> <?php echo $static_config['static_output']; ?></li>
                <li>• <?= L('Stats_Site_Domain'); ?> <?php echo STATIC_ROOT_URL; ?></li>
                <li>• <?= L('Stats_Total_Tags'); ?> <?php echo count($tags); ?></li>
                <li>• <?= L('Stats_Friend_Links'); ?> <?php echo count($static_config['qs_links'] ?? array()); ?></li>
                <li>• <?= L('Stats_Main_Menu'); ?> <?php echo count($static_config['qs_menu'] ?? array()); ?></li>
                <li>• <?= L('Stats_Side_Menu'); ?> <?php echo count($static_config['qs_side_menu'] ?? array()); 

/* ============================================================
 * 以下函数按 MySQL 版函数名对齐后补充（文件版此前没有实现）
 * ============================================================ */

function qs_ssg_static_url($link) {
    $link = trim((string)$link);
    if ($link === '') { return '/'; }
    if (preg_match('~^(https?:)?//~i', $link)) { return $link; }            // 外链原样
    if (preg_match('~^#~', $link)) { return $link; }                         // 锚点原样
    // 标签页：/tag/<名称>/ → /tag/<名称>/index.html
    if (preg_match('~^/tag/([^/?]+)/?$~u', $link, $m)) {
        return '/tag/' . $m[1] . '/index.html';
    }
    // 标签总览：/tags/ → /tags/index.html
    if (preg_match('~^/tags/?$~u', $link)) { return '/tags/index.html'; }
    // 分页列表：/page/2/ → /page/2/index.html
    if (preg_match('~^/page/(\d+)/?$~u', $link, $m)) {
        return '/page/' . $m[1] . '/index.html';
    }
    // 其余（/、.html 单页等）原样保留
    return $link;
}

function qs_ssg_menu_apply_static($items) {
    if (!is_array($items)) { return array(); }
    foreach ($items as $k => $item) {
        if (isset($item['link'])) {
            $items[$k]['link'] = qs_ssg_static_url($item['link']);
        }
    }
    return $items;
}

function qs_ssg_custom_apply_static($groups) {
    if (!is_array($groups)) { return array(); }
    foreach ($groups as $k => $g) {
        if (isset($g['items']) && is_array($g['items'])) {
            $groups[$k]['items'] = qs_ssg_menu_apply_static($g['items']);
        }
    }
    return $groups;
}

function qs_sc_ctx($key, $default = '')
    {
        if (isset($GLOBALS['static_config'][$key]) && $GLOBALS['static_config'][$key] !== '') {
            return $GLOBALS['static_config'][$key];
        }
        if (isset($GLOBALS['qs_config'][$key]) && $GLOBALS['qs_config'][$key] !== '') {
            return $GLOBALS['qs_config'][$key];
        }
        return $default;
    }

function qs_sc_current()
    {
        $d = $GLOBALS['static_config']['current_data'] ?? null;
        if (!$d && !empty($GLOBALS['qs_post'])) {
            $d = $GLOBALS['qs_post'];
        }
        return is_array($d) ? $d : array();
    }

function qs_site_link($print = true)
    {
        $link = rtrim((string)STATIC_ROOT_URL, '/');
        if ($print) {
            echo $link;
            return;
        }
        return $link;
    }

function qs_theme_url($path = '', $print = true)
    {
        $theme = (string)($GLOBALS['static_config']['static_theme_name'] ?? '');
        $url = rtrim((string)STATIC_ROOT_URL, '/') . '/files/theme/' . $theme . '/' . ltrim((string)$path, '/');
        if ($print) {
            echo $url;
            return;
        }
        return $url;
    }

function qs_post_link($post = null, bool $echo = true)
    {
        $mode = (string)qs_sc_ctx('sitemode', 'post');
        $data = is_array($post) ? $post : qs_sc_current();
        $root = rtrim((string)STATIC_ROOT_URL, '/');

        if (!empty($data['is_page']) || ($GLOBALS['static_config']['page_type'] ?? '') === 'page') {
            $path = (string)($data['path'] ?? ($data['id'] ?? ''));
            $url = $root . '/' . $path . '.html';
        } elseif ($mode === 'novel') {
            $book = trim((string)($data['bookname'] ?? ''));
            // 空书名统一归入 Uncategorized，与 SSG 生成目录保持一致，避免 /novel//id.html 双斜杠死链
            $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $book !== '' ? $book : 'Uncategorized');
            $url = $root . '/novel/' . $safeBook . '/' . (string)($data['id'] ?? '') . '.html';
        } else {
            $url = $root . '/' . $mode . '/' . (string)($data['id'] ?? '') . '.html';
        }

        // 静态站在页面里也要和内联链接保持一致：交给 rewriteLinks 统一补域名
        $url = str_replace($root, '', $url);
        if ($echo) {
            echo $url;
            return;
        }
        return $url;
    }

function qs_get_the_link() {
    return qs_the_link_url();
}

function qs_postedit_link()
    {
        return '';
    }

function qs_postedit($text)
    {
        return;
    }

function qs_site_name($print = true)
    {
        $v = htmlspecialchars((string)qs_sc_ctx('site_name'));
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_site_desc($print = true)
    {
        $v = htmlspecialchars((string)qs_sc_ctx('site_desc'));
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_site_tags($print = true)
    {
        $v = htmlspecialchars((string)qs_sc_ctx('site_tags'));
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_site_about()
    {
        echo (string)qs_sc_ctx('site_about');
    }

function qs_site_theme($print = true)
    {
        $v = htmlspecialchars((string)($GLOBALS['static_config']['static_theme_name'] ?? ''));
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_nick_code($print = true)
    {
        $v = htmlspecialchars((string)qs_sc_ctx('nick_code'));
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_user_nick()
    {
        echo htmlspecialchars((string)qs_sc_ctx('site_name'));
    }

function qs_sitelogo($print = true)
    {
        $v = (string)qs_sc_ctx('sitelogo');
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_sitelogoname()
    {
        $mode = (string)qs_sc_ctx('sitelogoname', 'name');
        echo '<a href="' . rtrim((string)STATIC_ROOT_URL, '/') . '">';
        if ($mode === 'all' || $mode === 'logo') {
            echo '<img class="logo regular" width="50" src="/' . qs_sitelogo(false) . '" alt="' . qs_site_name(false) . '">';
        }
        if ($mode === 'all' || $mode === 'name') {
            echo qs_site_name(false);
        }
        echo '</a>';
    }

function qs_ico(): string
    {
        $iconPath = trim((string)qs_sc_ctx('ico'));
        if ($iconPath === '') {
            return '';
        }
        return '<link rel="icon" href="' . htmlspecialchars('/' . $iconPath, ENT_QUOTES) . '">';
    }

function qs_head_code($print = true)
    {
        $v = (string)qs_sc_ctx('head_code');
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_footer_code()
    {
        echo (string)qs_sc_ctx('footer_code');
        if (function_exists('qs_footer_info')) {
            qs_footer_info();
        }
    }

function qs_other_code()
    {
        echo (string)qs_sc_ctx('other_code');
    }

function qs_is_search()
    {
        return ($GLOBALS['static_config']['page_type'] ?? '') === 'search';
    }

function qs_is_tags()
    {
        return ($GLOBALS['static_config']['page_type'] ?? '') === 'tags';
    }

function qs_is_date()
    {
        return ($GLOBALS['static_config']['page_type'] ?? '') === 'date';
    }

function qs_is_archive()
    {
        return ($GLOBALS['static_config']['page_type'] ?? '') === 'archive';
    }

function qs_tag_name($print = true)
    {
        $v = (string)($GLOBALS['static_config']['current_tag'] ?? '');
        if ($print) {
            echo htmlspecialchars($v);
            return;
        }
        return $v;
    }

function qs_date_name($print = true)
    {
        $v = (string)($GLOBALS['static_config']['current_date'] ?? '');
        if ($print) {
            echo htmlspecialchars($v);
            return;
        }
        return $v;
    }

function qs_page_title($print = true)
    {
        $d = qs_sc_current();
        $t = (string)($d['title'] ?? '');
        $v = htmlspecialchars($t);
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_tag_list($item_begin = '<li>', $item_gap = '', $item_end = '</li>')
    {
        $tags = $GLOBALS['static_config']['all_tags'] ?? array();
        if (!is_array($tags) || empty($tags)) {
            return;
        }
        $root = rtrim((string)STATIC_ROOT_URL, '/');
        $i = 0;
        $n = count($tags);
        foreach ($tags as $name => $posts) {
            if (is_string($name)) {
                $label = $name;
            } elseif (is_array($posts) && isset($posts['name'])) {
                $label = (string)$posts['name'];
            } elseif (is_array($posts) && isset($posts[0]) && is_string($posts[0])) {
                $label = (string)$posts[0];
            } else {
                continue;
            }
            $safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $label);
            echo $item_begin . '<a href="' . $root . '/tag/' . $safe . '/index.html">' . htmlspecialchars($label) . '</a>' . $item_end;
            if ($i < $n - 1) {
                echo $item_gap;
            }
            $i++;
        }
    }

function qs_tag_cloud($item_begin = '<li>', $item_gap = '', $item_end = '</li>', $limit = 40)
    {
        $tags = $GLOBALS['static_config']['all_tags'] ?? array();
        if (!is_array($tags) || empty($tags)) {
            return;
        }
        $root = rtrim((string)STATIC_ROOT_URL, '/');
        $i = 0;
        foreach ($tags as $name => $posts) {
            if ($limit > 0 && $i >= $limit) {
                break;
            }
            if (is_string($name)) {
                $label = $name;
            } elseif (is_array($posts) && isset($posts['name'])) {
                $label = (string)$posts['name'];
            } elseif (is_array($posts) && isset($posts[0]) && is_string($posts[0])) {
                $label = (string)$posts[0];
            } else {
                continue;
            }
            $safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $label);
            echo $item_begin . '<a href="' . $root . '/tag/' . $safe . '/index.html">' . htmlspecialchars($label) . '</a>' . $item_end;
            if ($i > 0) {
                echo $item_gap;
            }
            $i++;
        }
    }

function qs_date_list($item_begin = '<li>', $item_gap = '', $item_end = '</li>')
    {
        // 静态站不单独生成年月归档页，日期列表按标签聚合口径退化为空，避免死链
        return;
    }

function qs_first_image_src($html, $default = '')
    {
        $html = (string)$html;
        if (trim($html) === '') {
            return $default;
        }
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i', $html, $m)) {
            $src = trim($m[2]);
            if ($src !== '') {
                return $src;
            }
        }
        return $default;
    }

function qs_has_content_pic()
    {
        $d = qs_sc_current();
        if (!empty($d['content'])) {
            return qs_first_image_src($d['content'], '') !== '';
        }
        return false;
    }

function qs_card_media()
    {
        $d = qs_sc_current();
        $img = '';
        if (!empty($d['content'])) {
            $img = qs_first_image_src($d['content'], '');
        }
        if ($img === '') {
            $img = (string)qs_sc_ctx('defaultpostimage');
        }
        $video = trim((string)($d['video'] ?? ''));
        if ($video === '' && !empty($d['content'])) {
            $video = qs_content_video_src($d['content']);
        }
        if ($video === '') {
            return array('type' => 'image', 'src' => $img, 'poster' => $img, 'mime' => '');
        }
        $path = (string)parse_url($video, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'video/mp4';
        if ($ext === 'webm') {
            $mime = 'video/webm';
        } elseif ($ext === 'ogv' || $ext === 'ogg') {
            $mime = 'video/ogg';
        }
        return array('type' => 'video', 'src' => $video, 'poster' => $img, 'mime' => $mime);
    }

function qs_block_tag_posts($tag_name, $tag_desc = '', $limit = 10)
    {
        $all = $GLOBALS['static_config']['allPosts'] ?? array();
        $limit = ($limit === null || $limit === '') ? 10 : (int)$limit;
        $list = array();
        if (is_array($all)) {
            foreach ($all as $post) {
                if (!empty($post['tags']) && is_array($post['tags']) && in_array($tag_name, $post['tags'], true)) {
                    $list[] = $post;
                    if (count($list) >= $limit) {
                        break;
                    }
                }
            }
        }
        $safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $tag_name);
        $tag_url = rtrim((string)STATIC_ROOT_URL, '/') . '/tag/' . $safe . '/index.html';
        ?>
        <div class="category-area">
            <a href="<?php echo $tag_url; ?>"><hgroup class="cate-row"><h3><?php echo htmlspecialchars($tag_name); ?></h3><?php if ($tag_desc) { ?><p><?php echo htmlspecialchars($tag_desc); ?></p><?php } ?></hgroup></a>
            <?php if ($list) {
                foreach ($list as $post) {
                    $pid = (string)($post['id'] ?? '');
                    echo '<a href="/' . (string)qs_sc_ctx('sitemode', 'post') . '/' . $pid . '.html" class="post-item">' . htmlspecialchars((string)($post['title'] ?? '')) . '</a>';
                }
            } else { ?>
                <div class="post-item empty-tip">暂无文章</div>
            <?php } ?>
        </div>
        <?php
    }

function qs_carousel(array $qs_config, string $configKey): void
    {
        $raw = trim((string)($qs_config[$configKey] ?? ''));
        if ($raw === '') {
            return;
        }
        $bannerList = array();
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $arr = explode('|', $line, 2);
            $img = trim($arr[0] ?? '');
            if ($img !== '') {
                $bannerList[] = array('img' => $img, 'text' => trim($arr[1] ?? ''));
            }
        }
        if (empty($bannerList)) {
            return;
        }
        echo '<!-- 轮播 --><div class="banner"><div class="swiper-container"><div class="swiper-wrapper">';
        foreach ($bannerList as $row) {
            echo '<div class="swiper-slide"><img src="' . htmlspecialchars($row['img']) . '"><div class="banner-text">' . htmlspecialchars($row['text']) . '</div></div>';
        }
        echo '</div><div class="swiper-pagination"></div></div></div>';
    }

function qs_comment_code()
    {
        return '';
    }

if (!function_exists('qs_consent_enabled')) {
function qs_consent_enabled()
    {
        if (function_exists('qs_consent_config')) {
            $cfg = qs_consent_config();
            return !empty($cfg['enabled']);
        }
        return false;
    }
}

function qs_load_theme_config()
    {
        $theme = (string)($GLOBALS['static_config']['static_theme_name'] ?? '');
        $file = SITE_ROOT . '/files/theme/' . $theme . '/theme_config.php';
        $theme_tag_blocks = array();
        if (is_file($file)) {
            include $file;
        }
        return is_array($theme_tag_blocks) ? $theme_tag_blocks : array();
    }

function qs_custom_menu($key, $cssClass = '') {
        global $qs_custom_menus;
        if (!isset($qs_custom_menus[$key]['items']) || !is_array($qs_custom_menus[$key]['items'])) {
            return;
        }
        $cls = $cssClass !== '' ? ' class="' . htmlspecialchars($cssClass) . '"' : '';
        echo '<ul>';
        foreach ($qs_custom_menus[$key]['items'] as $item) {
            echo '<li><a href="' . $item['link'] . '" target="' . $item['tar'] . '"' . $cls . '>' . $item['name'] . '</a></li>';
        }
        echo '</ul>';
    }

function qs_safe_book($name) {
    $name = trim((string)$name);
    if ($name === '') { $name = 'Uncategorized'; }
    return preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $name);
}

function qs_the_time() {
    global $static_config;
    return isset($static_config['current_data']['time']) ? $static_config['current_data']['time'] : '';
}

function qs_the_link_url()
{
    global $static_config, $qs_config;
    $d = $static_config['current_data'];
    $siteMode = $qs_config['sitemode'];
    $staticRoot = $static_config['static_root_url'];
    if (!$d || !is_array($d)) {
        $val = '';
    } elseif ($static_config['page_type'] === 'page') {
        $path = isset($d['path']) ? $d['path'] : $d['id'];
        $val = "{$staticRoot}/{$path}.html";
    } elseif ($siteMode === 'novel') {
        $book = isset($d['bookname']) ? $d['bookname'] : '';
        $safeBook = qs_safe_book($book);
        $val = "{$staticRoot}/{$siteMode}/{$safeBook}/{$d['id']}.html";
    } else {
        $val = "{$staticRoot}/{$siteMode}/{$d['id']}.html";
    }
    return $val;
}

function qs_video_block($video, $autoplay = '', $video_picsrc = '', $class = '')
    {
        $video = trim((string)$video);
        if ($video === '') {
            return '';
        }
        $path = (string)parse_url($video, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = 'video/mp4';
        if ($ext === 'webm') {
            $mime = 'video/webm';
        } elseif ($ext === 'ogv' || $ext === 'ogg') {
            $mime = 'video/ogg';
        }
        $uid = 'qsVideo' . substr(md5($video), 0, 8);
        $auto = ($autoplay === '') ? '' : ' autoplay';
        $html = '<div class="qs-video-wrap" id="' . $uid . '">';
        $html .= '<video class="qs-video ' . htmlspecialchars((string)$class) . '" controls' . $auto . ' muted playsinline preload="metadata"';
        if ($video_picsrc !== '') {
            $html .= ' poster="' . htmlspecialchars((string)$video_picsrc) . '"';
        }
        $html .= '><source src="' . htmlspecialchars($video) . '" type="' . $mime . '">您的浏览器不支持视频播放。</video>';
        $html .= '<div class="qs-video-hint">当前静音，点击画面或拖动音量滑块即可收听</div></div>';
        return $html;
    }

function qs_the_video($autoplay = '', $video_picsrc = '', $class = '')
    {
        $d = qs_sc_current();
        $video = trim((string)($d['video'] ?? ''));
        if ($video === '' && !empty($d['content'])) {
            $video = qs_content_video_src($d['content']);
        }
        if ($video === '') {
            return;
        }
        echo qs_video_block($video, $autoplay, $video_picsrc, $class);
    }

function meta_tags($item_begin = '', $item_gap = ',', $item_end = '')
    {
        $post = $GLOBALS['static_config']['current_data'] ?? null;
        $tags = is_array($post) ? ($post['tags'] ?? []) : [];
        if (!is_array($tags)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', (string)$tags)), 'strlen'));
        }
        $count = count($tags);
        $i = 0;
        foreach ($tags as $t) {
            echo $item_begin . htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8') . $item_end;
            if ($i < $count - 1) {
                echo $item_gap;
            }
            $i++;
        }
    }



/* ============================================================
 * 以下函数按 MySQL 版函数名对齐后补充（文件版此前没有实现）
 * ============================================================ */

function qs_the_user($print = true)
    {
        $d = qs_sc_current();
        $v = (string)($d['user'] ?? '');
        if ($print) {
            echo $v;
            return;
        }
        return $v;
    }

function qs_content_video_src($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }
        if (preg_match('/(?:src|data-src)\s*=\s*(["\'])([^"\']+\.(?:mp4|webm|ogv|ogg|m3u8?)(?:[?#][^"\']*)?)\1/i', $html, $m)) {
            return trim($m[2]);
        }
        return '';
    }

?></li>
            </ul>
        </div>
    </div>
</body>
</html>
