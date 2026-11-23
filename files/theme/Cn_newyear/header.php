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
 * @File         header.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Cn_newyear": <head> meta tags, stylesheet links and the site header/navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><html>
<head>
<title><?php if (qs_is_post() || qs_is_page()) { ?><?= qs_the_title() ?> | <?= qs_site_name() ?><?php } else if (qs_is_tag()) { ?>关键词:<?= qs_tag_name() ?> | <?= qs_site_name() ?><?php } else { ?><?= qs_site_name() ?> | <?= qs_nick_code() ?><?php } ?></title>

 <!-- 告诉浏览器使用 Webkit 渲染引擎来渲染页面，通常用于指定某些浏览器（如QQ浏览器、360浏览器）应该使用 Webkit 内核。-->
 <meta name="renderer" content="webkit">
<!-- 禁止 iOS 设备自动将页面中的电子邮件地址转换为可点击的链接。-->
    <meta name="format-detection" content="email=no">
<!-- 禁止 iOS 设备自动将页面中的电话号码转换为可拨打的链接。-->
<meta name="format-detection" content="telephone=no">
<!-- 设置网页的字符编码为 UTF-8，确保页面可以正确显示各种字符，包括中文。-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- 设置页面在 Internet Explorer 中使用最新的渲染引擎（Edge 模式），同时启用 Chrome Frame（如果安装）。-->
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<!-- 禁止百度SiteAPP转码，禁止页面在移动设备上作为“Web应用”缓存，以避免被某些移动浏览器添加到主屏幕时出现问题。 -->
<meta http-equiv="Cache-Control" content="no-siteapp">
<!-- Chrome浏览器添加桌面快捷方式（安卓） -->
<link rel="icon" type="image/png" href="<?= qs_ico() ?>">
<!-- 控制页面在移动设备上的布局和缩放行为，确保页面适应不同设备的屏幕宽度，禁止用户缩放 user-scalable=no, ，设置初始缩放比例为，viewport-fit=cover 确保网页内容可以覆盖整个屏幕，并适配设备的“刘海”或圆角区域，而不会留下黑边或空白。 -->
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<!-- 指定网页的作者为 "Isher.Qiao"。 -->
<meta name="author" content="<?= qs_user_nick() ?>">
<!-- 启用 DNS 预解析，允许浏览器提前解析域名，提高页面加载速度。 -->
<meta http-equiv="x-dns-prefetch-control" content="on">
<!-- 指定网站的主页 URL，这里是 "https://www.Qsis.cn"。 -->
<meta name="site" content="<?= qs_site_link() ?>">
<!-- 指定当页面在社交媒体平台（如 Facebook）上被分享时，显示的缩略图或图片。 -->
<meta property="og:image" content="<?= qs_ico() ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= qs_site_name() ?>"/>
<meta property="og:description" content="<?php if (qs_is_post()) { ?><?= qs_the_summary() ?>-<?= qs_site_name() ?><?php } else if (qs_is_page()) { ?><?= qs_the_title() ?> - <?= qs_site_name() ?><?php } else if (qs_is_tag()) { ?>关键词为『<?= qs_tag_name() ?>』的文章 | <?= qs_site_name() ?><?php } else { ?><?= qs_site_name() ?>-<?= qs_site_desc() ?><?php } ?>"/>

<meta property="og:type" content="<?php if (qs_is_post() || qs_is_page()) { echo 'article'; } ?>"/>
<meta property="og:url" content="<?= qs_site_link() ?>">
<!-- 指定页面的语言和地区为简体中文（中国）。-->
<meta property="og:locale" content="zh_CN">
<meta property="og:title" content="<?php if (qs_is_post() || qs_is_page()) {  ?><?= qs_the_title() ?>-<?= qs_site_name() ?><?php } else if (qs_is_tag()) { ?>关键词为『<?= qs_tag_name() ?>』的文章 | <?= qs_site_name() ?><?php }  else { ?><?= qs_site_name() ?>-<?= qs_nick_code() ?><?php }?>" />
<meta property="twitter:partner" content="ogwp">
<link rel="icon" type="image/png" href="<?= qs_ico() ?>">
<meta name="description" centent="<?php if (qs_is_post()) { ?><?= qs_the_summary() ?>-<?= qs_site_name() ?><?php } else if (qs_is_page()) { ?><?= qs_the_title() ?> - <?= qs_site_name() ?><?php } else if (qs_is_tag()) { ?>关键词为:<?= qs_tag_name() ?>』的文章 | <?= qs_site_name() ?><?php } else { ?><?= qs_site_name() ?>-<?= qs_site_desc() ?><?php } ?>"/>
<meta name="keywords" content="<?php if (qs_is_post()) {  meta_tags();  ?>,<?php qs_site_tags(); } else if (qs_is_page()) { meta_tags(); ?>,<?php qs_site_name(); } else if (qs_is_tag()) { ?><?= qs_tag_name() ?>,<?php qs_site_name(); }  else { qs_site_tags(); }?>" />
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- 安卓添加到主屏 -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="<?= qs_site_name() ?>">
<link  id="style" style="text/css" rel="stylesheet" href="<?= qs_theme_url('style.css') ?>" />

<?= qs_head_code() ?>

</head>

<body>

<div id="main">
  <div id="header">
    <div class="title"></div>
    <div id="sitename">
        <a href="<?= qs_site_link() ?>" title="<?= qs_site_desc() ?>"><?= qs_site_name() ?></a>
</div>
  </div>
  <div class="clear"></div>
