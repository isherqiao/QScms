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
 * @Description  Template of the theme "Qsis_corp": footer columns, links and consent entry.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_corp_site  = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_corp_desc  = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_corp_year  = date('Y');

if (!function_exists('qs_consent_enabled') && is_file(__DIR__ . '/../../../control/consent_lib.php')) {
    require_once __DIR__ . '/../../../control/consent_lib.php';
}
?>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div class="footer-col footer-about">
            <h4><?php echo htmlspecialchars($qs_corp_site); ?></h4>
            <p><?php echo htmlspecialchars($qs_corp_desc); ?></p>
        </div>
        <div class="footer-col">
            <h4>快速导航</h4>
            <nav class="footer-nav"><?php qs_menu(); ?></nav>
        </div>
        <div class="footer-col">
            <h4>友情链接</h4>
            <div class="footer-links"><?php qs_links(); ?></div>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <p><?php echo isset($qs_config['footer_code']) ? $qs_config['footer_code'] : ''; ?></p>
        <?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
        <div class="footer-consent">
            <a href="#" data-qs-consent-open onclick="if(window.qsConsentOpen){window.qsConsentOpen();}return false;"><?= function_exists('L') ? L('CookieConsent_Reopen') : 'Cookie settings' ?></a>
        </div>
        <?php endif; ?>
        <p class="footer-copy">&copy; <?php echo $qs_corp_year; ?> <?php echo htmlspecialchars($qs_corp_site); ?>. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
