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
 * @File         heartbeat.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Session heartbeat endpoint: reports the login state and the role of the current user.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

session_start();

$isAdmin = !empty($_SESSION['Qsis_adminname']);
$isUser = !empty($_SESSION['Qsis_user_name']);
// 判断登录状态
if ($isAdmin || $isUser) {
    // 用户已登录（管理员或普通用户）
    $_SESSION['LAST_ACTIVITY'] = time(); // 更新活动时间

    // 返回用户角色信息
    echo json_encode([
        'status' => 'active',
        'role' => $isAdmin ? 'admin' : 'user',
        'username' => $isAdmin ? $_SESSION['Qsis_adminname'] : $_SESSION['Qsis_user_name']
    ]);
} else {
    // Session已过期
    session_unset();
    session_destroy();
    echo json_encode(['status' => 'expired']);
}
?>