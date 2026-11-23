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
 * @Description  Template of the theme "Qsis_detective": footer and consent entry.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_gd_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_gd_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_gd_year = date('Y');

if (!function_exists('qs_consent_enabled') && is_file(__DIR__ . '/../../../control/consent_lib.php')) {
    require_once __DIR__ . '/../../../control/consent_lib.php';
}
?>
<footer class="gd-footer">
    <div class="gd-wrap gd-footer-inner">
        <div class="gd-footer-main">
            <span class="gd-footer-tag">SYSTEM</span>
            <h3><?php echo htmlspecialchars($qs_gd_site); ?></h3>
            <p><?php echo htmlspecialchars($qs_gd_desc); ?></p>
        </div>
        <div class="gd-footer-nav">
            <h4>导航</h4>
            <?php qs_menu(); ?>
        </div>
        <div class="gd-footer-links">
            <h4>链接</h4>
            <?php qs_links(); ?>
        </div>
    </div>
    <div class="gd-wrap gd-footer-bottom">
        <?php echo isset($qs_config['footer_code']) ? $qs_config['footer_code'] : ''; ?>
        <?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
        <a class="gd-consent" href="#" data-qs-consent-open onclick="if(window.qsConsentOpen){window.qsConsentOpen();}return false;"><?= function_exists('L') ? L('CookieConsent_Reopen') : 'Cookie settings' ?></a>
        <?php endif; ?>
        <p class="gd-copy">&copy; <?php echo $qs_gd_year; ?> <?php echo htmlspecialchars($qs_gd_site); ?></p>
    </div>
</footer>

</body>
</html>
