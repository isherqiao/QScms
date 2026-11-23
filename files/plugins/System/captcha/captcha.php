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
 * @Description  Built-in graphic captcha provider: implements the render and check interface loaded by qs_captcha_boot().
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * 系统自带验证码插件 —— 统一接口实现
 * 由 control/functions.php 中的 qs_captcha_boot() 自动加载，不再作为独立入口直接访问。
 *
 * 接口约定（任何验证码插件都必须提供同名同签名的两个函数）：
 *   captcha_plugin_render() : void            输出登录页的验证码 UI
 *   captcha_plugin_check(array $post) : bool  校验用户提交，通过返回 true
 */
if (!defined('SITE_ROOT')) {
    exit;
}

if (!function_exists('captcha_plugin_name')) {
    /**
     * 后台下拉显示名
     */
    function captcha_plugin_name(): string
    {
        return '图形验证码（系统自带）';
    }
}

if (!function_exists('captcha_plugin_ready')) {
    /**
     * 系统自带图形验证码永远就绪
     */
    function captcha_plugin_ready(): bool
    {
        return true;
    }
}

if (!function_exists('captcha_plugin_render')) {
    /**
     * 输出系统默认验证码的登录页 UI
     */
    function captcha_plugin_render(): void
    {
        ?>
                <div class="label">验证码</div>
                <div class="textbox"><input name="captcha" type="text" required></div>
                <img src="<?php qs_site_link(); ?>/files/plugins/System/captcha/captcha_image.php" alt="验证码" style="margin-bottom:10px;">
<?php
    }
}

if (!function_exists('captcha_plugin_check')) {
    /**
     * 校验提交的验证码
     * @param array $post 表单提交数据
     * @return bool 通过为 true
     */
    function captcha_plugin_check(array $post): bool
    {
        $input = trim((string)($post['captcha'] ?? ''));
        $code  = (string)($_SESSION['captcha_code'] ?? '');
        if ($code === '' || $input === '') {
            return false;
        }
        return strcasecmp($code, $input) === 0;
    }
}
