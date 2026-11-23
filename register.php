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
 * @File         register.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Front-end user registration: email verification flow, captcha check and account creation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

session_start();
// 调试上线注释
// error_reporting(E_ALL);
// ini_set('display_errors',1);

require_once __DIR__.'/control/core.php';
require __DIR__.'/files/plugins/System/captcha/config.php';

$error = '';
$step = 1; //1=输入邮箱；2=激活成功，设置账号信息
$activateToken = $_GET['activate'] ?? '';
$tempEmail = '';

//===== 处理激活链接，校验token，进入第二步设置账号 =====
if(!empty($activateToken)){
    //session存临时激活token，本会话绑定邮箱
    if(isset($_SESSION['reg_activate_token']) && $_SESSION['reg_activate_token'] === $activateToken
        && !empty($_SESSION['reg_temp_email'])){
        $step = 2;
        $tempEmail = $_SESSION['reg_temp_email'];
    }else{
        // 校验token，session无则拒绝
        $error = L('activate_token_invalid');
    }
}

//===== POST提交处理 =====
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $postStep = (int)($_POST['step'] ?? 1);

    //---------- step1：提交邮箱 ----------
    if($postStep === 1){
        $email = trim($_POST['email'] ?? '');
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $error = L('reg_email_invalid');
        }else{
            //遍历正式用户，检查邮箱是否已存在
            $emailExist = false;
            foreach($sp_config_users as $u){
                if(isset($u['email']) && strcasecmp($u['email'],$email)===0){
                    $emailExist = true;
                    break;
                }
            }
            if($emailExist){
                $error = L('reg_email_already_exist');
                $_SESSION['reg_reset_email'] = $email;
            }else{
                //邮箱不存在：生成激活token，存入session，发激活邮件
                $token = bin2hex(random_bytes(16));
                $_SESSION['reg_activate_token'] = $token;
                $_SESSION['reg_temp_email'] = $email;
                $_SESSION['reg_token_create_time'] = time(); //新增过期时间戳

                $activateUrl = qs_site_link(false)."register.php?activate=".urlencode($token);
                $smtpFile = $controldata . 'mail/smtp_config.php';

                $notify_smtp = null;
                if(file_exists($smtpFile)){
                    require_once $smtpFile;
                    foreach ($smtp_accounts as $smtpItem) {
                        if (!empty($smtpItem['mail_notify'])) {
                            $notify_smtp = $smtpItem;
                            break;
                        }
                    }
if ($notify_smtp) {
    $subject = L('reg_activate_email_subject');
    // 加载激活邮件html模板，替换激活链接占位符
    $mailHtml = load_mail_template('reg_activate.html', [
        '{{ACTIVATE_URL}}' => htmlspecialchars($activateUrl)
    ]);
    if ($mailHtml !== null) {
        // 第6参数传1：开启HTML邮件模式
        send_smtp_mail($notify_smtp, [$email], $subject, $mailHtml, [], false, true);

    }
}

                }
                $step = 1;
                $error = L('reg_send_activate_mail_tip');
            }
        }
    }

//---------- step2：激活成功后，提交用户名密码，创建正式用户 ----------
elseif($postStep === 2){
    if(!isset($_SESSION['reg_activate_token']) || empty($_SESSION['reg_temp_email'])){
        $error = L('activate_token_invalid');
    }else{
        // token过期校验 24小时 86400秒
        if(isset($_SESSION['reg_token_create_time']) && (time() - $_SESSION['reg_token_create_time'])>86400){
            $error = L('activate_token_expire');
            unset($_SESSION['reg_activate_token'],$_SESSION['reg_temp_email'],$_SESSION['reg_token_create_time']);
            $step =1;
        }else{
            $username    = trim($_POST['username'] ?? '');
            $displayname = trim($_POST['displayname'] ?? '');
            $password    = $_POST['password'] ?? '';
            $password2   = $_POST['password2'] ?? '';
            $captcha_input = trim($_POST['captcha'] ?? '');
            $tempEmail = $_SESSION['reg_temp_email'];

            if ($username === '' || $displayname === '' || $password === '') {
                $error = L('reg_fill_all');
            } elseif ($password !== $password2) {
                $error = L('reg_pwd_not_match');
            }
            // 【修复】独立if，不要用elseif，否则关闭验证码的时候后面正则不会跑
            if ($captcha) {
                if ($captcha_input === '') {
                    $error = L('reg_captcha_err');
                } elseif (!function_exists('check_captcha') || !check_captcha($captcha_input)) {
                    $error = L('reg_captcha_err');
                }
            }
            if(empty($error) && !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                $error = L('reg_username_rule');
            }
            if(empty($error) && isset($sp_config_users[$username])){
                $error = L('reg_user_exists');
            }

            if(empty($error)){
                //创建正式用户，新增email字段；默认加入"用户"组，获得最基础权限
                $pwd_hash = hash_password_complex($password);
                // 默认组（permission.php 中 $qs_default_group），若为超级管理员组则置 admin_level 0
                $regGroups = [$qs_default_group];
                $regLevel = '99';
                if (!empty($qs_default_group) && in_array('*', $qs_groups[$qs_default_group]['perms'] ?? [])) {
                    $regLevel = '0';
                }
                $sp_config_users[$username] = [
                    'displayname'       => $displayname,
                    'qsis_userpassword' => $pwd_hash,
                    'role'              => 'member',
                    'email'             => $tempEmail,
                    'description'       => L('reg_from_front_page'),
                    'activate_time'     => time(),
                    'admin_level'       => $regLevel,
                    'groups'            => $regGroups,
                    'perms'             => [],
                    'deny_perms'        => [],
                ];
                $export_code = "<?php\n\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";
                $write_ok = file_put_contents($datapaths . $config_user, $export_code, LOCK_EX);
                if($write_ok){
                    //销毁注册临时session
                    unset($_SESSION['reg_activate_token'],$_SESSION['reg_temp_email'],$_SESSION['reg_reset_email'],$_SESSION['reg_token_create_time']);
                    header("Location: ".qs_site_link(false));
                    exit;
                }else{
                    $error = L('reg_save_failed');
                }
            }
        }
        $step = 2;
    }
}

    //---------- 发送重置密码邮件（邮箱已注册场景） ----------
    elseif($postStep === 99){
        if(!empty($_SESSION['reg_reset_email'])){
            $resetEmail = $_SESSION['reg_reset_email'];
            //此处：调用发送重置密码邮件逻辑；你后面实现重置密码功能
            $error = L('reg_reset_pwd_mail_sent');
        }
        $step = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title><?= L('user_register_title') ?></title>
<style>
.wrap{width:480px;margin:40px auto;}
.box{border:1px #ccc solid;padding:24px;border-radius:6px;}
.err{color:#d00;}
.ok{color:#080;}
.form-row{margin-bottom:14px;}
label{display:block;margin-bottom:4px;}
input{width:100%;box-sizing:border-box;padding:8px;}
.captcha-row{display:flex;gap:8px;align-items:center;}
.captcha-row input{flex-grow:1;}
img{cursor:pointer;border:1px #ccc solid;height:38px;}
button{padding:9px 16px;cursor:pointer;margin-right:8px;}
.tip-block{margin:12px 0;padding:10px;background:#f5f7fa;}
</style>
</head>
<body>
<div class="wrap">
<div class="box">
<?php if($step === 1): ?>
<h2><?= L('user_register_title') ?></h2>
<?php if ($error): ?>
<div class="<?= str_contains($error,'tip')?'ok':'err' ?>"><?= htmlspecialchars($error) ?></div>
<br>
<?php endif; ?>
<form method="post" action="">
    <input type="hidden" name="step" value="1">
    <div class="form-row">
        <label><?= L('reg_input_email') ?></label>
        <input type="email" name="email" autocomplete="off">
    </div>
    <button type="submit"><?= L('reg_next_step') ?></button>
</form>

<?php if(isset($_SESSION['reg_reset_email'])): ?>
<div class="tip-block">
    <p><?= L('reg_email_has_registered_tip') ?></p>
    <form method="post" action="">
        <input type="hidden" name="step" value="99">
        <button type="submit"><?= L('reg_send_reset_pwd_mail') ?></button>
        <a href="<?= qs_site_link(false) ?>/?login/"><?= L('goto_login') ?></a>
    </form>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if($step === 2): ?>
<h2><?= L('reg_step2_set_account') ?></h2>
<?php if ($error): ?>
<div class="err"><?= htmlspecialchars($error) ?></div>
<br>
<?php endif; ?>
<div class="tip-block">
<?= L('reg_activated_email_is') ?>：<strong><?=htmlspecialchars($tempEmail)?></strong>
</div>
<form method="post" action="">
    <input type="hidden" name="step" value="2">
    <div class="form-row">
        <label><?= L('reg_username') ?></label>
        <input type="text" name="username" autocomplete="off">
    </div>
    <div class="form-row">
        <label><?= L('reg_displayname') ?></label>
        <input type="text" name="displayname">
    </div>
    <div class="form-row">
        <label><?= L('reg_password') ?></label>
        <input type="password" name="password">
    </div>
    <div class="form-row">
        <label><?= L('reg_password2') ?></label>
        <input type="password" name="password2">
    </div>
    <?php if ($captcha): ?>
    <div class="form-row captcha-row">
        <div style="flex-grow:1;">
            <label><?= L('captcha') ?></label>
            <input type="text" name="captcha" autocomplete="off">
        </div>
        <div>
            <img src="./files/plugins/System/captcha/captcha_image.php"
                 onclick="this.src='./files/plugins/System/captcha/captcha_image.php?t='+Math.random()">
        </div>
    </div>
    <?php endif; ?>
    <br>
    <button type="submit"><?= L('reg_create_account') ?></button>
</form>
<?php endif; ?>
</div>
</div>
</body>
</html>
