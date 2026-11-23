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
 * @File         404.php
 * @Description  Template of the theme "Qsis_art": not found page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include 'header.php';
?>
<div class="la-wrap la-notfound">
    <p class="la-notfound-code">404</p>
    <h2>找不到这一页</h2>
    <p class="la-notfound-text">它可能躲在某个角落，也可能已经不在了。</p>
    <div class="la-hero-actions">
        <a class="la-btn la-btn-solid" href="<?php qs_site_link(); ?>">回到首页</a>
        <a class="la-btn la-btn-line" href="<?php echo htmlspecialchars(rtrim((string)qs_site_link(false), '/') . '/tags/'); ?>">看看关键词</a>
    </div>
</div>
<?php include 'footer.php'; ?>
