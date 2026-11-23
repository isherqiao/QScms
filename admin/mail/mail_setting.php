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
 * @File         mail_setting.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  SMTP settings panel: accounts, ports, encryption and the per-role sender binding.
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
$display_info = false;

// ==========【新增：SMTP角色绑定保存逻辑】==========
if(isset($_POST['save_smtp_role'])){
    $mail_default = $_POST['mail_default'] ?? '';
    $mail_backup  = $_POST['mail_backup'] ?? '';
    $mail_notify  = $_POST['mail_notify'] ?? '';

    //清空全部账号的三个角色标记
    foreach ($smtp_accounts as $k => &$acc) {
        unset($acc['mail_default'],$acc['mail_backup'],$acc['mail_notify']);
    }
    unset($acc);

    $roleMap = [
        'mail_default' => $mail_default,
        'mail_backup'  => $mail_backup,
        'mail_notify'  => $mail_notify,
    ];
    $validKeys = array_keys($smtp_accounts);
    foreach ($roleMap as $role => $submitKey) {
        if ($submitKey !== '' && in_array($submitKey, $validKeys, true)) {
            $smtp_accounts[$submitKey][$role] = true;
        }
    }

    $config_file = 'smtp_config.php';
    $content = "<?php\n\$smtp_accounts = ".var_export($smtp_accounts,true).";\n";
    file_put_contents($config_file,$content);
    $display_info = true;
}

//读取现有角色，用于下拉框selected
$mail_default = '';
$mail_backup  = '';
$mail_notify  = '';
foreach($smtp_accounts as $k=>$acc){
    if(!empty($acc['mail_default'])) $mail_default = $k;
    if(!empty($acc['mail_backup']))  $mail_backup  = $k;
    if(!empty($acc['mail_notify']))  $mail_notify  = $k;
}
// ==========【新增结束】==========

if (isset($_POST['save'])) {
$config_file = 'smtp_config.php';
$accounts = $_POST['accounts'] ?? [];
$delete = $_POST['delete'] ?? [];
$new = $_POST['new'] ?? [];

$new_config = [];

foreach ($accounts as $id => $conf) {
    if (!in_array($id, $delete)) {
        $new_config[$id] = [
            'host' => $conf['host'],
            'port' => (int)$conf['port'],
            'user' => $conf['user'],
            'pass' => $conf['pass']
        ];
        //继承原有角色标记，编辑账号不会丢失mail_default/mail_backup/mail_notify
        if(!empty($smtp_accounts[$id]['mail_default'])) $new_config[$id]['mail_default'] = true;
        if(!empty($smtp_accounts[$id]['mail_backup']))  $new_config[$id]['mail_backup'] = true;
        if(!empty($smtp_accounts[$id]['mail_notify'])) $new_config[$id]['mail_notify'] = true;
    }
}

if (!empty($new['id'])) {
    $id = trim($new['id']);
    if ($id !== '' && !isset($new_config[$id])) {
        $new_config[$id] = [
            'host' => $new['host'],
            'port' => (int)$new['port'],
            'user' => $new['user'],
            'pass' => $new['pass']
        ];
    }
}

// 保存为 PHP 文件
$content = "<?php\n\$smtp_accounts = " . var_export($new_config, true) . ";\n";
file_put_contents($config_file, $content);

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
  <a href="../set.php"><?= L('system_settings'); ?></a> <a href="mail_setting.php"><?= L('mail_setting'); ?></a></a>
</div>
<?php if ($display_info) {
    ?>
    <div class="updated">
<?= L('Operation_successful');?>
    </div>
    <?php
}
?>
<h3><a href="mail_setting.php">①<?= L('Smtp_Account_Management'); ?></a>--><a href="mail.php">②<?= L('Send_Mail'); ?></a></h3>
<fieldset style="padding:12px;margin-bottom:16px;background:#f7f7f7;">
<legend><strong><?= L('Smtp_Role_Binding_Legend'); ?></strong></legend>
<form method="post" action="">
    <label><?= L('Default_Test_Mail_Account_Label'); ?>
        <select name="mail_default">
            <option value=""><?= L('Not_Set_Option'); ?></option>
            <?php foreach($smtp_accounts as $k=>$acc):?>
            <option value="<?=htmlspecialchars($k)?>" <?= $mail_default===$k?'selected':''?>>
                <?=htmlspecialchars(strtoupper($k).' - '.$acc['user'])?>
            </option>
            <?php endforeach;?>
        </select>
    </label>
    <br><br>
    <label><?= L('Backup_Mail_Account_Label'); ?>
        <select name="mail_backup">
            <option value=""><?= L('Not_Set_Option'); ?></option>
            <?php foreach($smtp_accounts as $k=>$acc):?>
            <option value="<?=htmlspecialchars($k)?>" <?= $mail_backup===$k?'selected':''?>>
                <?=htmlspecialchars(strtoupper($k).' - '.$acc['user'])?>
            </option>
            <?php endforeach;?>
        </select>
    </label>
    <br><br>
    <label><?= L('Notify_Mail_Account_Label'); ?>
        <select name="mail_notify">
            <option value=""><?= L('Not_Set_Option'); ?></option>
            <?php foreach($smtp_accounts as $k=>$acc):?>
            <option value="<?=htmlspecialchars($k)?>" <?= $mail_notify===$k?'selected':''?>>
                <?=htmlspecialchars(strtoupper($k).' - '.$acc['user'])?>
            </option>
            <?php endforeach;?>
        </select>
    </label>
    <br><br>
    <input type="submit" name="save_smtp_role" value="<?= L('Save_Role_Binding_Button'); ?>">
</form>
</fieldset>

<div id="mail">
<div id="fenye">
<ta style="display:block">

	<form action="" method="post">
    <label>
        <?= L('Select_Account_To_Edit_Label'); ?>
        <select onchange="showAccount(this.value)">
            <option value=""><?= L('Please_Select_Option'); ?></option>
            <?php foreach ($smtp_accounts as $key => $conf): ?>
                <option value="<?= htmlspecialchars($key) ?>"><?= strtoupper($key) ?> - <?= htmlspecialchars($conf['user']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div id="account_forms">
        <?php foreach ($smtp_accounts as $key => $conf): ?>
            <div class="account-form" id="form_<?= htmlspecialchars($key) ?>">
                <fieldset>
                    <legend><?= sprintf(L('Account_Settings_Legend'), htmlspecialchars($key)); ?></legend>
                    <?= L('Identifier_Label'); ?><input name="accounts[<?= $key ?>][id]" value="<?= $key ?>" readonly><br>
                    <?= L('Smtp_Host_Label'); ?><input name="accounts[<?= $key ?>][host]" value="<?= $conf['host'] ?>"><br>
                    <?= L('Port_Label'); ?><input name="accounts[<?= $key ?>][port]" value="<?= $conf['port'] ?>"><br>
                    <?= L('Username'); ?>：<input name="accounts[<?= $key ?>][user]" value="<?= $conf['user'] ?>"><br>
                    <?= L('Password_Authcode_Label'); ?><input name="accounts[<?= $key ?>][pass]" value="<?= $conf['pass'] ?>"><br>
                    <label><input type="checkbox" name="delete[]" value="<?= $key ?>"> <?= L('Delete_This_Account'); ?></label>
                </fieldset>
            </div>
        <?php endforeach; ?>
    </div>

<div class="small_form new-account">
<div class="field">
<div class="label"><?= L('New_Identifier_Label'); ?></div><div class="labec"><input name="new[id]"></div>

</div>
<div class="field">
<div class="label"><?= L('Smtp_Host_Label'); ?></div><div class="labec"><input name="new[host]"></div>

</div>
<div class="field">
<div class="label"><?= L('Port_Label_Spaced'); ?></div><div class="labec"><input name="new[port]"></div>

</div>
<div class="field">
<div class="label"><?= L('Username'); ?>：</div><div class="labec"><input name="new[user]"></div>

</div>
<div class="field">
<div class="label"><?= L('Password_Authcode_Label'); ?></div><div class="labec"><input name="new[pass]"></div>

</div>
</div>

    <br><input type="submit" name="save" value="<?= L('Save_Settings'); ?>">
</form>
  </ta>
</div>

</div>
    <div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>	