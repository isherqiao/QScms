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
 * @Description  Template of the theme "Qsis_business": document head and single-row corporate header.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_biz_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_biz_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_biz_logo = isset($qs_config['sitelogo']) ? (string)$qs_config['sitelogo'] : '';

$qs_biz_canonical = '';
if (qs_is_post()) {
    $qs_biz_canonical = qs_post_link(null, false);
} else {
    $qs_biz_base = rtrim((string)qs_site_link(false), '/');
    $qs_biz_req  = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $qs_biz_canonical = $qs_biz_base . ($qs_biz_req === '' ? '/' : $qs_biz_req);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' - ' . $qs_biz_site;
} elseif (qs_is_tag()) {
    echo '专题：';
    qs_tag_name();
    echo ' - ' . $qs_biz_site;
} else {
    echo $qs_biz_site;
}
?></title>
<meta name="renderer" content="webkit">
<meta name="format-detection" content="telephone=no, email=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_biz_canonical); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_biz_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_biz_site); ?>">
<?php if (qs_is_post()): ?>
<meta name="description" content="<?php echo htmlspecialchars(qs_the_summary(90)); ?>">
<meta property="og:type" content="article">
<?php else: ?>
<meta name="description" content="<?php echo htmlspecialchars($qs_biz_desc); ?>">
<meta property="og:type" content="website">
<?php endif; ?>
<meta name="application-name" content="<?php echo htmlspecialchars($qs_biz_site); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars($qs_biz_site); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<?= isset($qs_config['ico']) ? qs_google_icon() : '' ?>
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
if (!empty($theme_options) && is_array($theme_options) && function_exists('qs_theme_opts_css')) {
    $qs_biz_vars = array();
    if (!empty($theme_options['color_brand'])) {
        $qs_biz_vars['biz-green'] = $theme_options['color_brand'];
    }
    if (!empty($theme_options['color_accent'])) {
        $qs_biz_vars['biz-brass'] = $theme_options['color_accent'];
    }
    $qs_biz_sb = isset($theme_options['custom_scrollbar']) && $theme_options['custom_scrollbar'] === '1';
    $qs_biz_css = qs_theme_opts_css($qs_biz_vars, $qs_biz_sb);
    if ($qs_biz_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_biz_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="qs-biz">

<header class="biz-header">
    <div class="wrap biz-header-inner">
        <a class="biz-brand" href="<?php qs_site_link(); ?>" title="<?php echo htmlspecialchars($qs_biz_site); ?>">
            <?php if ($qs_biz_logo !== ''): ?>
            <img src="<?php echo htmlspecialchars(qs_site_link(false) . $qs_biz_logo); ?>" alt="<?php echo htmlspecialchars($qs_biz_site); ?>">
            <?php else: ?>
            <span class="biz-brand-text"><?php echo htmlspecialchars($qs_biz_site); ?></span>
            <?php endif; ?>
        </a>
        <nav class="biz-nav"><?php qs_menu(); ?></nav>
        <div class="biz-contact">
            <a class="biz-cta" href="<?php echo htmlspecialchars(rtrim((string)qs_site_link(false), '/') . '/contact.html'); ?>">联系我们</a>
        </div>
    </div>
</header>
