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
 * @File         set_ssg_menu.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Static site menu settings: saves and reads the menu tree used only by the SSG output.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
require_once __DIR__ . '/menu_conf_lib.php';

$ssgMenuFile  = $datapaths . 'ssg_menu_conf.php';
$ssgMessage   = '';
$ssgMsgType   = '';

// ================= 保存静态站菜单 =================
if (isset($_POST['save_ssg_menu'])) {
    $ssgNewMenu = qs_menu_hierarchy_from_post(
        'ssg_menuname',
        'ssg_menuurl',
        'ssg_menutar',
        'ssg_menuparentid',
        'ssg_menuseSelfid'
    );
    $ssgNewSideMenu = qs_menu_hierarchy_from_post(
        'ssg_side_menuname',
        'ssg_side_menuurl',
        'ssg_side_menutar',
        'ssg_side_menuparentid',
        'ssg_side_menuseSelfid'
    );

    $ssgCode = qs_menu_conf_build_ssg($ssgNewMenu, $ssgNewSideMenu);
    if (qs_menu_conf_write($ssgMenuFile, $ssgCode)) {
        $ssgMessage = L('settings_saved_successfully');
        $ssgMsgType = 'success';
    } else {
        $ssgMessage = L('Save') . ' failed';
        $ssgMsgType = 'error';
    }
}

// ================= 读取当前静态站菜单 =================
$ssg_menu      = [];
$ssg_side_menu = [];
if (file_exists($ssgMenuFile)) {
    include $ssgMenuFile; // 定义 $qs_menu / $qs_side_menu
    $ssg_menu      = isset($qs_menu) ? (array) $qs_menu : [];
    $ssg_side_menu = isset($qs_side_menu) ? (array) $qs_side_menu : [];
}
?>

<div class="admin_page_name">
    <a href="set.php"><?= L('system_settings'); ?></a> >
    <a href="set.php#ssg"><?= L('QS_Ssg_Site_Settings'); ?></a> >
    Static Site Menu Settings
</div>
<div class="clear"></div>

<?php if ($ssgMessage !== ''): ?>
<div class="message <?= $ssgMsgType === 'success' ? 'success' : 'error'; ?>" style="margin:10px 0; padding:10px 14px; border-radius:4px; background:<?= $ssgMsgType === 'success' ? '#eaf7ed' : '#fdecea'; ?>; color:<?= $ssgMsgType === 'success' ? '#2e7d32' : '#c0392b'; ?>;">
    <?= htmlspecialchars($ssgMessage); ?>
</div>
<?php endif; ?>

<form action="" method="post" id="SsgMenuForm">
<div class="small_form small_form2">
    <b>Static Site Menu Settings</b> — <span style="color:#666; font-size:12px;">Only used for SSG static site generation, independent of the dynamic site menus.</span>
    <div class="clear"></div>
    <div style="margin:10px 0;">
        <button type="submit" name="save_ssg_menu" value="1" class="button" style="padding:8px 20px; cursor:pointer;">
            <?= L('Save'); ?>
        </button>
        <a href="set.php" class="button" style="padding:8px 20px; text-decoration:none;">Back</a>
    </div>
    <div class="clear"></div>
</div>

<div class="setting_block">
    <!-- ================= 静态站主菜单 ================= -->
    <h4><?= L('Main_Menu_Management'); ?></h4>

    <p class="menu-tip">
        <strong style="color:#28a745;">+</strong> <?= L('Add_Sub_Menu'); ?> |
        <strong style="color:#17a2b8;">^</strong> <?= L('Promote_to_Top'); ?> |
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <div id="ssg_menurows" class="menu-container">
                <?php if (!empty($ssg_menu)): ?>
                    <?php foreach ($ssg_menu as $index => $item):
                        $name     = htmlspecialchars($item['name'] ?? '');
                        $link     = htmlspecialchars($item['link'] ?? '');
                        $tar      = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId   = $item['self_id'] ?? ('ssg_item_' . $index);

                        $isSub    = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'linkdiv ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="ssg_menuseSelfid[]" value="<?= $selfId ?>">
                        <input type="hidden" name="ssg_menuparentid[]" value="<?= $parentId ?>">

                        <span class="menu-icon"><?= $iconChar ?></span>

                        <label><?= L('Name'); ?>:</label>
                        <input type="text" name="ssg_menuname[]" value="<?= $name ?>" placeholder="Name">

                        <label><?= L('Link'); ?>:</label>
                        <input type="text" name="ssg_menuurl[]" value="<?= $link ?>" placeholder="Link">

                        <select name="ssg_menutar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select>

                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)"
                               onclick="addChildClick('<?= $selfId ?>', 'ssg_menurows', 'linkdiv', 'ssg_menuname[]', 'ssg_menuurl[]', 'ssg_menutar[]', 'ssg_menuparentid[]', 'ssg_menuseSelfid[]')"
                               class="action-link btn-sub">+ <?= L('Sub_Menu'); ?></a>
                        <?php else: ?>
                            <a href="javascript:void(0)"
                               onclick="promoteToTop('<?= $selfId ?>', 'ssg_menuparentid[]')"
                               class="action-link btn-promote">^ <?= L('Promote'); ?></a>
                        <?php endif; ?>

                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>

                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-bar">
                <a href='javascript:void(0)'
                   onclick='handleAddClick(this);'
                   data-container="ssg_menurows"
                   data-class="linkdiv"
                   data-names="ssg_menuname[]"
                   data-urls="ssg_menuurl[]"
                   data-tars="ssg_menutar[]"
                   data-parent-id-name="ssg_menuparentid[]"
                   data-self-id-name="ssg_menuseSelfid[]"
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

    <div class="clear"></div>

    <!-- ================= 静态站侧边菜单 ================= -->
    <h4><?= L('Side_Menu_Management'); ?></h4>

    <p class="menu-tip">
        <strong style="color:#28a745;">+</strong> <?= L('Add_Sub_Menu'); ?> |
        <strong style="color:#17a2b8;">^</strong> <?= L('Promote_to_Top'); ?> |
        <span style="color:#ccc;">⇧⇩</span> <?= L('Drag_to_Sort'); ?>
    </p>

    <div class="small_form small_form2">
        <div class="field">
            <div id="ssg_side_menurows" class="menu-container">
                <?php if (!empty($ssg_side_menu)): ?>
                    <?php foreach ($ssg_side_menu as $index => $item):
                        $name     = htmlspecialchars($item['name'] ?? '');
                        $link     = htmlspecialchars($item['link'] ?? '');
                        $tar      = $item['tar'] ?? '_self';
                        $parentId = $item['parent_id'] ?? '0';
                        $selfId   = $item['self_id'] ?? ('ssg_side_item_' . $index);

                        $isSub    = (!empty($parentId) && $parentId !== '0');
                        $rowClass = 'linkdiv ' . ($isSub ? 'sub-menu-row' : '');
                        $iconChar = $isSub ? '└' : '●';
                    ?>
                    <div id="<?= $selfId ?>" class="<?= $rowClass ?>" draggable="true" data-new="false">
                        <input type="hidden" name="ssg_side_menuseSelfid[]" value="<?= $selfId ?>">
                        <input type="hidden" name="ssg_side_menuparentid[]" value="<?= $parentId ?>">

                        <span class="menu-icon"><?= $iconChar ?></span>

                        <label><?= L('Name'); ?>:</label>
                        <input type="text" name="ssg_side_menuname[]" value="<?= $name ?>" placeholder="Name">

                        <label><?= L('Link'); ?>:</label>
                        <input type="text" name="ssg_side_menuurl[]" value="<?= $link ?>" placeholder="Link">

                        <select name="ssg_side_menutar[]">
                            <option value="_blank" <?= $tar == '_blank' ? 'selected' : ''; ?>>_blank</option>
                            <option value="_self" <?= $tar == '_self' ? 'selected' : ''; ?>>_self</option>
                        </select>

                        <?php if (!$isSub): ?>
                            <a href="javascript:void(0)"
                               onclick="addChildClick('<?= $selfId ?>', 'ssg_side_menurows', 'linkdiv', 'ssg_side_menuname[]', 'ssg_side_menuurl[]', 'ssg_side_menutar[]', 'ssg_side_menuparentid[]', 'ssg_side_menuseSelfid[]')"
                               class="action-link btn-sub">+ <?= L('Sub_Menu'); ?></a>
                        <?php else: ?>
                            <a href="javascript:void(0)"
                               onclick="promoteToTop('<?= $selfId ?>', 'ssg_side_menuparentid[]')"
                               class="action-link btn-promote">^ <?= L('Promote'); ?></a>
                        <?php endif; ?>

                        <span class="drag-handle" title="Drag to sort" style="margin: 0 5px;">⇧⇩</span>

                        <a href="javascript:void(0)" onclick="deleteItem('<?= $selfId ?>')" class="action-link btn-delete"><?= L('Delete'); ?></a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="add-bar">
                <a href='javascript:void(0)'
                   onclick='handleAddClick(this);'
                   data-container="ssg_side_menurows"
                   data-class="linkdiv"
                   data-names="ssg_side_menuname[]"
                   data-urls="ssg_side_menuurl[]"
                   data-tars="ssg_side_menutar[]"
                   data-parent-id-name="ssg_side_menuparentid[]"
                   data-self-id-name="ssg_side_menuseSelfid[]"
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
</div>

<div class="clear"></div>
<div style="margin:16px 0;">
    <button type="submit" name="save_ssg_menu" value="1" class="button" style="padding:8px 20px; cursor:pointer;">
        <?= L('Save'); ?>
    </button>
</div>
</form>

<?php require 'foot.php'; ?>
