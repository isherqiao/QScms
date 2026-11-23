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
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         footer.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 14/09/26 04:40:46
 * @Description  Template of the theme "Qsis_swbj": site footer, copyright line and closing markup.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 if (!defined('SITE_ROOT')) exit;

// 引入主题配置
$theme_config_file = __DIR__ . '/theme_config.php';
if (file_exists($theme_config_file)) {
    include $theme_config_file;
} else {
    // 文件缺失，给空默认，防止变量未定义报错
    $theme_tag_blocks = [];
}
?>
<div class="friend-link">
  <div class="container">
  	 <h3 class="list-title">链接</h3>
<div class="friend-links">

<?php qs_links(); ?>
</div>
</div>

<div class="footer">

  <div class="container">
  
    <p><?php echo $qs_config['footer_code'] ?? ''; ?></p>
  
<?php
// 「重新设置 Cookie 选项」入口：组件启用时才显示，避免留下死链
if (!function_exists('qs_consent_enabled') && is_file(__DIR__ . '/../../../control/consent_lib.php')) {
    require_once __DIR__ . '/../../../control/consent_lib.php';
}
?>
<?php if (function_exists('qs_consent_enabled') && qs_consent_enabled()): ?>
<div id="cookieResetWrap">
    <a href="#" data-qs-consent-open
       onclick="if(window.qsConsentOpen){window.qsConsentOpen();}return false;"><?= function_exists('L') ? L('CookieConsent_Reopen') : 'Cookie settings'; ?></a>
</div>
<?php endif; ?>

  </div>
</div>

<script src="/files/vendor/jquery/3.7.1/jquery.min.js"></script>
<script src="/files/vendor/jquery-qrcode/1.0.3/jquery.qrcode.min.js"></script>

<script>
const prev = document.getElementById('prev')
const next = document.getElementById('next')
const curr = document.getElementById('curr')
let page = 1
const max = 3

function update(){
  prev.disabled = page === 1
  next.disabled = page === max
  curr.textContent = page
}

prev.onclick = () => { page--; update() }
next.onclick = () => { page++; update() }
update()
</script>

<script>
$(function(){
    $('.qrcode').each(function(){
        $(this).qrcode({
            text: $(this).data('url'),
            width: 100,
            height: 100,
            draw: false  
        });
    });
});
</script>

</body>
</html>