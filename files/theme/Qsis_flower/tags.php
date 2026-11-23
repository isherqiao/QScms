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
 * @File         tags.php
 * @Description  Template of the theme "Qsis_flower": tag cloud.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
$qs_t_all = isset($qs_all_tags) && is_array($qs_all_tags) ? $qs_all_tags : array();
?>

<section class="t-hero">
    <p class="t-hero-eyebrow">TOPICS</p>
    <h1 class="t-hero-title">所有关键词</h1>
    <p class="t-hero-text">共 <?php echo count($qs_t_all); ?> 个关键词。</p>
</section>

<div class="t-cloud-big">
    <?php if ($qs_t_all): ?>
    <?php foreach ($qs_t_all as $qs_t_tag => $qs_t_posts): ?>
    <?php
    $qs_t_safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '_', (string)$qs_t_tag);
    $qs_t_href = $qs_t_base . '/tag/' . rawurlencode($qs_t_safe) . '/';
    $qs_t_n    = is_array($qs_t_posts) ? count($qs_t_posts) : 0;
    ?>
    <a class="t-pill" href="<?php echo htmlspecialchars($qs_t_href); ?>">
        <?php echo htmlspecialchars((string)$qs_t_tag); ?>
        <em><?php echo (int)$qs_t_n; ?></em>
    </a>
    <?php endforeach; ?>
    <?php else: ?>
    <p class="t-empty">暂无关键词。</p>
    <?php endif; ?>
</div>
