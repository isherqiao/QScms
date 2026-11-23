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
 * @Description  Template of the theme "Qsis_consulting": editorial two-column home page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include __DIR__ . '/theme_config.php';

$qs_cs_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_cs_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_cs_base = rtrim((string)qs_site_link(false), '/');

$qs_cs_o = is_array($theme_options) ? $theme_options : array();

$qs_cs_link = function ($link, $base) {
    $link = trim((string)$link);
    if ($link === '') {
        return $base . '/';
    }
    if (preg_match('#^(https?:)?//#i', $link) || $link[0] === '#' || $link[0] === '/') {
        return $link;
    }
    return $base . '/' . ltrim($link, '/');
};

$qs_cs_title = !empty($qs_cs_o['intro_title']) ? $qs_cs_o['intro_title'] : '把判断做对，再把事情做完';
$qs_cs_text  = !empty($qs_cs_o['intro_text'])
    ? $qs_cs_o['intro_text']
    : ($qs_cs_desc !== '' ? mb_substr($qs_cs_desc, 0, 96) : '我们不做一次性交付，而是陪你把一件事从判断、到落地、再到稳定运转走完。先谈清楚边界，再谈合作方式。');

$qs_cs_items = qs_theme_opts_rows(isset($qs_cs_o['service_items']) ? $qs_cs_o['service_items'] : '', 2);
if (count($qs_cs_items) > 5) {
    $qs_cs_items = array_slice($qs_cs_items, 0, 5);
}
$qs_cs_feed = isset($qs_cs_o['feed_count']) ? (int)$qs_cs_o['feed_count'] : 7;
if ($qs_cs_feed < 1) {
    $qs_cs_feed = 7;
}
$qs_cs_intro_img = isset($qs_cs_o['intro_image']) ? trim((string)$qs_cs_o['intro_image']) : '';
if ($qs_cs_intro_img === '') {
    $qs_cs_intro_img = 'intro.jpg';
}
$qs_cs_intro_cap = isset($qs_cs_o['intro_image_caption']) ? trim((string)$qs_cs_o['intro_image_caption']) : '';

include 'header.php';

if (qs_is_post() || qs_is_page()) {
    include 'post.php';
} elseif (qs_is_tags()) {
    include 'tags.php';
} elseif (qs_is_tag()) {
    include 'list.php';
} else {
?>

<section class="cs-intro">
    <div class="wrap cs-intro-grid">
        <div class="cs-intro-copy">
            <p class="cs-intro-label"><?php echo htmlspecialchars(!empty($qs_cs_o['intro_label']) ? $qs_cs_o['intro_label'] : '独立顾问 · 长期合作'); ?></p>
            <h1 class="cs-intro-title"><?php echo htmlspecialchars($qs_cs_title); ?></h1>
            <p class="cs-intro-text"><?php echo htmlspecialchars($qs_cs_text); ?></p>
            <div class="cs-intro-actions">
                <a class="cs-link-strong" href="<?php echo htmlspecialchars($qs_cs_link(isset($qs_cs_o['link1_href']) ? $qs_cs_o['link1_href'] : '/contact.html', $qs_cs_base)); ?>"><?php echo htmlspecialchars(!empty($qs_cs_o['link1_text']) ? $qs_cs_o['link1_text'] : '预约一次沟通'); ?></a>
                <a class="cs-link-soft" href="<?php echo htmlspecialchars($qs_cs_link(isset($qs_cs_o['link2_href']) ? $qs_cs_o['link2_href'] : '#practice', $qs_cs_base)); ?>"><?php echo htmlspecialchars(!empty($qs_cs_o['link2_text']) ? $qs_cs_o['link2_text'] : '看看我们做过什么'); ?></a>
            </div>
        </div>
        <figure class="cs-intro-figure">
            <img src="<?php echo htmlspecialchars(qs_theme_url($qs_cs_intro_img, false)); ?>" alt="">
            <?php if ($qs_cs_intro_cap !== ''): ?>
            <figcaption><?php echo htmlspecialchars($qs_cs_intro_cap); ?></figcaption>
            <?php endif; ?>
        </figure>
    </div>
    <div class="wrap cs-intro-rule"></div>
</section>

<?php if ($qs_cs_items): ?>
<section class="cs-practice" id="practice">
    <div class="wrap cs-practice-grid">
        <div class="cs-practice-side">
            <p class="cs-side-kicker">Practice</p>
            <h2><?php echo htmlspecialchars(!empty($qs_cs_o['practice_title']) ? $qs_cs_o['practice_title'] : '我们在做的三件事'); ?></h2>
            <p class="cs-side-note"><?php echo htmlspecialchars(!empty($qs_cs_o['practice_note']) ? $qs_cs_o['practice_note'] : '不是所有问题都需要推倒重来。多数时候，先把判断做对，比加法更重要。'); ?></p>
        </div>
        <div class="cs-practice-main">
            <?php foreach ($qs_cs_items as $qs_cs_one): ?>
            <article class="cs-item">
                <h3><?php echo htmlspecialchars($qs_cs_one[0]); ?></h3>
                <p><?php echo htmlspecialchars($qs_cs_one[1]); ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cs-notes">
    <div class="wrap">
        <div class="cs-notes-head">
            <p class="cs-side-kicker">Notes</p>
            <h2><?php echo htmlspecialchars(!empty($qs_cs_o['notes_title']) ? $qs_cs_o['notes_title'] : '最近写下的内容'); ?></h2>
        </div>
        <ul class="cs-notes-list">
            <?php if (qs_next_post()): ?>
            <?php $qs_cs_i = 0; do { $qs_cs_i++; if ($qs_cs_i > $qs_cs_feed) break; ?>
            <li class="cs-note">
                <a class="cs-note-link" href="<?php qs_post_link(); ?>">
                    <span class="cs-note-title"><?= qs_the_title() ?></span>
                    <span class="cs-note-date"><?= qs_the_date() ?></span>
                </a>
            </li>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <li class="cs-notes-empty">暂无内容。</li>
            <?php endif; ?>
        </ul>
        <?php qs_pagination(); ?>
    </div>
</section>

<?php if (!isset($qs_cs_o['quote_show']) || $qs_cs_o['quote_show'] === '1'): ?>
<section class="cs-quote">
    <div class="wrap">
        <blockquote><?php echo htmlspecialchars(!empty($qs_cs_o['quote_text']) ? $qs_cs_o['quote_text'] : '把不确定留给自己，把确定交给你。'); ?></blockquote>
        <p class="cs-quote-sign">—— <?php echo htmlspecialchars($qs_cs_site); ?></p>
    </div>
</section>
<?php endif; ?>

<?php
}

include 'footer.php';
