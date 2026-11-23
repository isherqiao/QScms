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
 * @File         admin.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Captcha provider selector: lists the available providers and shows the key fields for the selected one.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$captcha_options = function_exists('qs_captcha_list') ? qs_captcha_list() : [];
$captcha_options['off'] = L('Captcha_off');
$current_provider = $captcha_provider ?? 'System/captcha';
?>
<div class="clear"></div>
<div class="field">
    <div class="label"><?php echo L('Captcha_provider'); ?></div>
    <div class="label">
        <select name="captcha_provider" id="captcha_provider_sel">
            <?php foreach ($captcha_options as $rel => $name): ?>
                <option value="<?php echo htmlspecialchars($rel); ?>" <?php if ($rel === $current_provider) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="info"><?php echo L('Captcha_provider_hint'); ?></div>
</div>

<div class="captcha-keys-panel" data-provider="System/captcha" style="display:none;">
    <div class="clear"></div>
    <div class="field">
        <div class="label"><?php echo L('Captcha_builtin'); ?></div>
        <div class="label"><div class="info"><?php echo L('Captcha_builtin_hint'); ?></div></div>
    </div>
</div>

<div class="captcha-keys-panel" data-provider="System/turnstile" style="display:none;">
    <div class="clear"></div>
    <div class="field">
        <div class="label">Turnstile Site Key</div>
        <div class="label">
            <input type="text" name="turnstile_sitekey" value="<?php echo htmlspecialchars($turnstile_sitekey ?? ''); ?>" >
        </div>
    </div>
    <div class="field">
        <div class="label">Turnstile Secret Key</div>
        <div class="label">
            <input type="text" name="turnstile_secret" value="<?php echo htmlspecialchars($turnstile_secret ?? ''); ?>" >
        </div>
    </div>
</div>

<div class="captcha-keys-panel" data-provider="System/hcaptcha" style="display:none;">
    <div class="clear"></div>
    <div class="field">
        <div class="label">hCaptcha Site Key</div>
        <div class="label">
            <input type="text" name="hcaptcha_sitekey" value="<?php echo htmlspecialchars($hcaptcha_sitekey ?? ''); ?>" >
        </div>
    </div>
    <div class="field">
        <div class="label">hCaptcha Secret Key</div>
        <div class="label">
            <input type="text" name="hcaptcha_secret" value="<?php echo htmlspecialchars($hcaptcha_secret ?? ''); ?>" >
        </div>
    </div>
</div>

<div class="captcha-keys-panel" data-provider="System/recaptcha" style="display:none;">
    <div class="clear"></div>
    <div class="field">
        <div class="label">reCAPTCHA Site Key</div>
        <div class="label">
            <input type="text" name="recaptcha_sitekey" value="<?php echo htmlspecialchars($recaptcha_sitekey ?? ''); ?>" >
        </div>
    </div>
    <div class="field">
        <div class="label">reCAPTCHA Secret Key</div>
        <div class="label">
            <input type="text" name="recaptcha_secret" value="<?php echo htmlspecialchars($recaptcha_secret ?? ''); ?>" >
        </div>
    </div>
</div>

<script>
(function(){
    var sel = document.getElementById('captcha_provider_sel');
    if (!sel) return;
    function toggle(){
        var v = sel.value;
        document.querySelectorAll('.captcha-keys-panel').forEach(function(el){
            el.style.display = (el.getAttribute('data-provider') === v) ? '' : 'none';
        });
    }
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
