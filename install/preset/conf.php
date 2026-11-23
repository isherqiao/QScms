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
 * @File         conf.php
 * @CreatedTime  05/11/26 23:05:52
 * @LastEditTime 05/11/26 23:05:52
 * @Description  Preset data: base site configuration used as the starting point by the installer, copied into control/data during installation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// QScms 预设站点配置（安装向导生成时以本文件为基底）
$qs_config = array (
  'site_link' => '',
  'openspeak' => 'open',
  'site_link_ipv4' => '',
  'site_link_ipv6' => '',
  'site_name' => '',
  'site_desc' => '',
  'nick_code' => '',
  'user_nick' => '',
  'showtime' => '1',
  'css_name' => '侧栏布局-奶油杏',
  'css_theme' => 'left-apricot.css',
  'v4v6auto' => '0',
  'site_theme' => 'Qsis_swbj',
  'per_page' => 9,
  'articlehead' => '150',
  'site_about' => 'QScms 是一套以文件存储为核心、支持静态化发布的内容管理系统。内容、菜单与站点参数都以可读文件保存，无需数据库即可部署，适合个人写作、知识整理与轻量企业站点。',
  'footer_code' => 'Powered by <a href="https://cms.qsis.cn" target="_blank">QScms</a>. Copyright © 2026 QScms Demo All Rights Reserved.',
  'other_code' => '',
  'comment_code' => '',
  'tags_list' => 'QScms,建站系统,静态站,内容管理,示例站点',
  'head_code' => '<meta name="viewport" content="width=device-width,initial-scale=1"/>',
  'site_tags' => 'QScms,建站系统,静态站,内容管理,示例站点',
  'liuyan_data_name' => '43wangzhanliuyan.php',
  'defaultpostimage' => 'files/images/bg.jpg',
  'ico' => 'files/images/btn.jpg',
  'videomod' => '0',
  'sitelogo' => '',
  'sitemode' => 'article',
  'user_manage' => '1',
  'site_directory' => '/',
  'imagehost' => '',
  'sitelogoname' => 'name',
  'sccpost_page' => '10',
  'sccpage_page' => '10',
  'site_backgrouppic' => '',
  'language_name' => '简体中文',
  'language_file' => 'lang_zh-CN.php',
  'edittools' => 'Quill',
  'remote_bak01' => 'email',
  'tar_exclude_directory' => '/backups/',
  'tar_name' => 'qscms_demo',
  'tar_directory' => '/',
  'des_directory' => '/backups/',
  'tar_email' => '',
  'tar_other' => '',
  'tar_passwd' => 'nopass',
  'verify_email' => '',
  'bookgenreslist' => '',
  'speakArticle' => 'web',
  'url_ends' => '.qshtml',
  'role' => '',
  'github_owner' => '',
  'github_repo' => '',
  'github_branch' => '',
  'github_sync_dir' => '',
  'github_commit_msg' => '',
  'github_token' => '',
  'static_site_backgrouppic' => '',
  'local_upload_dir' => '../upload',
  'custom_side_photo' => '',
  'carousel_config' => '',
  'custom_RSS' => '',
  'tagsonarticle' => '0',
  'site_close' => '1',
  'switch' => 
  array (
    'robots' => 1,
    'sitemap' => 1,
    'rss' => 1,
    'search_index' => 1,
    'page_404' => 1,
    'urls_txt' => 1,
    'cname' => 1,
  ),
  'robots' => '',
  'postapi_site_secret' => '',
  'post_api' => '',
  'remote_api_url' => '',
  'remote_api_key' => '',
  'qs_post_dir' => 'data/posts/',
  'qs_page_dir' => 'data/pages/',
  'google_ads' => '',
  'IndexNow' => '',
  'ai_api' => 'close',
  'ai_api_list' => 
  array (
  ),
  'site_close_desc' => '<div style="text-align:center; margin-top:120px; font-family:Microsoft YaHei;"><h2 style="color:#333;">站点维护中</h2><p style="font-size:17px; color:#555; margin:24px 0;">本站正在维护，请稍后再访问。</p></div>',
  'site_close_redirect' => '',
  'speak_tts_list' => 
  array (
    0 => 
    array (
      'key' => 'web',
      'name' => '浏览器原生朗读',
      'handler' => 'web',
      'enabled' => '1',
      'voice' => '',
      'lang' => 'zh-CN',
      'rate' => '1',
      'method' => 'POST',
      'api_url' => '',
      'api_key' => '',
      'api_secret' => '',
      'extra' => '',
      'cmd' => '',
      'cmd_workdir' => '',
    ),
  ),
);
?>