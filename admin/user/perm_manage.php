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
 * @File         perm_manage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Permission assignment: attaches groups and individual extra features to one user and writes control/data/confp.php.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__.'/../head.php';
//功能分配：给具体用户加组 / 勾选个人功能（追加与禁用）
//写入 control/data/confp.php

$tip = '';
$tipType = '';
$editUser = trim($_GET['edit'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $editUser = trim($_POST['edit_user'] ?? '');
    if ($editUser === '' || !isset($sp_config_users[$editUser])) {
        $tip = L('User_Not_Exist'); $tipType = 'error';
    } else {
        // 所属用户组
        $groups = $_POST['groups'] ?? [];
        $groups = is_array($groups) ? array_values(array_filter($groups, 'strlen')) : [];
        // 个人追加功能
        $extra = $_POST['extra_perms'] ?? [];
        $extra = is_array($extra) ? array_values(array_filter($extra, 'strlen')) : [];
        // 个人禁用功能（明确去掉）
        $deny = $_POST['deny_perms'] ?? [];
        $deny = is_array($deny) ? array_values(array_filter($deny, 'strlen')) : [];

        // 追加与禁用不能同时勾选同一功能
        $extra = array_values(array_diff($extra, $deny));

        $sp_config_users[$editUser]['groups'] = $groups;
        $sp_config_users[$editUser]['perms'] = $extra;
        $sp_config_users[$editUser]['deny_perms'] = $deny;

        if (qs_save_users()) {
            $tip = sprintf(L('Perms_Saved_Msg'), $editUser); $tipType = 'success';
        } else {
            $tip = L('Write_Failed_Check_Data_Dir_Perm'); $tipType = 'error';
        }
    }
}

$editUserInfo = ($editUser !== '' && isset($sp_config_users[$editUser])) ? $sp_config_users[$editUser] : null;
$userGroups = $editUserInfo ? qs_user_groups($editUser) : [];
$userExtra  = isset($editUserInfo['perms']) && is_array($editUserInfo['perms']) ? $editUserInfo['perms'] : [];
$userDeny   = isset($editUserInfo['deny_perms']) && is_array($editUserInfo['deny_perms']) ? $editUserInfo['deny_perms'] : [];
$userEff    = $editUserInfo ? qs_effective_perms($editUser) : [];
?>

<div class="admin_page_name">
  <a href="<?= QS_ADMIN_URL ?>/set.php"><?= L('system_settings'); ?></a>
  <a href="user_manage.php"><?= L('manage_user'); ?></a>
  <a href="group_manage.php"><?= L('User_Group_Settings'); ?></a>
  <a href="perm_manage.php"><?= L('Perm_Assign_Page'); ?></a>
</div>

<div id="fenye">
    <div class="container">
        <?php if ($tip): ?>
            <div class="alert <?= $tipType ?>"><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($editUserInfo): ?>
        <h2><?= sprintf(L('Perm_Assign_Title'), htmlspecialchars($editUser, ENT_QUOTES, 'UTF-8'), htmlspecialchars($editUserInfo['displayname'] ?? '', ENT_QUOTES, 'UTF-8')); ?></h2>
        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="edit_user" value="<?= htmlspecialchars($editUser, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label><?= L('User_Groups_Label_Multi'); ?></label>
                <div style="margin-top:6px;">
                    <?php foreach ($qs_groups as $gname => $ginfo): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                            <input type="checkbox" name="groups[]" value="<?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>"
                                <?= in_array($gname, $userGroups) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?>
                            <?php if (in_array('*', $ginfo['perms'] ?? [])): ?><span style="color:#c0392b;"><?= L('All_Perms_Bracket'); ?></span><?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label><?= L('Extra_Perms_Label_Full'); ?></label>
                <div style="margin-top:6px;">
                    <?php foreach ($qs_perm_groups as $cat => $items): ?>
                        <fieldset style="border:1px solid #dde2e7;border-radius:6px;padding:10px 14px;margin-bottom:12px;">
                            <legend style="font-weight:bold;padding:0 8px;"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></legend>
                            <?php foreach ($items as $key => $label): ?>
                                <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;"
                                    title="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="checkbox" name="extra_perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= in_array($key, $userExtra) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label><?= L('Deny_Perms_Label_Full'); ?></label>
                <div style="margin-top:6px;">
                    <?php foreach ($qs_perm_groups as $cat => $items): ?>
                        <fieldset style="border:1px solid #f0d6d6;border-radius:6px;padding:10px 14px;margin-bottom:12px;background:#fffafa;">
                            <legend style="font-weight:bold;padding:0 8px;color:#a94442;"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></legend>
                            <?php foreach ($items as $key => $label): ?>
                                <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                                    <input type="checkbox" name="deny_perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= in_array($key, $userDeny) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label><?= L('Effective_Perms_Preview_Label'); ?></label>
                <div style="margin-top:6px;padding:10px;background:#f5f7fa;border-radius:6px;font-size:13px;">
                    <?php if (in_array('*', $userEff)): ?>
                        <strong style="color:#c0392b;"><?= L('All_Perms_Super_Admin'); ?></strong>
                    <?php elseif ($userEff): ?>
                        <?php foreach ($qs_perm_groups as $cat => $items): ?>
                            <?php $hit = array_intersect($userEff, array_keys($items)); ?>
                            <?php if ($hit): ?>
                                <div style="margin-bottom:6px;">
                                    <strong><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>：</strong>
                                    <?php foreach ($hit as $k): ?>
                                        <span class="eff-tag" style="display:inline-block;background:#e8f0fe;color:#1a56a8;border-radius:4px;padding:1px 6px;margin-right:6px;"><?= htmlspecialchars($qs_perms[$k]['name'] ?? $k, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:#999;"><?= L('No_Perms_Tip'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <input class="button" type="submit" value="<?= L('Save_Perm_Assign_Button'); ?>">
            <a href="perm_manage.php" style="margin-left:12px;"><?= L('Back_To_User_List'); ?></a>
        </form>
        <?php else: ?>

        <h2><?= L('Perm_Assign_By_User_Title'); ?></h2>
        <table>
            <tr>
                <th><?= L('Username'); ?></th>
                <th><?= L('Display_Name'); ?></th>
                <th><?= L('Groups_Th'); ?></th>
                <th><?= L('Operations'); ?></th>
            </tr>
            <?php foreach ($sp_config_users as $uname => $uinfo): ?>
                <?php $ugs = qs_user_groups($uname); ?>
                <tr>
                    <td><?= htmlspecialchars($uname, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($uinfo['displayname'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($ugs): ?>
                            <?php foreach ($ugs as $g): ?>
                                <span style="display:inline-block;background:#e8f0fe;color:#1a56a8;border-radius:4px;padding:1px 6px;margin-right:4px;"><?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color:#999;"><?= L('No_Group_Legacy_Perm_Tip'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><a href="perm_manage.php?edit=<?= urlencode($uname) ?>"><?= L('Assign_Perms_Add_Group_Link'); ?></a></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div style="font-size:12px;color:#999;margin-top:8px;"><?= L('Perms_Table_Help_Tip'); ?></div>
        <?php endif; ?>

        <p style="margin-top:16px;"><a href="group_manage.php"><?= L('To_Group_Settings_Link'); ?></a></p>
    </div>
</div>
<div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>
