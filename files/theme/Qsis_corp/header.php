<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 */

/**
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @File         header.php
 * @Description  Template of the theme "Qsis_corp": document head, top bar and primary navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_corp_site  = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_corp_desc  = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_corp_logo  = isset($qs_config['sitelogo']) ? (string)$qs_config['sitelogo'] : '';

$qs_corp_canonical = '';
if (qs_is_post()) {
    $qs_corp_canonical = qs_post_link(null, false);
} else {
    $qs_corp_base = rtrim((string)qs_site_link(false), '/');
    $qs_corp_req  = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $qs_corp_canonical = $qs_corp_base . ($qs_corp_req === '' ? '/' : $qs_corp_req);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' - ' . $qs_corp_site;
} elseif (qs_is_tag()) {
    echo '专题：';
    qs_tag_name();
    echo ' - ' . $qs_corp_site;
} else {
    echo $qs_corp_site;
}
?></title>
<meta name="renderer" content="webkit">
<meta name="format-detection" content="telephone=no, email=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_corp_canonical); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_corp_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_corp_site); ?>">
<?php if (qs_is_post()): ?>
<meta name="description" content="<?php echo htmlspecialchars(qs_the_summary(90)); ?>">
<meta property="og:type" content="article">
<?php else: ?>
<meta name="description" content="<?php echo htmlspecialchars($qs_corp_desc); ?>">
<meta property="og:type" content="website">
<?php endif; ?>
<meta name="application-name" content="<?php echo htmlspecialchars($qs_corp_site); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars($qs_corp_site); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<?= isset($qs_config['ico']) ? qs_google_icon() : '' ?>
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
if (!empty($theme_options) && is_array($theme_options) && function_exists('qs_theme_opts_css')) {
    $qs_corp_vars = array();
    if (!empty($theme_options['color_brand'])) {
        $qs_corp_vars['brand'] = $theme_options['color_brand'];
    }
    if (!empty($theme_options['color_accent'])) {
        $qs_corp_vars['accent'] = $theme_options['color_accent'];
    }
    $qs_corp_sb = isset($theme_options['custom_scrollbar']) && $theme_options['custom_scrollbar'] === '1';
    $qs_corp_css = qs_theme_opts_css($qs_corp_vars, $qs_corp_sb);
    if ($qs_corp_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_corp_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="qs-corp">

<header class="site-header">
    <div class="topbar">
        <div class="wrap">
            <span class="topbar-slogan"><?php echo htmlspecialchars($qs_corp_desc !== '' ? $qs_corp_desc : $qs_corp_site); ?></span>
            <nav class="topbar-nav"><?php qs_sidemenu(); ?></nav>
        </div>
    </div>
    <div class="masthead">
        <div class="wrap">
            <a class="brand" href="<?php qs_site_link(); ?>" title="<?php echo htmlspecialchars($qs_corp_site); ?>">
                <?php if ($qs_corp_logo !== ''): ?>
                <img src="<?php echo htmlspecialchars(qs_site_link(false) . $qs_corp_logo); ?>" alt="<?php echo htmlspecialchars($qs_corp_site); ?>">
                <?php else: ?>
                <span class="brand-text"><?php echo htmlspecialchars($qs_corp_site); ?></span>
                <?php endif; ?>
            </a>
            <nav class="primary-nav"><?php qs_menu(); ?></nav>
        </div>
    </div>
</header>
