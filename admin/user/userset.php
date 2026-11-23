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
 * @File         userset.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  User account editor: changes password or e-mail using the same hashing routine as the login check.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__.'/../head.php';

$userName = $_SESSION['Qsis_user_name'] ?? '';
$tip = '';
$tipType = '';
$curr_user = $sp_config_users[$userName] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $newDesc = trim($_POST['description'] ?? '');
    $newDisplay = trim($_POST['displayname'] ?? '');
    $newPwd = trim($_POST['new_pwd'] ?? '');
    $confirmPwd = trim($_POST['confirm_pwd'] ?? '');

    if (!isset($sp_config_users[$userName])) {
        $tip = L('Current_User_Config_Missing');
        $tipType = 'error';
    } elseif (mb_strlen($newDesc) > 300) {
        $tip = L('Description_Max_300_Tip');
        $tipType = 'error';
    } elseif ($newDisplay === '') {
        $tip = L('Displayname_Cannot_Empty');
        $tipType = 'error';
    } elseif ($newPwd !== '' && $newPwd !== $confirmPwd) {
        $tip = L('New_Password_Mismatch');
        $tipType = 'error';
    } else {
        $sp_config_users[$userName]['displayname'] = $newDisplay;
        $sp_config_users[$userName]['description'] = $newDesc;
        if (!empty($newPwd)) {
            //调用Qsis CMS原生子账号密码加密函数，和后台编辑用户、登录校验逻辑统一
            $sp_config_users[$userName]['qsis_userpassword'] = hash_password_complex($newPwd);
        }
        $code = "<?php\n";
        $code .= "\$sp_config_users = " . var_export($sp_config_users, true) . ";\n";
        $code .= "?>";
file_put_contents($datapaths . $config_user, $code, LOCK_EX);
// 重读文件验证写入生效
$checkContent = file_get_contents($datapaths . $config_user);
if (trim($checkContent) === trim($code)) {
    $curr_user = $sp_config_users[$userName];
    $_SESSION['user_displayname'] = $curr_user['displayname'];
    $tip = L('Profile_Saved_Success');
    $tipType = 'success';
} else {
    $tip = L('Write_Did_Not_Take_Effect_Perm');
    $tipType = 'error';
}

    }
}
?>

<?php
if(qs_can('*')){
    echo '<div style="padding:12px 16px;background:#fff8dc;border:1px solid #e6d290;border-radius:6px;margin-bottom:16px;color:#8b6914;">
'.L('Admin_Profile_Warning_Tip').'
</div>';
    // 超级管理员直接终止下面内容输出
}else{
?>
<div class="admin_page_name">

    <a href="userset.php"><?= L('My_Profile_Link'); ?></a>
    <a href="sysset.php"><?= L('System_Style_Settings_Link'); ?></a>
	</div>
	
<div id="fenye">
    <div class="container">
        <?php if($tip): ?>
        <div class="alert <?= $tipType ?>"><?= htmlspecialchars($tip,ENT_QUOTES,'UTF-8') ?></div>
        <?php endif; ?>

        <div class="view-box">
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Username'); ?>：</label>
                <span style="min-width:0;word-break:break-all;"><?= htmlspecialchars($userName,ENT_QUOTES,'UTF-8'); ?><?= L('Not_Editable_Bracket'); ?></span>
            </div>
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Display_Name'); ?>：</label>
                <span style="min-width:0;word-break:break-all;"><?= htmlspecialchars($curr_user['displayname'] ?? '',ENT_QUOTES,'UTF-8'); ?></span>
            </div>
            <div class="form-row" style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;padding-top:8px;"><?= L('User_Profile_Label'); ?></label>
                <span style="min-width:0;word-break:break-all;"><?= htmlspecialchars($curr_user['description'] ?? '',ENT_QUOTES,'UTF-8'); ?></span>
            </div>
            <button class="edit_btn" type="button"><?= L('Edit_Profile_Button'); ?></button>
        </div>

        <form method="post" class="edit_box" style="display:none;margin-top:15px;">
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Username'); ?>：</label>
                <span style="min-width:0;word-break:break-all;"><?= htmlspecialchars($userName,ENT_QUOTES,'UTF-8'); ?><?= L('Not_Editable_Bracket'); ?></span>
            </div>
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Display_Name'); ?>：</label>
                <input type="text" name="displayname" style="width:100%;max-width:520px;box-sizing:border-box;padding:8px;" value="<?= htmlspecialchars($curr_user['displayname'] ?? '',ENT_QUOTES,'UTF-8') ?>">
            </div>
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('new_password'); ?>：</label>
                <input id="pwd1" type="password" name="new_pwd" style="width:100%;max-width:520px;box-sizing:border-box;padding:8px;" placeholder="<?= L('Old_Password_Keep_Placeholder'); ?>">
                <span onclick="togglePwd('pwd1',this)" style="cursor:pointer;flex-shrink:0;">👁️</span>
            </div>
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Confirm_Password_Label'); ?></label>
                <input id="pwd2" type="password" name="confirm_pwd" style="width:100%;max-width:520px;box-sizing:border-box;padding:8px;" placeholder="<?= L('Reenter_New_Password_Placeholder'); ?>">
                <span onclick="togglePwd('pwd2',this)" style="cursor:pointer;flex-shrink:0;">👁️</span>
            </div>
            <div class="form-row" style="display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;padding-top:8px;"><?= L('User_Profile_Label'); ?></label>
                <div style="flex:1;min-width:0;">
                   <textarea name="description" rows="5" style="width:100%;box-sizing:border-box;resize:vertical;padding:8px;"><?= htmlspecialchars($curr_user['description'] ?? '',ENT_QUOTES,'UTF-8') ?></textarea>
                    <div style="text-align:right;font-size:13px;color:#666;margin-top:4px;"><?= L('Max_300_Chars_Tip'); ?></div>
                </div>
            </div>

            <div style="padding-left:112px;gap:10px;display:flex;flex-wrap:wrap;">
                <button type="submit" name="save_profile"><?= L('Save_Changes_Button'); ?></button>
                <button type="button" class="cancel_btn"><?= L('Cancel'); ?></button>
            </div>
        </form>
    </div>
</div>

<div class="clear"></div>

<script>
function togglePwd(id,el){
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    el.innerText = input.type === 'password' ? '👁️' : '🙈';
}
const viewBox = document.querySelector('.view-box');
const editBox = document.querySelector('.edit_box');
document.querySelector('.edit_btn').addEventListener('click', ()=>{
    viewBox.style.display = 'none';
    editBox.style.display = 'block';
});
document.querySelector('.cancel_btn').addEventListener('click', ()=>{
    editBox.style.display = 'none';
    viewBox.style.display = 'block';
});
</script>
<?php
}
?>
<?php require_once __DIR__.'/../foot.php'; ?>
