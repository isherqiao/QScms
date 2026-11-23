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
 * @File         post.php
 * @Description  Template of the theme "Qsis_idol_arena": single post / page layout.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="gd-wrap gd-article">
    <main class="gd-article-main">
        <p class="gd-sector">RECORD</p>
        <h1 class="gd-article-title"><?= qs_the_title() ?></h1>
        <div class="gd-article-meta">
            <span><?= qs_the_date() ?></span>
            <?php $qs_gd_author = qs_the_user(false); ?>
            <?php if (!empty($qs_gd_author) && !qs_is_page()): ?>
            <span><?= qs_the_user() ?></span>
            <?php endif; ?>
        </div>
        <article id="article-content" class="gd-article-content">
            <?= qs_the_content() ?>
        </article>
        <?php if (qs_is_post()): ?>
        <div class="gd-article-tags"><?= qs_the_tags('', '', ' ') ?></div>
        <?php endif; ?>
        <div class="gd-article-actions">
            <a class="gd-btn gd-btn-line" href="<?php qs_site_link(); ?>">返回资料库</a>
        </div>
    </main>
</div>
