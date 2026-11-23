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
 * @File         payments.php
 * @Description  Donation records management: confirm/delete pending donations.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
require_once __DIR__ . '/../control/payment_lib.php';

// 处理操作
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    if ($id !== '') {
        if ($action === 'confirm') {
            qs_payment_confirm($id);
            $msg = L('Payment_Confirmed');
        } elseif ($action === 'delete') {
            qs_payment_delete($id);
            $msg = L('Payment_Deleted');
        }
    }
}

$list = qs_payments_load();
// 最新在前
usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
$pending = array_filter($list, fn($r) => ($r['status'] ?? '') === 'pending');
$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<title><?= L('Payment_Admin_Title') ?></title>

<div class="admin_page_name">
<?= L('Payment_Admin_Title') ?>
<?php if (count($pending) > 0): ?>
<span style="display:inline-block;background:#e53935;color:#fff;border-radius:10px;padding:1px 10px;font-size:13px;margin-left:8px;"><?= count($pending) ?> <?= L('Payment_Pending') ?></span>
<?php endif; ?>
</div>

<?php if ($msg): ?><div style="background:#e8f5e9;padding:10px 16px;border-radius:8px;margin-bottom:16px;"><?= $e($msg) ?></div><?php endif; ?>

<div id="fenye">
<ta style="display:block">
<table border="0" width="100%" cellpadding="8">
<tr style="border-bottom:2px solid #e5e7eb;">
    <th align="left"><?= L('Payment_Donor_Name') ?></th>
    <th align="left"><?= L('Payment_Donor_Amount') ?></th>
    <th align="left"><?= L('Payment_Donor_Msg') ?></th>
    <th align="left"><?= L('Payment_Method') ?></th>
    <th align="left"><?= L('Payment_Time') ?></th>
    <th align="left"><?= L('Payment_Status') ?></th>
    <th align="left"><?= L('Payment_Actions') ?></th>
</tr>
<?php if (empty($list)): ?>
<tr><td colspan="7" style="text-align:center;color:#999;padding:30px;"><?= L('Payment_No_Records') ?></td></tr>
<?php else: ?>
<?php foreach ($list as $r): ?>
<tr style="border-bottom:1px solid #eee; <?= ($r['status'] ?? '')==='pending' ? 'background:#fff8e1;' : '' ?>">
    <td><?= $e($r['nickname'] ?: L('Payment_Anonymous')) ?></td>
    <td><?= $r['amount']!=='' ? '¥'.$e($r['amount']) : '—' ?></td>
    <td style="max-width:200px;"><?= $e($r['message'] ?: '—') ?></td>
    <td><?= $e($r['method'] ?? '') ?></td>
    <td style="white-space:nowrap;"><?= $e($r['created_at'] ?? '') ?></td>
    <td>
        <?php if (($r['status'] ?? '') === 'confirmed'): ?>
            <span style="color:#2e7d32;"><?= L('Payment_Status_Confirmed') ?></span>
        <?php else: ?>
            <span style="color:#e65100;"><?= L('Payment_Status_Pending') ?></span>
        <?php endif; ?>
    </td>
    <td style="white-space:nowrap;">
        <?php if (($r['status'] ?? '') !== 'confirmed'): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="confirm">
            <input type="hidden" name="id" value="<?= $e($r['id']) ?>">
            <button type="submit" style="padding:4px 12px;background:#2e7d32;color:#fff;border:none;border-radius:4px;cursor:pointer;"><?= L('Payment_Confirm_Btn') ?></button>
        </form>
        <?php endif; ?>
        <form method="post" style="display:inline;" onsubmit="return confirm('<?= L('Payment_Delete_Confirm') ?>');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $e($r['id']) ?>">
            <button type="submit" style="padding:4px 12px;background:#c62828;color:#fff;border:none;border-radius:4px;cursor:pointer;"><?= L('Delete') ?></button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>
</ta>
</div>
<div class="clear"></div>
<?php require 'foot.php' ?>
