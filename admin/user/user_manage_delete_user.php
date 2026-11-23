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
 * @File         user_manage_delete_user.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Delete-user handler: refuses to remove the administrator, then deletes the account.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>	
<?php
// 获取要删除的用户名
$qsis_username = $_GET['qsis_username'] ?? '';

if (empty($qsis_username)) {
    echo L('Username_Cannot_Empty_Period');
    exit;
}

// 管理员账号不能删除（根据你的逻辑）

// 检查是否存在
if (!isset($sp_config_users[$qsis_username])) {
    echo L('User_Not_Exist_Period');
    exit;
}

// 执行删除
unset($sp_config_users[$qsis_username]);

// 保存修改到 confp.php
$new_content = "<?php\n\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";

if (file_put_contents($datapaths . $config_user, $new_content)) {
    header("Location: user_manage.php"); // 返回用户管理列表页
    exit;
} else {
    echo L('Delete_Failed_Write_Perm');
}
?>
