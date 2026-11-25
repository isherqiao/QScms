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
 * @File         core.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 12/11/26 10:40:00
 * @Description  Core bootstrap: path constants, the version number and shared configuration includes.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", "On");
error_reporting(E_ALL);

define('SITE_ROOT', dirname(__DIR__)); 
define('WEB_BASE', '/');   // 前端浏览器的访问路径\如虚拟目录是/book/虚拟目录地址可以填写 /book/

// ========== 后台目录：运行时自动识别，不再写死目录名 ==========
// 1) 后台请求：取 SCRIPT_NAME 第一段，并确认该目录下确实存在 head.php，避免误判
// 2) 前台请求：读 control/data/admin_dir.php（后台首次访问新目录时自动写回）
// 3) 两者都拿不到：回退 admin
// 结论：把 admin 目录改成 CCC 后，访问一次 /CCC/set.php 即可全站自动切换，无需改代码与配置。
$qs_admin_dir_file  = SITE_ROOT . '/control/data/admin_dir.php';
$qs_admin_dir       = '';
$qs_admin_saved     = '';

$qs_script_rel = ltrim(str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$qs_script_seg = $qs_script_rel === '' ? [] : explode('/', $qs_script_rel);
if (!empty($qs_script_seg[0]) && is_file(SITE_ROOT . '/' . $qs_script_seg[0] . '/head.php')) {
    // 当前请求就落在后台目录里：目录名即第一段（如 admin、user 的上一级）
    $qs_admin_dir = $qs_script_seg[0];
}

if (is_file($qs_admin_dir_file)) {
    $qs_admin_raw = (string)@file_get_contents($qs_admin_dir_file);
    if (preg_match("/return\\s*'([A-Za-z0-9_-]{1,64})'/", $qs_admin_raw, $qs_admin_m)) {
        $qs_admin_saved = $qs_admin_m[1];
    }
}

if ($qs_admin_dir === '') {
    $qs_admin_dir = $qs_admin_saved !== '' ? $qs_admin_saved : 'admin';
} elseif ($qs_admin_dir !== $qs_admin_saved) {
    // 目录改名后第一次进入后台：把新目录名记下来，供前台编辑链接、插件按钮、安装页使用
    $qs_admin_tmp  = $qs_admin_dir_file . '.tmp';
    $qs_admin_body = "<?php\n// 后台目录名：由 core.php 在后台首次访问时自动维护，请不要手工改成不存在的目录\nreturn '" . $qs_admin_dir . "';\n";
    if (@file_put_contents($qs_admin_tmp, $qs_admin_body, LOCK_EX) !== false) {
        @rename($qs_admin_tmp, $qs_admin_dir_file);
    }
}

define('QS_ADMIN_DIR', $qs_admin_dir);
define('QS_ADMIN_URL', WEB_BASE . QS_ADMIN_DIR);   // 相对站点根的后台 URL 前缀，如 /admin

// config set 2023-9-27 by isher
// 后台目录设置（跟随上面的自动识别结果）
$controldata         = SITE_ROOT . '/' . QS_ADMIN_DIR . '/';   // 后台本地路径
$controlcss          = $controldata . 'css/';                  // CSS 本地路径

$controldata_url     = QS_ADMIN_URL;                           // 前端浏览器的访问路径
$controlcss_url      = $controldata_url . '/css/';              // CSS 浏览器访问路径

// 主题目录
$theme_dir = SITE_ROOT .'/files/theme/';  // 主题目录
$static_theme_dir = $theme_dir;  // 静态主题目录（新版已与动态主题合并，统一为 files/theme/）
$language_dir = SITE_ROOT . '/files/language/admin/';   //后台语言包位置
$UI_language_dir = SITE_ROOT . '/files/language/UI/';   //网站前台语言包位置
$datapaths = SITE_ROOT . '/control/data/';  // 管理用数据文件所在路径
//定位配置文件
$set_ai_prompt = 'ai_prompt.php';
$config = 'conf.php'; // 配置数据
$config_user = 'confp.php';  // 用户数据
$plugin_conf = 'plugin_conf.php'; // 插件配置
$regPath = 'authorization.php';
$staticconfig = 'static_config.php';
$advcconfig = 'adv_config.php';
$adconfig = 'ads.php';
$staticmultisites = 'static_multisite_config.php';

// ===日志路径配置,同时修改verify文件===
$logspath = $datapaths . 'logs/';
define('DATA_LOG_PATH', $logspath);

// ==========自动加载 control/data/ 下全部php文件==========
//$excludeFile = 'liuyan.php';
//$files = glob($datapaths . '*.php');
//if (!empty($files)) {
//    foreach ($files as $file) {
//       if (is_file($file) && $file !== $excludeFile) {
//            require_once $file;
//        }
//    }
//}

//引用配置文件
require_once __DIR__ . '/verify.php';   //函数位置
require_once __DIR__ . '/functions.php';   //函数位置
// 投票数据层（文件存储：control/data/votes.php 与 control/data/vote_records.php）
if (is_file(__DIR__ . '/vote_lib.php')) {
    require_once __DIR__ . '/vote_lib.php';
}
require_once $datapaths  . $config;  // 配置数据
// 菜单配置（系统自带两个菜单 + 自定义菜单，统一由后台菜单管理维护）
if (file_exists($datapaths . 'menu_conf.php')) {
    require_once $datapaths . 'menu_conf.php';
}
require_once $datapaths  . $config_user;  //用户数据
require_once $datapaths  . 'permission.php';  //功能权限配置（功能清单 + 用户组默认值 + 页面守卫映射）
// 用户组可编辑数据文件（由 group_manage.php 维护，覆盖 permission.php 中的默认组）
if (file_exists($datapaths . 'groups.php')) {
    require_once $datapaths . 'groups.php';
}
require_once $datapaths  . $plugin_conf; //插件管理文件
require_once $datapaths  . $staticconfig; //插件管理文件static_config.php
require_once $datapaths  . $advcconfig; //开发设置
// 👉广告配置**不在这里强制引入**，按需加载，避免不必要开销
// 使用时加载方式： require_once $datapaths . $adconfig;

function qs_404() {
    header('HTTP/1.0 404 Not Found');
    require __DIR__ . '/404.php';
    exit();
}

// 版本号规则：YYMMDD + 当天两位序号（01 起，跨天重置）。例：26112301 = 2026-11-23 当天第 1 版
$version = '26112506';
// 原有其他控制程序已经添加到/index.php最底下
?>
