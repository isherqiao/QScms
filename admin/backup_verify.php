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
 * @File         backup_verify.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 14/11/26
 * @Description  Backup report page: pushes the selected backup files to the verify service together with the authorization code.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php'; ?>
<?php
/**
 * admin/backup_verify.php
 * 把 control/data/ 下的核心配置文件直接推送到验证服务器（verify）保存到 MySQL。
 * 按“一行一站点实例、一个文件名一列”存储：不打包、不压缩、服务器不落地 zip。
 */

// 允许备份的文件（与 verify 端 backup_columns 默认映射一致）
$bvFiles = array(
    'conf.php', 'confp.php', 'groups.php', 'permission.php', 'plugin_conf.php', 'plugin_status.php',
    'ads.php', 'ads_config.php', 'adv_config.php', 'ai_prompt.php', 'authorization.php',
    'blocked_keywords.php', 'link.php', 'menu_conf.php', 'ssg_menu_conf.php',
    'static_config.php', 'static_multisite_config.php',
);

// 已绑定的站点信息（在 admin/account.php「站点绑定」成功后写入 control/data/verify_site.php）
$bvSite = array();
if (is_file($datapaths . 'verify_site.php')) {
    include $datapaths . 'verify_site.php';
    if (isset($verify_site) && is_array($verify_site)) {
        $bvSite = $verify_site;
    }
}

$bvApi    = isset($_POST['api_url']) ? trim((string)$_POST['api_url']) : 'https://verify.qsis.cn:88/api/config_backup.php';
$bvDefaultEmail = !empty($bvSite['email']) ? $bvSite['email'] : ($qs_config['verify_email'] ?? '');
$bvEmail  = isset($_POST['verify_email']) ? trim((string)$_POST['verify_email']) : $bvDefaultEmail;
$bvDomain = isset($_POST['site_domain']) ? trim((string)$_POST['site_domain']) : '';
$bvAlias  = isset($_POST['site_alias']) ? trim((string)$_POST['site_alias']) : '';
$bvSn     = isset($_POST['sn']) ? trim((string)$_POST['sn']) : '';

if ($bvDomain === '' && !empty($bvSite['site_domain'])) {
    $bvDomain = $bvSite['site_domain'];
}
if ($bvAlias === '' && !empty($bvSite['site_alias'])) {
    $bvAlias = $bvSite['site_alias'];
}

// 站点域名 / 访问地址：默认取站点配置（含端口与子目录路径，同一域名下的多个实例靠它区分）
$bvCmsUrl = $qs_config['site_link'] ?? '';
$bvDefaultDomain = '';
if ($bvCmsUrl !== '') {
    $h = (string)parse_url($bvCmsUrl, PHP_URL_HOST);
    $p = parse_url($bvCmsUrl, PHP_URL_PORT);
    $path = trim((string)parse_url($bvCmsUrl, PHP_URL_PATH), '/');
    $bvDefaultDomain = $h . ($p ? ':' . $p : '') . ($path !== '' ? '/' . $path : '');
}
if ($bvDomain === '') {
    $bvDomain = $bvDefaultDomain;
}

// 授权码：优先用已绑定站点的 SN，其次从 authorization.php 的最后一行 //to_time: 读取
if ($bvSn === '' && !empty($bvSite['sn'])) {
    $bvSn = $bvSite['sn'];
}
if ($bvSn === '' && file_exists($datapaths . 'authorization.php')) {
    $authRaw = (string)@file_get_contents($datapaths . 'authorization.php');
    $authLast = '';
    foreach (preg_split('/\r\n|\r|\n/', $authRaw) as $authLine) {
        if (preg_match('#//to_time:\s*(.*)$#', $authLine, $m)) {
            $authLast = trim($m[1]);
        }
    }
    if ($authLast !== '' && preg_match('#^[A-Za-z0-9]+$#', $authLast)) {
        $bvSn = $authLast;
    }
}

// 留言文件：名字由 conf.php 的 liuyan_data_name 决定，站点可能自己改名
$bvLiuyanName = basename((string)($qs_config['liuyan_data_name'] ?? ''));
$bvLiuyanPath = $bvLiuyanName !== '' ? $datapaths . $bvLiuyanName : '';
$bvLiuyanExists = $bvLiuyanPath !== '' && is_file($bvLiuyanPath);
$bvLiuyanPicked = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'push')
    ? !empty($_POST['liuyan'])
    : $bvLiuyanExists;

$bvError = '';
$bvResult = null;
$bvSaved = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'push') {
    $bvPassword = (string)($_POST['verify_password'] ?? '');

    if ($bvEmail === '' || $bvPassword === '' || $bvSn === '') {
        $bvError = L('backup_verify_need_login');
    } elseif ($bvDomain === '') {
        $bvError = L('backup_verify_need_domain');
    } elseif (!preg_match('#^https?://#i', $bvApi)) {
        $bvError = L('backup_verify_bad_api');
    } else {
        // 收集勾选的文件
        $files = array();
        $cols = array();
        $total = 0;
        $pick = isset($_POST['files']) && is_array($_POST['files']) ? $_POST['files'] : array();
        foreach ($pick as $name) {
            $name = basename((string)$name);
            if (!in_array($name, $bvFiles, true)) {
                continue;
            }
            $path = $datapaths . $name;
            if (!is_file($path)) {
                continue;
            }
            $content = (string)@file_get_contents($path);
            if (strlen($content) > 5 * 1024 * 1024) {
                continue;
            }
            $files[$name] = $content;
            $total += strlen($content);
        }

        // 留言文件：按 liuyan_data_name 取实际文件，以列名 wangzhanliuyan 提交
        if ($bvLiuyanPicked && $bvLiuyanExists) {
            $lyContent = (string)@file_get_contents($bvLiuyanPath);
            if (strlen($lyContent) <= 5 * 1024 * 1024) {
                $cols['wangzhanliuyan'] = $lyContent;
                $total += strlen($lyContent);
            }
        }

        if (count($files) === 0 && count($cols) === 0) {
            $bvError = L('backup_verify_no_file');
        } elseif ($total > 30 * 1024 * 1024) {
            $bvError = L('backup_verify_too_large');
        } else {
            $payload = array(
                'email'       => $bvEmail,
                'password'    => $bvPassword,
                'sn'          => $bvSn,
                'site_domain' => $bvDomain,
                'cms_url'     => $bvCmsUrl,
                'site_alias'  => $bvAlias,
                'server_ip'   => get_client_ip(),
                'files'       => $files,
                'columns'     => $cols,
            );
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $bvApi);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
            $resp = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            if (PHP_VERSION_ID < 80000) {
                curl_close($ch);
            }

            if ($resp === false) {
                $bvError = L('backup_verify_net_error') . $curlErr;
            } else {
                $decoded = json_decode($resp, true);
                $bvResult = array('code' => $code, 'body' => $resp, 'data' => $decoded);
                if (is_array($decoded) && !empty($decoded['ok'])) {
                    $bvSaved = isset($decoded['saved']) && is_array($decoded['saved']) ? $decoded['saved'] : array();
                    write_operate_log(qs_get_current_admin_name(), L('backup_verify'), $bvDomain, sprintf(L('backup_verify_log'), count($bvSaved), $bvDomain));
                }
            }
        }
    }
}

// 文件大小格式化（给列表用）
function bv_size($bytes)
{
    $bytes = (int)$bytes;
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return round($bytes / 1024 / 1024, 1) . ' MB';
}
?>
<div class="admin_page_name">
<?= L('backup_verify'); ?>
</div>

<style>
/* 仅文件选择区用网格，避免 18 行的长表格；字号与后台其它页保持一致，不覆盖全局 */
#bv-files {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 8px;
    max-width: 820px;
}
#bv-files label {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    border: 1px solid #e6e8eb;
    border-radius: 4px;
    padding: 6px 9px;
    background: #fcfcfd;
    cursor: pointer;
    line-height: 1.5;
}
#bv-files label:hover { border-color: #b9c6d4; background: #fff; }
#bv-files label.off { opacity: .55; cursor: not-allowed; }
#bv-files label input { margin-top: 3px; }
#bv-files .fname { display: block; word-break: normal; overflow-wrap: break-word; font-size: 16px; }
#bv-files .fmeta { display: block; color: #888; font-size: 14px; }
/* 本页字段改成两列网格，避免后台默认 float 布局把说明挤到下一行右侧 */
#fenye.bv-form .field {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr);
    column-gap: 14px;
    align-items: start;
    margin: 0 0 16px 0;
    width: 100%;
}
#fenye.bv-form .field .label { grid-column: 1; width: auto; float: none; padding: 5px 0 0 0; }
#fenye.bv-form .field .price { grid-column: 2; width: auto; float: none; margin: 0; }
#fenye.bv-form .field .info { grid-column: 2; width: auto; float: none; padding: 4px 0 0 0; }
#fenye.bv-form .field .labec { grid-column: 2; }
#fenye.bv-form .field .price .input { width: 420px; max-width: 100%; }
/* 顶部说明段落不要参与 float 排布，否则会被挤到右侧绕排 */
#fenye.bv-form > .small_form > p { float: none; width: auto; }
#bv-files { width: auto; }
</style>

<div id="fenye" class="bv-form">
<div class="small_form small_form2">
<p class="info" style="margin:0 0 12px;"><?= L('backup_verify_desc'); ?></p>

<?php if (!empty($bvSite['site_domain'])): ?>
<p style="color:#188038;margin:0 0 12px;">当前绑定站点：<?= htmlspecialchars($bvSite['site_domain'], ENT_QUOTES, 'UTF-8'); ?><?= !empty($bvSite['site_alias']) ? '（' . htmlspecialchars($bvSite['site_alias'], ENT_QUOTES, 'UTF-8') . '）' : ''; ?>　<a href="account.php">重新绑定</a></p>
<?php else: ?>
<p style="color:#b45309;margin:0 0 12px;">本站还没有绑定到账户，<a href="account.php">去绑定</a>之后才能推送备份（一个账户可以绑定多个站点）。</p>
<?php endif; ?>

<?php if ($bvError !== ''): ?>
    <p style="color:#c0392b;"><?= htmlspecialchars($bvError, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<?php if ($bvResult !== null): ?>
    <?php if (is_array($bvResult['data']) && !empty($bvResult['data']['ok'])): ?>
        <p style="color:#188038;"><?= L('backup_verify_ok'); ?></p>
        <?php if (!empty($bvResult['data']['warning'])): ?>
            <p style="color:#b45309;"><?= htmlspecialchars((string)$bvResult['data']['warning'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
    <?php elseif ($bvResult['code'] === 404): ?>
        <p style="color:#c0392b;"><?= L('backup_verify_api_404'); ?></p>
    <?php else: ?>
        <p style="color:#c0392b;">
            <?= L('backup_verify_fail'); ?>（HTTP <?= $bvResult['code']; ?>）
            <br><code style="font-size:14px;"><?= htmlspecialchars((string)$bvResult['body'], ENT_QUOTES, 'UTF-8'); ?></code>
        </p>
    <?php endif; ?>
<?php endif; ?>

<form method="post" action="backup_verify.php" autocomplete="off">
<input type="hidden" name="action" value="push">

<div class="field">
    <div class="label"><?= L('verify_api_url'); ?>:</div>
    <div class="price"><input class="input" type="text" name="api_url" value="<?= htmlspecialchars($bvApi, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="info"><?= L('verify_api_url_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Email'); ?>:</div>
    <div class="price"><input class="input" type="text" name="verify_email" value="<?= htmlspecialchars($bvEmail, ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com"></div>
    <div class="info"><?= L('verify_email_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Password'); ?>:</div>
    <div class="price"><input class="input" type="password" name="verify_password" value=""></div>
    <div class="info"><?= L('verify_password_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Sn'); ?>:</div>
    <div class="price"><input class="input" type="text" name="sn" value="<?= htmlspecialchars($bvSn, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="info"><?= L('verify_sn_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('site_domain_label'); ?>:</div>
    <div class="price"><input class="input" type="text" name="site_domain" value="<?= htmlspecialchars($bvDomain, ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="info"><?= L('site_domain_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('site_alias_label'); ?>:</div>
    <div class="price"><input class="input" type="text" name="site_alias" value="<?= htmlspecialchars($bvAlias, ENT_QUOTES, 'UTF-8'); ?>" placeholder="主站 / 测试站"></div>
    <div class="info"><?= L('site_alias_tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('backup_files_label'); ?>:</div>
    <div class="price">
        <div style="color:#888;margin-bottom:6px;">
            已选文件 <span id="bv-count">—</span>
            <a href="javascript:;" id="bv-all" style="margin-left:10px;">全选</a>
            <a href="javascript:;" id="bv-none" style="margin-left:8px;">全不选</a>
        </div>
        <div id="bv-files">
        <?php foreach ($bvFiles as $f): $p = $datapaths . $f; $exists = is_file($p); ?>
        <label class="<?= $exists ? '' : 'off'; ?>">
            <input type="checkbox" name="files[]" value="<?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>"<?= $exists ? ' checked' : ' disabled'; ?>>
            <span>
                <span class="fname"><?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="fmeta"><?= $exists ? bv_size(filesize($p)) . ' · ' . date('m-d H:i', (int)filemtime($p)) : '文件不存在'; ?></span>
            </span>
        </label>
        <?php endforeach; ?>

        <label class="<?= $bvLiuyanExists ? '' : 'off'; ?>">
            <input type="checkbox" name="liuyan" value="1"<?= $bvLiuyanPicked && $bvLiuyanExists ? ' checked' : ''; ?><?= $bvLiuyanExists ? '' : ' disabled'; ?>>
            <span>
                <span class="fname"><?= htmlspecialchars($bvLiuyanName !== '' ? $bvLiuyanName : '—', ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="fmeta"><?= $bvLiuyanExists ? bv_size(filesize($bvLiuyanPath)) . ' · ' . date('m-d H:i', (int)filemtime($bvLiuyanPath)) : '文件不存在'; ?> · <?= L('backup_liuyan_note'); ?></span>
            </span>
        </label>
        </div>
    </div>
    <div class="info"><?= L('backup_files_tip'); ?></div>
</div>

<div class="field">
    <div class="label"></div>
    <div class="labec"><button class="button" type="submit"><?= L('push_backup_button'); ?></button></div>
</div>
</form>
</div>
</div>

<script>
(function () {
    var boxes = document.querySelectorAll('#bv-files input[type="checkbox"]:not([disabled])');
    var count = document.getElementById('bv-count');
    function refresh() {
        var n = 0;
        boxes.forEach(function (b) { if (b.checked) n++; });
        if (count) count.textContent = n + ' / ' + boxes.length;
    }
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });
    refresh();
    document.getElementById('bv-all').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = true; }); refresh(); });
    document.getElementById('bv-none').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = false; }); refresh(); });
})();
</script>

<?php require 'foot.php'; ?>
