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
 * @File         bug_report.php
 * @CreatedTime  16/11/26
 * @LastEditTime 16/11/26
 * @Description  Bug report form: only usable after the site is bound to a verify account. Nothing is stored locally; the report is POSTed to the verify server with ts/nonce/HMAC signature.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>

<?php include("Client.php") ?>

<?php
// 只有已绑定（verify_site.php 里有 email + sn）才可用
$vsFile = $datapaths . 'verify_site.php';
$vs = array('site_domain' => '', 'site_alias' => '', 'sn' => '', 'email' => '', 'api_url' => '', 'bound_at' => 0);
if (is_file($vsFile)) {
    include $vsFile;
    if (is_array($verify_site)) {
        $vs = array_merge($vs, $verify_site);
    }
}
$bound = ((string)$vs['sn'] !== '' && (string)$vs['email'] !== '');

// 接口地址：默认用绑定记录里的 install_auth 地址，换成同目录下的 bug_report.php
$baseApi = (string)$vs['api_url'] !== '' ? (string)$vs['api_url'] : 'https://verify.qsis.cn:88/api/install_auth.php';
if (preg_match('#install_auth\.php#', $baseApi)) {
    $bugApi = preg_replace('#install_auth\.php.*$#', 'bug_report.php', $baseApi);
} else {
    $bugApi = rtrim(dirname($baseApi), '/') . '/bug_report.php';
}

$curVersion = (isset($version) && $version !== '') ? $version : $versionNumber;

$brMsg   = '';
$brOk    = false;
$brBugId = 0;

// 表单回填默认值
$fTitle   = '';
$fType    = 'func';
$fContent = '';
$fSteps   = '';
$fExpect  = '';
$fContact = '';
$fCmsUrl  = (string)$vs['site_domain'] !== ''
    ? ((preg_match('#^https?://#i', (string)$vs['site_domain'])) ? (string)$vs['site_domain'] : 'http://' . (string)$vs['site_domain'])
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bug_report' && $bound) {
    $fTitle   = trim((string)($_POST['bug_title'] ?? ''));
    $fType    = (string)($_POST['bug_type'] ?? 'other');
    $fContent = trim((string)($_POST['bug_content'] ?? ''));
    $fSteps   = trim((string)($_POST['bug_steps'] ?? ''));
    $fExpect  = trim((string)($_POST['bug_expect'] ?? ''));
    $fContact = trim((string)($_POST['bug_contact'] ?? ''));
    $fCmsUrl  = trim((string)($_POST['bug_cms_url'] ?? ''));

    if (!in_array($fType, array('func', 'ui', 'perf', 'other'), true)) {
        $fType = 'other';
    }

    $shotTmp = '';
    $shotSha = '';
    if (!empty($_FILES['shot']['tmp_name']) && is_uploaded_file($_FILES['shot']['tmp_name'])) {
        $shotTmp = (string)$_FILES['shot']['tmp_name'];
        $size = (int)($_FILES['shot']['size'] ?? 0);
        $info = @getimagesize($shotTmp);
        $allowType = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP);
        if ($size <= 0 || $size > 8 * 1024 * 1024) {
            $brMsg = '截图大小需在 8MB 以内。';
            $shotTmp = '';
        } elseif (!$info || !in_array($info[2], $allowType, true)) {
            $brMsg = '截图只支持 jpg / png / gif / webp。';
            $shotTmp = '';
        } else {
            $shotSha = hash_file('sha256', $shotTmp);
        }
    }

    if ($brMsg === '' && $fTitle === '') {
        $brMsg = '标题要填。';
    }
    if ($brMsg === '' && $fContent === '') {
        $brMsg = '问题描述要填。';
    }

    if ($brMsg === '') {
        $fields = array(
            'email'       => (string)$vs['email'],
            'sn'          => (string)$vs['sn'],
            'title'       => $fTitle,
            'bug_type'    => $fType,
            'version'     => $curVersion,
            'site_domain' => (string)$vs['site_domain'],
            'cms_url'     => $fCmsUrl,
            'content'     => $fContent,
            'steps'       => $fSteps,
            'expect_text' => $fExpect,
            'contact'     => $fContact,
            'shot_sha'    => $shotSha,
        );

        // 防重放：ts + nonce + HMAC（密钥 = SN），字段清单必须与 verify 侧一致
        $payload = qs_api_sign($fields, (string)$vs['sn'], 'bug_report', array(
            'title', 'bug_type', 'version', 'site_domain', 'cms_url',
            'content', 'steps', 'expect_text', 'contact', 'shot_sha',
        ));

        $res = qs_verify_post_multipart($bugApi, $payload, 'shot', $shotTmp, 30);
        if (!$res['ok'] && $res['error'] !== '') {
            $brMsg = '连接验证服务器失败：' . $res['error'];
        } elseif (is_array($res['data']) && !empty($res['data']['ok'])) {
            $brOk    = true;
            $brBugId = (int)($res['data']['id'] ?? 0);
            $brMsg   = '已提交，编号 #' . $brBugId . '。本站不保存这份内容，进度请在认证网站的用户中心「我的反馈」里查看。';
            $fTitle = $fContent = $fSteps = $fExpect = $fContact = '';
        } elseif (is_array($res['data']) && !empty($res['data']['error'])) {
            $brMsg = '提交失败：' . $res['data']['error'];
        } else {
            $brMsg = '提交失败（HTTP ' . (int)$res['code'] . '）：' . mb_substr((string)$res['body'], 0, 200);
        }
    }
}
?>

<div class="admin_page_name">
Bug 上报
</div>

<style>
#fenye.br-form .field {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr);
    column-gap: 14px;
    align-items: start;
    margin: 0 0 16px 0;
    width: 100%;
}
#fenye.br-form .field .label { grid-column: 1; width: auto; float: none; padding: 5px 0 0 0; }
#fenye.br-form .field .price { grid-column: 2; width: auto; float: none; margin: 0; }
#fenye.br-form .field .info { grid-column: 2; width: auto; float: none; padding: 4px 0 0 0; }
#fenye.br-form .field .labec { grid-column: 2; }
#fenye.br-form .field .price .input,
#fenye.br-form .field .price select,
#fenye.br-form .field .price textarea { width: 600px; max-width: 100%; }
#fenye.br-form .field .price textarea { min-height: 90px; }
#fenye.br-form > .small_form > p { float: none; width: auto; }
</style>

<div id="fenye" class="br-form">
	<div class="small_form small_form2">

		<div class="field">
			<div class="label">上报状态:</div>
			<div class="labec">
<?php if ($bound): ?>
				已连接认证网站：<strong><?= htmlspecialchars((string)$vs['email'], ENT_QUOTES, 'UTF-8'); ?></strong>　站点 <?= htmlspecialchars((string)$vs['site_domain'], ENT_QUOTES, 'UTF-8'); ?>
<?php elseif (function_exists('qs_can') && qs_can('*')): ?>
				还没有连接认证网站。Bug 上报要先在「我的账户」完成认证绑定。
				<div class="info" style="font-weight:normal;"><a href="account.php">去我的账户完成绑定</a></div>
<?php else: ?>
				未登录或未注册，无法上传 Bug 报告。
<?php endif; ?>
			</div>
		</div>

<?php if ($brMsg !== ''): ?>
		<div class="field">
			<div class="label">结果:</div>
			<div class="labec" style="color:<?= $brOk ? '#188038' : '#c0392b'; ?>;"><?= htmlspecialchars($brMsg, ENT_QUOTES, 'UTF-8'); ?></div>
		</div>
<?php endif; ?>

<?php if ($bound): ?>
		<form method="post" action="bug_report.php" enctype="multipart/form-data" autocomplete="off">
			<input type="hidden" name="action" value="bug_report">

			<div class="field">
				<div class="label">标题:</div>
				<div class="price"><input class="input" type="text" name="bug_title" maxlength="120" value="<?= htmlspecialchars($fTitle, ENT_QUOTES, 'UTF-8'); ?>" placeholder="一句话说明问题"></div>
			</div>

			<div class="field">
				<div class="label">问题类型:</div>
				<div class="price">
					<select name="bug_type" class="input">
						<option value="func"<?= $fType === 'func' ? ' selected' : ''; ?>>功能异常</option>
						<option value="ui"<?= $fType === 'ui' ? ' selected' : ''; ?>>界面显示</option>
						<option value="perf"<?= $fType === 'perf' ? ' selected' : ''; ?>>性能问题</option>
						<option value="other"<?= $fType === 'other' ? ' selected' : ''; ?>>其他</option>
					</select>
				</div>
			</div>

			<div class="field">
				<div class="label">版本号:</div>
				<div class="labec"><?= htmlspecialchars($curVersion, ENT_QUOTES, 'UTF-8'); ?></div>
			</div>

			<div class="field">
				<div class="label">站点域名:</div>
				<div class="labec"><?= htmlspecialchars((string)$vs['site_domain'], ENT_QUOTES, 'UTF-8'); ?></div>
			</div>

			<div class="field">
				<div class="label">站点地址:</div>
				<div class="price"><input class="input" type="text" name="bug_cms_url" value="<?= htmlspecialchars($fCmsUrl, ENT_QUOTES, 'UTF-8'); ?>" placeholder="http://example.com"></div>
			</div>

			<div class="field">
				<div class="label">问题描述:</div>
				<div class="price"><textarea class="input" name="bug_content" placeholder="具体发生了什么，出现问题的页面或操作"><?= htmlspecialchars($fContent, ENT_QUOTES, 'UTF-8'); ?></textarea></div>
			</div>

			<div class="field">
				<div class="label">复现步骤:</div>
				<div class="price"><textarea class="input" name="bug_steps" placeholder="1. 打开…&#10;2. 点击…&#10;3. 出现…"><?= htmlspecialchars($fSteps, ENT_QUOTES, 'UTF-8'); ?></textarea></div>
			</div>

			<div class="field">
				<div class="label">期望结果:</div>
				<div class="price"><textarea class="input" name="bug_expect" placeholder="本来应该是什么样"><?= htmlspecialchars($fExpect, ENT_QUOTES, 'UTF-8'); ?></textarea></div>
			</div>

			<div class="field">
				<div class="label">截图:</div>
				<div class="price"><input type="file" name="shot" accept=".jpg,.jpeg,.png,.gif,.webp"></div>
				<div class="info">选填，jpg / png / gif / webp，8MB 以内。</div>
			</div>

			<div class="field">
				<div class="label">联系方式:</div>
				<div class="price"><input class="input" type="text" name="bug_contact" value="<?= htmlspecialchars($fContact, ENT_QUOTES, 'UTF-8'); ?>" placeholder="邮箱或 QQ，选填"></div>
			</div>

			<div class="field">
				<div class="label">提交说明:</div>
				<div class="labec" style="font-weight:normal;">
					提交后内容只保存在认证网站（verify），本站不落任何文件；官方通过状态标记进度，不会单独回复。
				</div>
			</div>

			<div class="field">
				<div class="label"></div>
				<div class="labec">
					<button type="submit" class="button">提交 Bug 上报</button>
					&nbsp;
					<a href="version.php" class="button" style="display:inline-block;text-decoration:none;">返回版权信息</a>
				</div>
			</div>
		</form>
<?php endif; ?>

	</div>
</div>
