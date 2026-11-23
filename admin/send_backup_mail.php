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
 * @File         send_backup_mail.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Cron / CLI mailer: sends a backup status mail through the configured SMTP account, with a token guard against unauthorised calls.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (php_sapi_name() !== 'cli') {
    exit('Access denied');
}
// cli模式session无效，注释
// session_status() === PHP_SESSION_NONE && session_start();
require_once '../control/verify.php';
require_once('../control/functions.php'); // 需要包含 $qs_config 配置
ini_set('max_execution_time', 300); // 设置最大执行时间 300秒
set_time_limit(300); // 设置脚本最大运行时间
require_once(__DIR__ . '/mail/smtp_config.php');
$logspath = '../control/data/logs/';
define('DATA_LOG_PATH', $logspath);

function log_backup_mail($message) {
    global $qs_config;
    if (empty($qs_config['enable_backup_mail_log'])) {
        return; // 如果配置中关闭了日志，直接返回
    }
    $log_file = __DIR__ . '/../logs/backupsendmail.log';
    $time = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[MAIL][$time] $message" . PHP_EOL, FILE_APPEND);
}

log_backup_mail("开始执行邮件发送命令行。");

$attachment_path = $argv[1] ?? '';
$filename = $argv[2] ?? '';
$tar_email_raw = $argv[3] ?? '';
$admin_user = $argv[4] ?? L('Unknown_Admin');
$tar_name_with_date = $argv[5] ?? '';
$tmp_token_file = $argv[6] ?? '';

//=================防护3 token校验 放在这里=================
if (empty($tmp_token_file) || !file_exists($tmp_token_file)) {
    log_backup_mail("【安全校验失败】临时token文件不存在，拒绝执行");
    exit("Token invalid\n");
}
//消费完成，立刻删除，只能使用一次
@unlink($tmp_token_file);

//附件路径白名单校验
$backupRealBase = realpath(__DIR__.'/../admin');
$realAttach = realpath($attachment_path);
if(!$realAttach || strpos($realAttach, $backupRealBase)!==0){
    log_backup_mail("【安全校验失败】附件路径非法：{$attachment_path}");
    exit("Illegal attachment path\n");
}
//========================================================

log_backup_mail("接收到参数：文件路径=$attachment_path，文件名=$filename，收件人=$tar_email_raw，操作人={$admin_user}，备份包={$tar_name_with_date}");

if (!$filename || str_contains($filename, '@')) {
    $tar_email_raw = $argv[2] ?? '';
    $filename = basename($attachment_path);
    log_backup_mail("自动解析出文件名：$filename");
}

// 多个收件人用逗号分隔
$recipients = array_map('trim', explode(',', $tar_email_raw));
log_backup_mail("解析后的收件人：" . implode(', ', $recipients));

if (!file_exists($attachment_path)) {
    log_backup_mail("错误：附件不存在 $attachment_path");
    write_operate_log($admin_user, L('Backup_Mail_Send_Failed'), L('Backup_Management'), sprintf(L('Log_Detail_Attachment_Missing'), $tar_name_with_date, implode(',',$recipients)));
    exit("Attachment not found: $attachment_path\n");
}

$default_smtp = null;
foreach ($smtp_accounts as $conf) {
    if (!empty($conf['mail_backup'])) {
        $default_smtp = $conf;
        break;
    }
}
if (!$default_smtp) {
    log_backup_mail("错误：未设置备份任务SMTP账号，请在SMTP账号管理配置备份角色(mail_backup)");
    write_operate_log($admin_user, L('Backup_Mail_Send_Failed'), L('Backup_Management'), sprintf(L('Log_Detail_No_Backup_SMTP'), $tar_name_with_date, implode(',',$recipients)));
    exit("No backup smtp\n");
}

log_backup_mail("使用SMTP账号：" . $default_smtp['user']);

$subject = sprintf(L('Mail_Subject_Package_Done'), $filename);
$body = sprintf(L('Mail_Body_Package_Success'), date("Y-m-d H:i:s"));
$attachment = [
    'name' => basename($attachment_path),
    'tmp_name' => $attachment_path,
    'error' => 0,
    'size' => filesize($attachment_path),
];

log_backup_mail("准备发送邮件……");
$result = send_smtp_mail($default_smtp, $recipients, $subject, $body, $attachment, 1, false);

// 根据发送结果分别记录操作日志
if ($result === true) {
    write_operate_log($admin_user, L('Backup_Mail_Sent_Success'), L('Backup_Management'), sprintf(L('Log_Detail_Backup_Sent'), $tar_name_with_date, implode(',', $recipients)));
} else {
    write_operate_log($admin_user, L('Backup_Mail_Send_Failed'), L('Backup_Management'), sprintf(L('Log_Detail_Backup_Sent'), $tar_name_with_date, implode(',', $recipients)));
}

log_backup_mail("邮件发送结果：" . var_export($result, true));