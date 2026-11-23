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
 * @Description  Template of the theme "Qsis_corp": single post / page layout.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>
<div class="wrap article-layout">
    <main class="article-main">
        <h1 class="article-title"><?= qs_the_title() ?></h1>
        <div class="article-meta">
            <span><?= qs_the_date() ?></span>
            <?php $qs_corp_author = qs_the_user(false); ?>
            <?php if (!empty($qs_corp_author) && !qs_is_page()): ?>
            <span>作者：<?= qs_the_user() ?></span>
            <?php endif; ?>
        </div>
        <article id="article-content" class="article-content">
            <?= qs_the_content() ?>
        </article>
        <?php if (qs_is_post()): ?>
        <div class="article-tags"><?= qs_the_tags('标签：', '', ' ') ?></div>
        <?php endif; ?>
        <div class="article-actions">
            <a class="btn btn-soft" href="<?php qs_site_link(); ?>/">返回首页</a>
        </div>
    </main>
</div>
