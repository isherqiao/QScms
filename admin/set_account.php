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
 * @File         set_account.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Account settings panel: changes the administrator credentials.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><b><?= L('Account_Settings'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>

    <!-- 账号字段 -->
    <div class="field">
        <div class="label">
            <?= L('Backend_Account'); ?>
        </div>
        <div class="price">
            <input class="input" placeholder="<?= L('Login'); ?><?= L('Account'); ?>" type="text" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" />
        </div>
        <div class="info"></div>
        <div class="clear"></div>
    </div>
    
    <div class="clear"></div>
    
        <!-- 账号字段 -->
    <div class="field">
        <div class="label">
            <?= L('Display_Account'); ?>
        </div>
        <div class="price">
            <input class="input" placeholder="<?= L('Display_Account'); ?><?= L('Account'); ?>" type="text" name="admin_displayname" value="<?php echo htmlspecialchars($admin_displayname); ?>" />
        </div>
        <div class="info"></div>
        <div class="clear"></div>
    </div>
    
    <div class="clear"></div>
    
        <!-- Ilike 字段 (此处已修复缺失的 </div>) -->
    <div class="field">
        <div class="label"> 
            <?= L('Ilike'); ?>
        </div>
        <div class="price">
        	<input class="input" placeholder="<?= L('C20A'); ?>" onblur="return checkPass();" datatype="*2-16" errormsg="<?= L('C21'); ?>" id="ilike" name="ilike" value='<?php if (@$_POST['ilike'] != '') { echo @$_POST['ilike']; } ?>' />
        </div>
        <div class="info">
            <span id="ilike_chanage" style="display:none; color:#ff0000">#<?= L('C23'); ?>#</span>

        </div>
        <div class="clear"></div>
    </div> 
    
    <!-- 密码字段 -->
    <div class="field">
        <div class="label">
            <?= L('Backend_Password'); ?>
        </div>
        <div class="price">
            <input class="input" placeholder="<?= L('C20'); ?>" onblur="return checkPass();" datatype="*2-16" errormsg="<?= L('C21'); ?>" id="password" name="user_pass" value='<?php if (@$_POST['user_pass'] != '') { echo @$_POST['user_pass']; } ?>' />
        </div>
<div class="info">
    <span id="nick_code_chanage" style="display:none; color:#ff0000"><?= L('C22'); ?></span><span id="password_chanage" style="display:none; color:#ff0000">#<?= L('C23'); ?>#</span><?= L('C24'); ?>
    <?php if (@$_POST['user_pass'] != '') {
	echo L('new_password') . ':' .@$_POST['user_pass'];
}
?>
        </div>
        <div class="info"></div>
    </div>
    
    <div class="clear"></div>

    <!-- 启用用户模式字段 -->
    <div class="field">
        <div class="label">
            <?= L('Enable_User_Mode'); ?>
        </div>
        <div class="price">
            <select class="select-1" name="user_manage" id="user_manage">
                <option value="1" <?php if ($user_manage == "1") { echo "selected"; } ?>><?= L('Enable'); ?></option>
                <option value="0" <?php if ($user_manage == "0") { echo "selected"; } ?>><?= L('Disable'); ?></option>
            </select>
            <?php if ($user_manage == "1"): ?>
                <a href="user/user_manage.php"><?= L('manage_user'); ?></a>
            <?php endif; ?>
        </div>
        <div class="info">
            <?= L('C3'); ?>
        </div>
    </div>

    <div class="clear"></div>
    
    <!-- 角色字段 (条件显示) -->
    <?php if ($user_manage == "1"): ?>
    <div class="field">
        <div class="label">
            <?= L('role'); ?>:
        </div>
        <div class="price">
            <input class="input" placeholder="<?= L('placeholder_role'); ?>" type="text" name="role" value="<?php echo $role; ?>" />
        </div>
        <div class="info">
            <?= L('role'); ?>
        </div>
    </div>
    <?php else: ?>
        <input type="hidden" name="role" value="<?php echo $role; ?>" />
    <?php endif; ?>

    <div class="clear"></div>
    
    <!-- 验证邮箱字段 -->
    <div class="field">
        <div class="label">
            <?= L('verify_email'); ?>:
        </div>
        <div class="price">
            <input class="input" placeholder="<?= L('verify_email'); ?>" type="text" name="verify_email" value="<?php echo $verify_email; ?>" />
        </div>
        <div class="info">
        </div>
    </div>

</div>