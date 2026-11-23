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

// set_payment.php

$pay_on        = $qs_config['payment_on'] ?? '';
$pay_slug      = $qs_config['payment_slug'] ?? 'donate';
$pay_title     = $qs_config['payment_page_title'] ?? '';
$pay_intro     = $qs_config['payment_intro'] ?? '';
$pay_alipay_qr = $qs_config['payment_alipay_qr'] ?? '';
$pay_alipay_url= $qs_config['payment_alipay_url'] ?? '';
$pay_wechat_qr = $qs_config['payment_wechat_qr'] ?? '';
$pay_wechat_url= $qs_config['payment_wechat_url'] ?? '';
$pay_paypal    = $qs_config['payment_paypal_url'] ?? '';
$pay_thanks    = $qs_config['payment_thanks'] ?? '';
$pay_return    = $qs_config['payment_return_url'] ?? '';
$pay_donors_on = $qs_config['payment_donors_on'] ?? '';
$pay_donors_slug = $qs_config['payment_donors_slug'] ?? 'donors';
$pay_api_on    = $qs_config['payment_api_on'] ?? '';
$pay_api_url   = $qs_config['payment_api_url'] ?? '';
$pay_api_pid   = $qs_config['payment_api_pid'] ?? '';
$pay_api_key   = $qs_config['payment_api_key'] ?? '';
$pay_paypal_on = $qs_config['payment_paypal_on'] ?? '';
$pay_paypal_client = $qs_config['payment_paypal_client'] ?? '';
$pay_paypal_mode = $qs_config['payment_paypal_mode'] ?? 'sandbox';
$pay_card_on   = $qs_config['payment_card_on'] ?? '';
$pay_manage_on = $qs_config['payment_manage_on'] ?? '';
$pay_alipay_slug = $qs_config['payment_alipay_slug'] ?? '';
$pay_wechat_slug = $qs_config['payment_wechat_slug'] ?? '';
$pay_paypal_slug = $qs_config['payment_paypal_slug'] ?? '';

if ($pay_slug === '') $pay_slug = 'donate';
$pay_preview = '../index.php?' . $pay_slug . '/';
$donors_preview = '../index.php?' . $pay_donors_slug . '/';
$alipay_preview = $pay_alipay_slug ? '../index.php?' . $pay_alipay_slug . '/' : '';
$wechat_preview = $pay_wechat_slug ? '../index.php?' . $pay_wechat_slug . '/' : '';
$paypal_preview = $pay_paypal_slug ? '../index.php?' . $pay_paypal_slug . '/' : '';
$e = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>
<b><?= L('Payment_Heading'); ?></b>

<style>
.pay-subtabs{display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin:12px 0 20px;flex-wrap:wrap}
.pay-subtab{padding:8px 18px;cursor:pointer;font-size:15px;color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-2px}
.pay-subtab.active{color:#2f7ed8;border-bottom-color:#2f7ed8;font-weight:600}
.pay-pane{display:none}
.pay-pane.active{display:block;padding-top:8px;padding-bottom:20px}
.pay-note{color:#666;font-size:14px;line-height:1.7;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #eee}
</style>

<div class="pay-subtabs">
<div class="pay-subtab active" onclick="payTab(this,0)">&#9881; <?= L('PayTab_Common') ?></div>
<div class="pay-subtab" onclick="payTab(this,1)">&#128273; <?= L('PayTab_Static') ?></div>
<div class="pay-subtab" onclick="payTab(this,2)">&#9889; <?= L('PayTab_Epay') ?></div>
<div class="pay-subtab" onclick="payTab(this,3)">&#128179; PayPal</div>
<div class="pay-subtab" onclick="payTab(this,4)">&#128179; <?= L('PayTab_Card') ?></div>
</div>

<div class="small_form small_form2">

<div class="pay-pane active" id="paypane0">
<div class="field">
    <div class="label"><?= L('Payment_Switch'); ?></div>
    <select name="payment_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Page_Title'); ?></div>
    <input type="text" class="textbox" name="payment_page_title" style="width:300px;" value="<?= $e($pay_title); ?>" placeholder="<?= L('Payment_Page_Title_Ph'); ?>">
    <input type="text" class="textbox" name="payment_slug" style="width:140px;" value="<?= $e($pay_slug); ?>" placeholder="donate">
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Intro'); ?></div>
    <textarea rows="3" class="textbox" name="payment_intro" style="width:100%;" placeholder="<?= L('Payment_Intro_Ph'); ?>"><?= $e($pay_intro); ?></textarea>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Thanks'); ?></div>
    <textarea rows="3" class="textbox" name="payment_thanks" style="width:100%;" placeholder="<?= L('Payment_Thanks_Ph'); ?>"><?= $e($pay_thanks); ?></textarea>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Return_Url'); ?></div>
    <input type="text" class="textbox" name="payment_return_url" style="width:420px;" value="<?= $e($pay_return); ?>" placeholder="/">
</div>
<div class="clear"></div>

<div style="border-top:1px solid #e0e0e0;margin:16px 0;"></div>

<div class="field">
    <div class="label"><?= L('Payment_Donors_Switch'); ?></div>
    <select name="payment_donors_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_donors_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_donors_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Donors_Slug'); ?></div>
    <input type="text" class="textbox" name="payment_donors_slug" style="width:200px;" value="<?= $e($pay_donors_slug); ?>" placeholder="donors">
    <a href="<?= $e($donors_preview); ?>" target="_blank" class="button" style="display:inline-block;padding:6px 14px;margin-left:8px;text-decoration:none;"><?= L('Privacy_Preview'); ?></a>
</div>
<div class="clear"></div>

<div style="border-top:1px solid #e0e0e0;margin:16px 0;"></div>

<div class="field">
    <div class="label"><?= L('PayTab_Manage_On'); ?></div>
    <select name="payment_manage_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_manage_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_manage_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
    <div class="info"><p><?= L('PayTab_Manage_Tip'); ?></p></div>
</div>
<div class="clear"></div>
</div>

<div class="pay-pane" id="paypane1">
<div class="pay-note"><?= L('PayTab_Static_Tip'); ?></div>

<div class="field">
    <div class="label"><?= L('Payment_Alipay'); ?></div>
    <div style="display:block;width:100%;">
    <?php if ($pay_alipay_qr): ?>
    <div style="display:block;margin-bottom:6px;">
        <img src="../<?= $e($pay_alipay_qr); ?>" style="max-width:120px;max-height:120px;border:1px solid #ddd;border-radius:6px;vertical-align:middle;">
        <label style="margin-left:10px;"><input type="checkbox" name="payment_alipay_qr_del" value="1"> <?= L('Payment_Del_QR'); ?></label>
    </div>
    <?php endif; ?>
    <div style="display:block;margin-bottom:6px;"><input type="file" class="textbox" name="payment_alipay_qr" accept="image/*" style="width:300px;"></div>
    <div style="display:block;margin-bottom:8px;"><input type="text" class="textbox" name="payment_alipay_url" style="width:420px;" value="<?= $e($pay_alipay_url); ?>" placeholder="https://qr.alipay.com/xxx"></div>
    <div style="display:block;">
        <input type="text" class="textbox" name="payment_alipay_slug" style="width:200px;" value="<?= $e($pay_alipay_slug); ?>" placeholder="独立页面地址（如 alipay）">
        <?php if ($alipay_preview): ?><a href="<?= $e($alipay_preview); ?>" target="_blank" class="button" style="display:inline-block;padding:6px 14px;margin-left:8px;text-decoration:none;vertical-align:middle;">预览</a><?php endif; ?>
    </div>
    </div>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_WeChat'); ?></div>
    <div style="display:block;width:100%;">
    <?php if ($pay_wechat_qr): ?>
    <div style="display:block;margin-bottom:6px;">
        <img src="../<?= $e($pay_wechat_qr); ?>" style="max-width:120px;max-height:120px;border:1px solid #ddd;border-radius:6px;vertical-align:middle;">
        <label style="margin-left:10px;"><input type="checkbox" name="payment_wechat_qr_del" value="1"> <?= L('Payment_Del_QR'); ?></label>
    </div>
    <?php endif; ?>
    <div style="display:block;margin-bottom:6px;"><input type="file" class="textbox" name="payment_wechat_qr" accept="image/*" style="width:300px;"></div>
    <div style="display:block;margin-bottom:8px;"><input type="text" class="textbox" name="payment_wechat_url" style="width:420px;" value="<?= $e($pay_wechat_url); ?>" placeholder="<?= L('Payment_WeChat_Link_Ph'); ?>"></div>
    <div style="display:block;">
        <input type="text" class="textbox" name="payment_wechat_slug" style="width:200px;" value="<?= $e($pay_wechat_slug); ?>" placeholder="独立页面地址（如 wechat）">
        <?php if ($wechat_preview): ?><a href="<?= $e($wechat_preview); ?>" target="_blank" class="button" style="display:inline-block;padding:6px 14px;margin-left:8px;text-decoration:none;vertical-align:middle;">预览</a><?php endif; ?>
    </div>
    </div>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_PayPal'); ?></div>
    <div style="display:block;width:100%;">
    <div style="display:block;margin-bottom:8px;"><input type="text" class="textbox" name="payment_paypal_url" style="width:420px;" value="<?= $e($pay_paypal); ?>" placeholder="https://www.paypal.me/yourname"></div>
    <div style="display:block;">
        <input type="text" class="textbox" name="payment_paypal_slug" style="width:200px;" value="<?= $e($pay_paypal_slug); ?>" placeholder="独立页面地址（如 paypal）">
        <?php if ($paypal_preview): ?><a href="<?= $e($paypal_preview); ?>" target="_blank" class="button" style="display:inline-block;padding:6px 14px;margin-left:8px;text-decoration:none;vertical-align:middle;">预览</a><?php endif; ?>
    </div>
    </div>
</div>
<div class="clear"></div>
</div>

<div class="pay-pane" id="paypane2">
<div class="pay-note"><?= L('Payment_Api_Tip'); ?></div>

<div class="field">
    <div class="label"><?= L('Payment_Api_On'); ?></div>
    <select name="payment_api_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_api_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_api_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Api_Url'); ?></div>
    <input type="text" class="textbox" name="payment_api_url" style="width:420px;" value="<?= $e($pay_api_url); ?>" placeholder="https://pay.example.com">
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Api_Pid'); ?></div>
    <input type="text" class="textbox" name="payment_api_pid" style="width:200px;" value="<?= $e($pay_api_pid); ?>" placeholder="1000">
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('Payment_Api_Key'); ?></div>
    <input type="text" class="textbox" name="payment_api_key" style="width:420px;" value="<?= $e($pay_api_key); ?>" placeholder="Key">
</div>
<div class="clear"></div>
</div>

<div class="pay-pane" id="paypane3">
<div class="pay-note"><?= L('PayTab_PayPal_Tip'); ?></div>

<div class="field">
    <div class="label"><?= L('PayTab_PayPal_On'); ?></div>
    <select name="payment_paypal_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_paypal_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_paypal_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('PayTab_PayPal_Client'); ?></div>
    <input type="text" class="textbox" name="payment_paypal_client" style="width:420px;" value="<?= $e($pay_paypal_client); ?>" placeholder="Abi...">
</div>
<div class="clear"></div>

<div class="field">
    <div class="label"><?= L('PayTab_PayPal_Mode'); ?></div>
    <select name="payment_paypal_mode" class="textbox" style="width:140px;">
        <option value="sandbox" <?= $pay_paypal_mode === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
        <option value="live" <?= $pay_paypal_mode === 'live' ? 'selected' : ''; ?>>Live</option>
    </select>
</div>
<div class="clear"></div>
</div>

<div class="pay-pane" id="paypane4">
<div class="pay-note">
银行卡/信用卡支付通过「易支付API」通道接入。在上一个选项卡配置好 API 地址、商户ID和密钥后，开启此开关即可在前台显示银联/银行卡支付按钮。支持银联卡、Visa/Mastercard、Apple Pay、Google Pay，具体以你的支付服务商支持范围为准。本站不记录银行卡信息，跳转到支付平台完成付款。
</div>

<div class="field">
    <div class="label"><?= L('PayTab_Card_On'); ?></div>
    <select name="payment_card_on" class="textbox" style="width:140px;">
        <option value="1" <?= $pay_card_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
        <option value="" <?= $pay_card_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
    </select>
</div>
<div class="clear"></div>
</div>

</div>

<script>
function payTab(el,i){
    document.querySelectorAll('.pay-subtab').forEach(function(t){t.classList.remove('active')});
    document.querySelectorAll('.pay-pane').forEach(function(p){p.classList.remove('active')});
    el.classList.add('active');
    document.getElementById('paypane'+i).classList.add('active');
}
</script>