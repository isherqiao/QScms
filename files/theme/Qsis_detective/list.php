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
 * @Description  Template of the theme "Qsis_detective": sector archive listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="gd-wrap gd-article">
    <main class="gd-article-main">
        <p class="gd-sector">SECTOR</p>
        <h1 class="gd-article-title"><?php qs_tag_name(); ?></h1>
        <div class="gd-feed-list">
            <?php if (qs_next_post()): ?>
            <?php $qs_gd_i = 0; do { $qs_gd_i++; ?>
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
            <p class="gd-empty">该分区暂无资料。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </main>
</div>
