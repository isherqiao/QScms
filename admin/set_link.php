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
 * @File         set_link.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Friend-link settings: maintains the link list displayed on the front end.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_link.php
if (!isset($qs_links) || !is_array($qs_links)) $qs_links = [];
?>

<div class="setting_block">
    <h4><?= L('Friendship_Link_Settings'); ?></h4>
    
    <p class="menu-tip">
        <strong style="color:#28a745;">➕</strong> <?= L('Add_Sub_Link'); ?> | 
        <strong style="color:#17a2b8;">⬆️</strong> <?= L('Promote_to_Top'); ?> | 
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <!-- 容器添加 class="link-container" -->
            <div id="links_menus" class="link-container">
                
                <?php if (!empty($qs_links)): ?>
                    <?php foreach ($qs_links as $index => $item): 
                        $name = htmlspecialchars($item['name'] ?? '');
                        $link = htmlspecialchars($item['link'] ?? '');
                        $tar = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId = $item['self_id'] ?? 'link_db_' . $index;
                        
                        $isSub = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'links_menu ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="linkseSelfid[]" value="<?= $selfId ?>">
                        <input type="hidden" name="linkparentid[]" value="<?= $parentId ?>">
                        
                        <span class="link-icon"><?= $iconChar ?></span>
                        
                        <label><?= L('Name') ?>:</label>
                        <input type="text" name="link_name[]" value="<?= $name ?>" /> 
                        
                        <label><?= L('Link') ?>:</label>
                        <input type="text" name="link_url[]" value="<?= $link ?>" /> 
                        
                        <select name="link_menutar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select> 

                        <!-- ✅ 修改：移除 action-group，将按钮直接放在 select 后，确保同行 -->

                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)" 
                               onclick="addChildClick('<?= $selfId ?>', 'links_menus', 'links_menu', 'link_name[]', 'link_url[]', 'link_menutar[]', 'linkparentid[]', 'linkseSelfid[]')" 
                               class="action-link btn-sub">➕ <?= L('Sub_Link'); ?></a> 
                        <?php else: ?>
                            <a href="javascript:void(0)" 
                               onclick="promoteToTop('<?= $selfId ?>', 'linkparentid[]')" 
                               class="action-link btn-promote">⬆️ <?= L('Promote'); ?></a> 
                        <?php endif; ?>
                        
                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>
                        
                        <!-- ✅ 删除按钮紧跟在后，位于同一行 -->
                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a> 
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </div>

            <!-- 底部添加栏 -->
            <div class="add-bar">
                <a href='javascript:void(0)' 
                   onclick='handleAddClick(this);' 
                   data-container="links_menus"
                   data-class="links_menu"
                   data-names="link_name[]"
                   data-urls="link_url[]"
                   data-tars="link_menutar[]"
                   data-parent-id-name="linkparentid[]" 
                   data-self-id-name="linkseSelfid[]" 
                   data-label-name="<?= L('Name') ?>"
                   data-label-link="<?= L('Link') ?>"
                   data-label-delete="<?= L('Delete') ?>"
                   class="btn-add-main">
                   + <?= L('Add_Main_Link'); ?>
                </a> 
                
                <a href="javascript:void(0)" onclick="deletelinks('links_menus');" class="action-link btn-delete"><?= L('Delete_all_newly_added'); ?></a>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>