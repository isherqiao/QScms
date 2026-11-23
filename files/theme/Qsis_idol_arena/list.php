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
 * @Description  Template of the theme "Qsis_idol_arena": night archive listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<section class="ph-list">
    <div class="gd-wrap">
        <header class="ph-board-head">
            <p class="gd-sector">ARENA ARCHIVE</p>
            <h1 class="ph-list-title"><?php qs_tag_name(); ?></h1>
        </header>
        <div class="ph-grid">
            <?php if (qs_next_post()): ?>
            <?php $qs_gd_i = 0; do { $qs_gd_i++; ?>
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
            <p class="gd-empty">这一区还没有记录。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </div>
</section>
