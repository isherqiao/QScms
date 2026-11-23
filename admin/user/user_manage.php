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
 * @File         user_manage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  User list: paged overview of the accounts with roles and quick actions.
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
// 每页显示的记录数
$items_per_page = 10;

// 获取当前页码（默认为第 1 页）
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// 搜索关键词：按用户名/邮箱过滤
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$qparam = $search !== '' ? '&q=' . urlencode($search) : '';
if ($search !== '') {
    $filtered_users = array_filter($sp_config_users, function ($info, $uname) use ($search) {
        $hay = strtolower($uname . ' ' . ($info['email'] ?? ''));
        return strpos($hay, strtolower($search)) !== false;
    }, ARRAY_FILTER_USE_BOTH);
} else {
    $filtered_users = $sp_config_users;
}

// 计算筛选后的用户数量
$total_users = count($filtered_users);
$total_pages = max(1, ceil($total_users / $items_per_page));
if ($page > $total_pages) $page = $total_pages;

// 获取当前页的数据范围
$start = ($page - 1) * $items_per_page;
$current_page_users = array_slice($filtered_users, $start, $items_per_page, true);
 // 保留用户名作为键
?>

<div class="admin_page_name">
  <a href="../set.php"><?= L('system_settings'); ?></a>  <a href="user_manage.php"><?= L('manage_user'); ?></a>
  <a href="group_manage.php"><?= L('User_Group_Settings'); ?></a>  <a href="perm_manage.php"><?= L('Perm_Assign_Page'); ?></a>
</div>

<div id="fenye">
	<ta style="display:block">
<div class="container">
    <form method="get" action="user_manage.php" style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= L('Keyword_Search_Placeholder') ?>">
        <input type="submit" class="button" value="<?= L('Search') ?>">
        <?php if ($search !== ''): ?><a href="user_manage.php"><?= L('Reset_Button') ?></a><?php endif; ?>
    </form>
    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table>
        <tr>
<th><?= L('Email'); ?></th>
<th><?= L('Username'); ?></th>
<th><?= L('Display_Name'); ?></th>
<th><?= L('Groups_Th'); ?></th>
<th><?= L('Description'); ?></th>
<th><?= L('Operations'); ?></th>
        </tr>
        <?php foreach ($current_page_users as $qsis_username => $info): ?>
            <?php $ugs = qs_user_groups($qsis_username); ?>
            <tr>
            	<td><?= htmlspecialchars($info['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($qsis_username, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($info['displayname'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($ugs): ?>
                        <?php foreach ($ugs as $g): ?>
                            <span style="display:inline-block;background:#e8f0fe;color:#1a56a8;border-radius:4px;padding:1px 6px;margin-right:4px;font-size:12px;"><?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:#999;font-size:12px;"><?= L('Legacy_Perm_Bracket'); ?></span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($info['description'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                <a href="user_manage_edit_user.php?qsis_username=<?= urlencode($qsis_username) ?>"><?= L('Edit_User_Info_Link'); ?></a> | 
                <a href="perm_manage.php?edit=<?= urlencode($qsis_username) ?>"><?= L('Assign_Perms_Link'); ?></a> | 
                <a href="user_manage_delete_user.php?qsis_username=<?= urlencode($qsis_username) ?>"><?= L('Delete'); ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
    
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="user_manage.php?page=<?= $page - 1 ?><?= $qparam ?>"><?= L('Previous_Page'); ?></a>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="user_manage.php?page=<?= $page + 1 ?><?= $qparam ?>"><?= L('Next_Page'); ?></a>
        <?php endif; ?>
    </div>

    <a href="user_manage_add_user.php"><?= L('Add_New_User_Link'); ?></a>
</div>

 </div>
    <div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>	