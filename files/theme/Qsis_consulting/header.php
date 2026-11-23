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
 * @Description  Template of the theme "Qsis_consulting": minimal editorial header.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_cs_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_cs_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_cs_logo = isset($qs_config['sitelogo']) ? (string)$qs_config['sitelogo'] : '';

if (qs_is_post()) {
    $qs_cs_canonical = qs_post_link(null, false);
} else {
    $qs_cs_canonical = rtrim((string)qs_site_link(false), '/') . (string)($_SERVER['REQUEST_URI'] ?? '/');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' | ' . $qs_cs_site;
} elseif (qs_is_tag()) {
    qs_tag_name();
    echo ' | ' . $qs_cs_site;
} else {
    echo $qs_cs_site;
}
?></title>
<meta name="renderer" content="webkit">
<meta name="format-detection" content="telephone=no, email=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_cs_canonical); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_cs_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_cs_site); ?>">
<?php if (qs_is_post()): ?>
<meta name="description" content="<?php echo htmlspecialchars(qs_the_summary(90)); ?>">
<meta property="og:type" content="article">
<?php else: ?>
<meta name="description" content="<?php echo htmlspecialchars($qs_cs_desc); ?>">
<meta property="og:type" content="website">
<?php endif; ?>
<meta name="application-name" content="<?php echo htmlspecialchars($qs_cs_site); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars($qs_cs_site); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<?= isset($qs_config['ico']) ? qs_google_icon() : '' ?>
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
if (!empty($theme_options) && is_array($theme_options) && function_exists('qs_theme_opts_css')) {
    $qs_cs_vars = array();
    if (!empty($theme_options['color_accent'])) {
        $qs_cs_vars['cs-accent'] = $theme_options['color_accent'];
    }
    if (!empty($theme_options['color_ink'])) {
        $qs_cs_vars['cs-ink'] = $theme_options['color_ink'];
    }
    $qs_cs_sb = isset($theme_options['custom_scrollbar']) && $theme_options['custom_scrollbar'] === '1';
    $qs_cs_css = qs_theme_opts_css($qs_cs_vars, $qs_cs_sb);
    if ($qs_cs_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_cs_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="qs-cs">

<header class="cs-header">
    <div class="wrap cs-header-inner">
        <a class="cs-brand" href="<?php qs_site_link(); ?>">
            <?php if ($qs_cs_logo !== ''): ?>
            <img src="<?php echo htmlspecialchars(qs_site_link(false) . $qs_cs_logo); ?>" alt="<?php echo htmlspecialchars($qs_cs_site); ?>">
            <?php else: ?>
            <?php echo htmlspecialchars($qs_cs_site); ?>
            <?php endif; ?>
        </a>
        <nav class="cs-nav"><?php qs_menu(); ?></nav>
    </div>
</header>
