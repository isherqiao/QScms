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
 * @File         list.php
 * @Description  Template of the theme "Qsis_art": keyword archive listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="la-wrap la-article">
    <main class="la-article-main">
        <p class="la-article-kicker">关键词</p>
        <h1 class="la-article-title"><?php qs_tag_name(); ?></h1>
        <div class="la-feed-grid">
            <?php if (qs_next_post()): ?>
            <?php do { ?>
            <article class="la-post-card">
                <a href="<?php qs_post_link(); ?>">
                    <p class="la-post-date"><?= qs_the_date() ?></p>
                    <h3 class="la-post-title"><?= qs_the_title() ?></h3>
                    <span class="la-post-more">继续读</span>
                </a>
            </article>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <p class="la-empty">这个关键词下还没有内容。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </main>
</div>
