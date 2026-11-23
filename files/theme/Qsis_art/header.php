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
 * @Description  Template of the theme "Qsis_art": document head and header.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_la_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_la_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_la_logo = isset($qs_config['sitelogo']) ? (string)$qs_config['sitelogo'] : '';

$qs_la_canonical = '';
if (qs_is_post()) {
    $qs_la_canonical = qs_post_link(null, false);
} else {
    $qs_la_base = rtrim((string)qs_site_link(false), '/');
    $qs_la_req  = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $qs_la_canonical = $qs_la_base . ($qs_la_req === '' ? '/' : $qs_la_req);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' - ' . $qs_la_site;
} elseif (qs_is_tag()) {
    echo '关键词：';
    qs_tag_name();
    echo ' - ' . $qs_la_site;
} else {
    echo $qs_la_site;
}
?></title>
<meta name="renderer" content="webkit">
<meta name="format-detection" content="telephone=no, email=no">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_la_canonical); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_la_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_la_site); ?>">
<?php if (qs_is_post()): ?>
<meta name="description" content="<?php echo htmlspecialchars(qs_the_summary(90)); ?>">
<meta property="og:type" content="article">
<?php else: ?>
<meta name="description" content="<?php echo htmlspecialchars($qs_la_desc); ?>">
<meta property="og:type" content="website">
<?php endif; ?>
<?= isset($qs_config['ico']) ? qs_google_icon() : '' ?>
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
if (!empty($theme_options) && is_array($theme_options) && function_exists('qs_theme_opts_css')) {
    $qs_la_vars = array();
    if (!empty($theme_options['color_main'])) {
        $qs_la_vars['la-ink'] = $theme_options['color_main'];
    }
    if (!empty($theme_options['color_accent'])) {
        $qs_la_vars['la-pop'] = $theme_options['color_accent'];
    }
    $qs_la_sb = isset($theme_options['custom_scrollbar']) && $theme_options['custom_scrollbar'] === '1';
    $qs_la_css = qs_theme_opts_css($qs_la_vars, $qs_la_sb, '#fff1e8', '#ff8a71');
    if ($qs_la_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_la_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="qs-la">

<header class="la-header">
    <div class="la-wrap la-header-inner">
        <a class="la-brand" href="<?php qs_site_link(); ?>" title="<?php echo htmlspecialchars($qs_la_site); ?>">
            <?php if ($qs_la_logo !== ''): ?>
            <img src="<?php echo htmlspecialchars(qs_site_link(false) . $qs_la_logo); ?>" alt="<?php echo htmlspecialchars($qs_la_site); ?>">
            <?php else: ?>
            <span class="la-brand-face" aria-hidden="true">
                <svg viewBox="0 0 40 40" width="34" height="34"><circle cx="20" cy="20" r="19" fill="#fff"/><circle cx="13.5" cy="17" r="3.4" fill="#3a2b31"/><circle cx="26.5" cy="17" r="3.4" fill="#3a2b31"/><path d="M14 26c3 4 9 4 12 0" stroke="#ff8a71" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>
            </span>
            <span class="la-brand-text"><?php echo htmlspecialchars($qs_la_site); ?></span>
            <?php endif; ?>
        </a>
        <nav class="la-nav"><?php qs_menu(); ?></nav>
        <nav class="la-nav-side"><?php qs_sidemenu(); ?></nav>
    </div>
</header>
