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
 * @File         user_manage_add_user.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Create-user form: username uniqueness check, group selection and individual extra features.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qsis_username = trim($_POST['qsis_username']) ?? '';
    $displayname = trim($_POST['displayname']) ?? '';
    $qsis_userpassword = trim($_POST['qsis_userpassword']) ?? '';
    $description = trim($_POST['description']) ?? '';
    $email = trim($_POST['email'] ?? '');

    // 所属用户组（多选）与个人追加功能
    $groups = $_POST['groups'] ?? [];
    $groups = is_array($groups) ? array_values(array_filter($groups, 'strlen')) : [];
    $extraPerms = $_POST['extra_perms'] ?? [];
    $extraPerms = is_array($extraPerms) ? array_values(array_filter($extraPerms, 'strlen')) : [];
    
// 检查用户名是否已存在
    if (isset($sp_config_users[$qsis_username])) {
        echo L('Username_Exists_Exclaim');
        exit;
    }

    // =========邮箱重复校验【新增】=========
    if ($email !== '') {
        foreach ($sp_config_users as $oneUser) {
            if (!empty($oneUser['email']) && strcasecmp($oneUser['email'], $email) === 0) {
                echo L('Email_Taken_Add');
                exit;
            }
        }
    }

// 加密密码
    $hashed_qsis_userpassword = hash_password_complex($qsis_userpassword);

// 新模型：权限交由 groups / perms 决定；admin_level 由登录时按组推导，不再持久化
// 写入新用户数据
    $sp_config_users[$qsis_username] = [
        'displayname' => $displayname,
        'qsis_userpassword' => $hashed_qsis_userpassword,
        'description' => $description,
        'email' => $email,
        'groups' => $groups,
        'perms' => $extraPerms,
    ];
    // 写入confp.php
    $content = "<?php\n\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";
if (file_put_contents($datapaths . $config_user, $content)) {
    header('Location: user_manage.php');
    exit;
} else {
    echo L('Write_Failed_File_Perm_Exclaim');
    exit;
}
}
?>

<div class="admin_page_name">
  <a href="<?= QS_ADMIN_URL ?>/set.php"><?= L('system_settings'); ?></a>  <a href="user_manage.php"><?= L('manage_user'); ?></a>
</div>

<div id="fenye">
	<ta style="display:block">
<div class="container">
    <h2><?= L('Add_User_Title'); ?></h2>
    <form method="post" onsubmit="return validateForm();">
    <div id="form-error" style="color:red;margin-bottom:10px;"></div>
<script>
function validateForm() {
    const fields = ['qsis_username', 'qsis_userpassword' , 'description' , 'displayname'];
    const errorBox = document.getElementById('form-error');
    errorBox.innerHTML = ''; // 清空旧提示

    // 获取表单值
    const qsis_username = document.getElementById('qsis_username').value.trim();
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

    // 用户名重复判断
    if (existingUsers.includes(qsis_username)) {
        errorBox.innerHTML = '<?php echo L('Username_Taken_Try_Again'); ?>';
        document.getElementById('qsis_username').focus();
        return false;
    }

    // 显示名称重复判断
    if (existingDisplayNames.includes(displayname)) {
        errorBox.innerHTML = '<?php echo L('Displayname_Taken_Try_Again'); ?>';
        document.getElementById('displayname').focus();
        return false;
    }

    return true;
}
</script>
<div class="form-group">
    <label for="email"><?= L('Email'); ?>:</label>
    <input type="email" id="email" name="email">
</div>
        <div class="form-group">
            <label for="qsis_username"><?= L('Username'); ?>:</label>
            <input type="text" id="qsis_username" name="qsis_username" required>
        </div>
        <div class="form-group">
            <label for="displayname"><?= L('Display_Name'); ?>:</label>
            <input type="text" id="displayname" name="displayname">
        </div>
        <div class="form-group">
            <label for="qsis_userpassword"><?= L('Password_Label_Half'); ?></label>
            <input type="qsis_userpassword" id="qsis_userpassword" name="qsis_userpassword" required>
        </div>
        <div class="form-group">
            <label><?= L('User_Groups_Label_Multi'); ?></label>
            <div style="margin-top:6px;">
                <?php foreach ($qs_groups as $gname => $ginfo): ?>
                    <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                        <input type="checkbox" name="groups[]" value="<?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($gname === $qs_default_group) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>
                        <?php if (in_array('*', $ginfo['perms'] ?? [])): ?><span style="color:#c0392b;"><?= L('All_Perms_Bracket'); ?></span><?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div style="font-size:12px;color:#999;"><?= sprintf(L('Default_Group_Tip'), htmlspecialchars($qs_default_group, ENT_QUOTES, 'UTF-8')); ?></div>
        </div>
        <div class="form-group">
            <label><?= L('Extra_Perms_Optional_Label'); ?></label>
            <div style="margin-top:6px;">
                <?php foreach ($qs_perm_groups as $cat => $items): ?>
                    <?php foreach ($items as $key => $label): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;"
                            title="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="checkbox" name="extra_perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                        </label>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="description"><?= L('User_Description_Label_Half'); ?></label>
            <input type="text" id="description" name="description">
        </div>

        <input class="button" type="submit" value="<?= L('Add'); ?>">
    </form>
    <a href="user_manage.php"><?= L('Back_To_User_List'); ?></a>
</div>

 </div>
    <div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>	
<script>
const existingUsers = <?= json_encode(array_keys($sp_config_users)); ?>;
const existingDisplayNames = <?= json_encode(array_column($sp_config_users, 'displayname')); ?>;
</script>