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
 * @File         set_backend.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Backend appearance settings: admin theme and layout options, scoped so the existing logic stays isolated.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_backend.php
?>
<b><?= L('backend_control'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>
    
    <!-- 后台主题选择 -->
    <div class="field">
        <div class="label"><?= L('Backend_Theme'); ?></div>
        <div class="price">
            <?php
            // 保持原有逻辑，但封装在局部作用域
            if (isset($controlcss) && is_dir($controlcss)) {
                echo '<select class="select-1" name="css_two_values" id="css_two_values">';
                echo '<option value="' . htmlspecialchars($css_name ?? '') . ',' . htmlspecialchars($css_theme ?? '') . '">' . L('Current_theme') . ' - ' . htmlspecialchars($css_name ?? '') . '-' . htmlspecialchars($css_theme ?? '') . '</option>';
                
                $mydir = opendir($controlcss);
                while ($file = readdir($mydir)) {
                    if (is_file("$controlcss/$file") && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == "css") {
                        $cssContent = @file_get_contents("$controlcss/$file");
                        if (preg_match_all('/\/\/(name:\s*(.*))\s*\/\/(design:\s*(.*))\s*\/\/(date:\s*(.*))/', $cssContent, $matches)) {
                            $name = isset($matches[2][0]) ? trim($matches[2][0]) : '';
                            $designer = isset($matches[4][0]) ? trim($matches[4][0]) : '';
                            echo "<option value='$name,$file'>$name - $designer - $file</option>";
                        } else {
                            echo "<option value='$file'>$file</option>";
                        }
                    }
                }
                closedir($mydir);
                echo "</select>";
            } else {
                echo "<span style='color:red'>CSS Directory not found.</span>";
            }
            ?>
        </div>
        <div class="info"></div>
    </div>
    <div class="clear"></div>

    <!-- 语言设置 -->
    <div class="field">
        <div class="label"><?= L('Language_Settings'); ?></div>
        <div class="price">
            <?php 
            if (function_exists('language_select')) {
                language_select(); 
            } else {
                echo "Function language_select() not found.";
            }
            ?>
        </div>
    </div>
    <div class="clear"></div>

    <!-- Logo 显示策略 -->
    <div class="field">
        <div class="label"><?= L('Backend_logo_display'); ?></div>
        <div class="price">
            <select class="select-1" name="sitelogoname" id="sitelogoname">
                <option value="all" <?= ($sitelogoname ?? '') == 'all' ? 'selected' : ''; ?>><?= L('display_all'); ?></option>
                <option value="logo" <?= ($sitelogoname ?? '') == 'logo' ? 'selected' : ''; ?>><?= L('display_logo_only'); ?></option>
                <option value="name" <?= ($sitelogoname ?? '') == 'name' ? 'selected' : ''; ?>><?= L('display_name_only'); ?></option>
            </select>
        </div>
        <div class="info"><?= L('F3'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- 文章分页数量 -->
    <div class="field">
        <div class="label"><?= L('Article_Display_Count'); ?></div>
        <div class="price"><input class="input" type="number" name="sccpost_page" value="<?= htmlspecialchars($sccpost_page ?? 10); ?>" /></div>
        <div class="info"><?= L('F4'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- 页面分页数量 -->
    <div class="field">
        <div class="label"><?= L('Page_Display_Count'); ?></div>
        <div class="price"><input class="input" type="number" name="sccpage_page" value="<?= htmlspecialchars($sccpage_page ?? 10); ?>" /></div>
        <div class="info"><?= L('F5'); ?></div>
    </div>
    <div class="clear"></div>
</div>