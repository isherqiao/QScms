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
 * @File         set_menu_manager.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Menu manager: builds, orders and saves the dynamic-site menu tree into menu_conf.php.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_menu_manager.php
if (!isset($qs_menu)) $qs_menu = [];
if (!isset($qs_side_menu)) $qs_side_menu = [];
?>

<div class="setting_block">
    <!-- ================= 第一部分：顶部主菜单 ================= -->
    <h4><?= L('Main_Menu_Management'); ?></h4>
    
    <p class="menu-tip">
        <strong style="color:#28a745;">➕</strong> <?= L('Add_Sub_Menu'); ?> | 
        <strong style="color:#17a2b8;">⬆️</strong> <?= L('Promote_to_Top'); ?> | 
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <div id="menurows" class="menu-container">
                <?php if (!empty($qs_menu)): ?>
                    <?php foreach ($qs_menu as $index => $item): 
                        $name = htmlspecialchars($item['name'] ?? '');
                        $link = htmlspecialchars($item['link'] ?? '');
                        $tar = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId = $item['self_id'] ?? 'db_item_' . $index;
                        
                        $isSub = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'linkdiv ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="menuseSelfid[]" value="<?= $selfId ?>">
                        <input type="hidden" name="menuparentid[]" value="<?= $parentId ?>">
                        
                        <span class="menu-icon"><?= $iconChar ?></span>
                        
                        <label><?= L('Name'); ?>:</label>
                        <input type="text" name="menuname[]" value="<?= $name ?>" placeholder="Name"> 
                        
                        <label><?= L('Link'); ?>:</label>
                        <input type="text" name="menuurl[]" value="<?= $link ?>" placeholder="Link"> 
                        
                        <select name="menutar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select> 

                        <!-- ✅ 修改：移除 action-group 包裹，直接将按钮放在 select 后面，确保同行 -->
                        
                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)" 
                               onclick="addChildClick('<?= $selfId ?>', 'menurows', 'linkdiv', 'menuname[]', 'menuurl[]', 'menutar[]', 'menuparentid[]', 'menuseSelfid[]')" 
                               class="action-link btn-sub">➕ <?= L('Sub_Menu'); ?></a> 
                        <?php else: ?>
                            <a href="javascript:void(0)" 
                               onclick="promoteToTop('<?= $selfId ?>', 'menuparentid[]')" 
                               class="action-link btn-promote">⬆️ <?= L('Promote'); ?></a> 
                        <?php endif; ?>
                        
                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>
                        
                        <!-- ✅ 删除按钮紧跟在后，位于同一行 -->
                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a> 
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-bar">
                <a href='javascript:void(0)' 
                   onclick='handleAddClick(this);' 
                   data-container="menurows"
                   data-class="linkdiv"
                   data-names="menuname[]"
                   data-urls="menuurl[]"
                   data-tars="menutar[]"
                   data-parent-id-name="menuparentid[]" 
                   data-self-id-name="menuseSelfid[]" 
                   data-label-name="<?= L('Name') ?>"
                   data-label-link="<?= L('Link') ?>"
                   data-label-delete="<?= L('Delete') ?>"
                   class="btn-add-main">
                   + <?= L('Add_Main_Menu'); ?>
                </a>
                <span style="color:#666; font-size:12px;"><?= L('Click_to_add_top_level'); ?></span>
            </div>
        </div>
    </div>
    
    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

    <!-- ================= 第二部分：侧边栏/第二菜单 ================= -->
    <h4><?= L('Side_Menu_Management'); ?></h4>
    
    <p class="menu-tip">
        <strong style="color:#28a745;">➕</strong> <?= L('Add_Sub_Menu'); ?> | 
        <strong style="color:#17a2b8;">⬆️</strong> <?= L('Promote_to_Top'); ?> | 
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <div id="side_menurows" class="menu-container">
                <?php if (!empty($qs_side_menu)): ?>
                    <?php foreach ($qs_side_menu as $index => $item): 
                        $name = htmlspecialchars($item['name'] ?? '');
                        $link = htmlspecialchars($item['link'] ?? '');
                        $tar = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId = $item['self_id'] ?? 'side_db_item_' . $index;
                        
                        $isSub = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'linkdiv ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="side_menuseSelfid[]" value="<?= $selfId ?>">
                        <input type="hidden" name="side_menuparentid[]" value="<?= $parentId ?>">
                        
                        <span class="menu-icon"><?= $iconChar ?></span>
                        
                        <label><?= L('Name'); ?>:</label>
                        <input type="text" name="side_menuname[]" value="<?= $name ?>" placeholder="Name"> 
                        
                        <label><?= L('Link'); ?>:</label>
                        <input type="text" name="side_menuurl[]" value="<?= $link ?>" placeholder="Link"> 
                        
                        <select name="side_menutar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select> 

                        <!-- ✅ 修改：移除 action-group 包裹，直接将按钮放在 select 后面，确保同行 -->

                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)" 
                               onclick="addChildClick('<?= $selfId ?>', 'side_menurows', 'linkdiv', 'side_menuname[]', 'side_menuurl[]', 'side_menutar[]', 'side_menuparentid[]', 'side_menuseSelfid[]')" 
                               class="action-link btn-sub">➕ <?= L('Sub_Menu'); ?></a> 
                        <?php else: ?>
                            <a href="javascript:void(0)" 
                               onclick="promoteToTop('<?= $selfId ?>', 'side_menuparentid[]')" 
                               class="action-link btn-promote">⬆️ <?= L('Promote'); ?></a> 
                        <?php endif; ?>
                        
                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>
                        
                        <!-- ✅ 删除按钮紧跟在后，位于同一行 -->
                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a> 
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-bar">
                <a href='javascript:void(0)' 
                   onclick='handleAddClick(this);' 
                   data-container="side_menurows"
                   data-class="linkdiv"
                   data-names="side_menuname[]"
                   data-urls="side_menuurl[]"
                   data-tars="side_menutar[]"
                   data-parent-id-name="side_menuparentid[]" 
                   data-self-id-name="side_menuseSelfid[]" 
                   data-label-name="<?= L('Name') ?>"
                   data-label-link="<?= L('Link') ?>"
                   data-label-delete="<?= L('Delete') ?>"
                   class="btn-add-main">
                   + <?= L('Add_Side_Menu'); ?>
                </a>
                <span style="color:#666; font-size:12px;"><?= L('Click_to_add_top_level'); ?></span>
            </div>
        </div>
    </div>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

    <!-- ================= 第三部分：自定义菜单 ================= -->

    <?php if (empty($qs_custom_menus)): ?>
        <p style="color:#888;">No custom menus yet</p>
    <?php else: ?>
        <?php foreach ($qs_custom_menus as $cmKey => $cm):
            if (!preg_match('/^[a-z][a-z0-9_]{0,30}$/', (string) $cmKey)) continue;
            $cmItems = isset($cm['items']) && is_array($cm['items']) ? $cm['items'] : [];
            $cmTitle = isset($cm['name']) ? $cm['name'] : $cmKey;
            $cmRowsId = 'cm_rows_' . $cmKey;
        ?>
        <hr style="margin: 24px 0; border: 0; border-top: 1px dashed #e5e5e5;">
        <h4>
            <?= htmlspecialchars($cmTitle); ?>
            <span style="font-size:12px; color:#17a2b8;">
                Call Function: qs_menu_<?= $cmKey ?>()
            </span>
        </h4>

        <div class="small_form small_form2">
            <div class="field">
                <div id="<?= $cmRowsId ?>" class="menu-container">
                    <?php foreach ($cmItems as $index => $item):
                        $name = htmlspecialchars($item['name'] ?? '');
                        $link = htmlspecialchars($item['link'] ?? '');
                        $tar = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId = $item['self_id'] ?? ('cm_' . $cmKey . '_item_' . $index);

                        $isSub = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'linkdiv ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="cm_<?= $cmKey ?>_self[]" value="<?= $selfId ?>">
                        <input type="hidden" name="cm_<?= $cmKey ?>_parent[]" value="<?= $parentId ?>">

                        <span class="menu-icon"><?= $iconChar ?></span>

                        <label><?= L('Name'); ?>:</label>
                        <input type="text" name="cm_<?= $cmKey ?>_name[]" value="<?= $name ?>" placeholder="Name">

                        <label><?= L('Link'); ?>:</label>
                        <input type="text" name="cm_<?= $cmKey ?>_url[]" value="<?= $link ?>" placeholder="Link">

                        <select name="cm_<?= $cmKey ?>_tar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select>

                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)"
                               onclick="addChildClick('<?= $selfId ?>', '<?= $cmRowsId ?>', 'linkdiv', 'cm_<?= $cmKey ?>_name[]', 'cm_<?= $cmKey ?>_url[]', 'cm_<?= $cmKey ?>_tar[]', 'cm_<?= $cmKey ?>_parent[]', 'cm_<?= $cmKey ?>_self[]')"
                               class="action-link btn-sub">+ <?= L('Sub_Menu'); ?></a>
                        <?php else: ?>
                            <a href="javascript:void(0)"
                               onclick="promoteToTop('<?= $selfId ?>', 'cm_<?= $cmKey ?>_parent[]')"
                               class="action-link btn-promote">^ <?= L('Promote'); ?></a>
                        <?php endif; ?>

                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>

                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="add-bar">
                    <a href='javascript:void(0)'
                       onclick='handleAddClick(this);'
                       data-container="<?= $cmRowsId ?>"
                       data-class="linkdiv"
                       data-names="cm_<?= $cmKey ?>_name[]"
                       data-urls="cm_<?= $cmKey ?>_url[]"
                       data-tars="cm_<?= $cmKey ?>_tar[]"
                       data-parent-id-name="cm_<?= $cmKey ?>_parent[]"
                       data-self-id-name="cm_<?= $cmKey ?>_self[]"
                       data-label-name="<?= L('Name') ?>"
                       data-label-link="<?= L('Link') ?>"
                       data-label-delete="<?= L('Delete') ?>"
                       class="btn-add-main">
                       + <?= L('Add_Main_Menu'); ?>
                    </a>
                    <input type="hidden" name="custom_menu_keys[]" value="<?= $cmKey ?>">
                    <label style="color:#c0392b; margin-left:12px;">
                        <input type="checkbox" name="del_custom_menu[]" value="<?= $cmKey ?>"> Delete this menu
                    </label>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<hr style="margin: 24px 0; border: 0; border-top: 1px dashed #e5e5e5;">
    <h4>Custom Menu Management</h4>

    <p class="menu-tip">
        <strong style="color:#28a745;">+</strong> <?= L('Add_Sub_Menu'); ?> |
        <strong style="color:#17a2b8;">^</strong> <?= L('Promote_to_Top'); ?> |
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <label>Add Custom Menu:</label>
            <input type="text" name="new_custom_menu_name" value="" placeholder="<?= L('Name'); ?>">
            <label>Menu Key:</label>
            <input type="text" name="new_custom_menu_key" value="" placeholder="news" style="width:120px;">
            <span style="color:#666; font-size:12px;">a-z, 0-9, _</span>
        </div>
    </div>

</div>