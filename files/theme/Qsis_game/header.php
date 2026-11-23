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
 * @Description  Template of the theme "Qsis_game": <head> meta tags, stylesheet links and the site header/navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><html lang="zh-CN">
<head>
<title><?php if (qs_is_post() || qs_is_page()) { ?><?= qs_the_title() ?><?php  ?> | <?php qs_site_name(); } else if (qs_is_tag()) { ?>关键词:<?php qs_tag_name(); ?> | <?php qs_site_name(); }  else { qs_site_name(); ?> | <?php qs_nick_code(); }?></title>

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
<link rel="icon" type="image/png" href="<?php qs_ico(); ?>">
<!-- 控制页面在移动设备上的布局和缩放行为，确保页面适应不同设备的屏幕宽度，禁止用户缩放 user-scalable=no, ，设置初始缩放比例为，viewport-fit=cover 确保网页内容可以覆盖整个屏幕，并适配设备的“刘海”或圆角区域，而不会留下黑边或空白。 -->
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<!-- 指定网页的作者为 "Isher.Qiao"。 -->
<meta name="author" content="<?php qs_user_nick(); ?>">
<!-- 启用 DNS 预解析，允许浏览器提前解析域名，提高页面加载速度。 -->
<meta http-equiv="x-dns-prefetch-control" content="on">
<!-- 指定网站的主页 URL，这里是 "https://www.Qsis.cn"。 -->
<meta name="site" content="<?php qs_site_link(); ?>">
<!-- 指定当页面在社交媒体平台（如 Facebook）上被分享时，显示的缩略图或图片。 -->
<meta property="og:image" content="<?php qs_ico(); ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?php qs_site_name(); ?>"/>
<meta property="og:description" content="<?php if (qs_is_post()) { qs_the_summary(); ?>-<?php qs_site_name(); } else if (qs_is_page()) { ?><?= qs_the_title() ?><?php  ?> - <?php qs_site_name(); } else if (qs_is_tag()) { ?>关键词为『<?php qs_tag_name(); ?>』的文章 | <?php qs_site_name(); } else { qs_site_name(); ?>-<?php qs_site_desc(); } ?>"/>

<meta property="og:type" content="<?php if (qs_is_post() || qs_is_page()) { echo 'article'; } ?>"/>
<meta property="og:url" content="<?php qs_site_link(); ?>">
<!-- 指定页面的语言和地区为简体中文（中国）。-->
<meta property="og:locale" content="zh_CN">
<meta property="og:title" content="<?php if (qs_is_post() || qs_is_page()) {  ?><?= qs_the_title() ?><?php   ?>-<?php qs_site_name(); } else if (qs_is_tag()) { ?>关键词为『<?php qs_tag_name(); ?>』的文章 | <?php qs_site_name(); }  else { qs_site_name(); ?>-<?php qs_nick_code(); }?>" />
<meta property="twitter:partner" content="ogwp">
<link rel="icon" type="image/png" href="<?php qs_ico(); ?>">
<meta name="description" centent="<?php if (qs_is_post()) { qs_the_summary(); ?>-<?php qs_site_name(); } else if (qs_is_page()) { ?><?= qs_the_title() ?><?php  ?> - <?php qs_site_name(); } else if (qs_is_tag()) { ?>关键词为:<?php qs_tag_name(); ?>』的文章 | <?php qs_site_name(); } else { qs_site_name(); ?>-<?php qs_site_desc(); } ?>"/>
<meta name="keywords" content="<?php if (qs_is_post()) {  meta_tags();  ?>,<?php qs_site_tags(); } else if (qs_is_page()) { meta_tags(); ?>,<?php qs_site_name(); } else if (qs_is_tag()) { ?><?php qs_tag_name(); ?>,<?php qs_site_name(); }  else { qs_site_tags(); }?>" />
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- 安卓添加到主屏 -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="<?php qs_site_name(); ?>">
<link rel="stylesheet" href="<?php qs_theme_url('app.css'); ?>" media="all">
<link rel="stylesheet" href="<?php qs_theme_url('icon.css'); ?>" media="all">
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" media="all">

<?php
if (!function_exists('game_opt')) {
    include __DIR__ . '/theme_config.php';
}
$gmHero1     = game_opt('hero_bg1', '#1b2a41');
$gmHero2     = game_opt('hero_bg2', '#0d1420');
$gmHeroImg   = game_opt('hero_bg_image', '');
$gmSearchBg  = game_opt('search_bg', '#ffffff');
$gmPageBg    = game_opt('page_bg', '#eef2f7');
$gmCardBg    = game_opt('card_bg', '#ffffff');
$gmAccent    = game_opt('accent', '#ff7a45');
$gmLogoColor = game_opt('logo_color', '#ff3131');
$gmTextColor = game_opt('text_color', '#111826');
$gmMutedColor = game_opt('muted_color', '#6b7a90');
$gmHeroImgUrl = '';
if ($gmHeroImg !== '') {
    $gmHeroImgUrl = (strpos($gmHeroImg, '//') !== false)
        ? $gmHeroImg
        : qs_theme_url($gmHeroImg, false);
}
?>
<style id="gm-theme-vars">
.module.search-bg{background:linear-gradient(135deg,<?php echo $gmHero1; ?>,<?php echo $gmHero2; ?>) !important;}
<?php if ($gmHeroImgUrl !== '') { ?>
.module.search-bg{background-image:url('<?php echo htmlspecialchars($gmHeroImgUrl); ?>') !important;background-size:cover !important;background-position:center center !important;}
<?php } ?>
body{background-color:<?php echo $gmPageBg; ?> !important;}
.gm-card{background-color:<?php echo $gmCardBg; ?> !important;}
.search-fields,.gm-side-search{background-color:<?php echo $gmSearchBg; ?> !important;}
.gm-play{background-color:<?php echo $gmAccent; ?> !important;}
.gm-kw{color:<?php echo $gmAccent; ?> !important;}
.gm-logo-text{color:<?php echo $gmLogoColor; ?> !important;}
.gm-card .entry-title,.gm-card .entry-title a{color:<?php echo $gmTextColor; ?> !important;}
.gm-card .entry-meta,.gm-card .meta-category-dot{color:<?php echo $gmMutedColor; ?> !important;}
.gm-card .meta-category-dot a{color:<?php echo $gmMutedColor; ?> !important;}
</style>

<script>
function qsGameSearch(el){
  var input = el;
  if (el && el.tagName === 'BUTTON') { input = el.parentNode.querySelector('input'); }
  if (!input) { return false; }
  var key = String(input.value || '').trim();
  if (key === '') { input.focus(); return false; }
  location.href = <?php echo json_encode(rtrim(qs_site_link(false), '/') . '/'); ?> + '?search/' + encodeURIComponent(key) + '/';
  return false;
}
document.addEventListener('keydown', function(e){
  if (e.key !== 'Enter') { return; }
  var t = e.target;
  if (t && t.classList && t.classList.contains('search')) { e.preventDefault(); qsGameSearch(t); }
});
</script>

</head>
<body class="home blog logged-in hfeed wide-screen navbar-sticky pagination-infinite_button paged-next no-off-canvas sidebar-none modular-title-1">
<div id="app" class="site">
<header class="site-header">
    <div class="container">
	    <div class="navbar">
			  <div class="logo-wrapper">
         <h2> <a class="logo-wrapper" href="<?php qs_site_link(); ?>">
<?php if (game_opt('logo_mode', 'image') === 'text') { ?>
        <span class="gm-logo-text"><?php echo htmlspecialchars(game_opt('logo_text', '')); if (game_opt('logo_text', '') === '') { qs_site_name(); } ?></span>
<?php } else { ?>
        <img class="logo regular" src="<?php qs_sitelogo(); ?>" alt="<?php qs_site_name(); ?>" onerror="this.style.display='none';"><span class="gm-logo-text"><?php echo htmlspecialchars(game_opt('logo_text', '')); if (game_opt('logo_text', '') === '') { qs_site_name(); } ?></span>
<?php } ?>
      </a></h2>
    
  </div> 			
			<div class="sep"></div>
			<nav class="main-menu d-none d-lg-block">
     <div class="amenum">
<?php qs_menu(); ?>
     </div>
		</nav>
		<!--
<div class="userlogo">
		  <button id="" class="burger "></button>

</div-->

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
    </div>
</header>

<div class="header-gap"></div>