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
 * @File         header.php
 * @Description  Template of the theme "Qsis_maruko": document head and HUD header.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_gd_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_gd_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_gd_logo = isset($qs_config['sitelogo']) ? (string)$qs_config['sitelogo'] : '';

$qs_gd_canonical = '';
if (qs_is_post()) {
    $qs_gd_canonical = qs_post_link(null, false);
} else {
    $qs_gd_base = rtrim((string)qs_site_link(false), '/');
    $qs_gd_req  = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $qs_gd_canonical = $qs_gd_base . ($qs_gd_req === '' ? '/' : $qs_gd_req);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' - ' . $qs_gd_site;
} elseif (qs_is_tag()) {
    echo 'SECTOR：';
    qs_tag_name();
    echo ' - ' . $qs_gd_site;
} else {
    echo $qs_gd_site;
}
?></title>
<meta name="renderer" content="webkit">
<meta name="format-detection" content="telephone=no, email=no">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_gd_canonical); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_gd_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_gd_site); ?>">
<?php if (qs_is_post()): ?>
<meta name="description" content="<?php echo htmlspecialchars(qs_the_summary(90)); ?>">
<meta property="og:type" content="article">
<?php else: ?>
<meta name="description" content="<?php echo htmlspecialchars($qs_gd_desc); ?>">
<meta property="og:type" content="website">
<?php endif; ?>
<?= isset($qs_config['ico']) ? qs_google_icon() : '' ?>
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
if (!empty($theme_options) && is_array($theme_options) && function_exists('qs_theme_opts_css')) {
    $qs_gd_vars = array();
    if (!empty($theme_options['color_main'])) {
        $qs_gd_vars['mk-red'] = $theme_options['color_main'];
    }
    if (!empty($theme_options['color_accent'])) {
        $qs_gd_vars['mk-yellow'] = $theme_options['color_accent'];
    }
    $qs_gd_sb = isset($theme_options['custom_scrollbar']) && $theme_options['custom_scrollbar'] === '1';
    $qs_gd_css = qs_theme_opts_css($qs_gd_vars, $qs_gd_sb, '#fffaf1', '#df4a4a');
    if ($qs_gd_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_gd_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="qs-gd">

<div class="gd-scanline" aria-hidden="true"></div>

<header class="gd-header">
    <div class="gd-wrap gd-header-inner">
        <a class="gd-brand" href="<?php qs_site_link(); ?>" title="<?php echo htmlspecialchars($qs_gd_site); ?>">
            <?php if ($qs_gd_logo !== ''): ?>
            <img src="<?php echo htmlspecialchars(qs_site_link(false) . $qs_gd_logo); ?>" alt="<?php echo htmlspecialchars($qs_gd_site); ?>">
            <?php else: ?>
            <span class="gd-brand-badge" aria-hidden="true">
                <svg viewBox="0 0 40 40" width="34" height="34"><path d="M20 3c5 4 8 7 8 11" fill="none" stroke="#5f8a4a" stroke-width="2.2" stroke-linecap="round"/><circle cx="15" cy="24" r="7.4" fill="#df4a4a"/><circle cx="26" cy="27" r="6" fill="#bd3030"/><circle cx="12.6" cy="20.6" r="2" fill="#fff" opacity=".55"/><path d="M13 9c-2 3-1 6 3 8" fill="none" stroke="#5f8a4a" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <span class="gd-brand-text"><?php echo htmlspecialchars($qs_gd_site); ?></span>
            <?php endif; ?>
        </a>
        <nav class="gd-nav"><?php qs_menu(); ?></nav>
        <nav class="gd-nav-side"><?php qs_sidemenu(); ?></nav>
    </div>
    <div class="gd-rule" aria-hidden="true"></div>
</header>
