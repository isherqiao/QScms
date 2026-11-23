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
 * @File         theme_code_editor.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Theme code editor: lists the theme packages and edits their template files in place.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
$dir_list = [
    'THEME_DIR' => $theme_dir,
    'STATIC_THEME_DIR'  => $static_theme_dir
];
$sel_subdir   = isset($_GET['subdir'])   ? $_GET['subdir']   : 'THEME_DIR';
$sel_themepkg = isset($_GET['themepkg']) ? $_GET['themepkg'] : '';
$sel_file     = isset($_GET['file'])     ? $_GET['file']     : '';
$msg    = '';
$msgType = '';

if (!isset($dir_list[$sel_subdir])) {
    $msg = L('Illegal_Module_Parameter');
    $msgType = 'error';
    $sel_subdir = 'THEME_DIR';
}
$current_root_dir = $dir_list[$sel_subdir];
$rootWithSlash = rtrim($current_root_dir, '/\\') . DIRECTORY_SEPARATOR;

// 读取主题根目录下 一级子文件夹列表（主题包）
$themepkg_list = [];
$dir_entries = scandir($rootWithSlash);
foreach ($dir_entries as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $full = $rootWithSlash . $entry;
    if (is_dir($full)) {
        $themepkg_list[] = $entry;
    }
}
// 未选主题包，自动取第一个
if(empty($sel_themepkg) && count($themepkg_list) > 0){
    $sel_themepkg = $themepkg_list[0];
}
// 当前选中主题包完整目录
$pkg_dir = $rootWithSlash . $sel_themepkg . DIRECTORY_SEPARATOR;

// 读取主题包内 php + css
$theme_file_list = [];
if(is_dir($pkg_dir)){
    $glob_php = glob($pkg_dir . '*.php');
    $glob_css = glob($pkg_dir . '*.css');
    $glob_res = array_merge($glob_php, $glob_css);
    foreach ($glob_res as $fpath){
        $theme_file_list[] = basename($fpath);
    }
}
if(empty($sel_file) && count($theme_file_list) > 0){
    $sel_file = $theme_file_list[0];
}

$target_full_path = $pkg_dir . $sel_file;
//PHP8 路径安全校验，防路径穿越
$safe_base_real = realpath($current_root_dir);
$safe_target_real = realpath($target_full_path);
$is_safe_file = ($safe_target_real && str_starts_with($safe_target_real, $safe_base_real));
$file_source = '';
$lineCount = 0;
if($is_safe_file && is_file($target_full_path)){
    $file_source = file_get_contents($target_full_path);
    $lines = explode("\n", $file_source);
    $lineCount = count($lines);
}

//保存整文件提交处理
if(isset($_POST['save_raw_file']) && $is_safe_file)
{
    $raw_input = $_POST['raw_source'] ?? '';
    //自动备份原文件
    $backup_filename = $sel_file . '.bak_' . date('Ymd_His');
    copy($target_full_path, $pkg_dir . $backup_filename);
    $write_ret = file_put_contents($target_full_path, $raw_input);
    if($write_ret !== false){
        $msg = L('Full_Theme_File_Saved_Backup') . $backup_filename;
        $msgType = 'success';
        //保存成功重新读取最新源码渲染
        $file_source = file_get_contents($target_full_path);
        $lines = explode("\n", $file_source);
        $lineCount = count($lines);
    }else{
        $msg = L('Write_Failed_Check_Dir_Writable');
        $msgType = 'error';
    }
}
?>
<div class="admin_page_name">
	<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    <a href="theme_code_editor.php" <?php echo $page_file == 'theme_code_editor.php' ? 'class="current"' : ''; ?>><?= L('Theme_Edit'); ?></a>
</div>
<?php if ($msg): ?>
    <div class="<?= $msgType === 'success' ? 'updated' : 'error'; ?>" style="padding:10px; margin:10px 0; border-radius:4px;">
        <?= $msg; ?>
    </div>
<?php endif; ?>
<div id="fenye">
    <ta style="display:block">
    <div class="container">
        <!-- 选择模块、主题包、文件 GET表单 -->
        <form method="get">
            <table>
                <tr>
                    <td style="width:240px;"><?= L('Theme_Directory');?></td>
                    <td>
                        <select name="subdir" onchange="this.form.submit()">
                            <?php foreach(array_keys($dir_list) as $dname):?>
                            <option value="<?php echo $dname;?>" <?php echo $sel_subdir===$dname?'selected':'';?>>
                                <?php 
                                if($dname === 'THEME_DIR'){
                                    echo L('PHP_Site_Theme_Dir');
                                }elseif($dname === 'STATIC_THEME_DIR'){
                                    echo L('SSG_Site_Theme_Dir');
                                }else{
                                    echo $dname;
                                }
                                ?>
                            </option>
                            <?php endforeach;?>
                        </select>
                        &nbsp;&nbsp;
                        <!-- 新增：主题包下拉 -->
                        <select name="themepkg" onchange="this.form.submit()">
                            <?php foreach ($themepkg_list as $pkg):?>
                            <option value="<?php echo htmlspecialchars($pkg);?>"
                                <?php echo $sel_themepkg === $pkg ? 'selected' : '';?>>
                                <?php echo htmlspecialchars($pkg);?>
                            </option>
                            <?php endforeach;?>
                        </select>
                        &nbsp;&nbsp;
                        <select name="file" onchange="this.form.submit()">
                            <?php foreach ($theme_file_list as $fname):?>
                            <option value="<?php echo htmlspecialchars($fname);?>"
                                <?php echo $sel_file === $fname ? 'selected' : '';?>>
                                <?php echo htmlspecialchars($fname);?>
                            </option>
                            <?php endforeach;?>
                        </select>
                        <span style="margin-left:12px;color:#666;">
                            <?= L('Currently_Editing');?><?php echo htmlspecialchars($sel_subdir.'/'.$sel_themepkg.'/'.$sel_file);?>
                        </span>
                    </td>
                </tr>
            </table>
        </form>
        <?php if($is_safe_file && is_file($target_full_path)):?>
        <form method="post">
            <table>
                <tr>
                    <td colspan="2">
                        <div style="display:flex;border:1px solid #ccc;height:600px;overflow:hidden;">
                            <!-- 左侧行号区域 -->
                            <div id="lineNumbers" style="width:50px;padding:5px 4px;text-align:right;background:#f7f7f7;color:#666;font-family:Consolas,Monaco,monospace;font-size:16px;line-height:1.5;overflow-y:auto;user-select:none;">
<?php for($i=1;$i<=$lineCount;$i++){ echo $i."<br>"; } ?>
                            </div>
                            <!-- 编辑文本域 -->
                            <textarea id="rawSource" name="raw_source" style="flex:1;border:none;padding:5px;font-family:Consolas,Monaco,monospace;font-size:16px;line-height:1.5;white-space:pre;resize:none;overflow-y:auto;"><?php echo htmlspecialchars($file_source); ?></textarea>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="submit" name="save_raw_file" value="<?= L('Save_Full_Theme_File'); ?>">
                    </td>
                </tr>
            </table>
        </form>
        <!-- 同步滚动JS -->
        <script>
            const lineNumbers = document.getElementById('lineNumbers');
            const rawSource = document.getElementById('rawSource');
            rawSource.addEventListener('scroll', ()=>{
                lineNumbers.scrollTop = rawSource.scrollTop;
            });
            lineNumbers.addEventListener('scroll', ()=>{
                rawSource.scrollTop = lineNumbers.scrollTop;
            });
            // 编辑增加/删除换行时，刷新页面才会更新行号（限制：原生textarea无法实时动态新增行号，保存刷新后更新）
        </script>
        <?php else:?>
        <div class="error" style="padding:10px;">
            <?= L('Theme_Package_No_Php_Css');?>
        </div>
        <?php endif;?>
    </div>
    </ta>
</div>
<div class="clear"></div>
<div style="margin:10px 0;font-size:13px;color:#d03030;line-height:1.6;">
    ⚠ <?= L('Theme_Edit_Direct_Warning'); ?>
</div>
<?php require 'foot.php'; ?>
