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
 * @Description  Template of the theme "Qsis_consulting": quiet single-line footer.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

$qs_cs_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_cs_year = date('Y');

if (!function_exists('qs_consent_enabled') && is_file(__DIR__ . '/../../../control/consent_lib.php')) {
    require_once __DIR__ . '/../../../control/consent_lib.php';
}
?>
<footer class="cs-footer">
    <div class="wrap cs-footer-inner">
        <div class="cs-footer-nav"><?php qs_sidemenu(); ?></div>
        <div class="cs-footer-links"><?php qs_links(); ?></div>
    </div>
    <div class="wrap cs-footer-base">
        <span class="cs-footer-copy">&copy; <?php echo $qs_cs_year; ?> <?php echo htmlspecialchars($qs_cs_site); ?></span>
        <?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
        <a class="cs-footer-consent" href="#" data-qs-consent-open onclick="if(window.qsConsentOpen){window.qsConsentOpen();}return false;"><?= function_exists('L') ? L('CookieConsent_Reopen') : 'Cookie settings' ?></a>
        <?php endif; ?>
    </div>
    <?php if (!empty($qs_config['footer_code'])): ?>
    <div class="wrap cs-footer-code"><?php echo $qs_config['footer_code']; ?></div>
    <?php endif; ?>
</footer>

</body>
</html>
