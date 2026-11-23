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
 * @Description  Not found page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include 'header.php';
?>
<div class="wrap notfound">
    <p class="notfound-code">404</p>
    <h2>页面走丢了</h2>
    <p class="notfound-text">这个地址已经没有内容，或者链接被改动过。</p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="<?php qs_site_link(); ?>/">回到首页</a>
        <a class="btn btn-ghost" href="<?php echo htmlspecialchars(rtrim((string)qs_site_link(false), '/') . '/tags/'); ?>">浏览关键词</a>
    </div>
</div>
<?php include 'footer.php'; ?>