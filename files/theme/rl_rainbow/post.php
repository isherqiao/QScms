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
 * @File         post.php
 * @Description  Template of the theme "rl_rainbow": single post / page view.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>

<div class="t-columns">
    <div class="t-main-col">
        <article class="t-post">
            <header class="t-post-head">
                <h1 class="t-post-title"><?php
                if (qs_is_page()) { qs_page_title(); } else { ?><?= qs_the_title() ?><?php  }
                ?></h1>
                <?php if (qs_is_post()): ?>
                <div class="t-meta">
                    <span><?= qs_the_date() ?></span>
                    <span><?= qs_the_user(false) ?></span>
                </div>
                <?php endif; ?>
            </header>
            <div class="t-post-body"><?= qs_the_content() ?></div>
            <?php if (qs_is_post()): ?>
            <footer class="t-post-foot">
                <div class="t-tags"><?= qs_the_tags('<span>', '', '</span>') ?></div>
            </footer>
            <?php endif; ?>
        </article>

        <?php if (qs_is_post() && function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')): ?>
        <section class="t-comments"><?php qs_comment_code(); ?></section>
        <?php endif; ?>
    </div>

    <aside class="t-side">
        <?php if (!empty($qs_t_about)): ?>
        <section>
            <h3>关于</h3>
            <p><?php echo htmlspecialchars($qs_t_about); ?></p>
        </section>
        <?php endif; ?>
        <section>
            <h3>关键词</h3>
            <ul class="t-cloud"><?php qs_tag_cloud('<li>', '', '</li>', 18); ?></ul>
        </section>
    </aside>
</div>
