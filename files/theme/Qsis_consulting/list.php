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
 * @Description  Template of the theme "Qsis_corp": tag archive listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="wrap article-layout">
    <main class="article-main">
        <header class="section-head">
            <p class="section-kicker">Archive</p>
            <h2><?php qs_tag_name(); ?></h2>
        </header>
        <ul class="feed-list">
            <?php if (qs_next_post()): ?>
            <?php do { ?>
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
    </main>
</div>
