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
 * @Description  Template of the theme "Qsis_novel": <head> meta tags, stylesheet links and the site header/navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><html>
<head>
<title><?php if (qs_is_post() || qs_is_page()) { ?><?= qs_the_title() ?><?php  ?>-<?php qs_site_name(); } else if (qs_is_tag()) { ?>tag:<?php qs_tag_name(); ?> | <?php qs_site_name(); }  else { qs_site_name(); ?>-<?php qs_nick_code(); }?></title>

<meta name="renderer" content="webkit">
<meta name="format-detection" content="email=no">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta http-equiv="Cache-Control" content="no-siteapp">
<link rel="icon" type="image/png" href="<?php qs_ico(); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="author" content="<?php qs_user_nick(); ?>">
<meta http-equiv="x-dns-prefetch-control" content="on">
<meta name="site" content="<?php qs_site_link(); ?>">
<meta property="og:image" content="<?php qs_ico(); ?>">

<meta property="og:type" content="<?php echo (qs_is_post() || qs_is_page()) ? 'article' : 'website'; ?>"/>
<meta property="og:site_name" content="<?php qs_site_name(); ?>"/>
<meta property="og:description" content="<?php echo qs_is_post() ? qs_the_summary()."-".qs_site_name() : (qs_is_page() ? qs_the_title()." - ".qs_site_name() : (qs_is_tag() ? "关键词为:".qs_tag_name()."的文章-".qs_site_name() : qs_site_name()."-".qs_site_desc())); ?>"/>
<meta property="og:url" content="<?php qs_site_link(); ?>">
<meta property="og:locale" content="zh_CN">
<meta property="og:title" content="<?php echo (qs_is_post() || qs_is_page()) ? qs_the_title()."-".qs_site_name() : (qs_is_tag() ? "关键词为:".qs_tag_name()."的文章-".qs_site_name() : qs_site_name()."-".qs_nick_code()); ?>"/>
<meta property="twitter:partner" content="ogwp">
<link rel="icon" type="image/png" href="<?php qs_ico(); ?>">

<meta name="description" content="<?php echo qs_is_post() ? qs_the_summary()."-".qs_site_name() : (qs_is_page() ? qs_the_title()." - ".qs_site_name() : (qs_is_tag() ? "关键词为:".qs_tag_name()."的文章-".qs_site_name() : qs_site_name()."-".qs_site_desc())); ?>"/>
<meta name="keywords" content="<?php echo qs_is_post() ? meta_tags().",".qs_site_tags() : (qs_is_page() ? meta_tags()." - ".qs_site_name() : (qs_is_tag() ? qs_tag_name().",".qs_site_name() : qs_site_tags())); ?>"/>

<meta name="apple-mobile-web-app-capable" content="yes">
<!-- 安卓添加到主屏 -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="<?php qs_site_name(); ?>">
<link style="text/css" rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<link style="text/css" rel="stylesheet" href="<?php qs_theme_url('icon.css'); ?>" />
<link style="text/css" rel="stylesheet" href="<?php qs_theme_url('nv.css'); ?>" />
<?php qs_head_code(); ?>

</head>

<body class="no-off-canvas sidebar-none modular-title-1">
	    <div class="navbar">
			  <div class="logo-wrapper">
         <h2> <a class="logo-wrapper" href="<?php qs_site_link(); ?>">
        <img class="logo regular" src="<?php qs_sitelogo(); ?>" alt="" onerror="this.style.display='none'"><?php qs_site_name(); ?>
      </a></h2>
    
  </div> 			

<nav class="main-menu d-none d-lg-block">
<div class="amenum">
<?php qs_menu(); ?>
</div>
</nav>
 <div class="actions">
<button id="toggleButton" class="burger dropbtn "></button>
<div id="sidebarmenu" >
  <div  class="dropdown-content">
   <div class="sidebarmenu-title">
   	菜单
   </div>
<?php qs_menu(); ?>
  </div>
		</div>
</div>
		
  <script>
    const toggleButton = document.getElementById('toggleButton');
    const closeButton = document.getElementById('closeButton');
    const sidebarmenu = document.getElementById('sidebarmenu');

    toggleButton.addEventListener('mouseover', () => {
      sidebarmenu.classList.add('active');
    });

    sidebarmenu.addEventListener('click', () => {
      sidebarmenu.classList.remove('active');
    });
</script>

</div>
<div class="header-search-div">
<input type="text" placeholder="全站搜索关键字" autocomplete="off" class="headersearch" name="search" id="text" value="">
<button class="header-search-submit search-icon" type="submit" onclick="Search()"></button>
</div>

