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
 * @File         tags.php
 * @Description  Template of the theme "Qsis_corp": tag cloud.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_corp_base = rtrim((string)qs_site_link(false), '/');
?>
<div class="wrap article-layout">
    <main class="article-main">
        <header class="section-head">
            <p class="section-kicker">Topics</p>
            <h2>所有关键词</h2>
        </header>
        <?php if (!empty($all_tags) && is_array($all_tags)): ?>
        <div class="tag-cloud">
            <?php foreach ($all_tags as $qs_corp_tag => $qs_corp_tag_posts): ?>
            <?php
            $qs_corp_safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '_', (string)$qs_corp_tag);
            $qs_corp_href = $qs_corp_base . '/tag/' . rawurlencode($qs_corp_safe) . '/';
            $qs_corp_n    = is_array($qs_corp_tag_posts) ? count($qs_corp_tag_posts) : 0;
            ?>
            <a class="tag-pill" href="<?php echo htmlspecialchars($qs_corp_href); ?>">
                <?php echo htmlspecialchars((string)$qs_corp_tag); ?>
                <span class="tag-count"><?php echo (int)$qs_corp_n; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="feed-empty">暂无关键词。</p>
        <?php endif; ?>
    </main>
</div>
