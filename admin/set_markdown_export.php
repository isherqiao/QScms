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
 * @File         set_markdown_export.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Markdown export endpoint: streams the selected posts as Markdown files for download.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

//set_markdown_export.php

$post_api = $qs_adv_config['post_api'] ?? 'close';
?>

<div style="padding:10px;" id="top">
    <h2><?= L('Markdown_Export'); ?></h2>
    <?php if($msg):?>
    <div class="updated"><?=htmlspecialchars($msg)?></div>
    <?php endif;?>

    <div style="border:1px solid #ccc;padding:15px;margin:10px 0;">
        <h3><?= L('Export_Selected_Articles_As_Zip'); ?></h3>
        <form method="post" action="md_export_action.php">
            <?php foreach($qs_posts as $pid=>$meta):?>
            <label style="display:block;">
                <input type="checkbox" name="export_ids[]" value="<?=htmlspecialchars($pid)?>">
                <?=htmlspecialchars($meta['title'])?>
            </label>
            <?php endforeach;?>
            <br>
            <button type="submit"><?= L('Export_Selected'); ?></button>
        </form>
    </div>
</div>
