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
 * @File         sendmail.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Mail sending helper: selects the default SMTP account, handles attachments and calls the sender.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 查找默认SMTP账号
$conf = null;
foreach ($smtp_accounts as $account) {
    if (isset($account['default']) && $account['default'] === true) {
        $conf = $account;
        break;
    }
}
if (!$conf) {
    exit(L('Default_Smtp_Account_Not_Found'));
}

$to_email = $_POST['to_email'];
$subject = $_POST['subject'];
$body = $_POST['body'];
$is_html = isset($_POST['is_html']) && $_POST['is_html'] == '1';  
$debug = isset($_POST['debug']) ? intval($_POST['debug']) : 1; 

// 附件处理：兼容直接传入 $_FILES['attachment'] 或构造好的数组
$attachment = isset($_FILES['attachment']) ? $_FILES['attachment'] : null;

// 调用发送函数
echo send_smtp_mail($conf, $to_email, $subject, $body, $attachment, $debug, $is_html);
?>
