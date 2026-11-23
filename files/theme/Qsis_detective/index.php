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
 * @Description  Template of the theme "Qsis_detective": HUD tactical home page.
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

$qs_gd_eyebrow = !empty($qs_gd_o['hero_eyebrow']) ? $qs_gd_o['hero_eyebrow'] : 'CASE FILE / NO.001';
$qs_gd_title   = !empty($qs_gd_o['hero_title']) ? $qs_gd_o['hero_title'] : '真相藏在细节里';
$qs_gd_lead    = !empty($qs_gd_o['hero_lead'])
    ? $qs_gd_o['hero_lead']
    : ($qs_gd_desc !== '' ? mb_substr($qs_gd_desc, 0, 96) : '每件事都有它的逻辑。这里存放已经梳理清楚的观察、推演与结论。');

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

<section class="gd-hero">
    <div class="gd-hero-media" aria-hidden="true"><img src="<?php qs_theme_url('hero.jpg'); ?>" alt=""></div>
    <div class="gd-wrap gd-hero-grid">
        <div class="gd-hero-copy">
            <p class="gd-eyebrow"><span class="gd-blink"></span><?php echo htmlspecialchars($qs_gd_eyebrow); ?></p>
            <h1 class="gd-hero-title"><?php echo htmlspecialchars($qs_gd_title); ?></h1>
            <p class="gd-hero-lead"><?php echo htmlspecialchars($qs_gd_lead); ?></p>
            <div class="gd-hero-actions">
                <a class="gd-btn gd-btn-solid" href="<?php echo htmlspecialchars($qs_gd_link(isset($qs_gd_o['btn1_link']) ? $qs_gd_o['btn1_link'] : '#feed', $qs_gd_base)); ?>"><?php echo htmlspecialchars(!empty($qs_gd_o['btn1_text']) ? $qs_gd_o['btn1_text'] : '进入资料库'); ?></a>
                <a class="gd-btn gd-btn-line" href="<?php echo htmlspecialchars($qs_gd_contact); ?>"><?php echo htmlspecialchars(!empty($qs_gd_o['btn2_text']) ? $qs_gd_o['btn2_text'] : '联系指挥'); ?></a>
            </div>
        </div>
        <div class="gd-hero-stage">
            <figure class="gd-unit">
                <img src="<?php qs_theme_url('image.jpg'); ?>" alt="档案影像">
                <span class="gd-unit-tag">FILE 01</span>
                <span class="gd-unit-id">ARCHIVE</span>
                <i class="gd-unit-c gd-unit-c1" aria-hidden="true"></i>
                <i class="gd-unit-c gd-unit-c2" aria-hidden="true"></i>
                <i class="gd-unit-c gd-unit-c3" aria-hidden="true"></i>
                <i class="gd-unit-c gd-unit-c4" aria-hidden="true"></i>
            </figure>
            <?php if ($qs_gd_hud): ?>
            <aside class="gd-hud">
                <header class="gd-hud-head">
                    <span><?php echo htmlspecialchars(!empty($qs_gd_o['hud_title']) ? $qs_gd_o['hud_title'] : '系统状态'); ?></span>
                    <span class="gd-hud-dot"></span>
                </header>
                <ul class="gd-hud-list">
                    <?php foreach ($qs_gd_hud as $qs_gd_one): ?>
                    <li>
                        <span class="gd-hud-k"><?php echo htmlspecialchars($qs_gd_one[0]); ?></span>
                        <span class="gd-hud-bar"><i></i></span>
                        <span class="gd-hud-v"><?php echo htmlspecialchars($qs_gd_one[1]); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="gd-hud-corner" aria-hidden="true"></div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
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

<section class="gd-feed" id="feed">
    <div class="gd-wrap">
        <header class="gd-feed-head">
            <p class="gd-sector">CASE ARCHIVE</p>
            <h2><?php echo htmlspecialchars(!empty($qs_gd_o['feed_title']) ? $qs_gd_o['feed_title'] : '资料库更新'); ?></h2>
        </header>
        <div class="gd-feed-list">
            <?php if (qs_next_post()): ?>
            <?php $qs_gd_i = 0; do { $qs_gd_i++; if ($qs_gd_i > $qs_gd_feed) break; ?>
            <article class="gd-item">
                <a href="<?php qs_post_link(); ?>">
                    <span class="gd-item-id"><?php echo str_pad((string)$qs_gd_i, 3, '0', STR_PAD_LEFT); ?></span>
                    <div>
                        <h3><?= qs_the_title() ?></h3>
                        <p class="gd-item-date"><?= qs_the_date() ?></p>
                    </div>
                    <span class="gd-item-arrow" aria-hidden="true">→</span>
                </a>
            </article>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <p class="gd-empty">暂无案卷。</p>
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
