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
 * @File         show_indexnow_batch.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  IndexNow batch viewer: shows one submission batch identified by a validated filename.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
<?php

$logBase = $datapaths . 'indexnow_log/';
$filename = $_GET['f'] ?? '';
$filename = trim($filename);

//文件名强校验，防目录穿越
if(empty($filename) || !preg_match('/^batch_\d{8}_\d{6}_no\d+\.json$/',$filename)){
    echo "<div class='warn'>".L('Illegal_Filename')."</div>";
    require_once __DIR__.'/../foot.php';
    exit;
}
$fullPath = $logBase.$filename;
if(!file_exists($fullPath)){
    echo "<div class='warn'>".L('Log_File_Not_Exist')."</div>";
    require_once __DIR__.'/../foot.php';
    exit;
}

$raw = file_get_contents($fullPath);
$json = json_decode($raw,true);
if(!isset($json['urlList']) || !is_array($json['urlList'])){
    echo "<div class='warn'>".L('Log_File_Format_Invalid')."</div>";
    require_once __DIR__.'/../foot.php';
    exit;
}

?>

<div id="content_box">
    <h3><?= sprintf(L('IndexNow_Batch_Log_Detail_Title'), htmlspecialchars($filename)); ?></h3>
    <div style="margin:15px 0;">
        <textarea readonly style="width:100%;height:600px;font-family:monospace;font-size:12px;"><?php
        foreach ($json['urlList'] as $u){
            echo htmlspecialchars($u)."\n";
        }
        ?></textarea>
    </div>
</div>

<?php require_once __DIR__.'/../foot.php'; ?>
