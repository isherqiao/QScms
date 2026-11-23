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
 * @File         captcha.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  hCaptcha provider: renders the widget and verifies the token through the hCaptcha API.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * hCaptcha 验证码插件 —— 统一接口实现
 * 文档：https://docs.hcaptcha.com/
 */
if (!defined('SITE_ROOT')) {
    exit;
}

if (!function_exists('captcha_plugin_name')) {
    function captcha_plugin_name(): string
    {
        return 'hCaptcha';
    }
}

if (!function_exists('captcha_plugin_ready')) {
    function captcha_plugin_ready(): bool
    {
        global $hcaptcha_sitekey, $hcaptcha_secret;
        return trim((string)$hcaptcha_sitekey) !== '' && trim((string)$hcaptcha_secret) !== '';
    }
}

if (!function_exists('captcha_plugin_render')) {
    function captcha_plugin_render(): void
    {
        global $hcaptcha_sitekey;
        if (empty($hcaptcha_sitekey)) {
            echo '<div style="color:red">hCaptcha Site Key 未在后台配置</div>';
            return;
        }
        ?>
                <div class="label">安全验证</div>
                <div class="textbox">
                    <div class="h-captcha" data-sitekey="<?php echo htmlspecialchars($hcaptcha_sitekey); ?>"></div>
                </div>
                <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<?php
    }
}

if (!function_exists('captcha_plugin_check')) {
    function captcha_plugin_check(array $post): bool
    {
        global $hcaptcha_secret;
        $token = $post['h-captcha-response'] ?? '';
        return qs_captcha_remote_verify(
            'https://api.hcaptcha.com/siteverify',
            $hcaptcha_secret ?? '',
            $token
        );
    }
}
