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
 * @File         user_manage_edit_user.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Edit-user form: groups, individual features and the account fields of one user.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

//位置： /admin/user/user_manage_edit_user.php
//当前文件位置： /admin/user/

require_once __DIR__.'/../head.php'; ?>
<?php
$qsis_username = $_GET['qsis_username'] ?? null;
$user = $sp_config_users[$qsis_username];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$displayname  = isset($_POST['displayname']) ? trim($_POST['displayname']) : '';
$qsis_userpassword     = isset($_POST['qsis_userpassword']) ? trim($_POST['qsis_userpassword']) : '';
$description  = isset($_POST['description']) ? trim($_POST['description']) : '';
$email = trim($_POST['email'] ?? '');

// 所属用户组（多选）与个人功能
$groups = $_POST['groups'] ?? [];
$groups = is_array($groups) ? array_values(array_filter($groups, 'strlen')) : [];
$extraPerms = $_POST['extra_perms'] ?? [];
$extraPerms = is_array($extraPerms) ? array_values(array_filter($extraPerms, 'strlen')) : [];
$denyPerms = $_POST['deny_perms'] ?? [];
$denyPerms = is_array($denyPerms) ? array_values(array_filter($denyPerms, 'strlen')) : [];
$extraPerms = array_values(array_diff($extraPerms, $denyPerms));

     if (!isset($sp_config_users[$qsis_username])) {
        echo L('User_Not_Exist');
        exit;
    }

// =========邮箱唯一性校验【编辑模式：跳过自己】=========
    if ($email !== '') {
        foreach ($sp_config_users as $uname => $uinfo) {
            // 跳过当前正在编辑的用户
            if ($uname === $qsis_username) {
                continue;
            }
            if (!empty($uinfo['email']) && strcasecmp($uinfo['email'], $email) === 0) {
                echo L('Email_Taken_Edit');
                exit;
            }
        }
    }
  // 按需更新字段
    if ($displayname !== '') {
        $sp_config_users[$qsis_username]['displayname'] = $displayname;
    }
if (isset($_POST['qsis_userpassword'])) {
    $trimmed_pass = trim($_POST['qsis_userpassword']);
    if ($trimmed_pass !== '') {
        $sp_config_users[$qsis_username]['qsis_userpassword'] = hash_password_complex($trimmed_pass);
    }
}
        $sp_config_users[$qsis_username]['description'] = $description;
        // 更新邮箱字段
        $sp_config_users[$qsis_username]['email'] = $email;

        // ===== 新模型：保存用户组与个人功能；admin_level 由登录时按组推导，不再持久化 =====
        $sp_config_users[$qsis_username]['groups'] = $groups;
        $sp_config_users[$qsis_username]['perms'] = $extraPerms;
        $sp_config_users[$qsis_username]['deny_perms'] = $denyPerms;

    // 保存回 confp.php
    $new_content = "<?php\n\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";

    if (file_put_contents($datapaths . $config_user, $new_content)) {
        header('Location: user_manage.php');
        exit;
    } else {
        echo L('Write_Failed_Check_Perm_Brief');
        exit;
    }
}
?>

<div class="admin_page_name">
  <a href="<?= QS_ADMIN_URL ?>/set.php"><?= L('system_settings'); ?></a>  <a href="user_manage.php"><?= L('manage_user'); ?></a>
</div>

<div id="fenye">
    <div class="container">
        <h2><?= L('Edit_User_Title'); ?></h2>
        
        <form method="post" onsubmit="return validateForm();">

        	<div id="form-error" style="color: red; margin-bottom: 10px;"></div>
<script>
function validateForm() {
    const fields = ['displayname', 'description'];
    const errorBox = document.getElementById('form-error');
    errorBox.innerHTML = ''; 

    const qsis_username = document.querySelector('input[name="qsis_username"]').value.trim();
    const displayname = document.getElementById('displayname').value.trim();

    // 基础非空判断
    for (let i = 0; i < fields.length; i++) {
        const field = document.getElementById(fields[i]);
        if (!field.value.trim()) {
            errorBox.innerHTML = '<?php echo L('All_Fields_Required'); ?>';
            field.focus();
            return false;
        }
    }

    // 显示名称重复判断，排除当前用户名的显示名
    for (let i = 0; i < existingDisplayNames.length; i++) {
        if (existingDisplayNames[i] === displayname && existingUsers[i] !== qsis_username) {
            errorBox.innerHTML = '<?php echo L('Displayname_Taken_Try_Again'); ?>';
            document.getElementById('displayname').focus();
            return false;
        }
    }

    return true;
}

</script>
<div class="form-group">
    <label for="email"><?= L('Email'); ?>:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($sp_config_users[$qsis_username]['email'] ?? ''); ?>">
</div>

            <input type="hidden" name="qsis_username" value="<?= htmlspecialchars($qsis_username, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="qsis_username"><?= L('Username'); ?>:</label>
                <?= htmlspecialchars($qsis_username, ENT_QUOTES, 'UTF-8'); ?>
            </div>
           <div class="form-group">
                <label for="position"><?= L('Display_Name'); ?>:</label>
                <input type="text" id="displayname" name="displayname" value="<?= htmlspecialchars($sp_config_users[$qsis_username]['displayname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="qsis_userpassword"><?= L('new_password'); ?>:</label>
                <input type="qsis_userpassword" id="qsis_userpassword" name="qsis_userpassword" value="">
            </div>

            <div class="form-group">
            <label><?= L('User_Groups_Label_Multi'); ?></label>
            <?php if (!array_key_exists('groups', $sp_config_users[$qsis_username] ?? [])): ?>
                <div style="margin:4px 0 8px;padding:8px 10px;background:#fff8e6;border:1px solid #f0d6a0;border-radius:4px;font-size:12px;color:#8a6d3b;">
                    <?= L('Legacy_Perm_Warning'); ?>
                </div>
            <?php endif; ?>
            <div style="margin-top:6px;">
                <?php
                $eu_groups = qs_user_groups($qsis_username);
                foreach ($qs_groups as $gname => $ginfo): ?>
                    <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                        <input type="checkbox" name="groups[]" value="<?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>"
                            <?= in_array($gname, $eu_groups) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>
                        <?php if (in_array('*', $ginfo['perms'] ?? [])): ?><span style="color:#c0392b;"><?= L('All_Perms_Bracket'); ?></span><?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $eu_extra = isset($sp_config_users[$qsis_username]['perms']) && is_array($sp_config_users[$qsis_username]['perms']) ? $sp_config_users[$qsis_username]['perms'] : [];
        $eu_deny  = isset($sp_config_users[$qsis_username]['deny_perms']) && is_array($sp_config_users[$qsis_username]['deny_perms']) ? $sp_config_users[$qsis_username]['deny_perms'] : [];
        ?>
        <div class="form-group">
            <label><?= L('Extra_Perms_Optional_Label'); ?></label>
            <div style="margin-top:6px;">
                <?php foreach ($qs_perm_groups as $cat => $items): ?>
                    <?php foreach ($items as $key => $label): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;"
                            title="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="checkbox" name="extra_perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                <?= in_array($key, $eu_extra) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label><?= L('Deny_Perms_Label_Brief'); ?></label>
            <div style="margin-top:6px;">
                <?php foreach ($qs_perm_groups as $cat => $items): ?>
                    <?php foreach ($items as $key => $label): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                            <input type="checkbox" name="deny_perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                <?= in_array($key, $eu_deny) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="description"><?= L('User_Description_Label_Half'); ?></label>
            <input type="text" id="description" name="description" value="<?= htmlspecialchars($sp_config_users[$qsis_username]['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

            <input type="submit" value="<?= L('Save_Changes_Button'); ?>">
        </form>
        <a href="user_manage.php"><?= L('Back_To_User_List'); ?></a>
    </div>
</div>
    <div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>	
<script>
const existingUsers = JSON.parse('<?= json_encode(array_keys($sp_config_users), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>');
const existingDisplayNames = JSON.parse('<?= json_encode(array_column($sp_config_users, 'displayname'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>');
</script>