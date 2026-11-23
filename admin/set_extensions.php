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
 * @File         set_extensions.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Upload extension settings: the allowed file extensions and related options.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_extensions.php

$customItems = [];
foreach ($qs_config as $key => $val) {
    if (str_starts_with($key, 'custom_')) {
        $name = str_replace('custom_', '', $key);
        $customItems[] = [
            'name' => $name,
            'value' => $val
        ];
    }
}
?>
<b><?= L('Extension_Codes'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>
    
    <div class="field">
        <div class="label"></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('head_content_addition'); ?> &lt;?php qs_head_code(); ?&gt;</div>
        <textarea rows="5" class="textbox" placeholder="<?= L('D1'); ?>" name="head_code"><?= htmlspecialchars($head_code ?? ''); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Footer_Copyright_Content'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('D2'); ?> &lt;?php qs_footer_code(); ?&gt;</div>
        <textarea rows="5" class="textbox" name="footer_code"><?= htmlspecialchars($footer_code ?? ''); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('tag_list'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('D3'); ?></div>
        <textarea rows="5" class="textbox" name="tags_list"><?= htmlspecialchars($tags_list ?? ''); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Side_Content_Position'); ?></div>
        <div style="margin-bottom:5px; color:#666;"><?= L('D4'); ?> &lt;?php qs_site_about(); ?&gt;</div>
        <textarea rows="5" class="textbox" name="site_about"><?= htmlspecialchars($site_about ?? ''); ?></textarea>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('Other_codes'); ?></div>
        <textarea rows="5" class="textbox" placeholder="<?= L('D6'); ?>" name="other_code"><?= htmlspecialchars($other_code ?? ''); ?></textarea>
        <div class="info">
            <p><?= L('D7'); ?></p>
            <p><?= L('D8'); ?></p>
        </div>
    </div>
    <div class="clear"></div>

<!-- ===================== 轮播图配置 ===================== -->
<div class="field">
    <div class="label"><b><?= L('Carousel_Config_Heading'); ?></b></div>
    <div style="color:#666; margin-bottom:6px;">
        <?= L('Carousel_Format_Tip'); ?>
    </div>

    <!-- 🔥 加了按钮 -->
    <button type="button" onclick="openImageLibrary(event, 'carousel_config')" style="margin-bottom:8px;">
    <?= L('Insert_Server_Image'); ?>
    </button>

    <textarea id="carousel_config" name="carousel_config" rows="8" class="textbox" style="width:100%;"><?= htmlspecialchars($qs_config['carousel_config'] ?? '') ?></textarea>
</div>
<div class="clear"></div>

    <div class="field">
        <div class="label"><b><?= L('Custom_Variable_Items_Heading'); ?></b></div>
        <div style="margin-bottom:8px;">
            <button type="button" class="add_custom_item" style="padding:5px 12px;cursor:pointer;"><?= L('Add_Custom_Item_Button'); ?></button>
        </div>

        <div id="custom_items_box">
            <?php if (!empty($customItems)): ?>
                <?php foreach ($customItems as $idx => $item): ?>
                    <div class="custom_item_row" style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <input type="text" name="custom_name[]" value="<?= htmlspecialchars($item['name'] ?? '') ?>" style="width:140px; padding:6px;" placeholder="<?= L('Custom_Name_Placeholder'); ?>" class="textbox">
                        
                        <input 
                            id="custom_<?= $idx ?>" 
                            type="text" 
                            name="custom_value[]" 
                            value="<?= htmlspecialchars($item['value'] ?? '') ?>" 
                            style="flex:1; padding:6px;" 
                            placeholder="<?= L('Content_Image_Address_Placeholder'); ?>" 
                            class="textbox"
                        >

                        <button type="button" onclick="openImageLibrary(event, 'custom_<?= $idx ?>')">
                        <?= L('Image_Picker_Button'); ?>
                        </button>

                        <button type="button" class="del_custom_item" style="padding:6px 10px; background:#ff4444; color:#fff; border:none; cursor:pointer;"><?= L('Delete_Item_Short'); ?></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="custom_item_row" style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                    <input type="text" name="custom_name[]" style="width:140px; padding:6px;" placeholder="<?= L('Custom_Name_Placeholder'); ?>" class="textbox">
                    <input id="custom_0" type="text" name="custom_value[]" style="flex:1; padding:6px;" placeholder="<?= L('Content_Image_Address_Placeholder'); ?>" class="textbox">
                    <button type="button" onclick="openImageLibrary(event, 'custom_0')"><?= L('Image_Picker_Button'); ?></button>
                    <button type="button" class="del_custom_item" style="padding:6px 10px; background:#ff4444; color:#fff; border:none; cursor:pointer;"><?= L('Delete_Item_Short'); ?></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.add_custom_item').addEventListener('click', function() {
        let idx = Date.now();
        let html = `
        <div class="custom_item_row" style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
            <input type="text" name="custom_name[]" style="width:140px; padding:6px;" placeholder="<?= L('Custom_Name_Placeholder'); ?>" class="textbox">
            <input id="custom_${idx}" type="text" name="custom_value[]" style="flex:1; padding:6px;" placeholder="<?= L('Content_Image_Address_Placeholder'); ?>" class="textbox">
            <button type="button" onclick="openImageLibrary(event, 'custom_${idx}')"><?= L('Image_Picker_Button'); ?></button>
            <button type="button" class="del_custom_item" style="padding:6px 10px; background:#ff4444; color:#fff; border:none; cursor:pointer;"><?= L('Delete_Item_Short'); ?></button>
        </div>`;
        document.getElementById('custom_items_box').insertAdjacentHTML('beforeend', html);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('del_custom_item')) {
            if (confirm('<?= L('Confirm_Delete_Simple'); ?>')) {
                e.target.closest('.custom_item_row').remove();
            }
        }
    });
});
</script>
<?php include 'image-library.php'; ?>