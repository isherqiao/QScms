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
 * @File         set_remote_fetch.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Remote source fetch settings: endpoint, credentials and fetch options for pulling remote content.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_remote_fetch.php｜B端远程源拉取配置
$remote_fetch = $qs_adv_config['remote_fetch'] ?? 'close';
$remote_api_url = $qs_config['remote_api_url'] ?? '';
$remote_api_key = $qs_config['remote_api_key'] ?? '';

?>
<div class="small_form small_form2">
    <b><?= L('Remote_Article_Source_Sync') ?></b>
    <?= $remote_fetch; ?>
    <?php if ($remote_fetch === 'open'): ?>
    <div class="field">
        <div class="label"><?= L('Remote_Source_API_URL') ?></div>
        <div class="price">
            <input type="text" name="remote_api_url" value="<?= htmlspecialchars($remote_api_url) ?>" class="input">
        </div>
        <div class="info"><?= L('Remote_Fetch_URL_Example') ?></div>
        <div class="clear"></div>
    </div>
    <div class="field">
        <div class="label"><?= L('Remote_Source_API_Key') ?></div>
        <div class="price">
            <input type="text" name="remote_api_key" value="<?= htmlspecialchars($remote_api_key) ?>" class="input">
        </div>
        <div class="info"><?= L('Remote_Source_Key_Tip') ?></div>
        <div class="clear"></div>
    </div>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <!-- 提交标记 do_fetch=1，提交到自身set.php -->
            <button type="submit" name="do_fetch" value="1" class="button"><?= L('Fetch_Remote_Index_Import') ?></button>
        </div>
        <div class="clear"></div>
    </div>
        <?php else: ?>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <span style="color:#999;"><?= L('Remote_Article_Source_Sync_Closed') ?></span>
        </div>
        <div class="clear"></div>
    </div>
    <?php endif; ?>
</div>
