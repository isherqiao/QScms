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
 * @Description  Cloudflare Turnstile provider: renders the widget and verifies the token through the Turnstile API.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * Cloudflare Turnstile 验证码插件 —— 统一接口实现
 * 文档：https://developers.cloudflare.com/turnstile/
 */
if (!defined('SITE_ROOT')) {
    exit;
}

if (!function_exists('captcha_plugin_name')) {
    function captcha_plugin_name(): string
    {
        return 'Cloudflare Turnstile';
    }
}

if (!function_exists('captcha_plugin_ready')) {
    function captcha_plugin_ready(): bool
    {
        global $turnstile_sitekey, $turnstile_secret;
        return trim((string)$turnstile_sitekey) !== '' && trim((string)$turnstile_secret) !== '';
    }
}

if (!function_exists('captcha_plugin_render')) {
    function captcha_plugin_render(): void
    {
        global $turnstile_sitekey;
        if (empty($turnstile_sitekey)) {
            echo '<div style="color:red">Turnstile Site Key 未在后台配置</div>';
            return;
        }
        ?>
                <div class="label">安全验证</div>
                <div class="textbox">
                    <div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstile_sitekey); ?>"></div>
                </div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php
    }
}

if (!function_exists('captcha_plugin_check')) {
    function captcha_plugin_check(array $post): bool
    {
        global $turnstile_secret;
        $token = $post['cf-turnstile-response'] ?? '';
        return qs_captcha_remote_verify(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            $turnstile_secret ?? '',
            $token
        );
    }
}
