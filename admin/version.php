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
 * @File         version.php
 * @CreatedTime  15/09/19 04:02:00
 * @LastEditTime 14/11/26
 * @Description  Version panel: a few lines of version/licence facts plus shortcuts to the account page, registration and remote backup.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>

<?php include("Client.php") ?>

<?php
// 版本号：以 control/core.php 的 $version 为准；授权文件里的 //version_number 只作兜底
$showVersion = (isset($version) && $version !== '') ? $version : $versionNumber;

// 是否已绑定站点：只读本地记录，不发请求
$vsBoundLabel = '';
$vsFile = $datapaths . 'verify_site.php';
if (is_file($vsFile)) {
    include $vsFile;
    if (is_array($verify_site) && !empty($verify_site['site_domain'])) {
        $vsBoundLabel = (string)$verify_site['site_domain'];
        if (!empty($verify_site['site_alias'])) {
            $vsBoundLabel .= '（' . (string)$verify_site['site_alias'] . '）';
        }
    }
}

// 本地 license 留档（认证并同步时生成，只读来展示，不参与判定）
$licVip    = 'normal';
$licExpire = '';
$licFile   = $datapaths . 'license.php';
if (is_file($licFile)) {
    include $licFile;
    if (isset($qs_license) && is_array($qs_license)) {
        if (!empty($qs_license['vip_level'])) {
            $licVip = strtolower((string)$qs_license['vip_level']);
        }
        if (!empty($qs_license['expire_at'])) {
            $licExpire = (string)$qs_license['expire_at'];
        }
    }
}

// 产品用户等级：普通 / VIP1-VIP4，认证服务器下发后落到 license
$vipMap  = array('vip1' => 'VIP1', 'vip2' => 'VIP2', 'vip3' => 'VIP3', 'vip4' => 'VIP4');
$vipShow = isset($vipMap[$licVip]) ? $vipMap[$licVip] : '普通';

// 授权截止日期：认证服务器同步来的优先，没有就用本地授权文件的 //to_time
$expireShow = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $licExpire)) ? $licExpire : $toTime;

// 还剩几天：用于顶部到期提醒（7 天内亮红条，过期也提示）
$vsDaysLeft = null;
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$expireShow)) {
    $vsDaysLeft = (int)floor((strtotime((string)$expireShow) - strtotime('today')) / 86400);
}

// 分级可见：超级管理员看全部授权信息与操作入口，普通用户只看版本/授权归属/授权状态等
$vsIsSuper  = function_exists('qs_can') ? (bool)qs_can('*') : false;
// Bug 上报必须先在本站完成官网注册与站点认证绑定
$vsBugReady = ($vsBoundLabel !== '' && !empty($verify_site['sn']) && !empty($verify_site['email']));

// 授权主体：未认证一律显示「未授权版本」；企业只显示企业名称，个人显示授权用户名
$vsAuthorized = ($vsBugReady && ($authorizationName !== '' || $companyName !== ''));
$vsAuthShow   = '未授权版本';
if ($vsAuthorized) {
    $vsAuthShow = ($authorizationNature === '企业' && $companyName !== '') ? $companyName : $authorizationName;
}

// 页面本身不发请求：距离上次同步超过 24 小时，才排一次后台静默同步
qs_auth_refresh_schedule($verify_site, $localFilePath, 86400);
?>

<div class="admin_page_name">
<?= L('Version_Authorize'); ?>
</div>

<?php if ($vsDaysLeft !== null && $vsDaysLeft <= 7): ?>
<div style="margin:0 0 16px;padding:14px 18px;border:1px solid #f0b6b2;border-left:5px solid #e53935;border-radius:8px;background:#fdf1f0;color:#8d1d18;line-height:1.8;">
	<div style="font-size:17px;font-weight:700;margin-bottom:4px;">
		授权还剩 <?= (int)$vsDaysLeft; ?> 天
	</div>
	<div style="font-size:14px;">
		到期日：<?= htmlspecialchars((string)$expireShow, ENT_QUOTES, 'UTF-8'); ?>。到期后站点仍可运行，但将不再享有版本更新、远程备份与授权校验支持。请前往认证系统延期。
	</div>
	<div style="margin-top:10px;font-size:14px;">
		<a href="https://verify.qsis.cn:88/account/vip.php" target="_blank" style="color:#c62828;font-weight:600;">前往认证系统延期 →</a>
		&nbsp;&nbsp;
		<a href="https://verify.qsis.cn:88/account/index.php" target="_blank" style="color:#c62828;">查看我的站点与备份</a>
	</div>
</div>
<?php endif; ?>

<div id="fenye">
	<div class="small_form small_form2">

		<div class="field">
			<div class="label"><?= L('Version'); ?>:</div>
			<div class="labec"><?= htmlspecialchars($showVersion, ENT_QUOTES, 'UTF-8'); ?></div>
		</div>

		<div class="field">
			<div class="label"><?= L('Authorized'); ?>:</div>
			<div class="labec"><?= ($vsAuthorized ? L('Authorized') : L('Not_Authorized')); ?></div>
		</div>

		<div class="field">
			<div class="label"><?= L('Authorize_to'); ?>:</div>
			<div class="labec"><?= htmlspecialchars($vsAuthShow, ENT_QUOTES, 'UTF-8'); ?></div>
		</div>

		<div class="field">
			<div class="label">产品用户等级:</div>
			<div class="labec"><?= htmlspecialchars($vipShow, ENT_QUOTES, 'UTF-8'); ?></div>
		</div>

<?php if ($vsIsSuper): ?>
		<div class="field">
			<div class="label">授权截止日期:</div>
			<div class="labec"><?= $expireShow !== '' ? htmlspecialchars($expireShow, ENT_QUOTES, 'UTF-8') : '-'; ?></div>
		</div>

		<div class="field">
			<div class="label"><?= L('Sn'); ?>:</div>
			<div class="labec">
				<?= $sn !== '' ? htmlspecialchars($sn, ENT_QUOTES, 'UTF-8') : '-'; ?>
<?php if ($sn === ''): ?>
				<div class="info" style="font-weight:normal;">SN 为在 QScms 官网注册时邮件送达的 SN 码</div>
<?php endif; ?>
			</div>
		</div>

		<div class="field">
			<div class="label">站点绑定:</div>
			<div class="labec"><?= $vsBoundLabel !== '' ? htmlspecialchars($vsBoundLabel, ENT_QUOTES, 'UTF-8') : '未绑定'; ?></div>
		</div>
<?php endif; ?>

<?php if ($newVersionLink !== ''): ?>
		<div class="field">
			<div class="label"><?= L('new_version'); ?>:</div>
			<div class="labec"><?= $newVersionLink; ?></div>
		</div>
<?php endif; ?>

		<div class="field">
			<div class="label">操作:</div>
			<div class="labec">
<?php if ($vsIsSuper): ?>
				<a href="account.php" class="button" style="display:inline-block;text-decoration:none;">我的账户</a>
				&nbsp;
				<a href="register_go.php" class="button" style="display:inline-block;text-decoration:none;"><?= L('register_backup_btn'); ?></a>
				&nbsp;
				<a href="backup_verify.php" class="button" style="display:inline-block;text-decoration:none;">远程备份</a>
				&nbsp;
<?php endif; ?>
				<a href="bug_report.php" class="button" style="display:inline-block;text-decoration:none;">Bug 上报</a>
<?php if (!$vsBugReady): ?>
				&nbsp;
				<span class="info" style="font-weight:normal;">未登录或未注册，无法上传 Bug</span>
<?php endif; ?>
			</div>
		</div>

	</div>
</div>

<?php require 'foot.php' ?>
