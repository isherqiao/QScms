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

// files/donate.php —— 收款打赏页

if (!function_exists('qs_payment_html')) {
    require_once __DIR__ . '/../control/payment_lib.php';
}

// AJAX: 创建订单（API 模式）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_create_order'])) {
    header('Content-Type: application/json; charset=utf-8');
    $amount = (float)($_POST['amount'] ?? 0);
    $type   = $_POST['pay_type'] ?? 'alipay';
    $nick   = trim((string)($_POST['donor_name'] ?? ''));
    $msg    = trim((string)($_POST['donor_message'] ?? ''));
    if ($amount < 0.01) {
        echo json_encode(['code' => -1, 'msg' => '金额不正确']);
        exit;
    }
    $name = 'Donation - ' . ($GLOBALS['qs_config']['site_name'] ?? '');
    $result = qs_epay_create_order($amount, $type, $name, mb_substr($nick,0,30), mb_substr($msg,0,200));
    echo json_encode($result);
    exit;
}

// 处理表单提交（静态二维码模式）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donor_name'])) {
    global $qs_config;
    $nickname = trim((string)$_POST['donor_name']);
    $amount   = trim((string)$_POST['donor_amount']);
    $message  = trim((string)$_POST['donor_message']);
    $method   = 'unknown';
    if (!empty($qs_config['payment_alipay_qr']) || !empty($qs_config['payment_alipay_url'])) $method = 'alipay';

    qs_payment_add([
        'nickname' => $nickname !== '' ? mb_substr($nickname, 0, 30) : '',
        'amount'   => is_numeric($amount) ? $amount : '',
        'message'  => $message !== '' ? mb_substr($message, 0, 200) : '',
        'method'   => $method,
    ]);

    $slug = trim((string)($qs_config['payment_slug'] ?? 'donate'));
    if ($slug === '') $slug = 'donate';
    header('Location: ?' . $slug . '&done=1');
    exit;
}

echo qs_payment_html();
