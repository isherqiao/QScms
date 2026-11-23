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
 * @File         mailform.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Mail form partial: provides the input fields of the message composer.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'smtp_config.php'; ?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title><?= L('Smtp_Send_Page_Title'); ?></title></head>
<body>
<h2><?= L('Attach_Mail_Form_Title'); ?></h2>
<form action="sendmail.php" method="post" enctype="multipart/form-data">
    <label><?= L('Select_Smtp_Account_Label'); ?>
        <select name="smtp_choice">
            <?php foreach ($smtp_accounts as $key => $conf): ?>
                <option value="<?= $key ?>"><?= strtoupper($key) ?> - <?= $conf['user'] ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <?= L('Recipient_Email_Label'); ?><input type="email" name="to_email" required><br>
    <?= L('Mail_Subject_Label'); ?><input type="text" name="subject" required><br>
    <?= L('Mail_Body_Label'); ?><br><textarea name="body" rows="6" cols="60" required></textarea><br>
    <?= L('Attachment_Label'); ?><input type="file" name="attachment"><br><br>
    <label><?= L('Select_Mail_Format_Label'); ?>
    <select name="is_html">
        <option value="0"><?= L('Plain_Text_Option'); ?></option>
        <option value="1">HTML</option>
    </select>
</label><br><br>
<label><?= L('Enable_Debug_Log_Label'); ?>
    <select name="debug">
        <option value="0"><?= L('Enable'); ?></option>
        <option value="1" selected><?= L('Disable'); ?></option>
    </select>
</label><br><br>
    <input type="submit" value="<?= L('Send_Mail'); ?>">
</form>
</body>
</html>
