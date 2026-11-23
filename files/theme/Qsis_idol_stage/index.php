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
 * @File         index.php
 * @Description  Template of the theme "Qsis_idol_stage": HUD tactical home page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include __DIR__ . '/theme_config.php';

$qs_gd_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_gd_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_gd_base = rtrim((string)qs_site_link(false), '/');

$qs_gd_o = is_array($theme_options) ? $theme_options : array();

$qs_gd_link = function ($link, $base) {
    $link = trim((string)$link);
    if ($link === '') {
        return $base . '/';
    }
    if (preg_match('#^(https?:)?//#i', $link) || $link[0] === '#' || $link[0] === '/') {
        return $link;
    }
    return $base . '/' . ltrim($link, '/');
};

$qs_gd_eyebrow = !empty($qs_gd_o['hero_eyebrow']) ? $qs_gd_o['hero_eyebrow'] : 'IDOL STAGE / LIVE TOUR';
$qs_gd_title   = !empty($qs_gd_o['hero_title']) ? $qs_gd_o['hero_title'] : '月下的预告函';
$qs_gd_lead    = !empty($qs_gd_o['hero_lead'])
    ? $qs_gd_o['hero_lead']
    : ($qs_gd_desc !== '' ? mb_substr($qs_gd_desc, 0, 96) : '城市睡了，屋顶上的白披风才刚展开。这里收藏夜行的观察、预告与留白。');

$qs_gd_hud    = qs_theme_opts_rows(isset($qs_gd_o['hud_items']) ? $qs_gd_o['hud_items'] : '', 2);
$qs_gd_metric = qs_theme_opts_rows(isset($qs_gd_o['metrics']) ? $qs_gd_o['metrics'] : '', 2);
if (count($qs_gd_hud) > 6) {
    $qs_gd_hud = array_slice($qs_gd_hud, 0, 6);
}
if (count($qs_gd_metric) > 4) {
    $qs_gd_metric = array_slice($qs_gd_metric, 0, 4);
}
$qs_gd_feed = isset($qs_gd_o['feed_count']) ? (int)$qs_gd_o['feed_count'] : 8;
if ($qs_gd_feed < 1) {
    $qs_gd_feed = 8;
}
$qs_gd_contact = $qs_gd_link(isset($qs_gd_o['btn2_link']) ? $qs_gd_o['btn2_link'] : '/contact.html', $qs_gd_base);

include 'header.php';

if (qs_is_post() || qs_is_page()) {
    include 'post.php';
} elseif (qs_is_tags()) {
    include 'tags.php';
} elseif (qs_is_tag()) {
    include 'list.php';
} else {
?>

<section class="ph-cover">
    <div class="ph-cover-bg ph-bg-wide" aria-hidden="true"><img src="<?php qs_theme_url('hero.jpg'); ?>" alt=""></div>
    <div class="ph-cover-bg ph-bg-tall" aria-hidden="true"><img src="<?php qs_theme_url('image.jpg'); ?>" alt=""></div>
    <div class="ph-cover-rail" aria-hidden="true"><span>IDOL STAGE</span><span>LIVE TOUR</span></div>
    <div class="gd-wrap ph-cover-inner">
        <p class="ph-kicker"><i class="ph-kicker-dot"></i><?php echo htmlspecialchars($qs_gd_eyebrow); ?></p>
        <h1 class="ph-title"><?php echo htmlspecialchars($qs_gd_title); ?></h1>
        <p class="ph-lead"><?php echo htmlspecialchars($qs_gd_lead); ?></p>
        <div class="ph-actions">
            <a class="ph-btn ph-btn-fill" href="<?php echo htmlspecialchars($qs_gd_link(isset($qs_gd_o['btn1_link']) ? $qs_gd_o['btn1_link'] : '#feed', $qs_gd_base)); ?>"><?php echo htmlspecialchars(!empty($qs_gd_o['btn1_text']) ? $qs_gd_o['btn1_text'] : '看巡演日程'); ?></a>
            <a class="ph-btn ph-btn-ghost" href="<?php echo htmlspecialchars($qs_gd_contact); ?>"><?php echo htmlspecialchars(!empty($qs_gd_o['btn2_text']) ? $qs_gd_o['btn2_text'] : '留下应援'); ?></a>
        </div>
    </div>
    <?php if ($qs_gd_hud): ?>
    <div class="ph-cover-foot">
        <div class="gd-wrap ph-foot-row">
            <?php foreach ($qs_gd_hud as $qs_gd_one): ?>
            <div class="ph-foot-cell">
                <span class="ph-foot-k"><?php echo htmlspecialchars($qs_gd_one[0]); ?></span>
                <span class="ph-foot-v"><?php echo htmlspecialchars($qs_gd_one[1]); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php if ($qs_gd_metric && (!isset($qs_gd_o['metrics_show']) || $qs_gd_o['metrics_show'] === '1')): ?>
<section class="gd-metrics">
    <div class="gd-wrap gd-metrics-row">
        <?php foreach ($qs_gd_metric as $qs_gd_one): ?>
        <div class="gd-metric">
            <span class="gd-metric-num"><?php echo htmlspecialchars($qs_gd_one[0]); ?></span>
            <span class="gd-metric-label"><?php echo htmlspecialchars($qs_gd_one[1]); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="ph-board" id="feed">
    <div class="gd-wrap">
        <header class="ph-board-head">
            <p class="gd-sector">LIVE ARCHIVE</p>
            <h2><?php echo htmlspecialchars(!empty($qs_gd_o['feed_title']) ? $qs_gd_o['feed_title'] : '台下的记录'); ?></h2>
        </header>
        <div class="ph-grid">
            <?php if (qs_next_post()): ?>
            <?php $qs_gd_i = 0; do { $qs_gd_i++; if ($qs_gd_i > $qs_gd_feed) break; ?>
            <article class="ph-tile">
                <a href="<?php qs_post_link(); ?>">
                    <span class="ph-tile-id"><?php echo str_pad((string)$qs_gd_i, 3, '0', STR_PAD_LEFT); ?></span>
                    <h3><?= qs_the_title() ?></h3>
                    <p class="ph-tile-date"><?= qs_the_date() ?></p>
                    <span class="ph-tile-more">阅读 <i aria-hidden="true">→</i></span>
                </a>
            </article>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <p class="gd-empty">这一场还没有留下记录。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </div>
</section>

<?php if (!isset($qs_gd_o['cta_show']) || $qs_gd_o['cta_show'] === '1'): ?>
<section class="gd-cta">
    <div class="gd-wrap gd-cta-inner">
        <div>
            <p class="gd-sector">TESTIMONY</p>
            <h2><?php echo htmlspecialchars(!empty($qs_gd_o['cta_title']) ? $qs_gd_o['cta_title'] : '需要一份可执行的路线图'); ?></h2>
            <p><?php echo htmlspecialchars(!empty($qs_gd_o['cta_lead']) ? $qs_gd_o['cta_lead'] : '把目标、约束和时间窗口说清楚，我们给出可执行的下一步。'); ?></p>
        </div>
        <a class="gd-btn gd-btn-solid" href="<?php echo htmlspecialchars($qs_gd_contact); ?>"><?php echo htmlspecialchars(!empty($qs_gd_o['cta_btn']) ? $qs_gd_o['cta_btn'] : '发起对接'); ?></a>
    </div>
</section>
<?php endif; ?>

<?php
}

include 'footer.php';
