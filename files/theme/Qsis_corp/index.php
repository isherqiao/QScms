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
 * @Description  Template of the theme "Qsis_corp": corporate home page without category block cards.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include __DIR__ . '/theme_config.php';

$qs_corp_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_corp_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_corp_base = rtrim((string)qs_site_link(false), '/');

$qs_corp_o = is_array($theme_options) ? $theme_options : array();

$qs_corp_link = function ($link, $base) {
    $link = trim((string)$link);
    if ($link === '') {
        return $base . '/';
    }
    if (preg_match('#^(https?:)?//#i', $link) || $link[0] === '#' || $link[0] === '/') {
        return $link;
    }
    return $base . '/' . ltrim($link, '/');
};

$qs_corp_eyebrow = !empty($qs_corp_o['hero_eyebrow']) ? $qs_corp_o['hero_eyebrow'] : $qs_corp_site;
$qs_corp_title   = !empty($qs_corp_o['hero_title']) ? $qs_corp_o['hero_title'] : '把复杂的事，做成可靠的结果';
$qs_corp_lead    = !empty($qs_corp_o['hero_lead'])
    ? $qs_corp_o['hero_lead']
    : ($qs_corp_desc !== '' ? mb_substr($qs_corp_desc, 0, 96) : '从需求梳理到交付落地，我们把每一次合作当作长期伙伴关系的起点。');

$qs_corp_services = qs_theme_opts_rows(isset($qs_corp_o['services']) ? $qs_corp_o['services'] : '', 3);
$qs_corp_metrics  = qs_theme_opts_rows(isset($qs_corp_o['metrics']) ? $qs_corp_o['metrics'] : '', 2);
if (count($qs_corp_services) > 6) {
    $qs_corp_services = array_slice($qs_corp_services, 0, 6);
}
if (count($qs_corp_metrics) > 4) {
    $qs_corp_metrics = array_slice($qs_corp_metrics, 0, 4);
}
$qs_corp_feed = isset($qs_corp_o['feed_count']) ? (int)$qs_corp_o['feed_count'] : 6;
if ($qs_corp_feed < 1) {
    $qs_corp_feed = 6;
}
$qs_corp_contact = $qs_corp_link(isset($qs_corp_o['hero_btn1_link']) ? $qs_corp_o['hero_btn1_link'] : '/contact.html', $qs_corp_base);

include 'header.php';

if (qs_is_post() || qs_is_page()) {
    include 'post.php';
} elseif (qs_is_tags()) {
    include 'tags.php';
} elseif (qs_is_tag()) {
    include 'list.php';
} else {
?>

<section class="hero">
    <div class="wrap">
        <p class="hero-eyebrow"><?php echo htmlspecialchars($qs_corp_eyebrow); ?></p>
        <h1 class="hero-title"><?php echo htmlspecialchars($qs_corp_title); ?></h1>
        <p class="hero-lead"><?php echo htmlspecialchars($qs_corp_lead); ?></p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?php echo htmlspecialchars($qs_corp_contact); ?>"><?php echo htmlspecialchars(!empty($qs_corp_o['hero_btn1_text']) ? $qs_corp_o['hero_btn1_text'] : '联系我们'); ?></a>
            <a class="btn btn-ghost" href="<?php echo htmlspecialchars($qs_corp_link(isset($qs_corp_o['hero_btn2_link']) ? $qs_corp_o['hero_btn2_link'] : '#services', $qs_corp_base)); ?>"><?php echo htmlspecialchars(!empty($qs_corp_o['hero_btn2_text']) ? $qs_corp_o['hero_btn2_text'] : '了解能力'); ?></a>
        </div>
    </div>
</section>

<?php if ($qs_corp_services): ?>
<section class="section" id="services">
    <div class="wrap">
        <header class="section-head">
            <p class="section-kicker">Capabilities</p>
            <h2><?php echo htmlspecialchars(!empty($qs_corp_o['services_title']) ? $qs_corp_o['services_title'] : '我们提供的核心能力'); ?></h2>
        </header>
        <div class="service-grid">
            <?php foreach ($qs_corp_services as $qs_corp_one): ?>
            <article class="service-card">
                <?php if ($qs_corp_one[0] !== ''): ?>
                <div class="service-no"><?php echo htmlspecialchars($qs_corp_one[0]); ?></div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($qs_corp_one[1]); ?></h3>
                <p><?php echo htmlspecialchars($qs_corp_one[2]); ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($qs_corp_metrics && (!isset($qs_corp_o['metrics_show']) || $qs_corp_o['metrics_show'] === '1')): ?>
<section class="metrics">
    <div class="wrap metrics-row">
        <?php foreach ($qs_corp_metrics as $qs_corp_one): ?>
        <div class="metric">
            <span class="metric-num"><?php echo htmlspecialchars($qs_corp_one[0]); ?></span>
            <span class="metric-label"><?php echo htmlspecialchars($qs_corp_one[1]); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="wrap">
        <header class="section-head">
            <p class="section-kicker">Updates</p>
            <h2><?php echo htmlspecialchars(!empty($qs_corp_o['feed_title']) ? $qs_corp_o['feed_title'] : '最新动态'); ?></h2>
        </header>
        <ul class="feed-list">
            <?php if (qs_next_post()): ?>
            <?php $qs_corp_i = 0; do { $qs_corp_i++; if ($qs_corp_i > $qs_corp_feed) break; ?>
            <li class="feed-item">
                <a class="feed-link" href="<?php qs_post_link(); ?>">
                    <span class="feed-title"><?= qs_the_title() ?></span>
                    <span class="feed-date"><?= qs_the_date() ?></span>
                </a>
            </li>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <li class="feed-empty">暂无内容。</li>
            <?php endif; ?>
        </ul>
        <?php qs_pagination(); ?>
    </div>
</section>

<?php if (!isset($qs_corp_o['cta_show']) || $qs_corp_o['cta_show'] === '1'): ?>
<section class="cta">
    <div class="wrap cta-inner">
        <div>
            <h2><?php echo htmlspecialchars(!empty($qs_corp_o['cta_title']) ? $qs_corp_o['cta_title'] : '准备好开始一次合作了吗'); ?></h2>
            <p><?php echo htmlspecialchars(!empty($qs_corp_o['cta_lead']) ? $qs_corp_o['cta_lead'] : '把您的需求告诉我们，我们会在一个工作日内给出初步判断。'); ?></p>
        </div>
        <a class="btn btn-light" href="<?php echo htmlspecialchars($qs_corp_contact); ?>"><?php echo htmlspecialchars(!empty($qs_corp_o['cta_btn']) ? $qs_corp_o['cta_btn'] : '发起咨询'); ?></a>
    </div>
</section>
<?php endif; ?>

<?php
}

include 'footer.php';
