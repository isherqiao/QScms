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
 * @File         index.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 15/09/26 22:41:35
 * @Description  Front controller: install-lock check, route resolution and dispatch to the requested page or post.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/* 未安装引导：没有 install/install.lock 时先跳到安装向导（锁是“是否已安装”的唯一判据） */
if (!is_file(__DIR__ . '/install/install.lock') && is_file(__DIR__ . '/install/install.php')) {
    header('Location: install/install.php');
    exit;
}

ob_start();
require_once __DIR__.'/control/core.php';
// 先解析路由（必须放最前面！）
$qs = $_SERVER['QUERY_STRING'];
// 隐私政策页地址：后台可在「页脚与隐私」里自定义，默认 privacy
$qs_privacy_slug = trim((string)($qs_config['privacy_slug'] ?? ''));
if ($qs_privacy_slug === '') {
    $qs_privacy_slug = 'privacy';
}
// 收款打赏页地址：后台可在「收款打赏」里自定义，默认 donate
$qs_payment_slug = trim((string)($qs_config['payment_slug'] ?? ''));
if ($qs_payment_slug === '') {
    $qs_payment_slug = 'donate';
}
// 收款列表页地址：默认 donors
$qs_donors_slug = trim((string)($qs_config['payment_donors_slug'] ?? ''));
if ($qs_donors_slug === '') {
    $qs_donors_slug = 'donors';
}
// 各支付方式独立页面地址（静态二维码模式下使用）
$qs_method_slugs = [];
$_ms = trim((string)($qs_config['payment_alipay_slug'] ?? '')); if ($_ms) $qs_method_slugs[$_ms] = 'alipay';
$_ms = trim((string)($qs_config['payment_wechat_slug'] ?? '')); if ($_ms) $qs_method_slugs[$_ms] = 'wechat';
$_ms = trim((string)($qs_config['payment_paypal_slug'] ?? '')); if ($_ms) $qs_method_slugs[$_ms] = 'paypal';
// 白名单：登录、登出 不受网站关闭影响
$allow_pages = ['login/', 'logout/'];

if(isset($qs_config['site_close']) && $qs_config['site_close'] == '0') {
    $is_admin_logged = qs_Pass() || qs_UserPass();
    if(!in_array($qs, $allow_pages) && !$is_admin_logged){
        $redirectUrl = trim($qs_config['site_close_redirect'] ?? '');
        $closeDescRaw = trim($qs_config['site_close_desc'] ?? '');
        $homeUrl = trim($qs_config['site_link'] ?? '');

        // 1.优先跳转链接
        if (!empty($redirectUrl)) {
            header("HTTP/1.1 302 Found");
            header("Location: " . $redirectUrl);
            exit;
        }

        // 2.输出自定义HTML关闭页面，返回503状态码
        if (!empty($closeDescRaw)) {
            header("HTTP/1.1 503 Service Unavailable");
            header('Retry-After: 3600');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
            echo $closeDescRaw;
            echo '</body></html>';
            exit;
        }

        //3.兜底默认提示
        header("HTTP/1.1 503 Service Unavailable");
        header('Retry-After: 3600');
        if(!empty($homeUrl)){
            die("Please visit ".htmlspecialchars($homeUrl));
        }else{
            die("Site is closed for maintenance");
        }
    }
}

if (!isset($qs_config)) {
    die(L('Please_check_config_file_exists.'));
}
$qs_post_per_page = $qs_config['per_page'];
// 去掉 & 后面的附加参数（如 ?donate&done=1），只保留路径部分做路由匹配
$qs = explode('&', $_SERVER['QUERY_STRING'])[0];
$sitemode = $qs_config['sitemode'];
$url_ends = $qs_config['url_ends'];
// 新增：封装page参数校验函数（复用性更高）
function validate_page_param($page) {
    // 1. 转换为整数（非数字会转为0）
    $page_int = intval($page);
    // 2. 强制校验：必须是大于0的正整数，否则返回1
    return ($page_int > 0) ? $page_int : 1;
}

function load_post_index_file(){
    // 第一步：提前声明，把$qs_posts绑定到全局作用域
    global $qs_config, $qs_posts;

    $postRoot = rtrim(SITE_ROOT . '/' . ltrim($qs_config['qs_post_dir'], './'), '/') . '/';
    $filePath = $postRoot . 'index/publish.php';

    if(file_exists($filePath) && filesize($filePath) === 0){
        file_put_contents($filePath, "<?php\n\$qs_posts=array (\n);\n?>");
    }
    require $filePath; //第二步，再引入文件，此时$qs_posts会写入全局

    if(!isset($qs_posts) || !is_array($qs_posts)){
        $qs_posts = [];
    }
}

if (preg_match('|^'.$sitemode.'/([a-z0-9]+)'.$url_ends.'$|', $qs, $matches)) {
    $qs_get_type = $sitemode;
    $qs_get_name = $matches[1];
} elseif (preg_match('|^tag/([^/]+)/(\?page=([0-9]+)){0,1}$|', $qs, $matches)) {
    $qs_get_type = 'tag';
    $qs_get_name = urldecode($matches[1]);
    // 修复：对正则匹配到的page参数也做校验（防止正则漏判）
    $qs_page_num = isset($matches[2]) ? validate_page_param($matches[3]) : 1;
} elseif (preg_match('|^bookname/([^/]+)/(\?page=([0-9]+)){0,1}$|', $qs, $matches)) {
    $qs_get_type = 'bookname';
    $qs_get_name = urldecode($matches[1]);
    // 修复：同上
    $qs_page_num = isset($matches[2]) ? validate_page_param($matches[3]) : 1;
} elseif (preg_match('|^search/([^/]+)/(\?page=([0-9]+)){0,1}$|', $qs, $matches)) {
    $qs_get_type = 'search';
    $qs_get_name = urldecode($matches[1]);
    // 修复：同上
    $qs_page_num = isset($matches[2]) ? validate_page_param($matches[3]) : 1;
} elseif (preg_match('|^date/([0-9]{4}-[0-9]{2})/(\?page=([0-9]+)){0,1}$|', $qs, $matches)) {
    $qs_get_type = 'date';
    $qs_get_name = urldecode($matches[1]);
    // 修复：同上
    $qs_page_num = isset($matches[2]) ? validate_page_param($matches[3]) : 1;
} elseif (preg_match('|^archive/$|', $qs, $matches)) {
    $qs_get_type = 'archive';
} elseif ($qs == 'tags/' || $qs == 'tags' . $url_ends) {
    // 全部关键词（标签云）
    $qs_get_type = 'tags';
    $qs_get_name = '';
} elseif ($qs == 'rss/') {
    $qs_post_per_page = '100';
    $qs_get_type = 'rss';
    $qs_get_name = '';
    // 修复1：读取page后通过校验函数过滤（负数/非数字转为1）
    $qs_page_num = isset($_GET['page']) ? validate_page_param($_GET['page']) : 1;
} elseif ($qs === $qs_privacy_slug . '/' || $qs === $qs_privacy_slug) {
    $qs_get_type = 'privacy';
    $qs_get_name = '';
} elseif ($qs === $qs_payment_slug . '/' || $qs === $qs_payment_slug) {
    $qs_get_type = 'donate';
    $qs_get_name = '';
} elseif ($qs === $qs_donors_slug . '/' || $qs === $qs_donors_slug) {
    $qs_get_type = 'donors';
    $qs_get_name = '';
} elseif ($qs === 'pay_notify/') {
    $qs_get_type = 'pay_notify';
    $qs_get_name = '';
} elseif (isset($qs_method_slugs[rtrim($qs, '/')])) {
    $qs_get_type = 'pay_method';
    $qs_get_name = $qs_method_slugs[rtrim($qs, '/')];
} elseif (preg_match('|^vote/([0-9]+)/?$|', $qs, $matches)) {
    $qs_get_type = 'vote';
    $qs_get_name = $matches[1];
} elseif ($qs === 'vote/' || $qs === 'vote') {
    $qs_get_type = 'vote';
    $qs_get_name = '';
} elseif ($qs == 'login/') {
    $qs_get_type = 'login';
    $qs_get_name = '';
} elseif ($qs == 'logout/') {
    $qs_get_type = 'logout';
    $qs_get_name = '';
} elseif ($qs == 'index') {
    $qs_get_name = '';
} elseif (preg_match('|^(([-a-zA-Z0-5]+/)+)$|', $qs, $matches)) {
    $qs_get_type = 'page';
    $qs_get_name = substr($matches[1], 0, -1);
} else {
    $qs_get_type = 'index';
    $qs_get_name = '';
    // 修复2：核心漏洞点——对默认路由的page参数做严格校验
    $qs_page_num = isset($_GET['page']) ? validate_page_param($_GET['page']) : 1;
}
$reverse_order = ($qs_config['sitemode'] === 'novel');
if ($qs_get_type == $sitemode) {
    load_post_index_file();
    if ($reverse_order) {
        $qs_posts = array_reverse($qs_posts);
    }
    if (array_key_exists($qs_get_name, $qs_posts)) {
        $qs_post_id = $qs_get_name;
        $qs_post = $qs_posts[$qs_post_id]; 
        
        $file_content = file_get_contents('data/posts/data/'.$qs_post_id.'.php');
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $qs_data = unserialize($file_content); 
    } else {
        qs_404();
    }
} elseif ($qs_get_type == 'tags') {
    // 全部关键词（标签云）：汇总已发布文章的所有标签，供主题 tags.php 渲染
    load_post_index_file();
    $qs_all_tags = array();
    foreach ($qs_posts as $qs_tag_one_id => $qs_tag_one_post) {
        $qs_tag_list = (isset($qs_tag_one_post['tags']) && is_array($qs_tag_one_post['tags'])) ? $qs_tag_one_post['tags'] : array();
        foreach ($qs_tag_list as $qs_tag_name) {
            $qs_tag_name = trim((string)$qs_tag_name);
            if ($qs_tag_name === '') { continue; }
            if (!isset($qs_all_tags[$qs_tag_name])) { $qs_all_tags[$qs_tag_name] = array(); }
            $qs_all_tags[$qs_tag_name][] = $qs_tag_one_post;
        }
    }
    $all_tags = $qs_all_tags;
    $allPosts = $qs_posts;
} elseif ($qs_get_type == 'tag') {
    load_post_index_file();
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
    $qs_tag_posts = array();
    for ($i = 0; $i < $qs_post_count; $i++) {
        $id = $qs_post_ids[$i];
        $post = $qs_posts[$id];
        if (in_array($qs_get_name, $post['tags'])) {
            $qs_tag_posts[$id] = $post;
        }
    }
    $qs_posts = $qs_tag_posts;
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
} elseif($qs_get_type == 'bookname') {
    load_post_index_file();
    $bookname_counts = array();
    foreach ($qs_posts as $post) {
        if (isset($post['bookname'])) {
            $bookname = $post['bookname'];
            if (!isset($bookname_counts[$bookname])) {
                $bookname_counts[$bookname] = 0;
            }
            $bookname_counts[$bookname]++;
        }
    }
    $qs_tag_posts = array();
    foreach ($qs_posts as $id => $post) {
        if (isset($post['bookname']) && $post['bookname'] == $qs_get_name) {
            $qs_tag_posts[$id] = $post;
        }
    }
    
    $qs_posts = $qs_tag_posts;
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
} elseif ($qs_get_type == 'search') {
    load_post_index_file();
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
    $qs_search_posts = array();
    // 安全修复：转义正则特殊字符
    $search_key = preg_quote($qs_get_name, '/');
    foreach ($qs_posts as $k => $v) {
        $content_file = 'data/posts/data/' . $k . '.php';
        $content = file_get_contents($content_file);
        if (
            preg_match('/' . $search_key . '/i', $v['title']) ||
            preg_match('/' . $search_key . '/i', implode(', ', $v['tags'])) ||
            preg_match('/' . $search_key . '/i', $content)
        ) {
            $qs_search_posts[$k] = $v;
        }
    }
    $qs_posts = $qs_search_posts;
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
} elseif ($qs_get_type == 'date') {
    load_post_index_file();
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
    $qs_date_posts = array();
    for ($i = 0; $i < $qs_post_count; $i++) {
        $id = $qs_post_ids[$i];
        $post = $qs_posts[$id];
        if (strpos($post['date'], $qs_get_name) === 0) {
            $qs_date_posts[$id] = $post;
        }
    }
    $qs_posts = $qs_date_posts;
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
} elseif ($qs_get_type == 'archive') {
    load_post_index_file();
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
    $tags_array = array();
    $date_array = array();
    for ($i = 0; $i < $qs_post_count; $i++) {
        $post_id = $qs_post_ids[$i];
        $post = $qs_posts[$post_id];
        $date_array[] = $post['date']; 
        $tags_array = array_merge($tags_array, $post['tags']);
    }
    $qs_tags  = array_values(array_unique($tags_array));
    $qs_dates = array_values(array_unique($date_array));
} elseif ($qs_get_type == 'page') {
    require 'data/pages/index/publish.php';
    if (array_key_exists($qs_get_name, $qs_pages)) {
        $qs_post_id = $qs_get_name;
        $qs_post = $qs_pages[$qs_post_id];
        $file_content = file_get_contents('data/pages/data/'.$qs_post['file'].'.php');
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $qs_data = unserialize($file_content);
    } else {
        qs_404();
    }
} else {

    load_post_index_file();
    $qs_post_ids = array_keys($qs_posts);
    $qs_post_count = count($qs_post_ids);
}
$parameters = $_SERVER['QUERY_STRING'];
if ($qs_get_type == 'passwd') {
    require '!passwd.php';
} elseif ($qs_get_type == 'privacy') {
    require 'files/privacy.php';
} elseif ($qs_get_type == 'donate') {
    require 'files/donate.php';
} elseif ($qs_get_type == 'vote') {
    require 'files/vote.php';
} elseif ($qs_get_type == 'donors') {
    require 'files/donors.php';
} elseif ($qs_get_type == 'pay_method') {
    echo qs_payment_single_html($qs_get_name);
} elseif ($qs_get_type == 'pay_notify') {
    require 'files/pay_notify.php';
    exit;
} elseif ($qs_get_type == 'login') {
    require 'control/login.php';
} elseif ($qs_get_type == 'logout') {
    require 'control/logout.php';
} else {
    // 旧图外链：配置了图床前缀时，把正文里 /upload/ 的本地图片换到外部地址
    if (isset($qs_data) && is_array($qs_data) && !empty($qs_data['content']) && is_file(__DIR__ . '/control/data/imagebed.php')) {
        require_once __DIR__ . '/control/imagebed_lib.php';
        if (function_exists('qs_imagebed_url_prefix') && qs_imagebed_url_prefix() !== '') {
            $qs_data['content'] = qs_imagebed_rewrite_html($qs_data['content']);
        }
    }
    require 'files/theme/'.$qs_config['site_theme'].'/index.php';
    search();
    if (!plugins_ok())  {
        // die(L('Please_check_config_file_exists.'));
    }
    // Cookie 同意组件：与主题无关，统一在主题输出之后注入到 </body> 之前
    if (is_file(__DIR__ . '/files/cookie-consent/consent.php')) {
        require_once __DIR__ . '/files/cookie-consent/consent.php';
        if (function_exists('qs_consent_enabled') && qs_consent_enabled()) {
            $qs_out = ob_get_contents();
            if (is_string($qs_out) && $qs_out !== '') {
                ob_clean();
                echo qs_consent_inject($qs_out);
            }
        }
    }
}
ob_end_flush();
?>
