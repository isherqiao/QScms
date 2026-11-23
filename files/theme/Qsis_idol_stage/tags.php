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
 * @Description  Template of the theme "Qsis_idol_stage": sector index.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_gd_base = rtrim((string)qs_site_link(false), '/');
?>
<div class="gd-wrap gd-article">
    <main class="gd-article-main">
        <p class="gd-sector">LIVE INDEX</p>
        <h1 class="gd-article-title">全部分区</h1>
        <?php if (!empty($all_tags) && is_array($all_tags)): ?>
        <div class="gd-tags">
            <?php foreach ($all_tags as $qs_gd_tag => $qs_gd_tag_posts): ?>
            <?php
            $qs_gd_safe = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '_', (string)$qs_gd_tag);
            $qs_gd_href = $qs_gd_base . '/tag/' . rawurlencode($qs_gd_safe) . '/';
            $qs_gd_num  = is_array($qs_gd_tag_posts) ? count($qs_gd_tag_posts) : 0;
            ?>
            <a class="gd-tag" href="<?php echo htmlspecialchars($qs_gd_href); ?>">
                <span><?php echo htmlspecialchars((string)$qs_gd_tag); ?></span>
                <em><?php echo (int)$qs_gd_num; ?></em>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="gd-empty">暂无分区。</p>
        <?php endif; ?>
    </main>
</div>
