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

// ===== 数据读写 =====
function qs_payments_file() {
    return __DIR__ . '/../data/payments.php';
}

function qs_payments_load() {
    $f = qs_payments_file();
    if (!file_exists($f)) return [];
    $data = include $f;
    return is_array($data) ? $data : [];
}

function qs_payments_save($list) {
    $f = qs_payments_file();
    $dir = dirname($f);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $out = "<?php\n// QScms donations record - auto-generated.\nreturn " . var_export($list, true) . ";\n";
    @file_put_contents($f, $out, LOCK_EX);
}

function qs_payment_add($row) {
    $list = qs_payments_load();
    $row['id'] = date('YmdHis') . substr(uniqid(), -4);
    $row['created_at'] = date('Y-m-d H:i:s');
    $row['status'] = 'pending';
    $list[] = $row;
    qs_payments_save($list);
    return $row['id'];
}

function qs_payment_confirm($id) {
    $list = qs_payments_load();
    foreach ($list as &$r) {
        if ($r['id'] === $id) {
            $r['status'] = 'confirmed';
            $r['confirmed_at'] = date('Y-m-d H:i:s');
        }
    }
    unset($r);
    qs_payments_save($list);
}

function qs_payment_delete($id) {
    $list = qs_payments_load();
    $list = array_values(array_filter($list, fn($r) => $r['id'] !== $id));
    qs_payments_save($list);
}

function qs_payments_confirmed() {
    return array_values(array_filter(qs_payments_load(), fn($r) => ($r['status'] ?? '') === 'confirmed'));
}

// ===== 易支付 API（彩虹易支付标准接口）=====

function qs_epay_config() {
    global $qs_config;
    return [
        'on'    => ($qs_config['payment_api_on'] ?? '') === '1',
        'url'   => rtrim((string)($qs_config['payment_api_url'] ?? ''), '/'),
        'pid'   => trim((string)($qs_config['payment_api_pid'] ?? '')),
        'key'   => trim((string)($qs_config['payment_api_key'] ?? '')),
    ];
}

function qs_epay_sign($params) {
    $cfg = qs_epay_config();
    // 去掉 sign 和 sign_type，按键名排序
    unset($params['sign'], $params['sign_type']);
    ksort($params);
    $pairs = [];
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) continue;
        $pairs[] = $k . '=' . $v;
    }
    return md5(implode('&', $pairs) . $cfg['key']);
}

function qs_epay_create_order($money, $type, $name, $nickname = '', $message = '') {
    $cfg = qs_epay_config();
    if (!$cfg['on'] || $cfg['url'] === '' || $cfg['pid'] === '' || $cfg['key'] === '') {
        return ['code' => -1, 'msg' => '支付接口未配置'];
    }
    $outTradeNo = date('YmdHis') . substr(uniqid(), -6);
    $siteUrl = rtrim((string)($GLOBALS['qs_config']['site_link'] ?? ''), '/');
    $slug = trim((string)($GLOBALS['qs_config']['payment_slug'] ?? 'donate'));
    if ($slug === '') $slug = 'donate';
    $notifyUrl = $siteUrl . '/index.php?pay_notify/';
    $returnUrl = $siteUrl . '/index.php?' . $slug . '&done=1';

    $params = [
        'pid'          => $cfg['pid'],
        'type'         => $type,
        'out_trade_no' => $outTradeNo,
        'notify_url'   => $notifyUrl,
        'return_url'   => $returnUrl,
        'name'         => $name,
        'money'        => number_format((float)$money, 2, '.', ''),
    ];
    $params['sign'] = qs_epay_sign($params);
    $params['sign_type'] = 'MD5';

    // 调用 mapi.php 获取 JSON 响应
    $url = $cfg['url'] . '/mapi.php?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'QScms-Payment/1.0',
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$resp) {
        return ['code' => -2, 'msg' => '支付接口请求失败'];
    }
    $json = json_decode($resp, true);
    if (!is_array($json)) {
        return ['code' => -3, 'msg' => '支付接口返回异常: ' . substr($resp, 0, 200)];
    }
    if (($json['code'] ?? 0) != 1) {
        return ['code' => -4, 'msg' => $json['msg'] ?? '创建订单失败'];
    }

    // 存记录为 pending
    qs_payment_add([
        'nickname'  => $nickname,
        'amount'    => $params['money'],
        'message'   => $message,
        'method'    => $type,
        'out_trade_no' => $outTradeNo,
    ]);

    return [
        'code' => 1,
        'qrcode' => $json['qrcode'] ?? ($json['url'] ?? ''),
        'url'    => $json['url'] ?? '',
        'out_trade_no' => $outTradeNo,
    ];
}

function qs_epay_verify_notify() {
    $cfg = qs_epay_config();
    if (!$cfg['on']) return false;
    $params = array_merge($_GET, $_POST);
    if (empty($params['sign'])) return false;
    $sign = qs_epay_sign($params);
    if ($sign !== $params['sign']) return false;
    if (($params['trade_status'] ?? '') !== 'TRADE_SUCCESS') return false;
    return $params;
}

function qs_epay_mark_paid($outTradeNo) {
    $list = qs_payments_load();
    $found = false;
    foreach ($list as &$r) {
        if (($r['out_trade_no'] ?? '') === $outTradeNo) {
            $r['status'] = 'confirmed';
            $r['confirmed_at'] = date('Y-m-d H:i:s');
            $found = true;
        }
    }
    unset($r);
    if ($found) qs_payments_save($list);
    return $found;
}

// ===== 配置判断 =====
function qs_payment_enabled() {
    global $qs_config;
    if (!is_array($qs_config)) return false;
    if (($qs_config['payment_on'] ?? '') !== '1') return false;
    $hasAlipay = trim((string)($qs_config['payment_alipay_qr'] ?? '')) !== '' || trim((string)($qs_config['payment_alipay_url'] ?? '')) !== '';
    $hasWechat = trim((string)($qs_config['payment_wechat_qr'] ?? '')) !== '' || trim((string)($qs_config['payment_wechat_url'] ?? '')) !== '';
    $hasPaypal = trim((string)($qs_config['payment_paypal_url'] ?? '')) !== '';
    return $hasAlipay || $hasWechat || $hasPaypal;
}

function qs_qr_url($data, $size = 300) {
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . (int)$size . 'x' . (int)$size . '&data=' . urlencode($data);
}

// ===== 打赏页 HTML =====
function qs_payment_html() {
    global $qs_config;
    // API 模式：走交互式页面
    if (($qs_config['payment_api_on'] ?? '') === '1') {
        return qs_payment_api_html();
    }
    $title = trim((string)($qs_config['payment_page_title'] ?? ''));
    if ($title === '') $title = L('Payment_Default_Title');
    $intro = (string)($qs_config['payment_intro'] ?? '');
    $thanks = (string)($qs_config['payment_thanks'] ?? '');
    $returnUrl = trim((string)($qs_config['payment_return_url'] ?? ''));
    $site = trim((string)($qs_config['site_name'] ?? ''));
    $copy = trim((string)($qs_config['footer_copyright'] ?? ''));
    $icp = trim((string)($qs_config['icp_no'] ?? ''));
    $home = trim((string)($qs_config['site_directory'] ?? '/'));
    if ($home === '') $home = '/';
    if ($returnUrl === '') $returnUrl = $home;
    $paySlug = trim((string)($qs_config['payment_slug'] ?? ''));
    if ($paySlug === '') $paySlug = 'donate';
    $donorsOn = ($qs_config['payment_donors_on'] ?? '') === '1';
    $donorsSlug = trim((string)($qs_config['payment_donors_slug'] ?? 'donors'));
    if ($donorsSlug === '') $donorsSlug = 'donors';

    $lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
    $lang = preg_replace('/^lang_|\.php$/i', '', $lang);
    $lang = str_replace('_', '-', $lang);
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

    $isDone = isset($_GET['done']) && $_GET['done'] == 1;
    $backHome = L('Payment_Back_Home');
    $paidBtn = L('Payment_Paid_Btn');
    $thanksDef = L('Payment_Thanks_Default');
    $doneTitle = L('Payment_Done_Title');
    $doneBackBtn = L('Payment_Done_Back');
    $scanTip = L('Payment_Scan_Tip');

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= $isDone ? $e($doneTitle) : $e($title) ?><?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
:root{--ink:#22252a;--sub:#6b7280;--line:#e5e7eb;--paper:#fff;--bg:#f6f7f8;--blue:#2f7ed8}
*{box-sizing:border-box}html{-webkit-text-size-adjust:100%}
body{margin:0;padding:32px 16px 48px;background:var(--bg);color:var(--ink);font:400 16px/1.85 -apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif}
.wrap{max-width:720px;margin:0 auto}.card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:40px 44px 36px}
h1{font-size:24px;line-height:1.4;margin:0 0 10px;font-weight:600;text-align:center}
.intro{color:var(--sub);font-size:15px;text-align:center;margin:0 0 28px}
.methods{display:flex;flex-wrap:wrap;gap:24px;justify-content:center}.method{text-align:center}
.method .qr-box{width:220px;height:220px;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center}
.method .qr-box img{width:100%;height:100%;object-fit:contain}.method .m-name{margin-top:10px;font-size:15px;font-weight:600}
.method .m-link{margin-top:2px;font-size:12px;word-break:break-all}.method .m-link a{color:var(--sub);text-decoration:none}
.scan-tip{text-align:center;color:var(--sub);font-size:13px;margin:20px 0}
.paid-area{text-align:center;margin-top:12px}
.paid-btn{display:inline-block;padding:11px 32px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer;text-decoration:none}
.paid-btn:hover{background:#256bb8}
.donor-form{max-width:420px;margin:24px auto 0}
.donor-form label{display:block;font-size:14px;color:var(--sub);margin:12px 0 4px;text-align:left}
.donor-form input,.donor-form textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-size:15px;font-family:inherit}
.donor-form textarea{resize:vertical;min-height:60px}
.foot{margin-top:26px;padding-top:18px;border-top:1px solid var(--line);color:var(--sub);font-size:13px;display:flex;flex-wrap:wrap;gap:6px 16px;align-items:center;justify-content:center}
.foot a{color:var(--sub);text-decoration:none}.foot a:hover{color:var(--ink)}
.done-icon{width:72px;height:72px;margin:0 auto 16px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center}
.done-icon svg{width:38px;height:38px;fill:#2e7d32}
.done-body{text-align:center;color:var(--ink);font-size:15px;line-height:1.9;margin:16px 0 24px}
.done-actions{text-align:center;margin-bottom:8px}
.done-btn{display:inline-block;padding:11px 32px;background:var(--blue);color:#fff;border-radius:8px;font-size:15px;text-decoration:none}
.done-btn:hover{background:#256bb8}
.donors-link{text-align:center;margin-top:20px}.donors-link a{color:var(--blue);font-size:14px;text-decoration:none}
@media(max-width:640px){body{padding:16px 10px 32px}.card{padding:26px 18px 22px;border-radius:10px}h1{font-size:20px}.methods{gap:18px}.method .qr-box{width:180px;height:180px}}
</style>
</head>
<body>
<div class="wrap"><article class="card">
<?php if ($isDone): ?>
<div class="done-icon"><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
<h1><?= $e($doneTitle) ?></h1>
<div class="done-body"><?php if ($thanks !== ''): ?><?= nl2br($e($thanks)) ?><?php else: ?><?= $e($thanksDef) ?><?php endif; ?></div>
<div class="done-actions"><a class="done-btn" href="<?= $e($returnUrl) ?>"><?= $e($doneBackBtn) ?></a></div>
<?php if ($donorsOn): ?><div class="donors-link"><a href="?<?= $e($donorsSlug) ?>/"><?= L('Payment_View_Donors') ?></a></div><?php endif; ?>
<?php else: ?>
<h1><?= $e($title) ?></h1>
<?php if ($intro !== ''): ?><div class="intro"><?= nl2br($e($intro)) ?></div><?php endif; ?>
<?php
$methods = [];
$alipayQr = trim((string)($qs_config['payment_alipay_qr'] ?? ''));
$alipayUrl = trim((string)($qs_config['payment_alipay_url'] ?? ''));
if ($alipayQr !== '' || $alipayUrl !== '') {
    if ($alipayQr !== '') $methods[] = ['name'=>L('Payment_Alipay'),'img'=>$e($alipayQr),'url'=>''];
    else $methods[] = ['name'=>L('Payment_Alipay'),'img'=>qs_qr_url($alipayUrl),'url'=>$e($alipayUrl)];
}
$wechatQr = trim((string)($qs_config['payment_wechat_qr'] ?? ''));
$wechatUrl = trim((string)($qs_config['payment_wechat_url'] ?? ''));
if ($wechatQr !== '' || $wechatUrl !== '') {
    if ($wechatQr !== '') $methods[] = ['name'=>L('Payment_WeChat'),'img'=>$e($wechatQr),'url'=>''];
    else $methods[] = ['name'=>L('Payment_WeChat'),'img'=>qs_qr_url($wechatUrl),'url'=>$e($wechatUrl)];
}
$paypalUrl = trim((string)($qs_config['payment_paypal_url'] ?? ''));
if ($paypalUrl !== '') {
    if (strpos($paypalUrl,'@')!==false && strpos($paypalUrl,'http')!==0) $paypalUrl='https://www.paypal.me/'.$paypalUrl;
    elseif (strpos($paypalUrl,'http')!==0) $paypalUrl='https://'.$paypalUrl;
    $methods[] = ['name'=>L('Payment_PayPal'),'img'=>qs_qr_url($paypalUrl),'url'=>$e($paypalUrl)];
}
?>
<div class="methods">
<?php foreach ($methods as $m): ?>
<div class="method">
<div class="qr-box"><img src="<?= $m['img'] ?>" alt="<?= $e($m['name']) ?>" loading="lazy"></div>
<div class="m-name"><?= $e($m['name']) ?></div>
<?php if (!empty($m['url'])): ?><div class="m-link"><a href="<?= $m['url'] ?>" target="_blank" rel="nofollow noopener"><?= $e($m['url']) ?></a></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<div class="scan-tip"><?= $e($scanTip) ?></div>
<form class="donor-form" method="post" action="?<?= $e($paySlug) ?>/">
<label><?= L('Payment_Donor_Name') ?></label>
<input type="text" name="donor_name" maxlength="30" placeholder="<?= L('Payment_Donor_Name_Ph') ?>">
<label><?= L('Payment_Donor_Amount') ?></label>
<input type="number" name="donor_amount" step="0.01" min="0" placeholder="&#165;">
<label><?= L('Payment_Donor_Msg') ?></label>
<textarea name="donor_message" maxlength="200" placeholder="<?= L('Payment_Donor_Msg_Ph') ?>"></textarea>
<div class="paid-area" style="margin-top:16px;">
<button type="submit" class="paid-btn"><?= $e($paidBtn) ?></button>
</div>
</form>
<?php if ($donorsOn): ?><div class="donors-link"><a href="?<?= $e($donorsSlug) ?>/"><?= L('Payment_View_Donors') ?></a></div><?php endif; ?>
<?php endif; ?>
<div class="foot">
<a href="<?= $e($home) ?>">&larr; <?= $e($backHome) ?></a>
<?php if ($copy !== ''): ?><span><?= $e($copy) ?></span><?php endif; ?>
<?php if ($icp !== ''): ?><span><?= $e($icp) ?></span><?php endif; ?>
</div>
</article></div>
</body></html>
    <?php
    return ob_get_clean();
}

// ===== API 模式交互式打赏页 =====

function qs_payment_api_html() {
    global $qs_config;
    $title = trim((string)($qs_config['payment_page_title'] ?? ''));
    if ($title === '') $title = L('Payment_Default_Title');
    $intro = (string)($qs_config['payment_intro'] ?? '');
    $site = trim((string)($qs_config['site_name'] ?? ''));
    $home = trim((string)($qs_config['site_directory'] ?? '/'));
    if ($home === '') $home = '/';
    $lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
    $lang = preg_replace('/^lang_|\.php$/i', '', $lang);
    $lang = str_replace('_', '-', $lang);
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $slug = trim((string)($qs_config['payment_slug'] ?? 'donate'));
    if ($slug === '') $slug = 'donate';
    $donorsOn = ($qs_config['payment_donors_on'] ?? '') === '1';
    $donorsSlug = trim((string)($qs_config['payment_donors_slug'] ?? 'donors'));

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= $e($title) ?><?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
:root{--ink:#22252a;--sub:#6b7280;--line:#e5e7eb;--paper:#fff;--bg:#f6f7f8;--blue:#2f7ed8;--green:#2e7d32}
*{box-sizing:border-box}body{margin:0;padding:32px 16px 48px;background:var(--bg);color:var(--ink);font:400 16px/1.85 -apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif}
.wrap{max-width:680px;margin:0 auto}.card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:36px 40px 32px}
h1{font-size:22px;text-align:center;margin:0 0 8px;font-weight:600}
.intro{color:var(--sub);font-size:14px;text-align:center;margin:0 0 24px}
.field{margin-bottom:16px}.field label{display:block;font-size:14px;color:var(--sub);margin-bottom:4px}
.field input,.field textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-size:15px;font-family:inherit}
.methods{display:flex;gap:12px;justify-content:center;margin:20px 0}
.method-btn{width:80px;height:80px;border:2px solid var(--line);border-radius:12px;background:#fff;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;transition:border-color .2s;font-size:12px;color:var(--sub)}
.method-btn:hover,.method-btn.active{border-color:var(--blue)}
.method-btn .icon{font-size:28px}
.qr-area{text-align:center;margin:20px 0;display:none}
.qr-area img{max-width:260px;border:1px solid var(--line);border-radius:8px}
.qr-tip{color:var(--sub);font-size:14px;margin-top:10px}
.status-area{text-align:center;margin:16px 0;display:none}
.done-icon{width:64px;height:64px;margin:0 auto 12px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center}
.done-icon svg{width:34px;height:34px;fill:var(--green)}
.btn{display:inline-block;padding:11px 32px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer;text-decoration:none}
.btn:hover{background:#256bb8}
.btn:disabled{background:#aaa;cursor:not-allowed}
.loading{text-align:center;color:var(--sub);padding:20px;display:none}
.foot{margin-top:24px;padding-top:16px;border-top:1px solid var(--line);color:var(--sub);font-size:13px;text-align:center}
.foot a{color:var(--sub);text-decoration:none}
.donors-link{text-align:center;margin-top:16px}.donors-link a{color:var(--blue);font-size:14px;text-decoration:none}
@media(max-width:640px){.card{padding:24px 16px}.methods{gap:8px}.method-btn{width:64px;height:64px}}
</style>
</head>
<body>
<div class="wrap"><article class="card">
<h1><?= $e($title) ?></h1>
<?php if ($intro !== ''): ?><div class="intro"><?= nl2br($e($intro)) ?></div><?php endif; ?>

<div id="step-form">
<div class="field"><label><?= L('Payment_Donor_Name') ?></label><input type="text" id="f_name" maxlength="30" placeholder="<?= L('Payment_Donor_Name_Ph') ?>"></div>
<div class="field"><label><?= L('Payment_Donor_Amount') ?></label><input type="number" id="f_amount" step="0.01" min="0.01" placeholder="&#165;" value="10"></div>
<div class="field"><label><?= L('Payment_Donor_Msg') ?></label><textarea id="f_msg" maxlength="200" placeholder="<?= L('Payment_Donor_Msg_Ph') ?>"></textarea></div>
<div class="methods">
<button class="method-btn" data-type="alipay"><span class="icon" style="color:#1677ff">&#19978;&#20140;&#29260;</span>&#38142;&#25343;&#30333;&#29260;</button>
<button class="method-btn" data-type="wxpay"><span class="icon" style="color:#07c160">&#24494;&#20142;&#20449;&#20431;</span>&#24494;&#20142;&#20449;&#20431;</button>
<button class="method-btn" data-type="qqpay"><span class="icon" style="color:#12b7f5">&#21452;&#31359;&#20449;&#20431;</span>&#21452;&#31359;&#20449;&#20431;</button>
<?php if (($qs_config['payment_paypal_on'] ?? '') === '1'): ?>
<button class="method-btn" data-type="paypal"><span class="icon" style="color:#003087">P</span>PayPal</button>
<?php endif; ?>
<?php if (($qs_config['payment_card_on'] ?? '') === '1'): ?>
<button class="method-btn" data-type="unionpay"><span class="icon" style="color:#d32f2f">&#128179;</span>&#38134;&#32032;/&#38065;&#34892;</button>
<?php endif; ?>
</div>
<div style="text-align:center;color:var(--sub);font-size:13px"><?= L('Payment_Select_Method') ?></div>
</div>

<div class="loading" id="loading"><?= L('Payment_Processing') ?></div>

<div class="qr-area" id="qr_area">
<div id="qr_method" style="font-weight:600;margin-bottom:10px"></div>
<img id="qr_img" src="">
<div class="qr-tip"><?= L('Payment_Scan_Tip') ?></div>
<button class="btn" style="margin-top:14px" onclick="checkPaid()"><?= L('Payment_Paid_Btn') ?></button>
</div>

<div class="status-area" id="success_area">
<div class="done-icon"><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
<h2 style="margin:0 0 8px"><?= L('Payment_Done_Title') ?></h2>
<p style="color:var(--sub)"><?= L('Payment_Thanks_Default') ?></p>
<a class="btn" href="<?= $e($home) ?>"><?= L('Payment_Done_Back') ?></a>
</div>

<div class="foot"><a href="<?= $e($home) ?>">&larr; <?= L('Payment_Back_Home') ?></a></div>
<?php if ($donorsOn): ?><div class="donors-link"><a href="?<?= $e($donorsSlug) ?>/"><?= L('Payment_View_Donors') ?></a></div><?php endif; ?>
</article></div>
<script>
var selectedType='';
document.querySelectorAll('.method-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
        document.querySelectorAll('.method-btn').forEach(function(b){b.classList.remove('active')});
        btn.classList.add('active');
        selectedType=btn.dataset.type;
        if(selectedType==='paypal'){startPayPal();}
        else{createOrder();}
    });
});
function startPayPal(){
    var amt=parseFloat(document.getElementById('f_amount').value);
    if(!amt||amt<0.01){alert('请输入正确金额');return;}
    var name=document.getElementById('f_name').value;
    var msg=document.getElementById('f_msg').value;
    // PayPal.me 链接方式：跳转
    var url='<?= $e($qs_config['payment_paypal_url'] ?? '') ?>';
    if(!url){alert('PayPal 链接未配置');return;}
    // 附加金额参数（PayPal.me 支持 ?amount=）
    if(url.indexOf('?')>-1){url+='&amount='+amt;}else{url+='?amount='+amt;}
    window.open(url,'_blank');
    document.getElementById('step-form').style.display='none';
    document.getElementById('qr_area').style.display='block';
    document.getElementById('qr_method').textContent='PayPal';
    document.getElementById('qr_img').style.display='none';
    document.querySelector('#qr_area .qr-tip').textContent='请在新打开的 PayPal 页面完成付款';
}
function createOrder(){
    var amt=parseFloat(document.getElementById('f_amount').value);
    if(!amt||amt<0.01){alert('请输入正确金额');return;}
    document.getElementById('step-form').style.display='none';
    document.getElementById('loading').style.display='block';
    var fd=new FormData();
    fd.append('ajax_create_order','1');
    fd.append('amount',amt);
    fd.append('pay_type',selectedType);
    fd.append('donor_name',document.getElementById('f_name').value);
    fd.append('donor_message',document.getElementById('f_msg').value);
    fetch('?<?= $e($slug) ?>/',{method:'POST',body:fd})
    .then(function(r){return r.json()})
    .then(function(data){
        document.getElementById('loading').style.display='none';
        if(data.code==1){
            document.getElementById('qr_method').textContent=selectedType==='alipay'?'支付宝扫码':(selectedType==='wxpay'?'微信扫码':(selectedType==='qqpay'?'QQ扫码':'银联/银行卡支付'));
            document.getElementById('qr_img').src=data.qrcode;
            document.getElementById('qr_area').style.display='block';
        }else{
            alert(data.msg||'创建订单失败');
            document.getElementById('step-form').style.display='block';
        }
    })
    .catch(function(){
        document.getElementById('loading').style.display='none';
        alert('网络错误，请重试');
        document.getElementById('step-form').style.display='block';
    });
}
function checkPaid(){
    // 简单跳转到成功页（回调会自动确认）
    document.getElementById('qr_area').style.display='none';
    document.getElementById('success_area').style.display='block';
    // 3秒后刷新捐献列表链接
    setTimeout(function(){location.reload();},3000);
}
</script>
</body></html>
    <?php
    return ob_get_clean();
}

// ===== 捐献列表页 HTML =====
function qs_donors_html() {
    global $qs_config;
    $donorsSlug = trim((string)($qs_config['payment_donors_slug'] ?? 'donors'));
    if ($donorsSlug === '') $donorsSlug = 'donors';
    $title = L('Payment_Donors_Title');
    $site = trim((string)($qs_config['site_name'] ?? ''));
    $home = trim((string)($qs_config['site_directory'] ?? '/'));
    if ($home === '') $home = '/';
    $lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
    $lang = preg_replace('/^lang_|\.php$/i', '', $lang);
    $lang = str_replace('_', '-', $lang);
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $list = qs_payments_confirmed();
    usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    $backHome = L('Payment_Back_Home');
    $donateLink = '?' . (trim((string)($qs_config['payment_slug'] ?? 'donate'))) . '/';
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index,follow">
<title><?= $e($title) ?><?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
:root{--ink:#22252a;--sub:#6b7280;--line:#e5e7eb;--paper:#fff;--bg:#f6f7f8;--blue:#2f7ed8}
*{box-sizing:border-box}
body{margin:0;padding:32px 16px 48px;background:var(--bg);color:var(--ink);font:400 16px/1.85 -apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif}
.wrap{max-width:720px;margin:0 auto}.card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:40px 44px 36px}
h1{font-size:24px;text-align:center;margin:0 0 24px;font-weight:600}
.donor-item{padding:16px 0;border-bottom:1px solid var(--line)}.donor-item:last-child{border-bottom:none}
.donor-head{display:flex;justify-content:space-between;align-items:baseline}
.donor-name{font-weight:600;font-size:15px}.donor-amt{color:#2e7d32;font-weight:600;font-size:15px}
.donor-msg{color:var(--sub);font-size:14px;margin-top:4px}.donor-date{color:#aaa;font-size:12px;margin-top:4px}
.empty{text-align:center;color:var(--sub);padding:30px 0}
.donate-cta{text-align:center;margin-top:24px}
.donate-cta a{display:inline-block;padding:10px 28px;background:var(--blue);color:#fff;border-radius:8px;text-decoration:none;font-size:15px}
.foot{margin-top:26px;padding-top:18px;border-top:1px solid var(--line);color:var(--sub);font-size:13px;display:flex;flex-wrap:wrap;gap:6px 16px;justify-content:center}
.foot a{color:var(--sub);text-decoration:none}
@media(max-width:640px){.card{padding:26px 18px}}
</style>
</head>
<body>
<div class="wrap"><article class="card">
<h1><?= $e($title) ?></h1>
<?php if (empty($list)): ?>
<div class="empty"><?= L('Payment_Donors_Empty') ?></div>
<?php else: ?>
<?php foreach ($list as $d): ?>
<div class="donor-item">
<div class="donor-head">
<span class="donor-name"><?= $e($d['nickname'] ?: L('Payment_Anonymous')) ?></span>
<?php if (!empty($d['amount'])): ?><span class="donor-amt">&#165;<?= $e($d['amount']) ?></span><?php endif; ?>
</div>
<?php if (!empty($d['message'])): ?><div class="donor-msg"><?= nl2br($e($d['message'])) ?></div><?php endif; ?>
<div class="donor-date"><?= $e($d['created_at'] ?? '') ?></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<div class="donate-cta"><a href="<?= $e($donateLink) ?>"><?= L('Payment_Be_Donor') ?></a></div>
<div class="foot"><a href="<?= $e($home) ?>">&larr; <?= $e($backHome) ?></a></div>
</article></div>
</body></html>
    <?php
    return ob_get_clean();
}

// ===== 单方法收款页（每个支付方式独立URL）=====
function qs_payment_single_html($method) {
    global $qs_config;
    $site = trim((string)($qs_config['site_name'] ?? ''));
    $home = trim((string)($qs_config['site_directory'] ?? '/'));
    if ($home === '') $home = '/';
    $lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
    $lang = preg_replace('/^lang_|\.php$/i', '', $lang);
    $lang = str_replace('_', '-', $lang);
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

    $methods = [
        'alipay' => ['name'=>L('Payment_Alipay'),'qr'=>trim((string)($qs_config['payment_alipay_qr']??'')),'url'=>trim((string)($qs_config['payment_alipay_url']??''))],
        'wechat' => ['name'=>L('Payment_WeChat'),'qr'=>trim((string)($qs_config['payment_wechat_qr']??'')),'url'=>trim((string)($qs_config['payment_wechat_url']??''))],
        'paypal' => ['name'=>'PayPal','qr'=>'','url'=>trim((string)($qs_config['payment_paypal_url']??''))],
    ];
    if (!isset($methods[$method])) return '';
    $m = $methods[$method];
    if (empty($m['qr']) && empty($m['url'])) return '';
    $qrImg = $m['qr'] !== '' ? '../' . $e($m['qr']) : qs_qr_url($m['url']);
    $title = $m['name'];
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= $e($title) ?><?= $site!==''?' - '.$e($site):'' ?></title>
<style>
:root{--ink:#22252a;--sub:#6b7280;--line:#e5e7eb;--paper:#fff;--bg:#f6f7f8;--blue:#2f7ed8}
*{box-sizing:border-box}body{margin:0;padding:32px 16px 48px;background:var(--bg);color:var(--ink);font:400 16px/1.85 -apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif}
.wrap{max-width:480px;margin:0 auto}.card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:36px 40px}
h1{font-size:22px;text-align:center;margin:0 0 20px;font-weight:600}
.qr-box{width:260px;height:260px;margin:0 auto;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center}
.qr-box img{width:100%;height:100%;object-fit:contain}
.scan-tip{text-align:center;color:var(--sub);font-size:14px;margin:16px 0 4px}
.m-link{text-align:center;font-size:12px;margin:8px 0 0;word-break:break-all}.m-link a{color:var(--sub);text-decoration:none}
.paid-area{text-align:center;margin-top:20px}
.paid-btn{display:inline-block;padding:11px 32px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer;text-decoration:none}
.paid-btn:hover{background:#256bb8}
.foot{margin-top:24px;padding-top:16px;border-top:1px solid var(--line);color:var(--sub);font-size:13px;text-align:center}.foot a{color:var(--sub);text-decoration:none}
@media(max-width:640px){.card{padding:24px 16px}.qr-box{width:220px;height:220px}}
</style>
</head>
<body>
<div class="wrap"><article class="card">
<h1><?= $e($title) ?></h1>
<div class="qr-box"><img src="<?= $qrImg ?>" alt="<?= $e($title) ?>"></div>
<div class="scan-tip"><?= L('Payment_Scan_Tip') ?></div>
<?php if(!empty($m['url'])): ?><div class="m-link"><a href="<?= $e($m['url']) ?>" target="_blank" rel="nofollow noopener"><?= $e($m['url']) ?></a></div><?php endif; ?>
<div class="paid-area"><a class="paid-btn" href="<?= $e($home) ?>"><?= L('Payment_Paid_Btn') ?></a></div>
<div class="foot"><a href="<?= $e($home) ?>">&larr; <?= L('Payment_Back_Home') ?></a></div>
</article></div>
</body></html>
    <?php
    return ob_get_clean();
}
