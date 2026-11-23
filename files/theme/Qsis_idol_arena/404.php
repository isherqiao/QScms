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
 * @Description  Template of the theme "Qsis_idol_arena": not found page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include 'header.php';
?>
<div class="gd-wrap gd-notfound">
    <p class="gd-notfound-code">404</p>
    <h2>SIGNAL LOST</h2>
    <p class="gd-notfound-text">该坐标没有可用资料，可能已被转移或未部署。</p>
    <div class="gd-hero-actions">
        <a class="gd-btn gd-btn-solid" href="<?php qs_site_link(); ?>">返回主界面</a>
        <a class="gd-btn gd-btn-line" href="<?php echo htmlspecialchars(rtrim((string)qs_site_link(false), '/') . '/tags/'); ?>">查看分区</a>
    </div>
</div>
<?php include 'footer.php'; ?>
