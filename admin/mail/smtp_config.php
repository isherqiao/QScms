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
 * @File         smtp_config.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  SMTP configuration loader: reads the stored mail settings into runtime variables.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$smtp_accounts = array (
  'gmail' => 
  array (
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'user' => 'qiaoshuo@gmail.com',
    'pass' => 'rjmabbnlklshojtw',
  ),
  'zoho' => 
  array (
    'host' => 'smtp.zoho.com',
    'port' => 587,
    'user' => 'admin@zohu.com',
    'pass' => '123456',
  ),
  'outlook' => 
  array (
    'host' => 'smtp.office365.com',
    'port' => 587,
    'user' => 'your_outlook@outlook.com',
    'pass' => 'your_outlook_app_password',
  ),
  'yandex' => 
  array (
    'host' => 'smtp.yandex.com',
    'port' => 465,
    'user' => 'your_yandex@yandex.com',
    'pass' => 'your_yandex_app_password',
  ),
  'mailgun' => 
  array (
    'host' => 'smtp.mailgun.org',
    'port' => 587,
    'user' => 'postmaster@sandboxXXX.mailgun.org',
    'pass' => 'your_mailgun_smtp_password',
  ),
  'sendinblue' => 
  array (
    'host' => 'smtp-relay.sendinblue.com',
    'port' => 587,
    'user' => 'your_sendinblue@email.com',
    'pass' => 'your_sendinblue_smtp_key',
  ),
  'smtp2go' => 
  array (
    'host' => 'mail.smtp2go.com',
    'port' => 587,
    'user' => 'your_smtp2go_username',
    'pass' => 'your_smtp2go_password',
  ),
  'postmark' => 
  array (
    'host' => 'smtp.postmarkapp.com',
    'port' => 587,
    'user' => 'your_postmark_token',
    'pass' => 'your_postmark_token',
  ),
  'qq' => 
  array (
    'host' => 'smtp.qq.cn',
    'port' => 465,
    'user' => 'qiaoshuo@qq.com',
    'pass' => 'ucxlyzjhnqtmbhgg',
  ),
  163 => 
  array (
    'host' => 'smtp.163.com',
    'port' => 465,
    'user' => 'your@163.com',
    'pass' => 'your_163_smtp_auth_code',
  ),
  126 => 
  array (
    'host' => 'smtp.126.com',
    'port' => 465,
    'user' => 'your@126.com',
    'pass' => 'your_126_smtp_auth_code',
  ),
  'aliyun' => 
  array (
    'host' => 'smtp.mxhichina.com',
    'port' => 465,
    'user' => 'your@yourdomain.com',
    'pass' => 'your_aliyun_smtp_password',
  ),
  'tencent_enterprise' => 
  array (
    'host' => 'smtp.exmail.qq.com',
    'port' => 465,
    'user' => 'your@yourdomain.com',
    'pass' => 'your_exmail_password',
  ),
  'qsis' => 
  array (
    'host' => 'smtp.zoho.eu',
    'port' => 587,
    'user' => 'noreply@qsis.cn',
    'pass' => 'yGYaSF0kwYz3',
    'mail_default' => true,
  ),
  '腾讯' => 
  array (
    'host' => 'smtp.qq.com',
    'port' => 465,
    'user' => 'qiaoshuo@qq.com',
    'pass' => 'ucxlyzjhnqtmbhgg',
    'mail_backup' => true,
    'mail_notify' => true,
  ),
);
