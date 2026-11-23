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
 * @File         liuyandel.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Guestbook: deletes one message by its id from the message store.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once '../control/core.php';

// 执行删除指定ID的留言信息
// 1,获取要删除留言的 id号
$id = $_GET["id"]; // url地址提交都是get方式提交的

// 2,从留言liuyan.txt信息文件中获取留言信息
$file = $datapaths.qs_liuyan_data_name(); // 获取所有以前的留言
$info = file_get_contents($file); // 获取所有以前的留言

// 3,以@@@符合拆分留言信息为一条一条的（将留言信息以@@@的符号拆分成留言数组）
$lylist = explode("@@@", $info);

// 4，使用unset删除指定ID的留言
unset($lylist[$id]);

// 5,还原留言信息为字串，并写回留言文件：liuyan.txt
$ninfo = implode("@@@", $lylist);
file_put_contents("$file", $ninfo);
// 6
echo L('Delete_Success_Msg');
header("Location: liuyanshow.php");
exit;
?>