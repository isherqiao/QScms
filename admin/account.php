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
 * @File         account.php
 * @CreatedTime  14/11/26
 * @LastEditTime 14/11/26
 * @Description  Account page: binds this deployment to a verify account site list. Requests are sent only when the owner submits the form.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>

<?php include("Client.php") ?>

<?php
/**
 * 站点绑定：把本套部署登记到 verify 账户下的站点列表（同一账户可挂多个站点）。
 * 绑定成功后站点出现在 verify 用户中心，之后才走 admin/backup_verify.php 推送备份。
 */
$vsFile = $datapaths . 'verify_site.php';
$verify_site = array();
if (is_file($vsFile)) {
    include $vsFile;
    if (!is_array($verify_site)) {
        $verify_site = array();
    }
}
$vs = array_merge(
    array('site_domain' => '', 'site_alias' => '', 'sn' => '', 'email' => '', 'bound_at' => 0, 'api_url' => ''),
    $verify_site
);

// 默认站点域名：取站点访问地址（含端口与子目录，同一域名下的多个实例靠它区分）
$vsSiteLink = isset($qs_config['site_link']) ? (string)$qs_config['site_link'] : '';
$vsAutoDomain = '';
if ($vsSiteLink !== '') {
    $h  = (string)parse_url($vsSiteLink, PHP_URL_HOST);
    $p  = parse_url($vsSiteLink, PHP_URL_PORT);
    $pa = trim((string)parse_url($vsSiteLink, PHP_URL_PATH), '/');
    $vsAutoDomain = $h . ($p ? ':' . $p : '') . ($pa !== '' ? '/' . $pa : '');
}
if ($vs['site_domain'] === '') {
    $vs['site_domain'] = $vsAutoDomain;
}

// 已登录后表单收起；只有主动点「重新绑定」才重新展开（换域名或换账户时用）
$vsRebind = isset($_GET['rebind']) && (string)$_GET['rebind'] === '1';

$vsApiDefault = 'https://verify.qsis.cn:88/api/install_auth.php';
// 已登录后的直达地址：由接口地址推出 verify 的 account/enter.php，带上邮箱和授权码
$vsEnterUrl = '';
if ((int)$vs['bound_at'] > 0 && $vs['email'] !== '' && $vs['sn'] !== '') {
    $vsEnterBase = preg_replace('#/api/[^/]*$#', '', $vs['api_url'] !== '' ? $vs['api_url'] : $vsApiDefault);
    $vsEnterUrl = rtrim((string)$vsEnterBase, '/') . '/account/enter.php?email=' . rawurlencode($vs['email']) . '&sn=' . rawurlencode($vs['sn']);
}

$vsMsg = '';
$vsOk  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) ? $_POST['action'] : '') === 'bind_site') {
    $bEmail = isset($_POST['bind_email']) ? trim((string)$_POST['bind_email']) : '';
    $bSn    = isset($_POST['bind_sn']) ? trim((string)$_POST['bind_sn']) : '';
    $bUrl   = isset($_POST['bind_url']) ? trim((string)$_POST['bind_url']) : '';
    $bAlias = isset($_POST['bind_alias']) ? trim((string)$_POST['bind_alias']) : '';
    $bApi   = isset($_POST['bind_api']) ? trim((string)$_POST['bind_api']) : $vsApiDefault;
    if ($bApi === '') {
        $bApi = $vsApiDefault;
    }

    // 从站点地址推导域名：host[:port][/path]
    $bDomain = '';
    if ($bUrl !== '') {
        $bFull = preg_match('#^https?://#i', $bUrl) ? $bUrl : 'http://' . $bUrl;
        $u = parse_url($bFull);
        if ($u && !empty($u['host'])) {
            $pathPart = !empty($u['path']) ? trim($u['path'], '/') : '';
            $bDomain = $u['host']
                . (!empty($u['port']) ? ':' . $u['port'] : '')
                . ($pathPart !== '' ? '/' . $pathPart : '');
        }
    }

    if ($bEmail === '' || $bSn === '') {
        $vsMsg = '注册邮箱和授权码（SN）两项要填。';
    } elseif ($bDomain === '') {
        $vsMsg = '站点地址填写不完整，需要是 http(s)://域名 的形式。';
    } elseif (!filter_var($bApi, FILTER_VALIDATE_URL)) {
        $vsMsg = '接口地址不合法。';
    } else {
        // 授权码当口令：只要邮箱和 SN 对得上，服务端就放行，不用记注册密码
        $payload = array(
            'email'       => $bEmail,
            'password'    => $bSn,
            'sn'          => $bSn,
            'site_domain' => $bDomain,
            'cms_url'     => preg_match('#^https?://#i', $bUrl) ? $bUrl : 'http://' . $bUrl,
            'site_alias'  => $bAlias,
        );

        // 主动请求：站长点了「确定」才发，平时一次都不发
        // 带 ts / nonce / sign 防重放（密钥 = SN），与 verify 侧 api/_auth.php 同一套规则
        $payload = qs_api_sign($payload, $bSn, 'install_auth', array('password', 'sn'));
        $res  = qs_verify_post($bApi, $payload, 15);
        $resp = $res['body'];
        $code = $res['code'];
        $dec  = $res['data'];

        if (!$res['ok'] && $res['error'] !== '') {
            $vsMsg = '连接验证服务器失败：' . $res['error'];
        } else {
            if ($code === 404) {
                $vsMsg = '验证服务器的登录接口（api/install_auth.php）还没有上线。';
            } elseif (is_array($dec) && !empty($dec['ok'])) {
                $vsOk  = true;
                $sites = isset($dec['sites']) && is_array($dec['sites']) ? $dec['sites'] : array();
                $siteN = count($sites);
                $known = false;
                foreach ($sites as $st) {
                    if (isset($st['site_domain']) && $st['site_domain'] === $bDomain) {
                        $known = true;
                        break;
                    }
                }
                $vsMsg = '登录成功：' . $bEmail . '，授权码 ' . (string)($dec['sn'] ?? $bSn) . '。'
                    . '账户下已有 ' . $siteN . ' 个站点'
                    . ($known ? '，本站（' . $bDomain . '）已经在列表里。' : '，本站记录在本地，第一次推送备份时会自动登记进账户。');

                $vs['site_domain'] = $bDomain;
                $vs['site_alias']  = $bAlias;
                $vs['email']       = $bEmail;
                $vs['sn']          = (string)($dec['sn'] ?? $bSn);
                $vs['api_url']     = $bApi;
                $vs['bound_at']    = time();
                $fileContent = "<?php\n\$verify_site = " . var_export($vs, true) . ";\n?>";
                @file_put_contents($vsFile, $fileContent, LOCK_EX);

                // 认证服务器下发的授权信息：等级 + 截止日期
                $licVip = isset($dec['vip_level']) ? strtolower(trim((string)$dec['vip_level'])) : 'normal';
                if (!in_array($licVip, array('normal', 'vip1', 'vip2', 'vip3', 'vip4'), true)) {
                    $licVip = 'normal';
                }
                $licExpire  = isset($dec['expire_at']) ? trim((string)$dec['expire_at']) : '';
                $licToTime  = isset($dec['to_time']) ? (string)$dec['to_time'] : '';
                $syncToTime = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $licExpire)) ? $licExpire : $localToTime;

                // 本地 license 留档：只记录版权与授权信息，不参与任何功能判定
                $licFile = $datapaths . 'license.php';
                $lic = array(
                    'product'   => 'QScms',
                    'email'     => $bEmail,
                    'sn'        => $vs['sn'],
                    'vip_level' => $licVip,
                    'expire_at' => $syncToTime,
                    'to_time'   => $licToTime,
                    'issued_at' => time(),
                    'version'   => $localVersion,
                );
                @file_put_contents(
                    $licFile,
                    "<?php\n// 本地授权留档：只记录版权与授权信息，不参与任何功能判定\n\$qs_license = " . var_export($lic, true) . ";\n?>",
                    LOCK_EX
                );

                // 登录成功即把认证服务器下发的授权信息写到本地：授权主体、性质、企业名、截止日期与 SN
                $syncName    = (isset($dec['authorization']) && (string)$dec['authorization'] !== '') ? (string)$dec['authorization'] : $localName;
                $syncNature  = isset($dec['nature']) ? trim((string)$dec['nature']) : '';
                $syncCompany = isset($dec['company_name']) ? trim((string)$dec['company_name']) : '';
                if ($vs['sn'] !== '') {
                    qs_auth_file_write($localFilePath, array(
                        'status'               => 'authorized',
                        'version_number'       => $localVersion,
                        'authorization_name'   => $syncName,
                        'authorization_nature' => $syncNature,
                        'company_name'         => $syncCompany,
                        'to_time'              => $syncToTime,
                        'sn'                   => $vs['sn'],
                    ));
                }
            } elseif (is_array($dec) && !empty($dec['error'])) {
                $vsMsg = '登录失败：' . $dec['error'];
            } else {
                $vsMsg = '登录失败（HTTP ' . $code . '）：' . mb_substr((string)$resp, 0, 200);
            }
        }
    }
}
?>

<div class="admin_page_name">
我的账户
</div>

<style>
/* 只在本页把后台默认的 float 字段布局改成两列网格：标签定宽、输入框右列、说明在输入框下方 */
#fenye.ac-form .field {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr);
    column-gap: 14px;
    align-items: start;
    margin: 0 0 16px 0;
    width: 100%;
}
#fenye.ac-form .field .label { grid-column: 1; width: auto; float: none; padding: 5px 0 0 0; }
#fenye.ac-form .field .price { grid-column: 2; width: auto; float: none; margin: 0; }
#fenye.ac-form .field .info { grid-column: 2; width: auto; float: none; padding: 4px 0 0 0; }
#fenye.ac-form .field .labec { grid-column: 2; }
#fenye.ac-form .field .labec .info { float: none; width: auto; padding: 2px 0 0 0; }
#fenye.ac-form .field .price .input { width: 420px; max-width: 100%; }
#fenye.ac-form > .small_form > p { float: none; width: auto; }
</style>

<div id="fenye" class="ac-form">
	<div class="small_form small_form2">

		<div class="field">
			<div class="label">账户状态:</div>
			<div class="labec">
<?php if ((int)$vs['bound_at'] > 0): ?>
				已登录：<strong><?= htmlspecialchars($vs['email'], ENT_QUOTES, 'UTF-8'); ?></strong>　授权码 <?= htmlspecialchars($vs['sn'], ENT_QUOTES, 'UTF-8'); ?>　本站 <?= htmlspecialchars($vs['site_domain'], ENT_QUOTES, 'UTF-8'); ?><?= $vs['site_alias'] !== '' ? '（' . htmlspecialchars($vs['site_alias'], ENT_QUOTES, 'UTF-8') . '）' : ''; ?>
				<div class="info" style="font-weight:normal;">登录时间 <?= date('Y-m-d H:i', (int)$vs['bound_at']); ?> · 一个账户可以有多个站点，站点在第一次推送备份时登记进账户</div>
<?php else: ?>
				还没有登录账户。填好注册邮箱和授权码后点下面的按钮，才会连一次验证服务器；平时一次都不连。
<?php endif; ?>
			</div>
		</div>

<?php if ($vsMsg !== ''): ?>
		<div class="field">
			<div class="label">结果:</div>
			<div class="labec" style="color:<?= $vsOk ? '#188038' : '#c0392b'; ?>;"><?= htmlspecialchars($vsMsg, ENT_QUOTES, 'UTF-8'); ?></div>
		</div>
<?php endif; ?>

<?php if ((int)$vs['bound_at'] <= 0 || $vsRebind): ?>
		<form method="post" action="account.php" autocomplete="off">
			<input type="hidden" name="action" value="bind_site">

			<div class="field">
				<div class="label">注册邮箱:</div>
				<div class="price"><input class="input" type="text" name="bind_email" value="<?= htmlspecialchars(isset($qs_config['verify_email']) ? (string)$qs_config['verify_email'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com"></div>
				<div class="info">在 QScms 官网注册时使用的邮箱。</div>
			</div>

			<div class="field">
				<div class="label">授权码 SN:</div>
				<div class="price"><input class="input" type="text" name="bind_sn" value="<?= htmlspecialchars($sn, ENT_QUOTES, 'UTF-8'); ?>"></div>
				<div class="info">注册邮件里的授权码（SN），它同时也当登录口令用，不用记注册密码。</div>
			</div>

			<div class="field">
				<div class="label">站点地址:</div>
				<div class="price"><input class="input" type="text" name="bind_url" value="<?= htmlspecialchars($vsAutoDomain !== '' ? 'http://' . $vsAutoDomain : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://your.site"></div>
				<div class="info">本站的访问地址，绑定到这个 URL；换域名就改这里再绑一次。</div>
			</div>

			<div class="field">
				<div class="label">站点别名:</div>
				<div class="price"><input class="input" type="text" name="bind_alias" value="<?= htmlspecialchars($vs['site_alias'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="主站 / 测试站"></div>
				<div class="info">可选，方便在用户中心辨认。</div>
			</div>

			<input type="hidden" name="bind_api" value="<?= htmlspecialchars($vs['api_url'] !== '' ? $vs['api_url'] : $vsApiDefault, ENT_QUOTES, 'UTF-8'); ?>">

			<div class="field">
				<div class="label"></div>
				<div class="labec"><button class="button" type="submit">认证并同步</button></div>
			</div>
		</form>
<?php endif; ?>

		<div class="field">
			<div class="label">操作:</div>
			<div class="labec">
<?php if ((int)$vs['bound_at'] > 0 && $vsEnterUrl !== ''): ?>
				<a href="<?= htmlspecialchars($vsEnterUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="button" style="display:inline-block;text-decoration:none;">进入认证网站</a>
				&nbsp;
				<a href="account.php?rebind=1" class="button" style="display:inline-block;text-decoration:none;">重新绑定 / 换账户</a>
				&nbsp;
<?php endif; ?>
				<a href="version.php" class="button" style="display:inline-block;text-decoration:none;">返回版权信息</a>
			</div>
		</div>

	</div>
</div>

<?php require 'foot.php' ?>
