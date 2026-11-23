<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/**
 * QsCms 项目文件
 *
 * @File         footer.php
 * @Description  Template of the theme "rl_rainbow": footer and document end.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
?>

</div><!-- /t-shell -->

<footer class="t-foot">
    <div class="t-wrap t-foot-inner">
        <div class="t-foot-nav"><?php qs_sidemenu(); ?></div>
        <div class="t-foot-links"><?php qs_links(); ?></div>
    </div>
    <div class="t-wrap t-foot-base">
        <span>&copy; <?php echo $qs_t_year; ?> <?php echo htmlspecialchars($qs_t_site); ?></span>
        <span class="t-foot-tip">由 QScms 构建</span>
    </div>
    <?php if (!empty($qs_config['footer_code'])): ?>
    <div class="t-wrap t-foot-code"><?php echo $qs_config['footer_code']; ?></div>
    <?php endif; ?>
</footer>

<?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
<?php endif; ?>

</body>
</html>
