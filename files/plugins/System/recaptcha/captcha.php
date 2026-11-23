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
 * @Description  Google reCAPTCHA v2 provider: renders the checkbox widget and verifies the token server-side.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * Google reCAPTCHA v2（"我不是机器人" 勾选框）—— 统一接口实现
 * 文档：https://developers.google.com/recaptcha/docs/display
 * 注意：国内网络访问 google.com 可能不稳定
 */
if (!defined('SITE_ROOT')) {
    exit;
}

if (!function_exists('captcha_plugin_name')) {
    function captcha_plugin_name(): string
    {
        return 'Google reCAPTCHA v2';
    }
}

if (!function_exists('captcha_plugin_ready')) {
    function captcha_plugin_ready(): bool
    {
        global $recaptcha_sitekey, $recaptcha_secret;
        return trim((string)$recaptcha_sitekey) !== '' && trim((string)$recaptcha_secret) !== '';
    }
}

if (!function_exists('captcha_plugin_render')) {
    function captcha_plugin_render(): void
    {
        global $recaptcha_sitekey;
        if (empty($recaptcha_sitekey)) {
            echo '<div style="color:red">reCAPTCHA Site Key 未在后台配置</div>';
            return;
        }
        ?>
                <div class="label">安全验证</div>
                <div class="textbox">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_sitekey); ?>"></div>
                </div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
    }
}

if (!function_exists('captcha_plugin_check')) {
    function captcha_plugin_check(array $post): bool
    {
        global $recaptcha_secret;
        $token = $post['g-recaptcha-response'] ?? '';
        return qs_captcha_remote_verify(
            'https://www.google.com/recaptcha/api/siteverify',
            $recaptcha_secret ?? '',
            $token
        );
    }
}
