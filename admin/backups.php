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
 * @File         backups.php
 * @CreatedTime  15/04/24 04:02:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Backup manager: packs the site data directory into a zip archive named with the current date.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>

<div class="admin_page_name">
<?= L('backup_system'); ?>
</div>

<div id="fenye">
<ta style="display:block">
	<div class="small_form small_form2">
<label><input type="checkbox" id="sendEmailCheckbox" value="1"> <?= L('Send_Email_After_Pack'); ?></label>

<button class="button" onclick="packAndDownload()"><?= L('Program_Pack'); ?></button>
<?php
// 定义调用变量
if  (isset($qs_config['tar_name'])){
$tar_name = $qs_config['tar_name'];
$tar_directory = $qs_config['tar_directory'];
$des_directory = $qs_config['des_directory'];
$des_directory1 = $qs_config['des_directory'];
$tar_exclude_directory = $qs_config['tar_exclude_directory'];
$tar_passwd = $qs_config['tar_passwd'];
$tar_email = $qs_config['tar_email'];
$tar_other = $qs_config['tar_other'];

$base_dir = dirname(__FILE__);
$des_directory = dirname($base_dir) .$des_directory;   // ../admin/
$tar_directory = dirname($base_dir) .$tar_directory;
$tar_exclude_directory = dirname($base_dir) .$tar_exclude_directory;
} else {
echo L('backups_noset');
}

// 处理打包操作（统一走 backup_lib：有 zip 命令则加密，否则回退 ZipArchive）
if (isset($_GET['pack'])) {
    require_once __DIR__ . '/backup_lib.php';

    $bkCfg  = qs_backup_cfg();
    $bkInfo = [];
    $packOk = qs_backup_pack($bkCfg, $bkInfo);
    $tar_name_with_date = $bkInfo['name'] ?? '';

    if (!$packOk) {
        echo '<div class="error" style="padding:10px">'
            . htmlspecialchars((string)($bkInfo['error'] ?? 'pack failed'), ENT_QUOTES) . '</div>';
    } else {
        echo '<br>' . L('has_been_executed') . '<br>';
        echo htmlspecialchars('[method] ' . $bkInfo['method'] . ' | size ' . round($bkInfo['size'] / 1048576, 2) . ' MB | ' . $bkInfo['elapsed'] . 's', ENT_QUOTES);
        if (!empty($bkInfo['warn'])) {
            echo '<br><span style="color:#b3261e">' . htmlspecialchars($bkInfo['warn'], ENT_QUOTES) . '</span>';
        }

        $download_link = $des_directory1 . $tar_name_with_date;

        $log_entry = "$tar_name_with_date - " . date("Y-m-d H:i:s") . " - $des_directory - $download_link" . PHP_EOL;
        file_put_contents($des_directory . "pack_log.txt", $log_entry, FILE_APPEND);
        write_operate_log(qs_get_current_admin_name(), L('Program_Pack'), L('Backup_Management'), sprintf(L('Backup_File'), $tar_name_with_date));

        // 后台发送邮件
        $url_file = $bkInfo['file'];
        $url_file_arg = escapeshellarg($url_file);
        $filename_arg = escapeshellarg($tar_name_with_date);
        $tar_email_arg = escapeshellarg($tar_email);
        $php_path = qs_backup_php_bin();
        $script_path = __DIR__ . '/send_backup_mail.php';

        if (isset($_GET['send_email']) && $_GET['send_email'] == '1') {
            echo '<br>' . htmlspecialchars($url_file, ENT_QUOTES);

            $last_size = 0;
            for ($i = 0; $i < 5; $i++) {
                if (!file_exists($url_file)) { sleep(1); continue; }
                $size = filesize($url_file);
                if ($size > 0 && $size == $last_size) { break; }
                $last_size = $size;
                sleep(1);
            }

            $log_path = escapeshellarg(__DIR__ . '/sendmail_exec.log');
            $admin_arg = escapeshellarg(qs_get_current_admin_name());

            $tmpDir = __DIR__ . '/tmp';
            if (!is_dir($tmpDir)) { @mkdir($tmpDir, 0700, true); }
            $tmp_token_file = $tmpDir . '/mail_' . md5(uniqid(mt_rand(), true)) . '.token';
            file_put_contents($tmp_token_file, date('Y-m-d H:i:s'));
            $token_arg = escapeshellarg($tmp_token_file);

            $cmd = '"' . $php_path . '" "' . $script_path . '" ' . $url_file_arg . ' ' . $filename_arg . ' '
                . $tar_email_arg . ' ' . $admin_arg . ' ' . $filename_arg . ' ' . $token_arg
                . ' >> ' . $log_path . ' 2>&1';

            if (PHP_OS_FAMILY === 'Windows') {
                @pclose(@popen('start /B "" ' . $cmd, 'r'));
            } else {
                @exec($cmd . ' &');
            }

            echo '<br>' . L('Email_Sent_In_Background');
        } else {
            echo '<br>' . L('Backup_Only_No_Attachment_Email');
        }
    }
}
//====本页面处理备份下载，记录日志====
if (isset($_GET['do_download']) && !empty($_GET['f'])) {
    if (!qs_Pass()) {
        exit(L('No_Permission_Download'));
    }
    $f = $_GET['f'];
    $backupRealBase = realpath(dirname(__FILE__) . '/../' . rtrim($qs_config['des_directory'],'/'));
    $realFilePath = realpath(dirname(__FILE__) . '/../' . $f);

    //安全校验：文件必须在备份目录以内，防止跨目录读取任意文件
    if (!$realFilePath || strpos($realFilePath, $backupRealBase) !== 0) {
        write_operate_log(qs_get_current_admin_name(), L('Download_Backup'), L('Backup_Management'), sprintf(L('Download_Failed_Illegal_Path'), $f));
        exit(L('Illegal_Access'));
    }
    if (!file_exists($realFilePath)) {
        write_operate_log(qs_get_current_admin_name(), L('Download_Backup'), L('Backup_Management'), sprintf(L('Download_Failed_File_Not_Exist'), $f));
        exit(L('File_Not_Exist_Msg'));
    }

    //记录下载操作日志
    write_operate_log(qs_get_current_admin_name(), L('Download_Backup'), L('Backup_Management'), sprintf(L('Download_File'), $f));

    //输出文件给浏览器下载
    $outName = basename($realFilePath);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $outName . '"');
    readfile($realFilePath);
    exit; //❗重点：输出文件后直接终止，不会输出页面HTML
}

// 处理删除操作
if (isset($_GET['delete']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    $file_to_delete = $_SERVER['DOCUMENT_ROOT'] . $_GET['file'];
   
    // 删除记录
    $log_file = $des_directory . "pack_log.txt";
    $log_content = file_get_contents($log_file);
    $log_content = str_replace($_GET['delete'], '', $log_content);
    file_put_contents($log_file, $log_content);
    // 删除压缩包文件 
   if (file_exists($file_to_delete)) {
    unlink($file_to_delete);
    echo sprintf(L('File_Deleted_Msg'), $file_to_delete);
    write_operate_log(qs_get_current_admin_name(), L('Delete_Backup'), L('Backup_Management'), sprintf(L('Deleted_Path'), $file));
} else {
    echo sprintf(L('File_Not_Exist_Path'), $file_to_delete);
    write_operate_log(qs_get_current_admin_name(), L('Delete_Backup'), L('Backup_Management'), sprintf(L('Delete_Failed_File_Not_Exist'), $file_to_delete));
}
}
?>
<div id="fileList">
<div class="table">
<div class="table-row header">
<div class="table-cell"><?= L('backupname'); ?></div>
<div class="table-cell"><?= L('tartime'); ?></div>
<div class="table-cell"><?= L('save_posi'); ?></div>
<div class="table-cell"><?= L('down_link'); ?></div>
<div class="table-cell"><?= L('operate'); ?></div>
</div>
<?php
// 显示打包记录列表
$log_file = $des_directory . "pack_log.txt"; // 记录文件路径
// 检查记录文件是否存在
if (file_exists($log_file)) {
    // 读取记录文件内容
    $log_content = file_get_contents($log_file);
    // 按行拆分记录内容
    $log_entries = explode(PHP_EOL, $log_content);
    // 格式化并显示记录列表
    foreach ($log_entries as $entry) {
        if (!empty($entry)) {
            $parts = explode(" - ", $entry);
            $filename = $parts[0];
            $time = $parts[1];
            $location = $parts[2];
            $download_link = $parts[3];
            echo "<div class='table-row'>";
            echo "<div class='table-cell'>$filename</div>";
            echo "<div class='table-cell'>$time</div>";
            echo "<div class='table-cell'>$location</div>";
            echo "<div class='table-cell'><a href='?do_download=1&f=$download_link'>".L('down_link')."</a></div>";

            echo "<div class='table-cell'><a href='?delete=$entry&file=$download_link'>". L('Delete')."</a></div>";
            echo "</div>";
        }
    }
} else {
    echo "<div class='table-row'><div class='table'>".L('not_backup_record')."</div></div>";
}
?>
</div>
</div>
<script>

//function packAndDownload() {
//    window.location.href = "?pack=1";
//}

function packAndDownload() {
    var sendEmail = document.getElementById("sendEmailCheckbox").checked ? "1" : "0";
    window.location.href = "?pack=1&send_email=" + sendEmail;
}

document.addEventListener("keydown", function(event) {
    if (event.keyCode === 116) {
        event.preventDefault();
    }
});

document.addEventListener("contextmenu", function(event) {
    event.preventDefault();
});
</script>

</ta>
<div class="info"></div>
</div>
</div>
<?php require 'foot.php' ?>
