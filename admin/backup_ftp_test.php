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
 * @File         backup_ftp_test.php
 * @CreatedTime  20/11/26 12:35:00
 * @LastEditTime 20/11/26 12:35:00
 * @Description  FTP 连接测试页：用最近一次生成的备份包验证 FTP 配置是否可用。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

if (!qs_can('set_backup')) {
    echo '<div class="error" style="padding:10px">403 Forbidden</div>';
    require 'foot.php';
    exit;
}

require_once __DIR__ . '/backup_lib.php';

$cfg   = qs_backup_cfg();
$state = qs_backup_state_get();

$probe = '';
if (!empty($state['last_file']) && is_file($state['last_file'])) {
    $probe = (string)$state['last_file'];
} else {
    $list = glob(qs_backup_des_dir($cfg) . '*.zip') ?: [];
    if ($list) {
        usort($list, static function ($a, $b) {
            return strcmp(basename($b), basename($a));
        });
        $probe = $list[0];
    }
}

$ok = false;
$msg = '';
if ($probe === '') {
    $msg = '暂无可上传的备份包，请先执行一次备份';
} else {
    $ok = qs_backup_ftp_upload($cfg, $probe, $msg);
}
?>
<div class="admin_page_name"><?= L('backup_ftp_test'); ?></div>
<div id="fenye">
<ta style="display:block">
	<div class="small_form small_form2">
		<div class="field">
			<div class="label"><?= L('backup_ftp_host'); ?></div>
			<div class="price"><?= htmlspecialchars($cfg['backup_ftp_host'], ENT_QUOTES) ?>:<?= (int)$cfg['backup_ftp_port'] ?></div>
		</div>
		<div class="clear"></div>
		<div class="field">
			<div class="label"><?= L('backup_ftp_dir'); ?></div>
			<div class="price"><?= htmlspecialchars($cfg['backup_ftp_dir'], ENT_QUOTES) ?></div>
		</div>
		<div class="clear"></div>
		<div class="field">
			<div class="label"><?= L('backup_ftp_test'); ?></div>
			<div class="price" style="font-weight:bold;<?= $ok ? 'color:#2a7a2a' : 'color:#b3261e' ?>">
				<?= $ok ? 'OK' : 'FAIL' ?> — <?= htmlspecialchars($msg, ENT_QUOTES) ?>
			</div>
		</div>
		<div class="clear"></div>
		<div class="field">
			<div class="label">&nbsp;</div>
			<div class="price"><a class="button" href="set.php">&larr; <?= L('backup_system') ?></a></div>
		</div>
	</div>
</ta>
</div>
<?php require 'foot.php'; ?>
