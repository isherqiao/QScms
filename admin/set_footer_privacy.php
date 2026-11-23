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
 * @File         set_footer_privacy.php
 * @CreatedTime  15/11/26 22:40:00
 * @LastEditTime 15/11/26 22:40:00
 * @Description  Footer & privacy settings panel: copyright line, footer links, ICP number and privacy policy text.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_footer_privacy.php

$fp_footer_info_on   = $qs_config['footer_info_on'] ?? '1';
$fp_footer_copyright = $qs_config['footer_copyright'] ?? '';
$fp_footer_links     = $qs_config['footer_links'] ?? '';
$fp_icp_no           = $qs_config['icp_no'] ?? '';
$fp_icp_url          = $qs_config['icp_url'] ?? '';
$fp_privacy_title    = $qs_config['privacy_title'] ?? '';
$fp_privacy_slug     = $qs_config['privacy_slug'] ?? 'privacy';
$fp_privacy_body     = $qs_config['privacy_body'] ?? '';
$fp_privacy_version  = $qs_config['privacy_version'] ?? '';
$fp_privacy_updated  = $qs_config['privacy_updated'] ?? '';

$fp_preview_url = '../index.php?' . ($fp_privacy_slug !== '' ? $fp_privacy_slug : 'privacy') . '/';
?>
<b><?= L('Footer_Privacy_Heading'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Footer_Info_Switch'); ?></div>
        <select name="footer_info_on" class="textbox" style="width:140px;">
            <option value="1" <?= $fp_footer_info_on === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
            <option value="" <?= $fp_footer_info_on !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
        </select>
        <div class="info">
            <p><?= L('Footer_Info_Switch_Tip'); ?></p>
        </div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Footer_Copyright_Text'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('Footer_Copyright_Tip'); ?></div>
        <input type="text" class="textbox" name="footer_copyright" style="width:100%;" value="<?= htmlspecialchars($fp_footer_copyright ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= L('Footer_Copyright_Placeholder'); ?>">
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Footer_Links'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('Footer_Links_Tip'); ?></div>
        <textarea rows="4" class="textbox" name="footer_links" style="width:100%;" placeholder="<?= L('Footer_Links_Placeholder'); ?>"><?= htmlspecialchars($fp_footer_links ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('ICP_Number'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('ICP_Number_Tip'); ?></div>
        <input type="text" class="textbox" name="icp_no" style="width:240px;" value="<?= htmlspecialchars($fp_icp_no ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= L('ICP_Number_Placeholder'); ?>">
        <input type="text" class="textbox" name="icp_url" style="width:320px;" value="<?= htmlspecialchars($fp_icp_url ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://beian.miit.gov.cn/">
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Privacy_Title'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('Privacy_Slug_Tip'); ?></div>
        <input type="text" class="textbox" name="privacy_title" style="width:300px;" value="<?= htmlspecialchars($fp_privacy_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= L('Privacy_Title_Placeholder'); ?>">
        <input type="text" class="textbox" name="privacy_slug" style="width:180px;" value="<?= htmlspecialchars($fp_privacy_slug ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="privacy">
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Privacy_Body'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('Privacy_Body_Tip'); ?></div>
        <textarea rows="16" class="textbox" name="privacy_body" style="width:100%; font-family:monospace;"><?= htmlspecialchars($fp_privacy_body ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Privacy_Version'); ?></div>
        <input type="text" class="textbox" name="privacy_version" style="width:140px;" value="<?= htmlspecialchars($fp_privacy_version ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="v1.0">
        <input type="date" class="textbox" name="privacy_updated" style="width:170px;" value="<?= htmlspecialchars($fp_privacy_updated ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <a href="<?= htmlspecialchars($fp_preview_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="button" style="display:inline-block; padding:6px 14px; margin-left:8px; text-decoration:none;"><?= L('Privacy_Preview'); ?></a>
    </div>
    <div class="clear"></div>
</div>
