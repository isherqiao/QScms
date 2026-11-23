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
 * @File         lang_overview.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Language pack overview: lists the available languages, saves a whole pack file and back ups the previous version.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$dir_list = [
    'admin' => $language_dir,
    'UI'  => $UI_language_dir
];

$sel_subdir = isset($_GET['subdir']) ? $_GET['subdir'] : 'admin';
$sel_file   = isset($_GET['file'])   ? $_GET['file']   : '';
$msg    = '';
$msgType = '';

if (!isset($dir_list[$sel_subdir])) {
    $msg = L('Illegal_Module_Parameter');
    $msgType = 'error';
    $sel_subdir = 'admin';
}
$current_real_dir = $dir_list[$sel_subdir];

$lang_file_list = [];
if(is_dir($current_real_dir)){
    $glob_res = glob($current_real_dir . 'lang_*.php');
    foreach ($glob_res as $fpath){
        $lang_file_list[] = basename($fpath);
    }
}

if(empty($sel_file) && count($lang_file_list) > 0){
    $sel_file = $lang_file_list[0];
}
$target_full_path = $current_real_dir . $sel_file;

//PHP8 路径安全校验，防路径穿越
$safe_base_real = realpath($current_real_dir);
$safe_target_real = realpath($target_full_path);
$is_safe_file = ($safe_target_real && str_starts_with($safe_target_real, $safe_base_real));

$file_source = '';
if($is_safe_file && is_file($target_full_path)){
    $file_source = file_get_contents($target_full_path);
}

//保存整文件提交处理
if(isset($_POST['save_raw_file']) && $is_safe_file)
{
    $raw_input = $_POST['raw_source'] ?? '';
    //自动备份原文件
    $backup_filename = $sel_file . '.bak_' . date('Ymd_His');
    copy($target_full_path, $current_real_dir . $backup_filename);

    $write_ret = file_put_contents($target_full_path, $raw_input);
    if($write_ret !== false){
        $msg = L('Full_Language_File_Saved_Backup') . $backup_filename;
        $msgType = 'success';
        //保存成功重新读取最新源码渲染
        $file_source = file_get_contents($target_full_path);
    }else{
        $msg = L('Write_Failed_Check_Dir_Writable');
        $msgType = 'error';
    }
}

?>

<div class="admin_page_name">
	<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    <a href="lang_editor.php" <?php echo $page_file == 'lang_editor.php' ? 'class="current"' : ''; ?>><?= L('Lang_Key_Value_Editor'); ?></a>
    <a href="lang_overview.php" <?php echo $page_file == 'lang_overview.php' ? 'class="current"' : ''; ?>><?= L('Lang_File_Overview_Edit'); ?></a>
</div>

<?php if ($msg): ?>
    <div class="<?= $msgType === 'success' ? 'updated' : 'error'; ?>" style="padding:10px; margin:10px 0; border-radius:4px;">
        <?= $msg; ?>
    </div>
<?php endif; ?>

<div id="fenye">
    <ta style="display:block">
    <div class="container">
        <!-- 选择模块与文件 GET表单 -->
        <form method="get">
            <table>
                <tr>
                    <td style="width:240px;"><?= L('Language_Module');?></td>
                    <td>
                        <select name="subdir" onchange="this.form.submit()">
                            <?php foreach(array_keys($dir_list) as $dname):?>
                            <option value="<?php echo $dname;?>" <?php echo $sel_subdir===$dname?'selected':'';?>>
                                <?php echo $dname; ?>
                            </option>
                            <?php endforeach;?>
                        </select>
                        &nbsp;&nbsp;
                        <select name="file" onchange="this.form.submit()">
                            <?php foreach ($lang_file_list as $fname):?>
                            <option value="<?php echo htmlspecialchars($fname);?>"
                                <?php echo $sel_file === $fname ? 'selected' : '';?>>
                                <?php echo htmlspecialchars($fname);?>
                            </option>
                            <?php endforeach;?>
                        </select>
                        <span style="margin-left:12px;color:#666;"><?= L('Currently_Editing');?><?php echo htmlspecialchars($sel_subdir.'/'.$sel_file);?></span>
                    </td>
                </tr>
            </table>
        </form>

        <?php if($is_safe_file && is_file($target_full_path)):?>
        <form method="post">
            <table>
                <tr>
                    <td colspan="2">
                        <textarea name="raw_source" style="width:100%;height:600px;font-family:Consolas,Monaco,monospace;font-size:13px;white-space:pre;"><?php echo htmlspecialchars($file_source); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="submit" name="save_raw_file" value="<?= L('Save_Full_Language_File'); ?>">
                    </td>
                </tr>
            </table>
        </form>
        <?php else:?>
        <div class="error" style="padding:10px;"><?= L('Lang_Dir_No_Php_File');?></div>
        <?php endif;?>
    </div>
    </ta>
</div>
<div class="clear"></div>
<div style="margin:10px 0;font-size:13px;color:#d03030;line-height:1.6;">
    ⚠ <?= L('Lang_Edit_Direct_Warning'); ?>
</div>

<?php require 'foot.php'; ?>
