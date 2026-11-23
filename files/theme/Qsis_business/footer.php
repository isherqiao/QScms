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
 * @File         footer.php
 * @Description  Template of the theme "Qsis_business": footer with contact block and sitemap columns.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_biz_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_biz_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_biz_year = date('Y');

if (!function_exists('qs_consent_enabled') && is_file(__DIR__ . '/../../../control/consent_lib.php')) {
    require_once __DIR__ . '/../../../control/consent_lib.php';
}
?>
<footer class="biz-footer">
    <div class="wrap biz-footer-inner">
        <div class="biz-footer-brand">
            <p class="biz-footer-name"><?php echo htmlspecialchars($qs_biz_site); ?></p>
            <p class="biz-footer-desc"><?php echo htmlspecialchars($qs_biz_desc); ?></p>
        </div>
        <div class="biz-footer-col">
            <h4>站点导航</h4>
            <nav class="biz-footer-nav"><?php qs_sidemenu(); ?></nav>
        </div>
        <div class="biz-footer-col">
            <h4>友情链接</h4>
            <div class="biz-footer-links"><?php qs_links(); ?></div>
        </div>
    </div>
    <div class="wrap biz-footer-base">
        <p><?php echo isset($qs_config['footer_code']) ? $qs_config['footer_code'] : ''; ?></p>
        <?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
        <p class="biz-footer-consent"><a href="#" data-qs-consent-open onclick="if(window.qsConsentOpen){window.qsConsentOpen();}return false;"><?= function_exists('L') ? L('CookieConsent_Reopen') : 'Cookie settings' ?></a></p>
        <?php endif; ?>
        <p class="biz-footer-copy">&copy; <?php echo $qs_biz_year; ?> <?php echo htmlspecialchars($qs_biz_site); ?>. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
