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
 * @File         indexnow_loglist.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  IndexNow submission log list: reviews the per-batch log files and deletes a single entry after a strict filename check.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
<?php
$logBase = $datapaths . 'indexnow_log/';
$logFiles = [];

//【新增】删除处理逻辑
$msg = '';
$msgType = '';
//单条删除
if (!empty($_GET['del']) && is_dir($logBase)) {
    $delFile = basename($_GET['del']);
    //严格校验文件名格式，防止任意文件删除
    if (preg_match('/^batch_(\d{8})_(\d{6})_no(\d+)\.json$/', $delFile)) {
        $fullPath = $logBase . $delFile;
        if (is_file($fullPath)) {
            unlink($fullPath);
            $msg = sprintf(L('IndexNow_Log_File_Deleted_Msg'), htmlspecialchars($delFile));
            $msgType = 'success';
        } else {
            $msg = L('File_Not_Exist');
            $msgType = 'error';
        }
    } else {
        $msg = L('Illegal_Filename');
        $msgType = 'error';
    }
}
//清空全部日志
if (!empty($_GET['clearall']) && $_GET['clearall'] === '1' && is_dir($logBase)) {
    $dh = opendir($logBase);
    while (($f = readdir($dh)) !== false) {
        if (preg_match('/^batch_(\d{8})_(\d{6})_no(\d+)\.json$/', $f)) {
            @unlink($logBase . $f);
        }
    }
    closedir($dh);
    $msg = L('IndexNow_Logs_Cleared_Msg');
    $msgType = 'success';
}

//扫描目录，只取出batch_*.json文件
if(is_dir($logBase)){
    $dh = opendir($logBase);
    while( ($f = readdir($dh)) !== false ){
        //匹配批次日志文件名
        if(preg_match('/^batch_(\d{8})_(\d{6})_no(\d+)\.json$/',$f,$m)){
            $fullFile = $logBase . $f;
            $stat = stat($fullFile);
            $logFiles[] = [
                'filename' => $f,
                'time' => $stat['mtime'],
                'dateStr' => date('Y-m-d H:i:s',$stat['mtime'])
            ];
        }
    }
    closedir($dh);
}

//按修改时间倒序，最新日志排在最上面
usort($logFiles,function($a,$b){
    return $b['time'] - $a['time'];
});
?>

<div id="content_box">
    <h3><?= L('IndexNow_Batch_Log_List_Title'); ?></h3>
    <div style="margin:10px 0;color:#666;"><?= L('IndexNow_Log_List_Hint'); ?></div>

    <?php if ($msg): ?>
    <div style="padding:8px 12px;margin:8px 0;<?php echo $msgType==='success'?'background:#e8f5e9;color:#2e7d32;':'background:#ffebee;color:#c62828'; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($logFiles)): ?>
    <div style="margin:8px 0;">
        <a href="?clearall=1" onclick="return confirm('<?= L('Confirm_Clear_All_IndexNow_Logs'); ?>');" style="display:inline-block;padding:4px 10px;background:#d32f2f;color:#fff;text-decoration:none;border-radius:3px;"><?= L('Clear_All_Logs_Button'); ?></a>
    </div>
    <?php endif; ?>

    <table style="width:100%;border-collapse:collapse;margin‑top:10px;">
        <thead>
        <tr style="background:#f5f5f5;">
            <th style="border:1px solid #ccc;padding:8px;text‑align:left;"><?= L('Log_File_Name_Th'); ?></th>
            <th style="border:1px solid #ccc;padding:8px;text‑align:left;"><?= L('Generate_Time_Th'); ?></th>
            <!--【新增操作列】 -->
            <th style="border:1px solid #ccc;padding:8px;text‑align:center;width:100px;"><?= L('Operations'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if(empty($logFiles)): ?>
            <tr>
                <td colspan="3" style="border:1px solid #ccc;padding:12px;color:#888;"><?= L('No_Push_Log'); ?></td>
            </tr>
        <?php else: ?>
            <?php foreach ($logFiles as $item): ?>
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">
                    <a href="show_indexnow_batch.php?f=<?php echo urlencode($item['filename']); ?>">
                        <?php echo htmlspecialchars($item['filename']); ?>
                    </a>
                </td>
                <td style="border:1px solid #ccc;padding:8px;">
                    <?php echo htmlspecialchars($item['dateStr']); ?>
                </td>
                <td style="border:1px solid #ccc;padding:8px;text-align:center;">
                    <!-- 移除onclick确认弹窗，点击直接删除 -->
                    <a href="?del=<?php echo urlencode($item['filename']); ?>" style="color:#c62828;text-decoration:none;"><?= L('Delete'); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__.'/../foot.php'; ?>
