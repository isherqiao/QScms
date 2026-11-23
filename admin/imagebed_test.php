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
 * @File         imagebed_test.php
 * @CreatedTime  15/11/26 10:35:00
 * @LastEditTime 15/11/26 10:35:00
 * @Description  图床连通性测试端点：拿后台当前表单值（不落盘），上传一张 1x1 PNG，返回 JSON 结果。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['Qsis_adminname']) && empty($_SESSION['Qsis_user_name'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'err' => '请先登录后台']);
    exit;
}

require_once __DIR__ . '/../control/imagebed_lib.php';

$input = isset($_POST['ib']) && is_array($_POST['ib']) ? $_POST['ib'] : [];
$cfg   = qs_imagebed_config_from($input);

$driver = (string)($cfg['driver'] ?? 'local');
if ($driver === '' || $driver === 'local') {
    echo json_encode(['ok' => false, 'err' => '当前只选了本地存储，先选一个图床驱动再测', 'driver' => 'local']);
    exit;
}

// 生成一张 1x1 的 PNG 作为测试图
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
$tmp = sys_get_temp_dir() . '/qscms_imagebed_test_' . mt_rand(1000, 9999) . '.png';
if (@file_put_contents($tmp, $png) === false) {
    echo json_encode(['ok' => false, 'err' => '无法写入临时文件', 'driver' => $driver]);
    exit;
}

$res = qs_imagebed_upload($tmp, 'qscms-imagebed-test-' . date('YmdHis') . '.png', 'image/png', $cfg);
@unlink($tmp);

echo json_encode([
    'ok'     => (bool)($res['ok'] ?? false),
    'url'    => (string)($res['url'] ?? ''),
    'err'    => (string)($res['err'] ?? ''),
    'driver' => (string)($res['driver'] ?? $driver),
    'key'    => (string)($res['key'] ?? ''),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
