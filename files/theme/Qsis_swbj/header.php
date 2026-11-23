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
 * @LastEditTime 15/09/26 22:42:24
 * @Description  Template of the theme "Qsis_swbj": <head> meta tags, stylesheet links and the site header/navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
$theme_config_file = __DIR__ . '/theme_config.php';
if (file_exists($theme_config_file)) {
    include $theme_config_file;
} else {
    $theme_tag_blocks = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php
if (qs_is_post()) {
    ?><?= qs_the_title() ?><?php 
	  echo ' - ';
	echo $qs_config['site_name'];
} elseif (qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' - ';
echo $qs_config['site_name'];
} elseif (qs_is_tag()) {
	echo '专栏：';
    qs_tag_name();
	echo '-'.$qs_config['site_name'];
} else {
    echo isset($qs_config['site_name']) ? $qs_config['site_name'] : '';
}
?></title>
    <meta name="renderer" content="webkit">
    <meta name="format-detection" content="email=no">
    <meta name="format-detection" content="telephone=no">
<?php
// 规范地址：文章用文章固定链接，其余按当前请求路径拼站点根，避免变量未定义
$canonical_url = '';
$canonical_base = rtrim((string)qs_site_link(false), '/');
if (qs_is_post()) {
    $canonical_url = qs_post_link(null, false);
} else {
    $canonical_req = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $canonical_url = $canonical_base . ($canonical_req === '' ? '/' : $canonical_req);
}
?>
<link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <link rel="alternate" type="application/rss+xml" href="<?php echo rtrim((string)qs_site_link(false), '/'); ?>/rss.xml" title="深悟笔记 RSS 订阅">

<?php
if (qs_is_post()) { ?>
<?php if (qs_the_content_pic()){ ?>
<meta name="thumbnail" content="<?php echo qs_the_content_pic(); ?>">
<meta name="description" content="<?php echo qs_the_summary(80); ?>">
<meta property="og:type" content="article">
<?php } else { ?>
<meta name="thumbnail" content="<?php echo qs_site_link(false).$qs_config['sitelogo']; ?>">
<meta name="description" content="<?php echo qs_the_summary(80); ?>">
<meta property="og:type" content="article">
<?php } ?>
<?php } elseif (qs_is_page()) { ?>
<meta name="thumbnail" content="<?php echo qs_site_link(false).$qs_config['sitelogo']; ?>">
<meta name="description" content="<?php echo isset($qs_data['content']) ? htmlspecialchars(strip_tags(mb_substr($qs_data['content'],0,80,'UTF-8'))) : ''; ?>">
<meta property="og:type" content="page">
<?php } elseif (qs_is_tag()) {
} elseif (qs_is_bookname()) {
} else { ?>
<meta name="thumbnail" content="<?php echo qs_site_link(false).$qs_config['sitelogo']; ?>">
<meta name="description" content="<?php echo $qs_config['site_desc'];?>">
<meta property="og:type" content="website">
	<?php }
?>

    <meta http-equiv="Cache-Control" content="no-siteapp">
<meta name="application-name" content="<?php echo $qs_config['site_name']; ?>" />
<meta property="og:site_name" content="<?php echo $qs_config['site_name']; ?>">
    <?=qs_google_icon()?>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <meta name="site" content="<?php echo rtrim(qs_site_link(false), '/'); ?>">
    <meta property="og:image" content="<?php echo $qs_config['ico']; ?>">
    <?php
    //仅首页输出WebSite结构化数据，用于搜索引擎识别中文站点名
    if(!qs_is_post() && !qs_is_page() && !qs_is_tag() && !qs_is_bookname()){
    ?>
<script type="application/ld+json">
{
    "@context":"https://schema.org",
    "@type":"WebSite",
    "name":"<?php echo $qs_config['site_name']; ?>",
    "url":"<?php echo rtrim(qs_site_link(false), '/'); ?>"
}
</script>
    <?php } ?>
    <?php if(qs_is_post()){
        ob_start();
        ?><?= qs_the_title() ?><?php 
        $articleTitle = ob_get_clean();
        $articleTitleEscaped = str_replace('"', '\"', $articleTitle);
    ?>
<script type="application/ld+json">
{
    "@context":"https://schema.org",
    "@type":"Article",
    "headline":"<?php echo $articleTitleEscaped; ?>",
    "url":"<?php echo qs_get_the_link(); ?>",
    "publisher":{
        "@type":"Organization",
        "name":"深悟笔记"
    }
}
</script>
<?php } ?>

    <link  style="text/css" rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php echo $qs_config['site_name']; ?>">
    <?php echo $qs_config['head_code']; ?>
<link rel="stylesheet" href="/files/vendor/swiper/8.4.7/swiper-bundle.min.css">
<script src="/files/vendor/swiper/8.4.7/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var el = document.querySelector('.swiper-container');
  if (!el || typeof Swiper === 'undefined') { return; }
  new Swiper(el, {
    loop: true,
    autoplay: { delay: 4000 },
    pagination: { el: '.swiper-pagination', clickable: true }
  });
});
</script>
</head>

<body>

<!-- 顶部导航 -->
<div class="header-top">
  <div class="container">
    <div>深悟笔记官方写作平台</div>
    <div class="top-nav">
<?php qs_sidemenu(); ?>
    </div>
  </div>
</div>

<!-- LOGO + 搜索 -->
<div class="header-main">
  <div class="container">
    <div class="logo"><a href="<?php qs_site_link(); ?>" class="logo" title="<?php echo isset($qs_config['site_name']) ? $qs_config['site_name'] : ''; ?>"><?php echo isset($qs_config['site_name']) ? $qs_config['site_name'] : ''; ?></a></div>
    <div class="search-box">
    	 <input type="text" id="search-input" placeholder="请输入关键词搜索" autocomplete="off">
      <button>搜索</button>
    </div>
  </div>
</div>

<!-- 主导航（带点击效果） -->
<div class="main-nav">
  <div class="container">
<?php qs_menu(); ?>
  </div>
</div>
<div class="container">
  <div id="search-result"></div>
 </div>