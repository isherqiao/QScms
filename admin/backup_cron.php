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
 * @File         backup_cron.php
 * @CreatedTime  20/11/26 12:10:00
 * @LastEditTime 20/11/26 12:10:00
 * @Description  系统备份执行入口：可被系统计划任务（CLI）、外部 HTTP 调用或后台懒触发调用。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (function_exists('date_default_timezone_set')) {
    @date_default_timezone_set('Asia/Shanghai');
}

// 允许客户端断开后继续执行（HTTP 自调用触发时不等待响应）
@ignore_user_abort(true);
@set_time_limit(0);

// CLI（系统计划任务）场景补全 $_SERVER，保证 core.php 能正确加载站点配置
if (PHP_SAPI === 'cli') {
    // 后台目录名取本文件所在目录，改目录名后 CLI 触发依然正确
    $qsCliDirName = basename(str_replace('\\', '/', __DIR__));
    $qsCliRoot = str_replace('\\', '/', dirname(__DIR__));
    $qsCliServer = [
        'DOCUMENT_ROOT'  => $qsCliRoot,
        'SCRIPT_NAME'    => '/' . $qsCliDirName . '/backup_cron.php',
        'PHP_SELF'       => '/' . $qsCliDirName . '/backup_cron.php',
        'REQUEST_URI'    => '/' . $qsCliDirName . '/backup_cron.php',
        'REQUEST_METHOD' => 'GET',
        'HTTP_HOST'      => 'localhost',
        'SERVER_NAME'    => 'localhost',
        'SERVER_PORT'    => '80',
        'REMOTE_ADDR'    => '127.0.0.1',
        'HTTPS'          => '',
    ];
    foreach ($qsCliServer as $qsKey => $qsVal) {
        if (empty($_SERVER[$qsKey])) {
            $_SERVER[$qsKey] = $qsVal;
        }
    }
}

require_once __DIR__ . '/backup_lib.php';

$isCli = (PHP_SAPI === 'cli');

/* ---------- 参数解析 ---------- */
$action = '';
$argvList = [];

if ($isCli) {
    $argvList = isset($argv) && is_array($argv) ? array_slice($argv, 1) : [];
    foreach ($argvList as $a) {
        $a = trim((string)$a);
        if ($a === '--auto') {
            $action = 'auto';
        } elseif ($a === '--run') {
            $action = 'run';
        } elseif ($a === '--ftp-test') {
            $action = 'ftp_test';
        } elseif ($a === '--status') {
            $action = 'status';
        }
    }
    if ($action === '') {
        $action = 'run';
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $action = isset($_GET['action']) ? (string)$_GET['action'] : 'status';
    $token  = isset($_GET['token']) ? (string)$_GET['token'] : '';
    if (!qs_backup_token_check($token)) {
        http_response_code(403);
        echo "403 forbidden: invalid token\n";
        exit;
    }
}

/* ---------- 执行 ---------- */
switch ($action) {
    case 'auto':
        $cfg = qs_backup_cfg();
        if (($cfg['backup_cron_on'] ?? 'off') !== 'on') {
            echo "定时备份未启用，跳过\n";
            break;
        }
        if (!qs_backup_is_due($cfg)) {
            $state = qs_backup_state_get();
            $next = qs_backup_next_run($cfg, (int)$state['last_run']);
            echo "未到执行时间，下次：" . date('Y-m-d H:i:s', $next) . "\n";
            break;
        }
        $r = qs_backup_run('cron');
        echo ($r['ok'] ? '[OK] ' : '[FAIL] ') . $r['msg'] . "\n";
        break;

    case 'run':
        $r = qs_backup_run($isCli ? 'cli' : 'http');
        echo ($r['ok'] ? '[OK] ' : '[FAIL] ') . $r['msg'] . "\n";
        if (!empty($r['removed_files'])) {
            echo "清理：" . implode(', ', $r['removed_files']) . "\n";
        }
        break;

    case 'ftp_test':
        $cfg = qs_backup_cfg();
        if (trim((string)$cfg['backup_ftp_host']) === '') {
            echo '[FAIL] 未配置 FTP 主机';
            break;
        }
        $state = qs_backup_state_get();
        $probe = $state['last_file'] !== '' && is_file($state['last_file']) ? $state['last_file'] : '';
        if ($probe === '') {
            echo '[FAIL] 暂无可上传的备份包，请先执行一次备份再测试';
            break;
        }
        $msg = '';
        $ok = qs_backup_ftp_upload($cfg, $probe, $msg);
        echo ($ok ? '[OK] ' : '[FAIL] ') . $msg;
        break;

    case 'status':
        $cfg = qs_backup_cfg();
        $state = qs_backup_state_get();
        echo "定时备份：" . (($cfg['backup_cron_on'] === 'on') ? '已启用' : '未启用') . "\n";
        echo "频率：" . $cfg['backup_cron_freq'] . " " . $cfg['backup_cron_time'] . "\n";
        if ((int)$state['last_run'] > 0) {
            echo "上次执行：" . date('Y-m-d H:i:s', (int)$state['last_run']) . " (" . $state['last_result'] . ")\n";
            echo "下次执行：" . date('Y-m-d H:i:s', qs_backup_next_run($cfg, (int)$state['last_run'])) . "\n";
        } else {
            echo "上次执行：从未执行\n";
        }
        echo "累计执行：" . (int)$state['run_count'] . " 次\n";
        echo "FTP 上传：" . (($cfg['backup_ftp_on'] === 'on') ? '已启用' : '未启用') . "\n";
        break;

    default:
        echo "未知操作：{$action}\n";
        break;
}
