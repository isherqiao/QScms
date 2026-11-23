<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         index.php
 * @Description  Control bar output: renders the admin shortcut bar for logged-in editors.
 *               改版后不再 fixed 浮在页面之上，而是插进 body 顶部、随内容排布，
 *               因此不会与任何主题的导航或首屏重叠，主题侧无需单独适配。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!empty($controlbar) && qs_Pass()) {
    global $qs_config, $controldata_url;

    if (!isset($controldata_url) || $controldata_url === '') {
        $controldata_url = defined('QS_ADMIN_URL') ? QS_ADMIN_URL : '/admin';
    }

    $qsBarTheme = isset($qs_config['site_theme']) ? (string)$qs_config['site_theme'] : '';
    $qsBarImgFile = $qsBarTheme !== ''
        ? SITE_ROOT . '/files/theme/' . $qsBarTheme . '/image.jpg'
        : '';
    if (!is_file($qsBarImgFile)) {
        $qsBarImgFile = SITE_ROOT . '/files/theme/' . $qsBarTheme . '/hero.jpg';
    }
    $qsBarImgUrl = is_file($qsBarImgFile) ? qs_theme_url(basename($qsBarImgFile), false) : '';
    ?>
<style>
.qs-bar {
    background: #0f1115;
    color: #cfd6dd;
    font-size: 13px;
    position: -webkit-sticky;
    position: sticky;
    top: 0;
    z-index: 9999;
    width: 100%;
}
.qs-bar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 7px 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.qs-bar-brand {
    color: #fff;
    font-weight: 700;
    letter-spacing: .04em;
    text-decoration: none;
    white-space: nowrap;
}
.qs-bar-brand:hover { color: #fff; text-decoration: none; }
.qs-bar-theme {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #9aa5b1;
    text-decoration: none;
    white-space: nowrap;
}
.qs-bar-theme:hover { color: #fff; text-decoration: none; }
.qs-bar-theme img {
    width: 22px;
    height: 22px;
    object-fit: cover;
    border-radius: 3px;
    display: block;
}
.qs-bar-links {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.qs-bar-links a {
    color: #cfd6dd;
    text-decoration: none;
    white-space: nowrap;
}
.qs-bar-links a:hover { color: #fff; text-decoration: none; }
@media (max-width: 720px) {
    .qs-bar-inner { padding: 6px 12px; gap: 10px; font-size: 12px; }
    .qs-bar-links { gap: 10px; margin-left: 0; width: 100%; }
}
</style>
<div class="qs-bar" id="qsControlBar">
    <div class="qs-bar-inner">
        <a class="qs-bar-brand" href="https://www.qsis.cn">【Qsis】</a>
        <a class="qs-bar-theme" href="<?= htmlspecialchars($controldata_url) ?>/set.php">
            <?php if ($qsBarImgUrl !== ''): ?><img src="<?= htmlspecialchars($qsBarImgUrl) ?>" alt=""><?php endif; ?>
            <span>当前主题:<?php qs_site_theme(); ?></span>
        </a>
        <div class="qs-bar-links">
            <a href="<?= htmlspecialchars($controldata_url) ?>/post.php" target="_blank"><?= UI('Write_Article') ?></a>
            <a href="/?liuyan/" target="_blank"><?= UI('Messages') ?></a>
            <a href="<?= htmlspecialchars($controldata_url) ?>/set.php" target="_blank"><?= UI('Settings') ?></a>
            <a href="/?logout/"><?= UI('Logout') ?></a>
        </div>
    </div>
</div>
<script>
(function () {
    function placeBar() {
        var bar = document.getElementById('qsControlBar');
        if (!bar || !document.body) { return; }
        if (document.body.firstElementChild !== bar) {
            document.body.insertBefore(bar, document.body.firstChild);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', placeBar);
    } else {
        placeBar();
    }
})();
</script>
<?php
}
?>
