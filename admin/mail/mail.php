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
 * @File         mail.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Mail composition page: builds a message, tags the role and sends it through the configured SMTP account.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
<?php require 'smtp_config.php'; ?>
<?php

/*
==%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*=
==%%%%%#*+++++++*#%%%%%#*++++++++++*%##++%%#*++++++++++#%%*=
==%%%#+==+**##*+==+%%%*==+*********#%#*++%*==**####****#%%*=
==%%#===#%%%%%%%#===%%===%%%%%%%%%%%%#####==+%%%%%%%%%%%%%*=
==%%===#%%%%%%%%%*==*%+==*######%%%%%#*==#===*#%%%%%%%%%%%*=
==%%===%%%%%%%%%%#==+%#+==========+#%#*==%#+==========+%%%*=
==%%===#%%%%%%%%%#==*%%%%%######*===*#*==%%%%%######*===#%*=
==%%#==+%%%%%%%%#+==#%%%%%%%%%%%%#==+#*==%%%%%%%%%%%%*==*%*=
==%%%*===*#%%%#*==+##%%##########+==##*==%%######%%%#===%%*=
==%%%%%*+============##===========+*%#*==%*===========+#%%*=
==%%%%%%%##**********%#*********###%%##**%#********###%%%%*=
==%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*=
*/

// 读取高级配置的SMTP调试全局开关
$globalDebugSwitch = $qs_adv_config['mail_smtp_debug_global'] ?? 'close';
$debug = ($globalDebugSwitch === 'open') ? 0 : 1;
$debug_text = $globalDebugSwitch === 'open' ? L('Debug_Log_On') : L('Debug_Log_Off');

$display_info = false;
$current_choice = null;

if (isset($_POST['sendmail'])) {
    $smtp_choice = $_POST['smtp_choice'];
    $current_choice = $smtp_choice;

    if (!isset($smtp_accounts[$smtp_choice])) {
        exit(L('Smtp_Account_Not_Exist'));
    }

    $selected_smtp = $smtp_choice;

    $conf = $smtp_accounts[$smtp_choice];

    $to_email = $_POST['to_email'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $attachment = $_FILES['attachment'];
$is_html = isset($_POST['is_html']) && $_POST['is_html'] == '1';

    $display_info = true;
}
?>
<style>
    .account-form { display: none; border: 1px solid #ccc; padding: 20px; margin-top: 10px; }
    label { display: block; margin: 5px 0; }
    .new-account { border: 1px solid #ccc; padding: 20px; margin-top: 30px; background: #f9f9f9; }
</style>
<script>
    function showAccount(id) {
        const forms = document.querySelectorAll('.account-form');
        forms.forEach(f => f.style.display = 'none');
        const selected = document.getElementById('form_' + id);
        if (selected) selected.style.display = 'block';
    }
</script>

<div class="admin_page_name">
  <a href="../set.php"><?= L('system_settings'); ?></a> <a href="mail_setting.php"><?= L('mail_setting'); ?></a>
</div>
<?php if ($display_info): ?>
<div class="updated"> <?= send_smtp_mail($conf, $to_email, $subject, $body, $attachment, $debug, $is_html); ?></div>
<?php endif; ?>

<h3><a href="mail_setting.php">①<?= L('Smtp_Account_Management'); ?></a>--><a href="mail.php">②<?= L('Send_Mail'); ?></a></h3>
<div id="mail">
<div id="fenye">
<ta style="display:block">

<form action="" method="post" enctype="multipart/form-data">
    <label><?= L('Select_Smtp_Account_Label'); ?>
        <select name="smtp_choice">
<?php foreach ($smtp_accounts as $key => $conf): ?>
    <?php
        $selected = '';
        if ($current_choice !== null) {
            $selected = ($current_choice === $key) ? 'selected' : '';
        } elseif (!empty($conf['mail_default'])) {
            $selected = 'selected';
        }

        //拼接角色标签
$tags = [];
if(!empty($conf['mail_default']))  $tags[] = '['.L('mail_default').']';
if(!empty($conf['mail_backup']))   $tags[] = '['.L('mail_backup').']';
if(!empty($conf['mail_notify']))   $tags[] = '['.L('mail_notify').']';
$tagText = implode('',$tags);

    ?>
    <option value="<?= htmlspecialchars($key) ?>" <?= $selected ?>>
        <?= strtoupper($key) ?> - <?= htmlspecialchars($conf['user']) ?> <?= htmlspecialchars($tagText) ?>
    </option>
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
    </label>
    <label>
<?= L('Enable_Debug_Log_Label'); ?><span style="padding:2px 6px;"><?php echo $debug_text; ?></span>
<br>
<small><?= sprintf(L('Debug_Master_Switch_Tip'), '<a href="../Adv/advset.php">'.L('Advanced_Settings_Link').'</a>'); ?></small>
    </label><br><br>
    <input type="submit" name="sendmail" value="<?= L('Send_Mail'); ?>">
</form>
</ta>
</div>
</div>
<div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>