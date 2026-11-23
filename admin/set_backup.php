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
 * @File         set_backup.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 20/11/26 12:30:00
 * @Description  Backup settings panel: archive destination, retention, scheduled task, FTP upload and mail notification options.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__ . '/backup_lib.php';

$bkCfg   = qs_backup_cfg();
$bkState = qs_backup_state_get();
$bkLast  = (int)$bkState['last_run'];
$bkNext  = $bkLast > 0 ? qs_backup_next_run($bkCfg, $bkLast) : qs_backup_next_run($bkCfg);

$bkOn        = ($bkCfg['backup_cron_on'] === 'on');
$bkFreq      = (string)$bkCfg['backup_cron_freq'];
$bkTime      = (string)$bkCfg['backup_cron_time'];
$bkWeek      = (string)$bkCfg['backup_cron_week'];
$bkDay       = (string)$bkCfg['backup_cron_day'];
$bkKeep      = (string)$bkCfg['backup_keep'];
$ftpOn       = ($bkCfg['backup_ftp_on'] === 'on');
$ftpHost     = (string)$bkCfg['backup_ftp_host'];
$ftpPort     = (string)$bkCfg['backup_ftp_port'];
$ftpUser     = (string)$bkCfg['backup_ftp_user'];
$ftpDir      = (string)$bkCfg['backup_ftp_dir'];
$ftpSsl      = (string)$bkCfg['backup_ftp_ssl'];
$ftpPasv     = (string)$bkCfg['backup_ftp_pasv'] === 'on';
$ftpPassSet  = !empty($bkCfg['backup_ftp_pass']);

?><b><?= L('backup_system'); ?></b>
<div class="small_form small_form2">
<div class="clear"></div>
<div class="field">
<div class="label">
	<?= L('tar_name'); ?>
</div>
 <div class="price">
 	<input class="input" type="text" name="tar_name" placeholder="" value="<?= $tar_name;  ?>" />
</div>
<div class="info">
<?= L('G1'); ?> 
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('tar_directory'); ?>
</div>
<div class="price">
	<?= $_SERVER['DOCUMENT_ROOT'];  ?>
<input class="input" type="text" name="tar_directory" placeholder="" value="<?= $tar_directory;  ?>" />
</div>
<div class="info"><?= L('G2');?></div>
<div class="clear">
	
</div>
</div>

<div class="field">
<div class="label">
<?= L('des_directory'); ?>
</div>
 <div class="price">
<?= $_SERVER['DOCUMENT_ROOT'];  ?>
<input class="input" type="text" name="des_directory" value="<?= $des_directory;  ?>" />
</div>
<div class="info">
	<?= L('G3'); ?> 
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
	<?= L('tar_exclude_directory'); ?>
</div>
 <div class="price">
 	<?= $_SERVER['DOCUMENT_ROOT'];  ?>
 	<input class="input" type="text" name="tar_exclude_directory" value="<?= $tar_exclude_directory;  ?>" />
</div>
<div class="info">
<?= L('G4'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
	<?= L('tar_passwd'); ?>
</div>
 <div class="price">
 	<input class="input" type="text" name="tar_passwd" placeholder="<?= $tar_passwd01;  ?>"  >

</div>
<div class="info">
<?= L('G5'); ?>
</div>
</div>

 <div class="clear"></div>
<div class="field">
<div class="label">
<?= L('remote/email'); ?>
</div>
<div class="price">
	<script>
function remote_bak_change(selectElement) {
    var value = selectElement.value;
    if (value == "email") {
        document.getElementById("email").style.display = 'block';
        document.getElementById("bak_other").style.display = 'none';
    } else if (value == "bak_other") {
        document.getElementById("email").style.display = 'none';
        document.getElementById("bak_other").style.display = 'block';
    }
}
</script>
<select name="remote_bak01" id="remote_bak_id" onchange="remote_bak_change(this);">
<option value="email" <?php if ($remote_bak01 == "email") { echo "selected";
}
?>><?= L('email'); ?></option>
<option value="bak_other" <?php if ($remote_bak01 == "bak_other") { echo "selected";
}
?>><?= L('other'); ?></option>
</select>
</div>
<div class="info">
	<?= L('G6'); ?>
</div>
</div>

<div id="email" <?php if($remote_bak01 == "email") { echo 'style="display:block"'; }
elseif ($remote_bak01 == "bak_other") { echo 'style="display:none"'; } ?>>
<div class="clear"></div>
<div class="field">
    <div class="label"><?= L('email'); ?></div>
    <div class="price">

        <input class="input" type="text" name="tar_email" value="<?= $tar_email; ?>"/>
    </div>
    <div class="info"> 	<?= L('G7'); ?></div>
</div>
</div>

<div id="bak_other" <?php if($remote_bak01 == "bak_other") { echo 'style="display:block"'; }
elseif ($remote_bak01 == "email") { echo 'style="display:none"'; } ?>>
<div class="clear"></div>
<div class="field">
    <div class="label">
<?= L('other'); ?>
    </div>
    <div class="price">
        <input class="input" type="text" name="tar_other" value="<?= $tar_other; ?>" />
    </div>
    <div class="info">
        	<?= L('G8'); ?>
    </div>
</div>
</div>

</div>

<div style="clear:both; height:14px"></div>
<b><?= L('backup_cron'); ?></b>
<div class="small_form small_form2">
<script>
function backup_cron_switch(v) {
    var body = document.getElementById("backup_cron_body");
    if (body) body.style.display = (v === "on") ? 'block' : 'none';
}
function backup_freq_switch(v) {
    document.getElementById("bk_week_row").style.display = (v === "weekly") ? 'block' : 'none';
    document.getElementById("bk_day_row").style.display = (v === "monthly") ? 'block' : 'none';
}
</script>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_cron_on'); ?></div>
<div class="price">
<select name="backup_cron_on" onchange="backup_cron_switch(this.value);">
<option value="on" <?= $bkOn ? 'selected' : '' ?>><?= L('Enable'); ?></option>
<option value="off" <?= !$bkOn ? 'selected' : '' ?>><?= L('Disable'); ?></option>
</select>
</div>
<div class="info"><?= L('backup_cron_tip'); ?></div>
</div>

<div id="backup_cron_body" <?= $bkOn ? 'style="display:block"' : 'style="display:none"' ?>>
<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_cron_freq'); ?></div>
<div class="price">
<select name="backup_cron_freq" onchange="backup_freq_switch(this.value);">
<option value="daily" <?= $bkFreq === 'daily' ? 'selected' : '' ?>><?= L('backup_cron_daily'); ?></option>
<option value="weekly" <?= $bkFreq === 'weekly' ? 'selected' : '' ?>><?= L('backup_cron_weekly'); ?></option>
<option value="monthly" <?= $bkFreq === 'monthly' ? 'selected' : '' ?>><?= L('backup_cron_monthly'); ?></option>
</select>
</div>
<div class="info">&nbsp;</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_cron_time'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_cron_time" style="width:100px" value="<?= htmlspecialchars($bkTime, ENT_QUOTES) ?>" placeholder="03:00" />
</div>
<div class="info">HH:MM (24h)</div>
</div>

<div id="bk_week_row" class="field" <?= $bkFreq === 'weekly' ? 'style="display:block"' : 'style="display:none"' ?>>
<div class="label"><?= L('backup_cron_week'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_cron_week" style="width:100px" value="<?= htmlspecialchars($bkWeek, ENT_QUOTES) ?>" />
</div>
<div class="info"><?= L('backup_week_tip'); ?></div>
</div>

<div id="bk_day_row" class="field" <?= $bkFreq === 'monthly' ? 'style="display:block"' : 'style="display:none"' ?>>
<div class="label"><?= L('backup_cron_day'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_cron_day" style="width:100px" value="<?= htmlspecialchars($bkDay, ENT_QUOTES) ?>" />
</div>
<div class="info"><?= L('backup_month_tip'); ?></div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_keep'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_keep" style="width:100px" value="<?= htmlspecialchars($bkKeep, ENT_QUOTES) ?>" />
</div>
<div class="info"><?= L('backup_keep_tip'); ?></div>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_last_run'); ?></div>
<div class="price">
<?= $bkLast > 0 ? date('Y-m-d H:i:s', $bkLast) . ' / ' . htmlspecialchars($bkState['last_result'] === 'ok' ? 'OK' : 'FAIL', ENT_QUOTES) : L('backup_never_run'); ?>
</div>
<div class="info">
<?= L('backup_next_run'); ?>: <?= date('Y-m-d H:i:s', $bkNext); ?>
<?php if (!empty($bkState['last_msg'])): ?>
<br><span style="color:#888"><?= htmlspecialchars($bkState['last_msg'], ENT_QUOTES) ?></span>
<?php endif; ?>
</div>
</div>

</div>

<div style="clear:both; height:14px"></div>
<b><?= L('backup_ftp'); ?></b>
<div class="small_form small_form2">
<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_on'); ?></div>
<div class="price">
<select name="backup_ftp_on">
<option value="on" <?= $ftpOn ? 'selected' : '' ?>><?= L('Enable'); ?></option>
<option value="off" <?= !$ftpOn ? 'selected' : '' ?>><?= L('Disable'); ?></option>
</select>
</div>
<div class="info"><?= L('backup_ftp_tip'); ?></div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_host'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_ftp_host" style="width:170px" value="<?= htmlspecialchars($ftpHost, ENT_QUOTES) ?>" placeholder="ftp.example.com" />
<?= L('backup_ftp_port'); ?>
<input class="input" type="text" name="backup_ftp_port" style="width:60px" value="<?= htmlspecialchars($ftpPort, ENT_QUOTES) ?>" placeholder="21" />
</div>
<div class="info">&nbsp;</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_user'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_ftp_user" value="<?= htmlspecialchars($ftpUser, ENT_QUOTES) ?>" />
</div>
<div class="info">&nbsp;</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_pass'); ?></div>
<div class="price">
<input class="input" type="password" name="backup_ftp_pass" autocomplete="new-password" placeholder="<?= $ftpPassSet ? L('backup_ftp_pass_set') : '' ?>" />
</div>
<div class="info">&nbsp;</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_dir'); ?></div>
<div class="price">
<input class="input" type="text" name="backup_ftp_dir" value="<?= htmlspecialchars($ftpDir, ENT_QUOTES) ?>" placeholder="/backup" />
</div>
<div class="info">&nbsp;</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_ssl'); ?></div>
<div class="price">
<select name="backup_ftp_ssl">
<option value="none" <?= $ftpSsl === 'none' ? 'selected' : '' ?>><?= L('backup_ftp_none'); ?></option>
<option value="ftps" <?= $ftpSsl === 'ftps' ? 'selected' : '' ?>><?= L('backup_ftp_ftps'); ?></option>
</select>
</div>
<div class="info"><?= L('backup_ftp_pasv'); ?>
<select name="backup_ftp_pasv">
<option value="on" <?= $ftpPasv ? 'selected' : '' ?>><?= L('Enable'); ?></option>
<option value="off" <?= !$ftpPasv ? 'selected' : '' ?>><?= L('Disable'); ?></option>
</select>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label"><?= L('backup_ftp_test'); ?></div>
<div class="price">
<a class="button" href="backup_ftp_test.php" target="_blank"><?= L('backup_ftp_test'); ?></a>
</div>
<div class="info">&nbsp;</div>
</div>

</div>
