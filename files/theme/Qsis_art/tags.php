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
 * @Description  Template of the theme "Qsis_art": tag cloud.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_la_base = rtrim((string)qs_site_link(false), '/');
?>
<div class="la-wrap la-article">
    <main class="la-article-main">
        <p class="la-article-kicker">全部关键词</p>
        <h1 class="la-article-title">按关键词找找看</h1>
        <?php if (!empty($all_tags) && is_array($all_tags)): ?>
        <div class="la-tags">
            <?php foreach ($all_tags as $qs_la_tag => $qs_la_tag_posts): ?>
            <?php
            $qs_la_safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '_', (string)$qs_la_tag);
            $qs_la_href = $qs_la_base . '/tag/' . rawurlencode($qs_la_safe) . '/';
            $qs_la_num  = is_array($qs_la_tag_posts) ? count($qs_la_tag_posts) : 0;
            ?>
            <a class="la-tag" href="<?php echo htmlspecialchars($qs_la_href); ?>"><?php echo htmlspecialchars((string)$qs_la_tag); ?><em><?php echo (int)$qs_la_num; ?></em></a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="la-empty">暂无关键词。</p>
        <?php endif; ?>
    </main>
</div>
