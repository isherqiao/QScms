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
 * @File         group_manage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  User group manager: creates, renames and deletes groups and ticks the features each group may use; writes control/data/groups.php.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__.'/../head.php';
//用户组设置：新增 / 重命名 / 删除用户组，并勾选每个组可使用的功能清单
//数据写入 control/data/groups.php，覆盖 permission.php 中的默认组

$groupFile = $datapaths . 'groups.php';
$tip = '';
$tipType = '';
$editingGroup = ''; // 当前正在编辑的组名
$isNew = false;

// ---------- 保存组数据 ----------
function save_groups_file($groups, $groupFile) {
    $code = "<?php\n// 用户组配置（由 user/group_manage.php 维护）\n\$qs_groups = " . var_export($groups, true) . ";\n?>";
    return file_put_contents($groupFile, $code, LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ===== 新增组 =====
    if ($action === 'add') {
        $name = trim($_POST['new_group_name'] ?? '');
        $desc = trim($_POST['new_group_desc'] ?? '');
        if ($name === '') {
            $tip = L('Group_Name_Cannot_Empty'); $tipType = 'error';
        } elseif (isset($qs_groups[$name])) {
            $tip = L('Group_Already_Exists'); $tipType = 'error';
        } else {
            $qs_groups[$name] = ['perms' => [], 'desc' => $desc];
            if (save_groups_file($qs_groups, $groupFile)) {
                $tip = sprintf(L('Group_Created_Perms_Tip'), $name); $tipType = 'success';
                $editingGroup = $name; $isNew = true;
            } else {
                $tip = L('Write_Failed_Check_Data_Dir_Perm'); $tipType = 'error';
            }
        }
    }

    // ===== 保存某组的功能勾选 / 重命名 / 修改说明 =====
    elseif ($action === 'update') {
        $oldName = trim($_POST['old_group_name'] ?? '');
        $newName = trim($_POST['group_name'] ?? '');
        $desc    = trim($_POST['group_desc'] ?? '');
        $perms   = $_POST['perms'] ?? [];
        $perms   = is_array($perms) ? array_values($perms) : [];

        if (!isset($qs_groups[$oldName])) {
            $tip = L('Old_Group_Not_Exist'); $tipType = 'error';
        } elseif ($newName === '') {
            $tip = L('Group_Name_Cannot_Empty'); $tipType = 'error';
        } elseif ($newName !== $oldName && isset($qs_groups[$newName])) {
            $tip = L('New_Group_Name_Exists'); $tipType = 'error';
        } else {
            // 重命名：同步所有用户的 groups 引用
            if ($newName !== $oldName) {
                foreach ($sp_config_users as $uname => &$uinfo) {
                    if (!empty($uinfo['groups']) && is_array($uinfo['groups'])) {
                        foreach ($uinfo['groups'] as $i => $g) {
                            if ($g === $oldName) $uinfo['groups'][$i] = $newName;
                        }
                    }
                }
                unset($uinfo);
            }
            $qs_groups[$oldName]['perms'] = $perms;
            $qs_groups[$oldName]['desc']  = $desc;
            if ($newName !== $oldName) {
                $qs_groups[$newName] = $qs_groups[$oldName];
                unset($qs_groups[$oldName]);
            }
            // 保存组 + 保存用户（可能含组名引用更新）
            $okGroups = save_groups_file($qs_groups, $groupFile);
            $okUsers  = qs_save_users();
            if ($okGroups && $okUsers) {
                $tip = sprintf(L('Group_Saved_Msg'), $newName); $tipType = 'success';
                $editingGroup = $newName;
            } else {
                $tip = L('Write_Failed_Check_Dir_Perm'); $tipType = 'error';
            }
        }
    }

    // ===== 删除组 =====
    elseif ($action === 'delete') {
        $name = trim($_POST['del_group_name'] ?? '');
        if (isset($qs_groups[$name])) {
            // 从所有用户的所属组中移除
            foreach ($sp_config_users as $uname => &$uinfo) {
                if (!empty($uinfo['groups']) && is_array($uinfo['groups'])) {
                    $uinfo['groups'] = array_values(array_diff($uinfo['groups'], [$name]));
                }
            }
            unset($uinfo);
            unset($qs_groups[$name]);
            $okGroups = save_groups_file($qs_groups, $groupFile);
            $okUsers  = qs_save_users();
            if ($okGroups && $okUsers) {
                $tip = sprintf(L('Group_Deleted_Msg'), $name); $tipType = 'success';
            } else {
                $tip = L('Write_Failed_Check_Dir_Perm'); $tipType = 'error';
            }
        } else {
            $tip = L('Group_Not_Exist'); $tipType = 'error';
        }
    }
}

// 进入编辑模式的组
$editingGroup = $editingGroup !== '' ? $editingGroup : ($_GET['edit'] ?? '');
$editPerms = isset($qs_groups[$editingGroup]) ? $qs_groups[$editingGroup]['perms'] : [];

// 统计各组成员数
$memberCount = [];
foreach ($sp_config_users as $uname => $uinfo) {
    foreach (qs_user_groups($uname) as $g) {
        $memberCount[$g] = ($memberCount[$g] ?? 0) + 1;
    }
}
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

        <?php if ($editingGroup !== '' && isset($qs_groups[$editingGroup])): ?>
        <h2><?= sprintf(L('Edit_Group_Title'), htmlspecialchars($editingGroup, ENT_QUOTES, 'UTF-8')); ?></h2>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="old_group_name" value="<?= htmlspecialchars($editingGroup, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="group_name"><?= L('Group_Name'); ?>：</label>
                <input type="text" id="group_name" name="group_name" value="<?= htmlspecialchars($editingGroup, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="group_desc"><?= L('Description_Label'); ?>：</label>
                <input type="text" id="group_desc" name="group_desc" value="<?= htmlspecialchars($qs_groups[$editingGroup]['desc'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label><?= L('Available_Perms_Label'); ?></label>
                <div style="margin-top:6px;">
                    <?php foreach ($qs_perm_groups as $cat => $items): ?>
                        <fieldset style="border:1px solid #dde2e7;border-radius:6px;padding:10px 14px;margin-bottom:12px;">
                            <legend style="font-weight:bold;padding:0 8px;"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></legend>
                            <?php foreach ($items as $key => $label): ?>
                                <label style="display:inline-flex;align-items:center;gap:4px;width:32%;margin:4px 0;font-size:13px;">
                                    <input type="checkbox" name="perms[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= in_array($key, $editPerms) || in_array('*', $editPerms) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
                <div style="font-size:12px;color:#999;"><?= L('All_Perms_Super_Admin_Tip'); ?></div>
            </div>
            <input class="button" type="submit" value="<?= L('Save_Group_Button'); ?>">
            <a href="group_manage.php" style="margin-left:12px;"><?= L('Back_To_Group_List'); ?></a>
        </form>
        <br>
        <form method="post" onsubmit="return confirm('<?= sprintf(L('Confirm_Delete_Group'), htmlspecialchars($editingGroup, ENT_QUOTES, 'UTF-8')); ?>');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="del_group_name" value="<?= htmlspecialchars($editingGroup, ENT_QUOTES, 'UTF-8') ?>">
            <input class="button" type="submit" value="<?= L('Delete_Group_Button'); ?>" style="background:#d9534f;color:#fff;border:none;padding:8px 16px;cursor:pointer;">
        </form>
        <?php else: ?>

        <h2><?= L('Add_Group_Title'); ?></h2>
        <form method="post" style="margin-bottom:24px;">
            <input type="hidden" name="action" value="add">
            <div class="form-group" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <label style="width:auto;"><?= L('Group_Name'); ?>：</label>
                <input type="text" name="new_group_name" placeholder="<?= L('Group_Name_Placeholder'); ?>" style="width:220px;padding:8px;">
                <label style="width:auto;"><?= L('Description_Label'); ?>：</label>
                <input type="text" name="new_group_desc" placeholder="<?= L('Group_Desc_Placeholder'); ?>" style="width:260px;padding:8px;">
                <input class="button" type="submit" value="<?= L('Create_Group_Assign_Perms_Button'); ?>">
            </div>
        </form>

        <h2><?= L('Group_List_Title'); ?></h2>
        <table>
            <tr>
                <th><?= L('Group_Name'); ?></th>
                <th><?= L('Description_Label'); ?></th>
                <th><?= L('Perm_Count_Th'); ?></th>
                <th><?= L('Member_Count_Th'); ?></th>
                <th><?= L('Operations'); ?></th>
            </tr>
            <?php foreach ($qs_groups as $gname => $ginfo): ?>
                <?php $gperms = $ginfo['perms'] ?? []; $isAll = in_array('*', $gperms); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($gname, ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars(L($ginfo['desc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $isAll ? L('All_Perms') : count($gperms) ?></td>
                    <td><?= $memberCount[$gname] ?? 0 ?></td>
                    <td>
                        <a href="group_manage.php?edit=<?= urlencode($gname) ?>"><?= L('Edit_Perms_Link'); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div style="font-size:12px;color:#999;margin-top:8px;"><?= L('Group_List_Help_Tip'); ?></div>
        <?php endif; ?>

        <p style="margin-top:16px;"><a href="perm_manage.php"><?= L('To_Perm_Assign_Link'); ?></a></p>
    </div>
</div>
<div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>
