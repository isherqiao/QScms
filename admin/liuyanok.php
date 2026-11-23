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
 * @File         liuyanok.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Guestbook: appends a new message, adding the client IP and timestamp, to the message store.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once '../control/core.php';

if (isset($_POST['submit'])) {
// 执行留言信息添加操作
// 1.获取要添加的留言信息，并且补上其他辅助消息（IP地址，添加时间）
$author = $_POST["author"]; // 获取留言者
$content = $_POST["content"]; // 留言内容
$email = $_POST["email"]; // 留言内容

$ip = $_SERVER["REMOTE_ADDR"]; // IP地址
$addtime = time(); // 添加时间 （时间戳的格式）

// 2.拼装（组装）留言信息
$ly = "{$author}##{$content}##{$email}##{$ip}##{$addtime}@@@";

// 3.将留言信息追加到../files/目录下，具体请到程序后台设置
$file = $datapaths.qs_liuyan_data_name(); // 获取所有以前的留言

if (file_exists($file)) {
   $info = file_get_contents($file); // 获取所有以前的留言
} 
file_put_contents($file , @$info . $ly); // 覆盖式的写入，会对原有数据进行覆盖
}
?>

<html>
 <head>
<meta charset="UTF-8" />
        <title><?= L('Message_Completed'); ?></title>
        <style>    
        </style>
    </head>
    <body>
<h3 align="center" id="p1">
        <h3 align="center" id="p1"><?= L('Message_Success_Thanks'); ?></h3>
        <h3 align="center" id="p1"><?= L('Redirect_Home_After_5_Seconds'); ?></h3>
        <h3 align="center" id="p1"><?= L('Message_Page_Back_Link'); ?></h3>
    </body>
    </html>
<?php
header('refresh:4; url=/?liuyan/');
exit;
?>
