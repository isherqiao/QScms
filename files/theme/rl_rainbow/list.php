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
 * @File         list.php
 * @Description  Template of the theme "rl_rainbow": tag, date, archive, search listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>

<section class="t-hero">
    <p class="t-hero-eyebrow"><?php
    if (qs_is_tag()) { echo 'TAG'; }
    elseif (qs_is_search()) { echo 'SEARCH'; }
    elseif (qs_is_date()) { echo 'DATE'; }
    else { echo 'ARCHIVE'; }
    ?></p>
    <h1 class="t-hero-title"><?php
    if (qs_is_tag()) { qs_tag_name(); }
    elseif (qs_is_search()) { echo '搜索结果'; }
    elseif (qs_is_date()) { qs_date_name(); }
    else { echo '文章存档'; }
    ?></h1>
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
        </article>
        <?php } while (qs_next_post()); ?>
        <?php else: ?>
        <p class="t-empty">暂无内容。</p>
        <?php endif; ?>
        <?php qs_pagination(); ?>
    </div>

    <aside class="t-side">
        <section>
            <h3>关键词</h3>
            <ul class="t-cloud"><?php qs_tag_cloud('<li>', '', '</li>', 18); ?></ul>
        </section>
        <section>
            <h3>存档</h3>
            <ul class="t-entry">
                <li><a href="<?php echo htmlspecialchars($qs_t_base . '/?archive/'); ?>">按月份浏览</a></li>
                <li><a href="<?php echo htmlspecialchars($qs_t_base . '/?tags/'); ?>">所有关键词</a></li>
            </ul>
        </section>
    </aside>
</div>
