<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/**
 * QsCms 项目文件
 *
 * @File         header.php
 * @Description  Template of the theme "rl_rainbow": page head and top navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_t_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_t_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_t_o    = isset($theme_options) && is_array($theme_options) ? $theme_options : array();
$qs_t_base = rtrim((string)qs_site_link(false), '/');
$qs_t_year = date('Y');

if (qs_is_post() || qs_is_page()) {
    $qs_t_canonical = (string)qs_post_link(null, false);
} else {
    $qs_t_canonical = $qs_t_base . (string)($_SERVER['REQUEST_URI'] ?? '/');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?php
if (qs_is_post() || qs_is_page()) {
    ?><?= qs_the_title() ?><?php 
    echo ' | ' . $qs_t_site;
} elseif (qs_is_tag()) {
    qs_tag_name();
    echo ' | ' . $qs_t_site;
} else {
    echo $qs_t_site;
}
?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="format-detection" content="telephone=no, email=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="renderer" content="webkit">
<link rel="canonical" href="<?php echo htmlspecialchars($qs_t_canonical); ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($qs_t_site); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($qs_t_canonical); ?>">
<?php if (qs_is_post()): ?>
<meta property="og:type" content="article">
<?php else: ?>
<meta property="og:type" content="website">
<?php endif; ?>
<meta name="description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($qs_t_desc), 0, 120)); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<?php echo isset($qs_config['head_code']) ? $qs_config['head_code'] : ''; ?>
<link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>" />
<?php
$qs_t_vars = array();
if (!empty($qs_t_o['color_brand']))  { $qs_t_vars['brand']  = $qs_t_o['color_brand']; }
if (!empty($qs_t_o['color_accent'])) { $qs_t_vars['accent'] = $qs_t_o['color_accent']; }
$qs_t_sb = isset($qs_t_o['custom_scrollbar']) && $qs_t_o['custom_scrollbar'] === '1';
if (function_exists('qs_theme_opts_css')) {
    $qs_t_css = qs_theme_opts_css($qs_t_vars, $qs_t_sb, 'var(--bg)', 'var(--brand)');
    if ($qs_t_css !== '') {
        echo '<style id="qs-theme-opts">' . $qs_t_css . '</style>' . "\n";
    }
}
?>
</head>
<body class="t-body">

<header class="t-top">
    <div class="t-wrap t-top-inner">
        <a class="t-brand" href="<?php qs_site_link(); ?>"><?php echo htmlspecialchars($qs_t_site); ?></a>
        <nav class="t-nav"><?php qs_menu(); ?></nav>
    </div>
</header>

<div class="t-wrap t-shell">
