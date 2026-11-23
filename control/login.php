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
 * @File         login.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Backend login page, shared by the super admin and regular users: credential verification, captcha handling and session start.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once "verify.php";

if (qs_Pass() || qs_UserPass()) {
    header("Location: " . qs_admin_url(QS_ADMIN_DIR . '/post.php'));
    exit;
}
?>
<html>
<head>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?php qs_site_name(); ?> | 后台登录</title>
    <link rel="stylesheet" href="<?php qs_theme_url('style.css'); ?>"/>
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "PingFang SC", "Microsoft YaHei", sans-serif;
        }

        /* 站点标识：与登录框同宽居中，顶部留白，避免紧贴左上角 */
        #sitename {
            width: 360px;
            margin: 64px auto 0;
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: .5px;
            line-height: 1.4;
        }

        #sitename a {
            color: #1f2d27;
            text-decoration: none;
        }

        #login_title {
            text-align: center;
            width: 360px;
            margin: 22px auto 0;
            font-size: 26px;
            color: #333;
        }

        #login_form {
            width: 360px;
            margin: 20px auto;
            border: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 3px;
        }

        #login_form_box {
            padding: 16px;
        }

        .label {
            font-weight: bold;
            padding-bottom: 6px;
            color: #333;
        }

        .textbox input {
            border: none;
            font-size: 20px;
            width: 100%;
            outline: none;
        }

        .textbox {
            border: 1px solid #e0e0e0;
            padding: 8px;
            margin-bottom: 20px;
            border-radius: 3px;
        }

        .bottom {
            text-align: right;
        }

        .button {
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div id="main">
    <div id="sitename"><a href="<?php qs_site_link(); ?>"><?php qs_site_name(); ?></a></div>
    <div id="login_title">登录后台</div>
    <form action="" method="post">
        <div id="login_form">
            <div id="login_form_box">
                <?php qs_Login(); ?>
                <div class="label">账号</div>
                <div class="textbox"><input name="user" type="text" required></div>
                <div class="label">密码</div>
                <div class="textbox"><input name="pass" type="password" required></div>

<?php if (!empty($captcha) && qs_captcha_boot()): ?>
                    <?php if (!empty($GLOBALS['_captcha_fallback_from'])): ?>
                    <div style="color:#999;font-size:12px;margin-bottom:6px;"><?php echo L('Captcha_fallback_notice'); ?></div>
                    <?php endif; ?>
                    <?php captcha_plugin_render(); ?>
                <?php endif; ?>

                <div class="bottom">
                    <input name="login" type="submit" value="登录" class="button">
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>