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
 * @File         liuyanshow.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Guestbook: reads the message store and renders the message list as a table.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php' ?>
<title><?= L('View_messages'); ?></title>

<div class="admin_page_name">
<?= L('View_messages'); ?>
<!-- 小红点提醒 -->
</div>

<div id="liuyan">
<div id="fenye">
<ta style="display:block">
<table border="0" width="100%">
            <?php

// 获取留言信息，解析后输出到表格中
// 1,从留言liuyan.txt信息文件中获取留言信息
$file = $datapaths.qs_liuyan_data_name();
$info = file_get_contents($file);
// 2,取出留言内容最后的三个@@@符号
$info = rtrim($info, "@");//

if (strlen($info)>=8){

// 3,以@@@符合拆分留言信息为一条一条的（将留言信息以@@@的符号拆分成留言数组）
$lylist = explode("@@@", $info);
// 4,遍历留言信息数组，对每条留言做再次解析
foreach ($lylist as $key => $value) {
    $ly = explode("##", $value); // 将每条留言信息以##拆分成每个留言字段
    echo "<div>" . L('name_or_nike') . "：{$ly[0]}</div>";
echo "<div class=del><a href='liuyandel.php?id={$key}'>".L('Delete')."</a></div>";
    echo "<div>". L('Email'). "：{$ly[2]}</div></tr>";
    echo "<tr><div>". L('IP').": {$ly[3]}</div>";
    echo "<div>" . L('Date') .": ". @gmdate("Y-m-d H:i:s", $ly[4] + 8 * 3600) . "</div>"; // 双引号之中是不能使用函数的
 echo "<tr><div colspan=2>" . L('Messages') ."：{$ly[1]}</div></tr>"; // 字符串截取函数
    echo "</tr><tr><div colspan=2><hr></div>";

}
}
?>
        </table>
    </div>
        </div>
    <div class="clear"></div>
<?php require 'foot.php' ?>