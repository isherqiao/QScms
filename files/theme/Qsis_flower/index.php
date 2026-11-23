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
 * @File         index.php
 * @Description  Template of the theme "Qsis_flower": routing, home page, and includes.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
if (!isset($theme_options) || !is_array($theme_options)) { $theme_options = array(); }

include __DIR__ . '/header.php';

$qs_t_hero_title = !empty($qs_t_o['hero_title']) ? $qs_t_o['hero_title'] : '慢慢开，也慢慢写';
$qs_t_hero_text  = !empty($qs_t_o['hero_text'])  ? $qs_t_o['hero_text']  : '柔和地记录生活里的细节，不急，一朵一朵来。';
$qs_t_hero_eye   = !empty($qs_t_o['hero_eyebrow']) ? $qs_t_o['hero_eyebrow'] : 'SLOW LIVING';
$qs_t_about      = !empty($qs_t_o['side_about']) ? $qs_t_o['side_about'] : $qs_t_desc;

if (qs_is_post() || qs_is_page()) {
    include __DIR__ . '/post.php';
} elseif (qs_is_tags()) {
    include __DIR__ . '/tags.php';
} elseif (qs_is_tag() || qs_is_date() || qs_is_archive() || qs_is_search()) {
    include __DIR__ . '/list.php';
} else {
?>

<section class="t-hero">
    <?php if ($qs_t_hero_eye !== ''): ?>
    <p class="t-hero-eyebrow"><?php echo htmlspecialchars($qs_t_hero_eye); ?></p>
    <?php endif; ?>
    <h1 class="t-hero-title"><?php echo htmlspecialchars($qs_t_hero_title); ?></h1>
    <p class="t-hero-text"><?php echo htmlspecialchars($qs_t_hero_text); ?></p>
</section>

<div class="t-columns">
    <div class="t-main-col">
        <?php if (qs_next_post()): ?>
        <?php do { ?>
        <article class="t-card">
            <h2 class="t-card-title">
                <a href="<?php echo htmlspecialchars((string)qs_post_link(null, false)); ?>"><?= qs_the_title() ?></a>
            </h2>
            <div class="t-meta">
                <span><?= qs_the_date() ?></span>
                <span><?= qs_the_user(false) ?></span>
            </div>
            <div class="t-excerpt"><?= qs_the_content() ?></div>
            <div class="t-card-foot">
                <div class="t-tags"><?= qs_the_tags('<span>', '', '</span>') ?></div>
                <a class="t-more" href="<?php echo htmlspecialchars((string)qs_post_link(null, false)); ?>">阅读全文</a>
            </div>
        </article>
        <?php } while (qs_next_post()); ?>
        <?php else: ?>
        <p class="t-empty">暂无内容。</p>
        <?php endif; ?>
        <?php qs_pagination(); ?>
    </div>

    <aside class="t-side">
        <?php if ($qs_t_about !== ''): ?>
        <section>
            <h3>关于</h3>
            <p><?php echo htmlspecialchars($qs_t_about); ?></p>
        </section>
        <?php endif; ?>
        <section>
            <h3>关键词</h3>
            <ul class="t-cloud"><?php qs_tag_cloud('<li>', '', '</li>', 18); ?></ul>
        </section>
        <section>
            <h3>逛逛</h3>
            <ul class="t-entry">
                <li><a href="<?php echo htmlspecialchars($qs_t_base . '/?tags/'); ?>">所有关键词</a></li>
                <li><a href="<?php echo htmlspecialchars($qs_t_base . '/?archive/'); ?>">文章存档</a></li>
            </ul>
        </section>
    </aside>
</div>

<?php
}

include __DIR__ . '/footer.php';
