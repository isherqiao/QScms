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
 * @Description  Template of the theme "Qsis_art": single post / page layout.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="la-wrap la-article">
    <main class="la-article-main">
        <h1 class="la-article-title"><?= qs_the_title() ?></h1>
        <div class="la-article-meta">
            <span><?= qs_the_date() ?></span>
            <?php $qs_la_author = qs_the_user(false); ?>
            <?php if (!empty($qs_la_author) && !qs_is_page()): ?>
            <span><?= qs_the_user() ?></span>
            <?php endif; ?>
        </div>
        <article id="article-content" class="la-article-content">
            <?= qs_the_content() ?>
        </article>
        <?php if (qs_is_post()): ?>
        <div class="la-article-tags"><?= qs_the_tags('', '', ' ') ?></div>
        <?php endif; ?>
        <div class="la-article-actions">
            <a class="la-btn la-btn-line" href="<?php qs_site_link(); ?>">回到首页</a>
        </div>
    </main>
</div>
