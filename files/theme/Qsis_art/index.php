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
 * @Description  Template of the theme "Qsis_art": illustrated split home page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include __DIR__ . '/theme_config.php';

$qs_la_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_la_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_la_base = rtrim((string)qs_site_link(false), '/');

$qs_la_o = is_array($theme_options) ? $theme_options : array();

$qs_la_link = function ($link, $base) {
    $link = trim((string)$link);
    if ($link === '') {
        return $base . '/';
    }
    if (preg_match('#^(https?:)?//#i', $link) || $link[0] === '#' || $link[0] === '/') {
        return $link;
    }
    return $base . '/' . ltrim($link, '/');
};

$qs_la_eyebrow = !empty($qs_la_o['hero_eyebrow']) ? $qs_la_o['hero_eyebrow'] : $qs_la_site;
$qs_la_title   = !empty($qs_la_o['hero_title']) ? $qs_la_o['hero_title'] : '慢慢来，也能走到很远';
$qs_la_lead    = !empty($qs_la_o['hero_lead'])
    ? $qs_la_o['hero_lead']
    : ($qs_la_desc !== '' ? mb_substr($qs_la_desc, 0, 96) : '一只毛茸茸的小家伙守着这个页面。这里的东西不宏大，但都是认真写下的。');

$qs_la_cards = qs_theme_opts_rows(isset($qs_la_o['cards']) ? $qs_la_o['cards'] : '', 2);
if (count($qs_la_cards) > 6) {
    $qs_la_cards = array_slice($qs_la_cards, 0, 6);
}
$qs_la_feed = isset($qs_la_o['feed_count']) ? (int)$qs_la_o['feed_count'] : 6;
if ($qs_la_feed < 1) {
    $qs_la_feed = 6;
}
$qs_la_contact = $qs_la_link(isset($qs_la_o['btn2_link']) ? $qs_la_o['btn2_link'] : '/contact.html', $qs_la_base);

include 'header.php';

if (qs_is_post() || qs_is_page()) {
    include 'post.php';
} elseif (qs_is_tags()) {
    include 'tags.php';
} elseif (qs_is_tag()) {
    include 'list.php';
} else {
?>

<section class="la-hero">
    <div class="la-wrap la-hero-grid">
        <div class="la-hero-copy">
            <p class="la-eyebrow"><?php echo htmlspecialchars($qs_la_eyebrow); ?></p>
            <h1 class="la-hero-title"><?php echo htmlspecialchars($qs_la_title); ?></h1>
            <p class="la-hero-lead"><?php echo htmlspecialchars($qs_la_lead); ?></p>
            <div class="la-hero-actions">
                <a class="la-btn la-btn-solid" href="<?php echo htmlspecialchars($qs_la_link(isset($qs_la_o['btn1_link']) ? $qs_la_o['btn1_link'] : '#feed', $qs_la_base)); ?>"><?php echo htmlspecialchars(!empty($qs_la_o['btn1_text']) ? $qs_la_o['btn1_text'] : '从这里开始'); ?></a>
                <a class="la-btn la-btn-line" href="<?php echo htmlspecialchars($qs_la_contact); ?>"><?php echo htmlspecialchars(!empty($qs_la_o['btn2_text']) ? $qs_la_o['btn2_text'] : '关于这里'); ?></a>
            </div>
        </div>
        <div class="la-hero-art">
            <div class="la-hero-blob" aria-hidden="true"></div>
            <img src="<?php qs_theme_url('hero.jpg'); ?>" alt="">
        </div>
    </div>
</section>

<?php if ($qs_la_cards): ?>
<section class="la-cards">
    <div class="la-wrap">
        <h2 class="la-cards-title"><?php echo htmlspecialchars(!empty($qs_la_o['cards_title']) ? $qs_la_o['cards_title'] : '这里有什么'); ?></h2>
        <div class="la-cards-grid">
            <?php foreach ($qs_la_cards as $qs_la_one): ?>
            <article class="la-card">
                <h3><?php echo htmlspecialchars($qs_la_one[0]); ?></h3>
                <p><?php echo htmlspecialchars($qs_la_one[1]); ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="la-feed" id="feed">
    <div class="la-wrap">
        <header class="la-feed-head">
            <h2><?php echo htmlspecialchars(!empty($qs_la_o['feed_title']) ? $qs_la_o['feed_title'] : '最近写下的'); ?></h2>
            <span>共 <?php echo (int)$qs_post_count; ?> 篇</span>
        </header>
        <div class="la-feed-grid">
            <?php if (qs_next_post()): ?>
            <?php $qs_la_i = 0; do { $qs_la_i++; if ($qs_la_i > $qs_la_feed) break; ?>
            <article class="la-post-card">
                <a href="<?php qs_post_link(); ?>">
                    <p class="la-post-date"><?= qs_the_date() ?></p>
                    <h3 class="la-post-title"><?= qs_the_title() ?></h3>
                    <span class="la-post-more">继续读</span>
                </a>
            </article>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <p class="la-empty">还没有内容，先发布一篇吧。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </div>
</section>

<?php if (!isset($qs_la_o['cta_show']) || $qs_la_o['cta_show'] === '1'): ?>
<section class="la-cta">
    <div class="la-wrap la-cta-inner">
        <div>
            <h2><?php echo htmlspecialchars(!empty($qs_la_o['cta_title']) ? $qs_la_o['cta_title'] : '想第一时间看到更新'); ?></h2>
            <p><?php echo htmlspecialchars(!empty($qs_la_o['cta_lead']) ? $qs_la_o['cta_lead'] : '收藏这个页面，或者直接留个言，都会看到。'); ?></p>
        </div>
        <a class="la-btn la-btn-solid" href="<?php echo htmlspecialchars($qs_la_contact); ?>"><?php echo htmlspecialchars(!empty($qs_la_o['cta_btn']) ? $qs_la_o['cta_btn'] : '留个言'); ?></a>
    </div>
</section>
<?php endif; ?>

<?php
}

include 'footer.php';
