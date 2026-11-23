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
 * @File         head.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 31/08/26 17:39:56
 * @Description  Backend master include: session bootstrap, configuration loading, page-level permission guard, CSS/JS assembly and the side menu.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

session_status() === PHP_SESSION_NONE && session_start();  // 开启 session
date_default_timezone_set('Asia/Shanghai');
ini_set("display_errors", "On"); error_reporting(E_ALL);

require_once __DIR__ . '/../control/core.php';

(!qs_Pass() && !qs_UserPass()) && header("Location: " . qs_admin_url('?login/')) && exit;

$page_file = basename($_SERVER['PHP_SELF']);

// ===== 页面级权限守卫：无权限访问后台功能页时给出提示并中止 =====
$page_dir = basename(dirname($_SERVER['PHP_SELF']));
$page_rel = ($page_dir === 'user' ? 'user/' : '') . $page_file;
if (isset($qs_page_perm[$page_rel]) && !qs_can($qs_page_perm[$page_rel])) {
    $needName = $qs_perms[$qs_page_perm[$page_rel]]['name'] ?? $qs_page_perm[$page_rel];
    ?><!DOCTYPE html>
<html lang="zh-CN">
<head><meta charset="UTF-8"><title><?php echo L('No_Permission_Access'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>body{font-family:system-ui,-apple-system,"Microsoft YaHei",sans-serif;background:#f4f5f7;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.box{background:#fff;border:1px solid #e3e6ea;border-radius:10px;padding:40px 48px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,.06);max-width:420px;}
.box h1{font-size:22px;margin:0 0 12px;color:#c0392b;}
.box p{font-size:14px;color:#666;line-height:1.7;margin:0 0 20px;}
.box a{color:#1a73e8;text-decoration:none;font-size:14px;}</style>
</head>
<body><div class="box">
<h1><?= L('No_Permission_Access'); ?></h1>
<p><?= sprintf(L('Permission_Denied_Tip'), htmlspecialchars($needName, ENT_QUOTES, 'UTF-8')); ?></p>
<a href="<?= qs_admin_url('?logout/') ?>"><?= L('Logout'); ?></a>
</div></body></html><?php
    exit;
}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <title><?= L('Control_Panel');?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?=qs_ico()?>

<?php
$loginUser = $_SESSION['Qsis_user_name'] ?? '';
$defaultTheme = $qs_config['css_theme'];
$loadTheme = $defaultTheme;

// 用户已登录且themes有有效值优先读取
if (!empty($loginUser) && !empty($sp_config_users[$loginUser]['themes'])) {
    $loadTheme = $sp_config_users[$loginUser]['themes'];
} elseif (!empty($_SESSION['user_theme'])) {
    $loadTheme = $_SESSION['user_theme'];
}
$loadTheme = htmlspecialchars($loadTheme, ENT_QUOTES, 'UTF-8');
?>
<link rel="stylesheet" href="<?= qs_admin_base() . $controlcss_url . $loadTheme; ?>">
<link rel="stylesheet" href="<?= qs_admin_base() . $controlcss_url; ?>admin-responsive.css">

<script language="javascript" src="<?= qs_admin_base() . $controldata_url; ?>/set.js"></script>
<script>
	setInterval(() => {
    fetch('heartbeat.php', { 
        method: 'POST',
        credentials: 'include' // 关键：携带Cookie
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'expired') {
            window.location.href = "<?= qs_admin_url('?login/') ?>";
        }
    });
}, 300000); // 300秒 = 10分钟

document.querySelectorAll('.switch-checkbox').forEach(function(ck){
    ck.addEventListener('change', function(){
        // 获取当前配置字段名：enable_backup_mail_log
        const name = this.dataset.name;
        // 找到同名的隐藏input
        const hiddenInput = document.querySelector(`input[name="${name}"].switch-input`);
        // 勾选 → open；未勾选 → close
        hiddenInput.value = this.checked ? 'open' : 'close';
    });
});
</script>
<style>
/* 授权到期提醒红点 */
.qs-lic-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#e53935;margin-left:6px;vertical-align:middle;box-shadow:0 0 0 2px rgba(229,57,53,.16);}
</style>
</head>
<body>
<div id="content">
<div id="top_box"> 
  <span class="left-text">
  <span id="qs_clock"></span>
  <script>
  (function(){
    var pad = function(n){ return n < 10 ? '0' + n : '' + n; };
    var wd = ['日','一','二','三','四','五','六'];
    var el = document.getElementById('qs_clock');
    function tick(){
      var d = new Date();
      var s = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
            + ' 星期' + wd[d.getDay()]
            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
      if (el) el.textContent = s;
    }
    tick();
    setInterval(tick, 1000);
  })();
  </script>
  </span> 
  <span class="right-text">
  	<?= L('Welcome_to');?> :
  <?php
$userName = $_SESSION['Qsis_user_name'] ?? '';
$displayName = $_SESSION['user_displayname'] ?? '';
$adminName = $_SESSION['Qsis_adminname'] ?? '';
$adminLevel = $_SESSION['admin_level'] ?? '';
if (!empty($userName)) {
    $showName = !empty($displayName) ? $displayName : $userName;
    $groupNames = qs_user_groups($userName);
    $groupText = (is_array($groupNames) && count($groupNames) > 0) ? $groupNames[0] : '用户';
    echo $showName . ' (' . htmlspecialchars($groupText, ENT_QUOTES, 'UTF-8') . ')';
} elseif (!empty($adminName)) {
    $levelText = $adminLevel === "0" ? 'SuperAdmin' : L('Admin') . ' - ' . $adminLevel;
    echo $adminName . ' (' . $levelText . ')';
}
  ?>
  </span>
</div>
</div>
  <div id="menu">
    <h3 id="menu_title"><?php echo qs_sitelogoname(); ?></h3>
    <h3 id="menu_title_logout"><a href=<?=  qs_admin_base();  ?>/?logout/><?= L('Logout');?></a></h3>
    <ul>
<?php if (qs_can('post_manage')): ?>
      <li <?php echo $page_file == 'post.php' || $page_file == 'post-edit.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/post.php"><?= L('Article');?></a></li>
<?php endif; ?>
<?php if (qs_can('page_manage')): ?>
      <li <?php echo $page_file == 'page.php' || $page_file == 'page-edit.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/page.php"><?= L('Webpage');?></a></li>
<?php endif; ?>
<?php if (qs_can('system_setting')): ?>
      <li <?php echo $page_file == 'set.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/set.php"><?= L('Settings');?></a></li>
<?php endif; ?>
<?php if (qs_can('plugins')): ?>
      <li <?php echo $page_file == 'plugins.php'  ||  $page_file == 'plugins.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/plugins.php"><?= L('Plugins');?></a></li>
<?php endif; ?>
<?php if (qs_can('backup_system')): ?>
       <li <?php echo $page_file == 'backups.php'  ||  $page_file == 'backups.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/backups.php"><?= L('backup_system');?></a></li>
<?php endif; ?>

<?php if (qs_can('messages')): ?>
 <li <?php echo $page_file == 'liuyan.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/liuyanshow.php"><?= L('View_messages');?></a></li>
<?php endif; ?>
<?php if (qs_can('vote_manage')): ?>
 <li <?php echo $page_file == 'vote.php' || $page_file == 'vote_edit.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/vote.php"><?php echo L('vote_menu_title'); ?></a></li>
<?php endif; ?>
<?php if (qs_can('set_extensions') && ($qs_config['payment_manage_on'] ?? '') === '1'): ?>
 <li <?php echo $page_file == 'payments.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/payments.php"><?= L('Payment_Admin_Title');?></a></li>
<?php endif; ?>
<?php if (qs_can('user_set') && !qs_can('*')): ?>
 <li <?php echo $page_file == 'userset.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/user/userset.php"><?= L('User_set');?></a></li>
<?php endif; ?>
<?php if (qs_can('user_manage')): ?>
 <li <?php echo $page_file == 'user_manage.php' || $page_file == 'group_manage.php' || $page_file == 'perm_manage.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/user/user_manage.php"><?= L('manage_user');?></a></li>
<?php endif; ?>

<?php if (qs_can('ads')): ?>
<?php if (($qs_adv_config['ads'] ?? '') === 'open'): ?>
  <li <?php echo $page_file == 'ads.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/ads.php"><?= L('Ads');?></a></li>
<?php endif; ?>
<?php endif; ?>
<?php if (qs_can('advset')): ?>
 <li <?php echo $page_file == 'advset.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/Adv/advset.php"><?= L('Advset');?></a></li>
<?php endif; ?>
<?php if (qs_can('log_operate')): ?>
<?php if( isset($qs_adv_config['enable_system_logs']) == 'open' ): ?>
  <li <?php echo $page_file == 'log.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/log.php"><?= L('Log_operate');?></a></li>
<?php endif; ?>
<?php endif; ?>

<?php
// 授权到期提醒：剩 7 天内（含已过期）在「版权信息」菜单项上亮红点。
// 只读本地 license / 授权文件，不发任何请求。
$qsLicDaysLeft = null;
$qsLicExpire = '';
$qsLicFile = isset($datapaths) ? $datapaths . 'license.php' : '';
if ($qsLicFile !== '' && is_file($qsLicFile)) {
    include $qsLicFile;
    if (isset($qs_license['expire_at']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$qs_license['expire_at'])) {
        $qsLicExpire = (string)$qs_license['expire_at'];
    }
}
if ($qsLicExpire === '' && !empty($toTime) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$toTime)) {
    $qsLicExpire = (string)$toTime;
}
if ($qsLicExpire !== '') {
    $qsLicDaysLeft = (int)floor((strtotime($qsLicExpire) - strtotime('today')) / 86400);
}
?>
 <li <?php echo $page_file == 'version.php' ? 'class="current"' : ''; ?>><a href="<?= QS_ADMIN_URL ?>/version.php"><?= L('Copyright_Info');?><?php if ($qsLicDaysLeft !== null && $qsLicDaysLeft <= 7): ?><span class="qs-lic-dot" title="授权还剩 <?= $qsLicDaysLeft; ?> 天"></span><?php endif; ?></a></li>
    </ul>
    <div class="clear"></div>
  </div>

  <div id="content">
    <div id="content_box"> 

<?php
// 系统备份计划任务：后台访问时按时间戳懒触发（未启用定时备份时几乎零开销）
if (is_file(__DIR__ . '/backup_lib.php')) {
    require_once __DIR__ . '/backup_lib.php';
    qs_backup_cron_tick();
}
?>
