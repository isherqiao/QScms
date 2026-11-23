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
 * @File         functions.php
 * @CreatedTime  06/03/17 23:02:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Global function library: i18n helpers, URL/routing builders, TTS glue, captcha bootstrap and general utilities.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

//function.php

// 语音朗读引擎（TTS），函数集中在 control
require_once __DIR__ . '/tts.php';

function getTargetDirectory($url = '') {
    // 1. 自动获取当前页面完整URL（未传参时）
    if (empty($url)) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST']; // 域名+端口（如x.qsis.cn:88）
        $path = $_SERVER['REQUEST_URI']; // 路径+参数（如/program/abc/admin/set.php?a=1）
        $url = $protocol . $host . $path;
        $url = strtok($url, '?'); // 移除URL参数，只保留路径
    }

    // 2. 解析URL路径并验证格式
    $urlParts = parse_url($url);
    if (!isset($urlParts['path']) || strtolower(substr($urlParts['path'], -7)) !== 'set.php') {
        return '';
    }
    $path = $urlParts['path'];

    // 3. 切割路径，移除set.php和其直接上级目录
    $pathSegments = array_filter(explode('/', rtrim(substr($path, 0, -7), '/')), fn($s) => $s !== '');
    $pathSegments = array_values($pathSegments);
    if (count($pathSegments) > 0) array_pop($pathSegments);

    // 4. 拼接最终路径
    $result = '/' . implode('/', $pathSegments) . '/';
    return $result === '//' ? '/' : $result;
}

function plugins_ok() {
  global $plugins_config, $pluginC, $indexfilename,$datapaths;

$pluginConfFile = $datapaths . 'plugin_conf.php';
  // 检查插件配置文件是否存在
  if (!file_exists($pluginConfFile)) {
  	
    // 如果配置文件不存在，尝试创建它
    $plugin_conf_data = '<?php' . PHP_EOL;
    $plugin_conf_data .= '/* 插件配置文件 */' . PHP_EOL;
    $plugin_conf_data .= '$pluginC = false;' . PHP_EOL;  // 默认值，可以根据需要修改
    $plugin_conf_data .= '?>' . PHP_EOL;
    
    // 尝试创建文件
    if (file_put_contents('control/data/plugin_conf.php', $plugin_conf_data)) {
      echo "插件配置文件创建成功，请根据需要修改它。";
    } else {
      echo "创建插件配置文件失败，请检查权限设置。";
      return; // 如果文件创建失败，终止函数执行
    }
  }

  // 包含插件配置文件
  include('control/data/plugin_conf.php');
  
  // 如果 $pluginC 为 true，则加载插件
  if ($pluginC) {
    foreach (glob('files/plugins/System/*/index.php') as $indexfilename) {
      require $indexfilename;
      }
    foreach (glob('files/plugins/User/*/index.php') as $indexfilename) {
      require $indexfilename;
    
  }
}
}

/**
 * 验证码插件统一接口加载器
 *
 * 目录约定：验证码插件目录必须同时具备 admin.php、config.php（符合插件规范），
 *           并提供 captcha.php 作为接口文件，其中定义：
 *               captcha_plugin_name()   : string          后台下拉显示名
 *               captcha_plugin_render() : void           输出登录页验证码 UI
 *               captcha_plugin_check(array $post) : bool  校验提交，通过返回 true
 *
 * 选择规则：读取全局 $captcha_provider（由 System/captcha_choose 插件后台保存），
 *           指向哪个目录就加载哪个；为空或文件不存在时回落到 System/captcha。
 *
 * @return bool 是否成功加载到可用的验证码插件
 */
/**
 * 检查某个验证码插件是否已填入必要的配置（在 require 之前判断，避免函数被锁定）
 * @param string $rel 插件相对路径，如 System/turnstile
 */
function qs_captcha_keys_ready(string $rel): bool
{
    global $turnstile_sitekey, $turnstile_secret,
           $hcaptcha_sitekey, $hcaptcha_secret,
           $recaptcha_sitekey, $recaptcha_secret;
    $map = [
        'System/turnstile' => ['turnstile_sitekey', 'turnstile_secret'],
        'System/hcaptcha'  => ['hcaptcha_sitekey', 'hcaptcha_secret'],
        'System/recaptcha' => ['recaptcha_sitekey', 'recaptcha_secret'],
    ];
    if (!isset($map[$rel])) {
        return true;
    }
    foreach ($map[$rel] as $var) {
        if (trim((string)($GLOBALS[$var] ?? '')) === '') {
            return false;
        }
    }
    return true;
}

function qs_captcha_boot(): bool
{
    static $loaded = null;
    if ($loaded !== null) {
        return $loaded;
    }
    global $captcha_provider;

    // 明确关闭验证码
    if ($captcha_provider === 'off') {
        $GLOBALS['_captcha_off'] = true;
        return $loaded = false;
    }

    $base = SITE_ROOT . '/files/plugins/';
    $candidates = [];
    if (!empty($captcha_provider)) {
        $candidates[] = $captcha_provider;
    }
    // 默认回落：系统自带图形验证码
    $candidates[] = 'System/captcha';

    foreach ($candidates as $rel) {
        $dir = $base . $rel;
        $file = $dir . '/captcha.php';
        if (!is_file($file) || !is_file($dir . '/admin.php') || !is_file($dir . '/config.php')) {
            continue;
        }
        // 先检查是否配置就绪，未就绪且不是默认图形验证码 → 回落，不 require（防止函数被锁定）
        if ($rel !== 'System/captcha' && !qs_captcha_keys_ready($rel)) {
            $GLOBALS['_captcha_fallback_from'] = $rel;
            continue;
        }
        require_once $file;
        if (function_exists('captcha_plugin_render') && function_exists('captcha_plugin_check')) {
            return $loaded = true;
        }
    }
    return $loaded = false;
}

/**
 * 列出所有可用的验证码插件（供后台 captcha_choose 下拉框使用）
 * 每个插件通过独立的 captcha_info.php 返回显示名，避免多个插件同时 require 时函数冲突
 * @return array [相对路径 => 显示名]
 */
function qs_captcha_list(): array
{
    $base = SITE_ROOT . '/files/plugins/';
    $out = [];
    foreach (['System', 'User'] as $layer) {
        $files = glob($base . $layer . '/*/captcha.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            $dir = dirname($file);
            if (!is_file($dir . '/admin.php') || !is_file($dir . '/config.php')) {
                continue;
            }
            $rel = trim(str_replace($base, '', $dir), '/');
            $infoFile = $dir . '/captcha_info.php';
            $name = is_file($infoFile) ? include $infoFile : $rel;
            $out[$rel] = $name;
        }
    }
    return $out;
}

/**
 * 向第三方 siteverify 接口 POST 校验 token（Turnstile / hCaptcha / reCAPTCHA 通用）
 * @param string $url    siteverify 接口地址
 * @param string $secret 服务端密钥
 * @param string $token   用户提交的 response token
 * @return bool 校验通过返回 true
 */
function qs_captcha_remote_verify(string $url, string $secret, string $token): bool
{
    if ($secret === '' || $token === '') {
        return false;
    }
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => $secret, 'response' => $token]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    if ($resp === false) {
        return false;
    }
    $data = json_decode($resp, true);
    return !empty($data['success']);
}

function IPaddress() {
header('Content-Type: text/plain');

// 获取客户端 IP 地址
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// 输出 IP 地址
echo $ip;

}

function getCurrentURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'];
    $url = $protocol . $domain;
    return $url;
}

function qs_imagehost($print = true) {
global $qs_config;
if ($qs_config['imagehost'] != "") {
 	$imagehost = $qs_config['imagehost']; 
 	if ($print) {
  echo $imagehost;
   return;
}	
}
}

function qs_site_link($print = true) {
global $qs_config;

$site_base = (string)($qs_config['site_link'] ?? '');
$base_host = strtolower((string)parse_url($site_base, PHP_URL_HOST));
$cur_host  = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));

/*
 * 「设置 → 网站地址」填的是回环地址（127.0.0.1 / localhost / ::1）时，改为跟随当前访问的主机。
 * 否则用手机、局域网 IP、内网穿透打开站点，页面上每个绝对链接都会把人拽回本机回环地址，
 * 连登录页都落在 127.0.0.1 上。正式站填了真实域名时行为完全不变。
 */
if ($base_host !== '' && $cur_host !== '' && in_array($base_host, ['127.0.0.1', 'localhost', '::1', '[::1]'], true)) {
	$cur_scheme = 'http';
	if ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
	 || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
		$cur_scheme = 'https';
	}
	$site_base = $cur_scheme . '://' . $cur_host;
}

if ($qs_config['site_directory'] != ""){
	$site_link = $site_base . $qs_config['site_directory'];
} else {
	$site_link = getCurrentURL();
}

if ($print) {
  echo $site_link;
   return;
}
 return $site_link;
}

/**
 * 后台链接基址：取当前请求的协议 + 主机
 *
 * 后台的跳转和资源一律用它。这样「设置」里填的站点地址（site_link）
 * 只用于前台绝对链接（SEO / RSS / 静态生成），本机、局域网 IP、手机
 * 从哪个地址进后台就留在哪个地址，不会被拽回配置里那个域名。
 */
function qs_admin_base() {
global $qs_config;

$scheme = 'http';
if ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
 || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
	$scheme = 'https';
}

$host = (string)($_SERVER['HTTP_HOST'] ?? '');
if ($host === '') {
	return rtrim((string)($qs_config['site_link'] ?? ''), '/');
}

return $scheme . '://' . $host;
}

/**
 * 后台 URL：传入相对站点根的路径，如 admin/set.php、?login/
 */
function qs_admin_url($path = '') {
$base = rtrim(qs_admin_base(), '/');
return $base . WEB_BASE . ltrim((string)$path, '/');
}

function qs_theme_url($path, $print = true) {
  global $qs_config;
  $url = rtrim((string)qs_site_link(false), '/') . '/files/theme/' . $qs_config['site_theme'] . '/' . ltrim((string)$path, '/');
  if ($print) {
    echo $url;
    return;
  }
  return $url;
}

function qs_site_theme($print = true) {
  global $qs_config;
  $site_theme = htmlspecialchars($qs_config['site_theme']);
  if ($print) {
    echo $site_theme;
    return;
  }
  return $site_theme;
}

function qs_site_name($print = true) {
  global $qs_config;
  $site_name = htmlspecialchars($qs_config['site_name']);
  if ($print) {
    echo $site_name;
    return;
  }
  return $site_name;
}

function qs_site_tags($print = true) {
  global $qs_config;
  $site_tags = htmlspecialchars($qs_config['site_tags']);
    if ($print) {
    echo $site_tags;
    return;
  }
  return $site_tags;
}

function qs_site_about() {
  global $qs_config;
  echo isset($qs_config['site_about']) ? $qs_config['site_about'] : '';
}

function qs_nick_code($print = true) {
  global $qs_config;
  $nick_code = htmlspecialchars($qs_config['nick_code']);
  if ($print) {
    echo $nick_code;
    return;
  }
  return $nick_code;
}

function qs_site_desc($print = true) {
  global $qs_config;
  $site_desc = htmlspecialchars($qs_config['site_desc']);
  if ($print) {
    echo $site_desc;
    return;
  }
  return $site_desc;
}

function qs_head_code($print = true) {
  global $qs_config;
  $head_code = $qs_config['head_code'];
  if ($print) {
    echo $head_code;
    return;
  }
  return $head_code;
}

function qs_user_nick() {
  global $qs_config;
  $user_nick = htmlspecialchars($qs_config['site_name']);
    echo $user_nick;
}

function qs_is_post() {
  global $qs_get_type, $qs_config, $sitemode;
  $sitemode = $qs_config['sitemode'];
  return $qs_get_type == $sitemode;
  //return $qs_get_type == 'game';
}

function qs_is_page() {
  global $qs_get_type;
  return $qs_get_type == 'page';
}

function qs_is_search() {
  global $qs_get_type;	
  return $qs_get_type == 'search';
}

function qs_is_tag() {
  global $qs_get_type;
  return $qs_get_type == 'tag';
}

function qs_is_bookname() {
  global $qs_get_type;
  return $qs_get_type == 'bookname';
}

function qs_is_date() {
  global $qs_get_type;
  return $qs_get_type == 'date';
}

function qs_is_archive() {
  global $qs_get_type;
  return $qs_get_type == 'archive';
}

function qs_is_index() {
  global $qs_get_type;
  return $qs_get_type == 'index';
}

function qs_tag_name($print=true) {
  global $qs_get_name;
  if ($print) {
    echo htmlspecialchars($qs_get_name);
    return;
  }
  return $qs_get_name;
}

function qs_bookname_name($print=true) {
  global $qs_get_name;
  if ($print) {
    echo htmlspecialchars($qs_get_name);
    return;
  }
  return $qs_get_name;
}

function qs_date_name($print=true) {
  global $qs_get_name;
  if ($print) {
    echo htmlspecialchars($qs_get_name);
    return;
  }
  return $qs_get_name;
}

function qs_has_new() {
  global $qs_page_num;
  return $qs_page_num != 1;
}

function qs_has_old() {
  global $qs_page_num, $qs_post_count, $qs_post_per_page;
  return $qs_page_num < ($qs_post_count / $qs_post_per_page);
}
/*
前后分页调用（还有页码分页）
    <div id="page_bar">
      <?php if (qs_has_new()) { ?><?php qs_goto_new('«'); ?><?php } ?>
      <?php if (qs_has_old()) { ?><?php qs_goto_old('»'); ?><?php } ?>
    </div>
*/

function qs_goto_old($text) {
  global $qs_get_type, $qs_get_name, $qs_page_num, $qs_config;
  if ($qs_get_type == 'tag') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?tag/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num + 1);
    echo '" class="nextpage">';
    echo $text;
    echo '</a>';
  } 
    elseif ($qs_get_type == 'bookname') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?bookname/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num + 1);
    echo '" class="nextpage">';
    echo $text;
    echo '</a>';
  }
  elseif ($qs_get_type == 'date') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?date/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num + 1);
    echo '" class="nextpage">';
    echo $text;
    echo '</a>';
  } else {
    echo '<a href="';
    echo qs_site_link();
    echo '/?page=';
    echo ($qs_page_num + 1);
    echo '" class="nextpage">';
    echo $text;
    echo '</a>';
  }
}

function qs_goto_new($text) {
  global $qs_get_type, $qs_get_name, $qs_page_num, $qs_config;
  if ($qs_get_type == 'tag') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?tag/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num - 1);
    echo '" class="prevpage">';
    echo $text;
    echo '</a>';
  }
  elseif ($qs_get_type == 'bookname') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?bookname/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num - 1);
    echo '" class="prevpage">';
    echo $text;
    echo '</a>';
  }
  elseif ($qs_get_type == 'date') {
    echo '<a href="';
    echo qs_site_link();
    echo '/?date/';
    echo $qs_get_name;
    echo '/?page=';
    echo ($qs_page_num - 1);
    echo '" class="prevpage">';
    echo $text;
    echo '</a>';
  } else {
    echo '<a href="';
    echo qs_site_link();
    echo '/?page=';
    echo ($qs_page_num - 1);
    echo '" class="prevpage">';
    echo $text;
    echo '</a>';
  }
}

function qs_date_list($item_begin='<li>', $item_gap='', $item_end='</li>') {
  global $qs_dates, $qs_config;
  if (isset($qs_dates)) {
    $date_count = count($qs_dates);
    for ($i = 0; $i < $date_count; $i ++) {
      $date = $qs_dates[$i];
      echo $item_begin;
      echo '<a href="';
      echo qs_site_link();
      echo '/?date/';
      echo $date;
      echo '/">';
      echo $date;
      echo '</a>';
      echo $item_end;
      if ($i < $date_count - 1)
        echo $item_gap;
    }
  }
}



// 根据年月筛选文章
function filter_posts_by_date($posts, $year, $month) {
    $filtered_posts = [];
    foreach ($posts as $post) {
        if (substr($post['date'], 0, 7) == "$year-$month") {
            $filtered_posts[] = $post;
        }
    }
    return $filtered_posts;
}



//输出全站标签，标签云
function qs_tag_list($item_begin='<li>', $item_gap='', $item_end='</li>') {
  global $qs_tags, $qs_config;
  if (isset($qs_tags)) {
    $tag_count = count($qs_tags);
    for ($i = 0; $i < $tag_count; $i ++) {
      $tag = $qs_tags[$i];
      echo $item_begin;
      echo '<a href="';
      echo qs_site_link();
      echo '/?tag/';
      echo urlencode($tag);
      echo '/">';
      echo $tag;
      echo '</a>';
      echo $item_end;
      if ($i < $tag_count - 1)
        echo $item_gap;
    }
  }
}


function qs_next_post() {
  global $sitemode, $qs_config, $qs_posts, $qs_post_ids, $qs_post_count, $qs_post_i, $qs_post_i_end, $qs_post_id, $qs_post, $qs_page_num, $qs_post_per_page;
  if (!isset($qs_posts) || empty($qs_posts) || !isset($qs_post_ids) || empty($qs_post_ids)) {
    return false; // 提前判断数组是否存在/为空
  }
  if (!isset($qs_post_i)) {
    $qs_post_i = 0 + ($qs_page_num - 1) * $qs_post_per_page;
    $qs_post_i_end = $qs_post_i + $qs_post_per_page;
    if ($qs_post_count < $qs_post_i_end)
      $qs_post_i_end = $qs_post_count;
  }
  if ($qs_post_i == $qs_post_i_end)
    return false;
  
  if ($qs_config['sitemode'] == "novel") {
    // 修复：先判断索引是否存在
    $target_index = $qs_post_i_end - 1;
    $qs_post_id = isset($qs_post_ids[$target_index]) ? $qs_post_ids[$target_index] : '';
    // 修复：判断post_id和qs_posts键是否存在
    $qs_post = !empty($qs_post_id) && isset($qs_posts[$qs_post_id]) ? $qs_posts[$qs_post_id] : [];
    $qs_post_i_end -= 1;
  } else {
    // 修复：先判断索引是否存在
    $qs_post_id = isset($qs_post_ids[$qs_post_i]) ? $qs_post_ids[$qs_post_i] : '';
    $qs_post = !empty($qs_post_id) && isset($qs_posts[$qs_post_id]) ? $qs_posts[$qs_post_id] : [];
    $qs_post_i += 1;
  }

  return !empty($qs_post); // 确保返回前post非空
}




function qs_the_date($type = 'all') {
  global $qs_post;
  $d = isset($qs_post['date']) ? $qs_post['date'] : '';
  switch ($type) {
    case 'year': $format = 'Y'; break;
    case 'month': $format = 'm'; break;
    case 'day': $format = 'd'; break;
    default: $format = '';
  }
  if ($format !== '') {
    return is_numeric($d) ? date($format, (int)$d) : $d;
  }
  return $d;
}


function qs_the_user() {
  global $qs_post;
  global $sp_config_users;
  // 显示名优先：文章记录只存登录名，这里按登录名映射到用户的显示名；改显示名不影响文章与账号的关联
  $display = isset($qs_post['user']) ? $qs_post['user'] : '';
  if ($display !== '' && isset($sp_config_users[$display]['displayname']) && $sp_config_users[$display]['displayname'] !== '') {
    $display = $sp_config_users[$display]['displayname'];
  }
  return $display;
}



function qs_the_time() {
  global $qs_post;
  return isset($qs_post['time']) ? $qs_post['time'] : '';
}


function qs_book($operation) {
  global $qs_post, $qs_config, $novel_title, $novel_title1, $bookname, $bookgenres;
  
  $bookgenres = $qs_post['bookgenres'] ?? "";
  $bookname = $qs_post['bookname'] ?? "";
  $bookpic_raw = $qs_post['bookpic'] ?? "";
  $clean_pic_path = ltrim(preg_replace('/^\.\.\//', '', $bookpic_raw), '/');
  $bookpic = rtrim(qs_site_link(false), '/') . '/' . $clean_pic_path;
  
  
  $novel_link = '/?bookname/' . $bookname;
  if ($operation === 'pic') {
    return $bookpic;
  } elseif ($operation === 'name') {
    return $bookname;
  } elseif ($operation === 'bookgenres') {
    return $bookgenres;
  } elseif ($operation === 'link') {
    return '/?bookname/' . $bookname .'/';
  }
  return;
}



function qs_the_title($post = null) {
    if (is_array($post) && isset($post['title'])) {
        $title = $post['title'];
    } else {
        global $qs_post;
        $title = isset($qs_post['title']) ? $qs_post['title'] : '';
    }
    $title = is_string($title) ? $title : '';
    return htmlspecialchars($title);
}

//输出当前文章的全部标签超链接
function qs_the_tags($item_begin='', $item_gap=', ', $item_end='') {
  global $qs_post, $qs_config;
  $tags = isset($qs_post['tags']) ? (array)$qs_post['tags'] : array();
  $count = count($tags);
  $out = '';
  for ($i = 0; $i < $count; $i ++) {
    $tag = htmlspecialchars($tags[$i]);
    $out .= $item_begin;
    $out .= '<a href="';
    $out .= rtrim(qs_site_link(false), '/');
    $out .= '/?tag/';
    $out .= urlencode($tag);
    $out .= '/">';
    $out .= $tag;
    $out .= '</a>';
    $out .= $item_end;
    if ($i < $count - 1)
      $out .= $item_gap;
  }
  return $out;
}

//只输出纯标签文本，不带任何 `<a>` 超链接
function meta_tags($item_begin='', $item_gap=',', $item_end='') {
  global $qs_post, $qs_config;
  $tags = $qs_post['tags'];
  $count = count($tags);
  for ($i = 0; $i < $count; $i ++) {
    $tag = htmlspecialchars($tags[$i]);
    echo $item_begin;
    echo $tag;
    echo $item_end;
    if ($i < $count - 1)
      echo $item_gap;
  }
}

function qs_the_content($operation = '') {
    ob_start();
    qs_the_content_output($operation);
    return ob_get_clean();
}

  
  
function updatenew($data) {
 global  $qs_post, $latest_post,$title,$bookname,$bookgenres,$qs_config,$bookpic;
 $publishFile = $qs_config['qs_post_dir'] . 'index/publish.php';

// 核心修复：判断 $qs_posts 是否为空，避免 reset() 返回 false
$latest_post = !empty($qs_posts) ? reset($qs_posts) : [];

    switch ($data) {
        case 'title':
            $title = $latest_post['title'] ?? ''; // 空值合并运算符兜底
            break;
        case 'bookname':
            $bookname = $latest_post['bookname'] ?? '';
            break;
        case 'bookpic':
           $bookpic = $latest_post['bookpic'] ?? '';
            break;
        case 'bookgenres':
           $bookgenres = $latest_post['bookgenres'] ?? '';
            break;
            
        default:
            echo '无效参数';
            break;
}
}
 
function qs_the_summary($length = 0) {
  global  $qs_post, $qs_data, $html,$qs_post_id,$qs_config;
  $qs_sum_len = $length > 0 ? (int)$length : (int)($qs_config['articlehead'] ?? 150);
  if ($qs_sum_len < 1) { $qs_sum_len = 150; }
    if (function_exists('qs_db_post_get')) {
        // MySQL 版：文章数据从数据库取
        $one = qs_db_post_get((string)$qs_post_id);
        if (is_array($one)) { $qs_post = $one; }
    } else {
        $base = ltrim($qs_config['qs_post_dir'], './');
        $realPostRoot = rtrim(SITE_ROOT . '/' . $base, '/') . '/';
        $file_path = $realPostRoot . 'data/' . $qs_post_id . '.php';
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $qs_post =   unserialize($file_content);
    }
  	if (empty(@$qs_post['post_passwd'])){   // 检查密码是否为空
  		if (isset($qs_post['summary'])) {
			if (!empty($qs_post['summary'])){ 
				$html = $qs_post['title']. strip_tags(mb_substr($qs_post['summary'], 0, $qs_sum_len), '') . "……";
					} else {
						$html = $qs_post['title']. strip_tags(mb_substr($qs_post['content'],0,$qs_sum_len), '') . "……";
					}
  	} else {
  	  $html = $qs_post['title']. strip_tags(mb_substr($qs_post['content'],0,$qs_sum_len), '') . "……";
  	  
  	}

    return $html;
}
}

function qs_links($cssClass = '') {
	global $qs_links;
if (!is_array($qs_links)) {
   echo '没有友情链接可显示。';
   echo 'No friend links menu items available';
   return; // 如果不是数组则退出函数
}
foreach($qs_links as $url) {
        echo '<a href="' . $url["link"] . '" target="' . $url["tar"] . '" class="' . $cssClass .  '">' . $url["name"] . '</a>';
    }
}


function qs_get_the_link()
{
    global $static_config, $qs_config, $qs_post;

    if (!empty($static_config['current_data'])) {
        $d = $static_config['current_data'];
    } else {
        $d = $qs_post;
    }

    if (!$d || !is_array($d)) {
        return '';
    }

    $siteMode = $qs_config['sitemode'];
    $urlEnds  = $qs_config['url_ends'] ?? '';
    $siteLink = rtrim(qs_site_link(false), '/');

    // page独立页面
    if (isset($static_config['page_type']) && $static_config['page_type'] === 'page') {
        $path = isset($d['path']) ? $d['path'] : $d['id'];
        return "{$siteLink}/?page/{$path}{$urlEnds}";
    }

    // novel模式，带上过滤书名
    if ($siteMode === 'novel') {
        $book = isset($d['bookname']) ? $d['bookname'] : 'Other';
        $safeBook = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '', $book);
        return "{$siteLink}/?{$siteMode}/{$safeBook}/{$d['id']}{$urlEnds}";
    } else {
        // 普通模式，对应你写的：sitemode/post_id+url_ends
        return "{$siteLink}/?{$siteMode}/{$d['id']}{$urlEnds}";
    }
}

function isAccessible($url) {
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) {
        return true;
    }
    return false;
}

function getRemoteServer() {
    // 不再探测客户端 IP：站点在 CDN / 反向代理 / NAT 后面时报上来的地址没有判定意义，
    // SN 才是唯一识别依据，这里只给出接口候选地址，由调用方按顺序尝试，失败即静默降级。
    $servers = [
        'https://verify.qsis.cn/verify.php',
        'https://verify.qsis.cn:88/verify.php',
    ];
    return [
        'clientIP'     => $_SERVER['SERVER_ADDR'] ?? '',
        'remoteServer' => $servers[0],
        'candidates'   => $servers
    ];
}


/**
 * 从正文 HTML 中取第一张图片的 src
 *
 * 服务器未安装 dom 扩展（php-xml）时 DOMDocument 不存在，
 * 这里退回正则匹配，避免整站报 Fatal error 白屏。
 *
 * @param string $html    正文 HTML
 * @param string $default 没有图片时返回的默认图
 * @return string
 */
function qs_first_image_src($html, $default = '') {
    $html = (string) $html;
    if (trim($html) === '') {
        return $default;
    }
    if (class_exists('DOMDocument')) {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $images = $doc->getElementsByTagName('img');
        if ($images->length > 0) {
            $src = trim((string) $images->item(0)->getAttribute('src'));
            if ($src !== '') {
                return $src;
            }
        }
        return $default;
    }
    // 兜底：没有 dom 扩展，用正则取第一个 <img src="...">
    if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i', $html, $m)) {
        $src = trim($m[2]);
        if ($src !== '') {
            return $src;
        }
    }
    return $default;
}


function qs_the_content_pic() {
 global $qs_post_id, $qs_config, $firstImage;
    $base = ltrim($qs_config['qs_post_dir'], './');
    $realPostRoot = rtrim(SITE_ROOT . '/' . $base, '/') . '/';
    $file_path = $realPostRoot . 'data/' . $qs_post_id . '.php';
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
    $data =   unserialize($file_content);

//拦截空/损坏数据，防止空传给loadHTML
    if (!is_array($data) || empty($data['content']) || trim($data['content']) === '') {
        $firstImage = $qs_config['defaultpostimage'];
        return $firstImage;
    }

$Document = $data['content'];//首页列表文章完整显示

    $firstImage = qs_first_image_src($Document, $qs_config['defaultpostimage'] ?? '');

    return $firstImage;
}



function qs_has_content_pic(){
    global $qs_post_id, $qs_config;
    $base = ltrim($qs_config['qs_post_dir'], './');
    $realPostRoot = rtrim(SITE_ROOT . '/' . $base, '/') . '/';
    $file_path = $realPostRoot . 'data/' . $qs_post_id . '.php';

    if (!file_exists($file_path)) {
        return false;
    }
    $file_content = file_get_contents($file_path);
    $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
    $file_content = preg_replace('/\?>$/', '', $file_content);
    $data = unserialize($file_content);

    if (!is_array($data) || empty($data['content']) || trim($data['content']) === '') {
        return false;
    }
    return qs_first_image_src($data['content'], '') !== '';
}



//thank for gpt on 2023.9.1 isher
function qs_the_video($autoplay = '', $video_picsrc = '', $class = '') {
    global $qs_post_id, $qs_config, $video;
        $base = ltrim($qs_config['qs_post_dir'], './');
    $realPostRoot = rtrim(SITE_ROOT . '/' . $base, '/') . '/';
    $file_path = $realPostRoot . 'data/' . $qs_post_id . '.php';
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
      if ($file_content === false) {
            $html = '无法读取文章内容。';
            echo $html;
            return;
        }
        $data = unserialize($file_content);
    
        if (!isset($data['video']) || empty($data['video'])) {
        // 如果没有视频数据，直接返回空
        return;
    }
    
    $video = $data['video'] ?? "";

  $output = '<div id="videoContainer"><video controls '.$autoplay.' muted  poster="'.$video_picsrc.'"  type="video/mp4" class="' . $class . '" id="videoElement" src="' . $video . '"></video><div id="muteIndicator">当前静音,请调整视频音量收听</div></div>';
  $output .= '
    <script>
      const video = document.getElementById("videoElement");
      const muteIndicator = document.getElementById("muteIndicator");
      const videoContainer = document.getElementById("videoContainer");

      if (video.muted) {
        muteIndicator.style.display = "block";
      }

      video.addEventListener("volumechange", () => {
        if (video.muted) {
          muteIndicator.style.display = "block";
        } else {
          muteIndicator.style.display = "none";
        }
      });

      videoContainer.addEventListener("click", () => {
        video.muted = !video.muted;
      });
    </script>';
    echo $output;
}




function qs_sitelogo($print = true) {
	 global $qs_config;
	$sitelogo = $qs_config['sitelogo'];
  if ($print) {
  echo $sitelogo;
   return;
}
return $sitelogo;

}

function qs_sitelogoname() { // all/logo/name
	 global $sitelogoname,$qs_config,$print;
	 $sitelogoname = $qs_config['sitelogoname'];

	   echo '<a href="'. qs_site_link($print). '" target=_"blank">';
   if ($sitelogoname == "all") {
        echo '<img class="logo regular" width="50px" src="'. qs_site_link($print) .'/' . qs_sitelogo($print) . '" alt="' . qs_site_name($print) . '">' . qs_site_name($print); 
    } else if ($sitelogoname == "logo"){
        echo '<img class="logo regular" width="50px" src="'. qs_site_link($print) .'/' . qs_sitelogo($print) . '" alt="' . qs_site_name($print) . '">';
    } else if ($sitelogoname == "name") {
        // 默认情况下显示 logo 和站点名称
        echo qs_site_name($print);
    }
        echo '</a>';
}


function qs_image_url() {
	 global $ico, $qs_config;
	$ico = $qs_config['ico'];
  echo $ico;
}


function qs_google_icon(): string
{
    global $qs_config;
    $googleIconPath = trim($qs_config['google_icon'] ?? '');
    if ($googleIconPath === '') {
        return '';
    }
    $hrefEscaped = htmlspecialchars('/'.$googleIconPath, ENT_QUOTES);
    return '<link rel="icon" href="' . $hrefEscaped . '">';
}


function qs_ico(): string
{
    global $qs_config;
    $iconPath = trim($qs_config['ico'] ?? '');
    if ($iconPath === '') {
        return '';
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

    $hrefEscaped = htmlspecialchars('/'.$iconPath, ENT_QUOTES);
    $mimeVal = $mimeMap[$ext];

    if ($mimeVal === null) {
        return '<link rel="icon" href="' . $hrefEscaped . '">';
    }

    $mimeEscaped = htmlspecialchars($mimeVal, ENT_QUOTES);
    return '<link rel="icon" type="' . $mimeEscaped . '" href="' . $hrefEscaped . '">';
}

/**
 * 加载语言包（核心函数 L()）
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
        // 2. 读取浏览器的 Accept-Language 请求头
        $user_lang = 'en'; // 默认英文
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            
            // 针对中文做特殊处理（zh-CN/zh-TW）
            if ($user_lang === "zh") {
                $user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
                // 兼容不同格式的中文（如zh-CN/zh-cn/zh）
                $user_lang = str_replace('-', '_', strtolower($user_lang));
                if (!in_array($user_lang, ['zh_cn', 'zh_tw'])) {
                    $user_lang = 'zh_CN'; // 兜底简体中文
                }
            }
        }

        // 3. 拼接语言文件路径（关键：不再强行赋值zh-CN）
        $lang_file = $language_dir . 'lang_' . $user_lang . '.php';
        
        // 4. 设置语言名称
        if ($user_lang === 'zh_CN') {
            $language_name = "简体中文";
        } elseif ($user_lang === 'zh_TW') {
            $language_name = "正体中文";
        } else {
            $language_name = "English";
        }
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
 * 加载UI语言包（UI()函数，同逻辑修复）
 */
function UI($key) {
    global $qs_config, $language_dir, $UI_language_dir;
    $lang_file = '';
    $language_name = '';

    // 1. 优先使用配置文件指定的语言
    if (isset($qs_config['language_name']) && $qs_config['language_name'] !== '') {
        $lang_file = $UI_language_dir . $qs_config['language_file'];
        $language_name = $qs_config['language_name'];
    } else {
        // 2. 读取浏览器的 Accept-Language 请求头
        $user_lang = 'en'; // 默认英文
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            
            // 针对中文做特殊处理（zh-CN/zh-TW）
            if ($user_lang === "zh") {
                $user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
                $user_lang = str_replace('-', '_', strtolower($user_lang));
                if (!in_array($user_lang, ['zh_cn', 'zh_tw'])) {
                    $user_lang = 'zh_CN';
                }
            }
        }

        // 3. 拼接UI语言文件路径
        $lang_file = $UI_language_dir . 'lang_' . $user_lang . '.php';
        
        // 4. 设置语言名称
        if ($user_lang === 'zh_CN') {
            $language_name = "简体中文";
        } elseif ($user_lang === 'zh_TW') {
            $language_name = "正体中文";
        } else {
            $language_name = "English";
        }
    }

    // 5. 加载UI语言文件（不存在则 fallback 到英文）
    if (file_exists($lang_file)) {
        $translations = include($lang_file);
    } else {
        $lang_file = $UI_language_dir . 'lang_en.php';
        $translations = file_exists($lang_file) ? include($lang_file) : [];
    }

    return isset($translations[$key]) ? $translations[$key] : $key;
}

function language_select() {
	global $qs_config, $language_dir;
function language_set($directory) {
    global $language,$language_file,$language_name;
    $mydir = opendir($directory);
    // 使用 opendir() 打开目录
    echo '<select class="select-1" name="LanguageGlobal" id="language" >';
    echo '<option value="' .$language_name. ',' . $language_file. '">' . L('Current_Language') . ':' .$language_name .'</option>';
    while ($file = readdir($mydir)) {
        // 使用 readdir() 读取目录
        if ((is_file("$directory/$file")) && (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == "php")) {
            // 读取文件内容
            $languageContent = file_get_contents("$directory/$file");
          // 获取第一行和第二行内容
						  preg_match_all('/\/\/([^-\n]*) - \(([^-\n]*)\) - ([^\n]*)\s*\/\/(lang\s*(.*))/', $languageContent, $matches);
            // 如果找到匹配，则显示相应的信息
            if (isset($matches[0][0])) {
                $name = $matches[1][0];
                $language = $matches[2][0];
                $cn_description = $matches[3][0];
                $langfile = $matches[4][0];
                echo "<option value='$name,$file'>$name - $language - $langfile</option>";
            } else {
                // 如果没有找到匹配，则显示文件名
                echo "<option value='$file'>$file</option>";
            }
        }
    }
    closedir($mydir);
    // 关闭目录句柄
    echo "</select>";
}

language_set($language_dir);
}


function SitemodeSelect($selectedValue = null) {
    $options = [
        "blog" => "Blog_System",
        "business" => "Business_System",
        "video" => "Video_System",
        "novel" => "Novel_System",
        "article" => "Literature_System",
        "game" => "Game_System",
        "download" => "Download_System",
    ];

    echo '<select class="select-1" name="sitemode" id="sitemode">';
    foreach ($options as $value => $label) {
        $selected = ($value === $selectedValue) ? ' selected' : '';
        echo '<option value="' . $value . '" ' . $selected . '>' . L($label) . '</option>';
    }
    echo '</select>';
}




//直接输出完整的 <a href="url" target="_blank">文章标题</a>完整链接 HTML
function qs_the_link() {
  global $qs_post_id, $qs_post, $qs_config, $sitemode;
  
  $sitemode = $qs_config['sitemode'];
  $url_ends = $qs_config['url_ends'];
  echo '<a href="';
  echo qs_site_link();
 	//echo '/?post/';
  echo '/?'.$sitemode.'/';
  echo $qs_post_id;
  echo $url_ends.'" target=_"blank">';
  echo htmlspecialchars($qs_post['title']);
  echo '</a>';
}

//只输出文章 URL 地址，不输出 <a> 标签
function qs_post_link($post = null, bool $echo = true) {
    global $qs_post_id, $qs_post, $qs_config, $sitemode;
    $sitemode = $qs_config['sitemode'];
    $url_ends = $qs_config['url_ends'];
    if(is_array($post) && isset($post['id'])){
        $pid = $post['id'];
    }else{
        $pid = $qs_post_id;
    }
    $url = rtrim((string)qs_site_link(false), '/') . '/?'.$sitemode.'/';
    $url .= $pid;
    $url .= $url_ends;

    if($echo){
        echo $url;
    }else{
        return $url;
    }
}


function qs_postedit_link() {
  global $qs_post_id, $qs_post, $qs_config, $controldata_url;
if ($qs_config['sitemode'] == "novel") {
 echo qs_site_link();
  echo $controldata_url.'/post-edit.php?id=';
  echo $qs_post_id;
  echo '&bookpic=';
  echo qs_book('pic');
  echo '&bookname=';
  echo qs_book('name');
  echo '&bookgenres=';
  echo qs_book('bookgenres');
  } else {
  echo qs_site_link();
  echo $controldata_url.'/post-edit.php?id=';
  echo $qs_post_id;
}
}


function qs_postedit($text) {
    if (!qs_Pass()) {
        return; // 如果没有权限，直接返回，不输出任何内容
    }
    
    echo '<a href="';
    qs_postedit_link();
    echo '">' . htmlspecialchars($text) . '</a>';
}


function qs_page_title($print = true) {
  global $qs_data,$qs_post_id;
  // 1. 兜底null值：确保$qs_data是数组，title是字符串
  $title = isset($qs_data['title']) ? $qs_data['title'] : '';
  $title = is_string($title) ? $title : ''; // 强制转字符串
  
  if ($print) {
    echo htmlspecialchars($title); // 第1296行（修复后）
    return;
  }
  return htmlspecialchars($title);
}


function qs_footer_code() {
  global $qs_config;
  echo isset($qs_config['footer_code']) ? $qs_config['footer_code'] : '';
  qs_footer_info();
}

// 页脚/隐私渲染库（与 SSG 生成器共用同一份实现）
if (!function_exists('qs_footer_info')) {
    require_once __DIR__ . '/footer_privacy_lib.php';
}


function qs_liuyan_data_name() {
  global $qs_config;
  return isset($qs_config['liuyan_data_name']) ? $qs_config['liuyan_data_name'] : '';
}

function qs_other_code() {
  global $qs_config;
  echo isset($qs_config['other_code']) ? $qs_config['other_code'] : '';
}




function qs_menu ($cssClass = '') {
	global $qs_menu, $qs_config, $page_file;  
	if (!is_array($qs_menu)) {
        echo '没有主菜单项可用。';
        echo 'No main menu items available';
        return; // 如果不是数组则退出函数
    }
echo '<ul>';
foreach($qs_menu as $url)
{
	
//$now = urldecode($url["link"]);
$urlParts = parse_url($url["link"]);
$path = isset($urlParts['path']) ? $urlParts['path'] : '';
$query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
$now = $path . $query;
$full_url = urldecode($_SERVER['REQUEST_URI']);
echo '<li ' . ($full_url == $now ? 'class="current"' : '') . '><a href="' . $url["link"] . '" target="' . $url["tar"] . '" class="' . $cssClass .  '">' . $url["name"] . '</a></li>';
}
echo '</ul>';
}



function qs_sidemenu($cssClass = '') {
	global $qs_side_menu;
	 if (!is_array($qs_side_menu)) {
        echo '没有子菜单/侧边菜单项可用。';
        echo 'No sub-menu/side-menu items available';
        return; // 如果不是数组则退出函数
    }
    
	echo '<ul>';
	foreach($qs_side_menu as $url) {
$urlParts = parse_url($url["link"]);
$path = isset($urlParts['path']) ? $urlParts['path'] : '';
$query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
$now = $path . $query;
$full_url = urldecode($_SERVER['REQUEST_URI']);
echo '<li ' . ($full_url == $now ? 'class="current"' : '') . '><a href="' . $url["link"] . '" target="' . $url["tar"] . '" class="' . $cssClass .  '">' . $url["name"] . '</a></li>';	

}
echo '</ul>';
}



function shorturl($input) {
  $base32 = array (
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
    'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
    'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
    'y', 'z', '0', '1', '2', '3', '5', '6',
    '7', '8', '9'
  );
  $hex = md5('prefix'.$input.'surfix'.time());
  $hexLen = strlen($hex);
  $subHexLen = $hexLen / 8;
  $output = array();
  for ($i = 0; $i < $subHexLen; $i++) {
    $subHex = substr ($hex, $i * 8, 8);
  $int = 0x3FFFFFFF & hexdec($subHex);  
    $out = '';
    for ($j = 0; $j < 6; $j++) {
      $val = 0x0000001F & $int;
      $out .= $base32[$val];
      $int = $int >> 5;
    }
    $output[] = $out;
  }
  return $output;
}
function post_sort($a, $b) {
  $a_date = $a['date'];
  $b_date = $b['date'];
  if ($a_date != $b_date)
    return $a_date > $b_date ? -1 : 1;
  return $a['time'] > $b['time'] ? -1 : 1;
}


function qs_pagination()
{
    global $qs_page_num, $qs_post_count, $qs_post_per_page, $qs_config;

    $current_page = (int)$qs_page_num;
    $total_pages = (int)ceil($qs_post_count / $qs_post_per_page);

    if ($total_pages <= 1) {
        return;
    }

    $visible_pages = 8;
    $start = max(1, $current_page - intval($visible_pages / 2));
    $end = min($total_pages, $start + $visible_pages - 1);
    if (($end - $start + 1) < $visible_pages) {
        $start = max(1, $end - $visible_pages + 1);
    }

    // 拿原始查询串，只剔除 page 参数本身，绝不吞掉路径末尾的 /
    $rawQuery = $_SERVER['QUERY_STRING'] ?? '';
    $cleanQuery = preg_replace('#(?:\?|&)page=\d+#', '', $rawQuery); // 中间的 ?page= / &page=
    $cleanQuery = preg_replace('#^page=\d+#', '', $cleanQuery);      // 无 tag 时开头的 page=
    $cleanQuery = preg_replace('#(?:\?|&)$#', '', $cleanQuery);      // 清掉残留的分隔符
    $cleanQuery = trim($cleanQuery);

    $makeLink = function (int $p) use ($cleanQuery) {
        $q = $cleanQuery === '' ? '' : '?' . $cleanQuery;

        if ($p === 1) {
            // 第一页：原样返回，末尾斜杠保留，不追加任何问号
            return $q === '' ? '/' : htmlspecialchars($q);
        }

        if ($q === '') {
            return '?page=' . $p;
        }

        // 已有末尾 / 就直接接 ?page=N，没有才补一个 /
        return htmlspecialchars(rtrim($q, '/') . '/?page=' . $p);
    };

    $html = '<div class="pagination" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:1rem 0;"><div class="pagination-numbers" style="display:flex;gap:6px;">';

    // 首页
    if ($current_page > 1) {
        $html .= '<a href="' . $makeLink(1) . '" class="page-item">首页</a>';
    }
    // 上一页
    if ($current_page > 1) {
        $prev = $current_page - 1;
        $html .= '<a href="' . $makeLink($prev) . '" class="page-item">上页</a>';
    }
    // 中间页码
    for ($i = $start; $i <= $end; $i++) {
        if ($i === $current_page) {
            $html .= '<span class="pagination-active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $makeLink($i) . '" class="page-item">' . $i . '</a>';
        }
    }
    // 下一页
    if ($current_page < $total_pages) {
        $next = $current_page + 1;
        $html .= '<a href="' . $makeLink($next) . '" class="page-item">下页</a>';
    }
    // 尾页
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $makeLink($total_pages) . '" class="page-item">尾页</a>';
    }

    $html .= '</div></div>';
    echo $html;
}



function qs_search_name() {
  // 获取当前页面的URL
$currentURL = $_SERVER['REQUEST_URI'];

// 解析URL，获取最后一个斜杠后的内容
$parts = explode('?search/', $currentURL);
$lastPart = end($parts);

// 如果你希望显示URL中的中文内容，可以使用urldecode解码
$decodedLastPart = urldecode($lastPart);
$decodedLastPart = rtrim($decodedLastPart, '/');

// 显示提取的中文内容
echo $decodedLastPart;

}

function search() {
    echo '<script type="text/javascript">
        function Search() {
            var key = document.getElementById("text").value;
            location.href = "./?search/" + key + "/";
        }
    </script>';
}

//send_smtp_mail by qisoshuo 【已修改：支持465‑SSL / 587‑STARTTLS】
function send_smtp_mail($conf, $to_email, $subject, $body, $attachment = null, $debug = 0, $is_html = false) {
    $log = ''; // 调试日志内容
    $host = $conf['host'];
    $port = intval($conf['port']);
    $errno = 0;
    $errstr = '';
    $socket = null;

    if ($port === 465) {
        // 465 直接SSL
        $socket = @fsockopen("ssl://{$host}", $port, $errno, $errstr, 15);
    } elseif ($port === 587) {
        // 587 普通tcp，后续STARTTLS
        $socket = @fsockopen($host, $port, $errno, $errstr, 15);
    } else {
        return "不支持的端口：{$port}，仅支持465(SSL)、587(STARTTLS)";
    }

    if (!$socket) {
        $msg = "套接字连接失败：host={$host}，port={$port}，errstr={$errstr}，errno={$errno}";
        if ($debug == 0) {
            if ($port === 587) {
                $msg .= "\n提示：587使用普通TCP连接，之后执行STARTTLS；请确认服务器网络可连通该主机587端口";
            } else {
                $msg .= "\n提示：465使用ssl://直连SSL套接字";
            }
            $msg .= "\n调试：PHP尝试建立套接字失败，请检查服务器网络、端口、DNS、openssl扩展";
        }
        return $msg;
    }

    // 带调试日志的命令发送函数
    $command = function($conn, $cmd, $expect = null) use (&$log, $debug) {
        fwrite($conn, $cmd . "\r\n");
        if ($debug == 0) $log .= ">> $cmd\n";
        $response = '';
        while ($line = fgets($conn, 515)) {
            if ($debug == 0) $log .= "<< $line";
            $response .= $line;
            if (!$expect || preg_match("/^$expect/", $line)) break;
        }
        return $response;
    };

    // 读取服务器欢迎响应
    $welcome = fgets($socket, 515);
    if ($debug == 0) $log .= "<< $welcome";

    // ========== 587端口：执行STARTTLS握手 ==========
    if ($port === 587) {
        $command($socket, "EHLO localhost", 250);
        $resp_starttls = $command($socket, "STARTTLS", 220);
if (!preg_match('/^220/m', $resp_starttls)) {
    $out = "STARTTLS命令失败：" . $resp_starttls;
    if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
    fclose($socket);
    return $out;
}

        // PHP开启流TLS加密
        $cryptoOk = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if (!$cryptoOk) {
            $out = "stream_socket_enable_crypto 开启TLS加密失败，请确认PHP openssl扩展已启用";
            if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
            fclose($socket);
            return $out;
        }
        // TLS加密完成后必须重新EHLO
        $command($socket, "EHLO localhost", 250);
    } else {
        // 465 SSL端口直接EHLO
        $command($socket, "EHLO localhost", 250);
    }

    // 账号密码认证
$command($socket, "AUTH LOGIN", 334);
$command($socket, base64_encode($conf['user']), 334);
$resp_auth = $command($socket, base64_encode($conf['pass']), 235);
if(!preg_match('/^235/m',$resp_auth)){
    $out = "AUTH登录认证失败！返回：".$resp_auth;
    if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
    fclose($socket);
    return $out;
}


    $response = $command($socket, "MAIL FROM:<{$conf['user']}>", 250);
    if (strpos($response, "250") === false) {
        $out = "MAIL FROM失败：\n" . $response;
        if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
        fclose($socket);
        return $out;
    }

    $emails = is_array($to_email) ? $to_email : explode(',', $to_email);
    $emails = array_map('trim', $emails);
    foreach ($emails as $email) {
        $response = $command($socket, "RCPT TO:<$email>", 250);
        if (strpos($response, "250") === false) {
            $out = "RCPT TO失败：$response";
            if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
            fclose($socket);
            return $out;
        }
    }

    $response = $command($socket, "DATA", 354);
    if (strpos($response, "354") === false) {
        $out = "DATA命令失败：$response";
        if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
        fclose($socket);
        return $out;
    }

    $to_header = implode(', ', array_map(fn($e) => "<$e>", $emails));
$headers = "From: <{$conf['user']}>\r\n";
$headers .= "To: $to_header\r\n";
$headers .= "Subject: $subject\r\n";
$headers .= "Date: " . date("r") . "\r\n";
$headers .= "Message-ID: <" . uniqid() . "@qsis.cn>\r\n";
$headers .= "MIME-Version: 1.0\r\n";

// 判断是否真实存在附件
$has_attach = false;
if ($attachment && (
    (isset($attachment['tmp_name']) && is_uploaded_file($attachment['tmp_name'])) ||
    (isset($attachment['tmp_name']) && file_exists($attachment['tmp_name']))
)) {
    $has_attach = true;
}

$message = '';
$boundary = md5(time());

if ($is_html) {
    // HTML模式：multipart/alternative
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=utf-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body . "\r\n";
    $message .= "--$boundary--\r\n";
} else {
    // 纯文本
    if ($has_attach) {
        // 纯文本 + 有附件 → multipart/mixed
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=utf-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $body . "\r\n";
    } else {
        // ✅纯文本、无附件：单部分text/plain，没有boundary！
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
        $message .= $body . "\r\n";
    }
}


// 如果有附件，追加附件块（仅multipart模式才生效）
if ($has_attach) {
    $filename = basename($attachment['name']);
    $filedata = chunk_split(base64_encode(file_get_contents($attachment['tmp_name'])));
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= $filedata . "\r\n";
    $message .= "--$boundary--\r\n";
}

fwrite($socket, $headers . $message);

    $response = $command($socket, ".", 250);
    if (strpos($response, "250") === false) {
        $out = "邮件内容发送失败：$response";
        if ($debug == 0) $out .= "\n调试信息：\n" . nl2br(htmlspecialchars($log));
        fclose($socket);
        return $out;
    }

    $command($socket, "QUIT");
    fclose($socket);

    return "邮件发送完成。" . ($debug == 0 ? ("\n调试信息：\n" . nl2br(htmlspecialchars($log))) : '');
}


/**
 * 加载邮件html模板，替换占位符
 * @param string $tplName 模板文件名，例 reg_activate.html
 * @param array $vars 替换数组 ['{{ACTIVATE_URL}}'=>'https://xxx']
 * @return string|null 返回html内容；模板不存在返回null
 */
function load_mail_template(string $tplName, array $vars = [])
{
    $tplPath = __DIR__ . '/../files/mail_tpl/' . $tplName;
    if (!file_exists($tplPath)) {
        return null;
    }
    $html = file_get_contents($tplPath);
    foreach ($vars as $search => $replace) {
        $html = str_replace($search, $replace, $html);
    }
    return $html;
}


function qs_site_backgroup($print = true) {
  global $qs_config;
  if (!isset($qs_config['site_backgroup_pic'])){
  	$BackgroupFileType = pathinfo($qs_config['site_backgroup_pic']["name"], PATHINFO_EXTENSION);
  	if (in_array($BackgroupFileType, array("mp4", "mov", "avi", "mkv"))) {
    echo '<video autoplay muted loop>';
    echo '<source src="default.'.$BackgroupFileType .'" type="video/mp4">';
    echo L('bowerdontsupportvideo');
  	echo   '</video>';
} else if (in_array($BackgroupFileType, array("jpg", "png", "jpeg", "Apng", "gif", "webp"))) 
    echo '<div class="background-image " src="default.'.$BackgroupFileType .'" type="video/mp4">';
} else {
    echo L('dontsupporttype');
}
  if ($print) {
    echo $qs_post['time'];
    return;
  }
  return $qs_post['time'];
}


function readAuthFile($file) {
    if (!file_exists($file)) {
        throw new Exception("文件不存在: " . $file);
    }

    $content = file_get_contents($file);
    $lines = explode("\n", $content);

    $authInfo = array();
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $authInfo[trim($key)] = trim($value);
        }
    }

    return $authInfo;
}


function checkAuth($info) {
    $authorized = isset($info['status']) && $info['status'] === 'authorized';
    $validSerial = isset($info['serial_number']) && strlen($info['serial_number']) === 16;

    return $authorized && $validSerial;
}


// 判断是否为苹果设备并根据传入的内容显示提示信息
function checkAppleDevice($appleMessage, $nonAppleMessage) {
    // 获取用户代理字符串
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // 判断是否为苹果设备
    if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
        // 是苹果设备，输出苹果设备的提示信息
        echo '<p>' . $appleMessage . '</p>';
    } else {
        // 不是苹果设备，输出非苹果设备的提示信息
        echo '<p>' . $nonAppleMessage . '</p>';
    }
}
//调用方法 
//checkAppleDevice(L("Aplee"), L("Noapple"));



// 根据标签获取文章列表
function get_posts_by_tag(string $target_tag, ?int $limit = null) {
    global $qs_posts, $qs_config;
    $filtered_posts = [];

    // 条数优先级：传入limit > $qs_config['per_page'] > 默认5
    if ($limit === null) {
        $limit = isset($qs_config['per_page']) && is_int($qs_config['per_page']) ? $qs_config['per_page'] : 5;
    }

    foreach ($qs_posts as $post) {
        if (!empty($post['tags']) && is_array($post['tags']) && in_array($target_tag, $post['tags'], true)) {
            $filtered_posts[] = $post;
            if (count($filtered_posts) >= $limit) break;
        }
    }
    return $filtered_posts;
}

function get_posts_by_tag_limit(string $tagName, ?int $num = null) {
    return get_posts_by_tag($tagName, $num);
}




//调用
// qs_block_tag_posts("认知心理", "人文思考与社会观察", $qs_config['per_page']);
// qs_block_tag_posts("人生成长", "自我提升与人生感悟", $qs_config['per_page']);
// qs_block_tag_posts("实操方略", "人文思考与社会观察", 10); 
function qs_block_tag_posts($tag_name, $tag_desc = '', $limit = 10)
{
    global $qs_config, $qs_posts;
    $filtered_posts = [];
    $limit = $limit ?? (isset($qs_config['per_page'])&&is_int($qs_config['per_page'])?$qs_config['per_page']:5);
    foreach ($qs_posts as $post) {
        if(!empty($post['tags'])&&is_array($post['tags'])&&in_array($tag_name,$post['tags'],true)){
            $filtered_posts[]=$post;
            if(count($filtered_posts)>=$limit)break;
        }
    }
    $list=$filtered_posts;
    $tag_url=rtrim((string)qs_site_link(false), '/').'/?tag/'.urlencode($tag_name).'/';
    ?>
    <div class="category-area">
    <a href="<?=$tag_url?>"><hgroup class="cate-row"><h3><?=htmlspecialchars($tag_name)?></h3><?php if($tag_desc):?><p><?=htmlspecialchars($tag_desc)?></p><?php endif;?></hgroup></a>
    <?php if($list):foreach($list as $post):?>
        <a href="<?php qs_post_link($post) ?>" class="post-item"><?= qs_the_title($post) ?></a>
    <?php endforeach;else:?><div class="post-item empty-tip">暂无文章</div><?php endif;?>
    </div>
    <?php
}


function qs_load_theme_config()
{
    global $qs_config;
    $themeName = $qs_config['site_theme'];
    $file = SITE_ROOT . "/files/theme/{$themeName}/theme_config.php";
    $theme_tag_blocks = [];
    if (file_exists($file)) {
        include $file;
    }
    if (!isset($theme_tag_blocks) || !is_array($theme_tag_blocks)) {
        $theme_tag_blocks = [];
    }
    return $theme_tag_blocks;
}

function qs_save_theme_config(array $blocks)
{
    global $qs_config;
    $themeName = $qs_config['site_theme'];
    $dir = SITE_ROOT . "/files/theme/{$themeName}/";
    $file = $dir . "theme_config.php";
    if (!is_dir($dir)) return "主题目录不存在";
    if (!is_writable($dir)) return "主题目录无写入权限";

    $export = var_export($blocks, true);
    $content = '<?php' . PHP_EOL;
    $content .= 'if (!defined(\'SITE_ROOT\')) exit;' . PHP_EOL;
    $content .= '$theme_tag_blocks = ' . $export . ';' . PHP_EOL;
    $content .= '?>' . PHP_EOL;

    $rs = file_put_contents($file, $content);
    if ($rs === false) return "写入theme_config.php失败";
    return '';
}




/**
 * 轮播Banner大图，支持传入任意配置key
 * @param array $qs_config 系统配置数组
 * @param string $configKey,后台设置里，可以设置自定义字段 qs_config里面的键名，例如 'carousel_config'、'caro_config'
 * @return void 直接输出HTML
调用： <?php qs_carousel($qs_config, 'carousel_config'); ?>
 */
function qs_carousel(array $qs_config, string $configKey): void
{
    $carousel_config = trim($qs_config[$configKey] ?? '');
    $bannerList = [];
    if ($carousel_config) {
        $lines = explode("\n", $carousel_config);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            $arr = explode('|', $line, 2);
            $img = trim($arr[0] ?? '');
            $text = trim($arr[1] ?? '');
            if ($img) {
                $bannerList[] = [
                    'img'  => $img,
                    'text' => $text
                ];
            }
        }
    }

    if (!empty($bannerList)) {
        echo '<!-- 轮播 -->
<div class="banner">
  <div class="swiper-container">
    <div class="swiper-wrapper">';
        foreach ($bannerList as $row) {
            $img  = htmlspecialchars($row['img']);
            $text = htmlspecialchars($row['text']);
            echo <<<HTML
      <div class="swiper-slide">
        <img src="{$img}">
        <div class="banner-text">{$text}</div>
      </div>
HTML;
        }
        echo '
    </div>
    <div class="swiper-pagination"></div>
  </div>
</div>';
    }
}


function qs_get_carousel_data(array $qs_config, string $configKey): array
{
    $raw = trim($qs_config[$configKey] ?? '');
    $bannerList = [];
    if ($raw) {
        $lines = explode("\n", $raw);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            $arr = explode('|', $line, 2);
            $img = trim($arr[0] ?? '');
            $text = trim($arr[1] ?? '');
            if ($img) {
                $bannerList[] = ['img' => $img, 'text' => $text];
            }
        }
    }
    return $bannerList;
}


/**
 *$banners = qs_get_carousel_data($qs_config, 'caro_config');
// 自己循环写html
 *foreach($banners as $row){
 *    echo htmlspecialchars($row['img']);
 *}
**/


/**
 * 根据广告ID生成对应的 adX.js 文件
 * @param string $aid 广告id
 * @param array|null $adsItem 广告数组，传null则删除该js文件
 */
/**
 * 根据广告ID生成对应的 adX.js 文件
 * @param string $aid 广告id
 * @param array|null $adsItem 广告数组，传null则删除该js文件
 */
function writeAdJsFile(string $aid, ?array $adsItem = null){
    global $datapaths,$adsConfig;
    $adDir = SITE_ROOT . '/data/ads/';
    if(!is_dir($adDir)){
        mkdir($adDir,0755,true);
    }
    $filePath = $adDir . $aid . '.js';
    if($adsItem === null){
        if(file_exists($filePath)) unlink($filePath);
        return;
    }

    $adEnable = var_export(!empty($adsItem['enable']),true);
    $adScope  = json_encode($adsItem['ad_scope'] ?? 'both');
    $adCodeJs = json_encode($adsItem['ad_code'] ?? '');

    $adAllEnable      = var_export(!empty($adsConfig['ad_all_enable']),true);
    $templateAdEnable = var_export(!empty($adsConfig['template_ad_enable']),true);
    $articleAdEnable  = var_export(!empty($adsConfig['article_ad_enable']),true);

    $jsTpl = <<<JS
(function(){
    const slot = document.querySelector('[data-ad-id="{$aid}"]');
    if(!slot) return;
    if(slot.dataset.adRendered === '1') return;

    const pageContext = slot.dataset.adContext;
    const ad_all_enable = {$adAllEnable};
    const template_ad_enable = {$templateAdEnable};
    const article_ad_enable = {$articleAdEnable};
    const ad_enable = {$adEnable};
    const ad_scope = {$adScope};
    const ad_html = {$adCodeJs};

    if(!ad_all_enable){
        return;
    }
    if(!ad_enable){
        return;
    }

    let pass = false;
    if(pageContext === 'theme'){
        if(template_ad_enable){
            if(ad_scope === 'theme' || ad_scope === 'both'){
                pass = true;
            }
        }
    }else if(pageContext === 'post'){
        if(article_ad_enable){
            if(ad_scope === 'post' || ad_scope === 'both'){
                pass = true;
            }
        }
    }
    if(!pass){
        return;
    }

    slot.innerHTML = ad_html;
    slot.dataset.adRendered = '1';
    (adsbygoogle = window.adsbygoogle || []).push({});
})();
JS;
    file_put_contents($filePath, $jsTpl);
}




/*
<div class="post-list">
<h3>生活分类</h3>
<?php
$life_posts = get_posts_by_tag("生活");
$box1   = get_posts_by_tag_limit("认知心理", $qs_config['per_page']);
foreach ($life_posts as $post): ?>
<a href="post.php?id=<?php echo $post['id']; ?>" class="post-item">
<?php echo $post['title']; ?>
</a>
<?php endforeach; ?>
</div>
*/

/* ============================================================
 * 文件版兼容函数
 * 从 MySQL 版移植过来的主题会调用这几个函数，这里按文件版
 * 的数据来源（$qs_tags / $qs_post）做等价实现，签名保持一致。
 * ============================================================ */

/**
 * 是否处于「标签汇总」页面
 *
 * @return bool
 */
function qs_is_tags() {
  global $qs_get_type;
  return $qs_get_type == 'tags';
}

/**
 * 输出全站标签云（签名与 MySQL 版 qs_tag_cloud 一致）
 *
 * @param string $item_begin
 * @param string $item_gap
 * @param string $item_end
 * @param int    $limit
 * @return void
 */
function qs_tag_cloud($item_begin = '<li>', $item_gap = '', $item_end = '</li>', $limit = 40) {
  global $qs_tags;
  if (!isset($qs_tags) || !is_array($qs_tags) || !$qs_tags) {
    return;
  }
  $qs_tag_n = count($qs_tags);
  if ($limit > 0 && $qs_tag_n > (int)$limit) {
    $qs_tag_n = (int)$limit;
  }
  $qs_tag_base = rtrim((string) qs_site_link(false), '/');
  for ($qs_tag_i = 0; $qs_tag_i < $qs_tag_n; $qs_tag_i ++) {
    $qs_tag_one = (string) $qs_tags[$qs_tag_i];
    echo $item_begin;
    echo '<a href="' . htmlspecialchars($qs_tag_base . '/?tag/' . urlencode($qs_tag_one) . '/') . '">';
    echo htmlspecialchars($qs_tag_one);
    echo '</a>';
    echo $item_end;
    if ($qs_tag_i < $qs_tag_n - 1) {
      echo $item_gap;
    }
  }
}

/**
 * 列表卡片的媒体信息：有视频用视频，否则用封面图
 *
 * @return array{type:string,src:string,poster:string,mime:string}
 */
function qs_card_media() {
  global $qs_post;
  $img = function_exists('qs_the_content_pic') ? (string) qs_the_content_pic() : '';
  $video = (is_array($qs_post) && !empty($qs_post['video'])) ? trim((string) $qs_post['video']) : '';
  if ($video === '') {
    return array('type' => 'image', 'src' => $img, 'poster' => $img, 'mime' => '');
  }
  $ext = strtolower(pathinfo((string) parse_url($video, PHP_URL_PATH), PATHINFO_EXTENSION));
  $mime = 'video/mp4';
  if ($ext === 'webm') {
    $mime = 'video/webm';
  } elseif ($ext === 'ogv' || $ext === 'ogg') {
    $mime = 'video/ogg';
  }
  return array('type' => 'video', 'src' => $video, 'poster' => $img, 'mime' => $mime);
}


/* ============================================================
 * 以下函数按 MySQL 版函数名对齐后补充（文件版此前没有实现）
 * ============================================================ */

function qs_the_content_output($operation = '') {
    global $qs_data,$qs_config,$qs_post_id;

    // MySQL 版：列表页 / 标签页没有预置 $qs_data，按当前文章 id 从库里补一份
    if (function_exists('qs_db_post_get') && !empty($qs_post_id)) {
        $qs_tc_id = (string)$qs_post_id;
        if (!isset($qs_data['id']) || (string)$qs_data['id'] !== $qs_tc_id) {
            $qs_tc_row = qs_db_post_get($qs_tc_id);
            if (is_array($qs_tc_row)) {
                $qs_data = $qs_tc_row;
            }
        }
    }

    if (!isset($qs_data)) {
    $base = ltrim($qs_config['qs_post_dir'], './');
    $realPostRoot = rtrim(SITE_ROOT . '/' . $base, '/') . '/';
    $file_path = $realPostRoot . 'data/' . $qs_post_id . '.php';
        // MySQL 版：正文存库，文件已退役；不在就直接给提示，别去读空路径
        if (!is_file($file_path)) {
            echo '无法读取文章内容。';
            return;
        }
        $file_content = file_get_contents($file_path);
        if ($file_content === false) {
            $html = '无法读取文章内容。';
            echo $html;
            return;
        }

        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $data = unserialize($file_content);

        if (empty($data['post_passwd'])) {
            if ($qs_config['articlehead'] == "0") {

                 if (!empty($data['tags']) && $qs_config['tagsonarticle'] == '1') {
                    $tags = array_filter(array_map('trim', $data['tags']));
                    usort($tags, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if ($tag === '') continue;

                        $tag_url = "?tag/" . urlencode($tag);
                        $data['content'] = preg_replace(
                            "/(?!<a[^>]*>)(?!<[^>]*>)(".preg_quote($tag, "/").")(?![^<]*<\/a>)/u",
                            "<a href=\"$tag_url\" target=\"_blank\" style=\"color:#007bff;text-decoration:underline;\">$1</a>",
                            $data['content']
                        );
                    }
                }

                $html = $data['content'];
            } else {
                if ($qs_config['sitemode'] == "novel") {
                    $html = strip_tags(mb_substr($data['content'], 0, $qs_config['articlehead']), '') . "……";
                } else {
                    $summary = $data['summary'] ?? '';
                    $content = $data['content'] ?? '';
                    $html = empty($summary) ? strip_tags(mb_substr($content, 0, $qs_config['articlehead']), '') . "……" : strip_tags(mb_substr($summary, 0, $qs_config['articlehead']), '<br><p>') . "……";
                }
            }
        } else {
            $html = UI('Post_passwd');
        }
        echo $html;
    } else {
        if ($operation === 'download') {
            $downlinks = (isset($qs_data['download']) && is_string($qs_data['download']) && trim($qs_data['download'])!== '') 
                ? explode(',', $qs_data['download']) : [];
            if (!empty($downlinks)) {
                foreach ($downlinks as $index => $downlink) {
                    $serial = $index + 1;
                    echo '<a href="' . htmlspecialchars($downlink) . '" target="_blank">download - ' . $serial . '</a><br>';
                }
            } else {
                echo '<span>No links</span>';
            }

        } elseif ($operation === 'download_size') {
            $html = $qs_data['download_size'] ?? '';
            echo $html;
        } else {
        	global $qs_config;
            if (empty($qs_data['post_passwd'])) {

                if (!empty($qs_data['tags']) && $qs_config['tagsonarticle'] == '1') {
                    $tags = array_filter(array_map('trim', $qs_data['tags']));
                    usort($tags, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if ($tag === '') continue;

                        $tag_url = "?tag/" . urlencode($tag);
                        $qs_data['content'] = preg_replace(
                            "/(?!<a[^>]*>)(?!<[^>]*>)(".preg_quote($tag, "/").")(?![^<]*<\/a>)/u",
                            "<a href=\"$tag_url\" target=\"_blank\" style=\"color:#007bff;text-decoration:underline;\">$1</a>",
                            $qs_data['content']
                        );
                    }
                }

                if (qs_is_post() || qs_is_page()) {
                    $html = $qs_data['content'];
                    // 投票短标签：[vote id="N"] → 在文章内渲染投票卡片
                    if (is_file(__DIR__ . '/vote_embed_lib.php')) {
                        require_once __DIR__ . '/vote_embed_lib.php';
                        $html = qs_vote_shortcodes((string)$html);
                    }
                } else {
                    // 列表 / 标签 / 存档页：只出摘要，别把整篇塞进列表
                    $qs_tc_head = isset($qs_config['articlehead']) ? (int)$qs_config['articlehead'] : 150;
                    if ($qs_tc_head < 40) {
                        $qs_tc_head = 150;
                    }
                    $qs_tc_src = !empty($qs_data['summary']) ? $qs_data['summary'] : $qs_data['content'];
                    $html = htmlspecialchars(mb_substr(strip_tags($qs_tc_src), 0, $qs_tc_head)) . '……';
                }
            } else {
                $password = $_POST['pw'] ?? '';
                if ($qs_data['post_passwd'] == md5($password)) {
                    $html = base64_decode($qs_data['content']);
                } else {
                    $html = "<form action='" . $_SERVER['REQUEST_URI'] . "' method='post'>";
                    $html .= UI('Password') . " ：<input type='password' name='pw' placeholder='" . UI('Article') . UI('Password') . "'>";
                    $html .= "</form>";
                }
            }
            echo $html;
        }
    }
}

function qs_content_video_src($html) {
    $html = (string) $html;
    if ($html === '') {
        return '';
    }
    if (preg_match('/(?:src|data-src)\s*=\s*(["\'])([^"\']+\.(?:mp4|webm|ogv|ogg|m3u8?)(?:[?#][^"\']*)?)\1/i', $html, $m)) {
        return trim($m[2]);
    }
    return '';
}

function qs_video_block($video, $autoplay = '', $video_picsrc = '', $class = '') {
    $video = trim((string) $video);
    if ($video === '') {
        return '';
    }
    $path = (string) parse_url($video, PHP_URL_PATH);
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = 'video/mp4';
    if ($ext === 'webm') {
        $mime = 'video/webm';
    } elseif ($ext === 'ogv' || $ext === 'ogg') {
        $mime = 'video/ogg';
    }
    $uid = 'qsVideo' . substr(md5($video), 0, 8);
    $auto = ($autoplay === '') ? '' : ' autoplay';

    $html  = '<div class="qs-video-wrap" id="' . $uid . '">';
    $html .= '<video class="qs-video ' . htmlspecialchars((string) $class) . '" controls' . $auto . ' muted playsinline preload="metadata"';
    if ($video_picsrc !== '') {
        $html .= ' poster="' . htmlspecialchars((string) $video_picsrc) . '"';
    }
    $html .= '>';
    $html .= '<source src="' . htmlspecialchars($video) . '" type="' . $mime . '">';
    $html .= '您的浏览器不支持视频播放。';
    $html .= '</video>';
    $html .= '<div class="qs-video-hint">当前静音，点击画面或拖动音量滑块即可收听</div>';
    $html .= '</div>';
    return $html;
}

function qs_the_link_url() {
  global $qs_post_id, $qs_post, $qs_config;
  $sitemode = $qs_config['sitemode'];
  $url_ends = $qs_config['url_ends'];
  return rtrim((string)qs_site_link(false), '/') . '/?' . $sitemode . '/' . $qs_post_id . $url_ends;
}

function qs_the_post_link() {
  return qs_the_link_url();
}

function qs_the_author() { return qs_the_user(); }

function qs_the_tag_name() { global $qs_get_name; return htmlspecialchars((string)($qs_get_name ?? '')); }

function qs_the_intro() { global $qs_post; return htmlspecialchars((string)($qs_post['intro'] ?? '')); }

function qs_the_book_pic() { return qs_book('pic'); }

function qs_the_render_all_tags($before = '', $after = '</div>') {
  if (!function_exists('qs_tag_cloud')) { return ''; }
  ob_start();
  qs_tag_cloud('<span style="display:inline-block;margin:0 6px 8px 0;">', ' ', '</span>');
  return $before . ob_get_clean() . $after;
}

?>