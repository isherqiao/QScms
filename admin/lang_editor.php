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
 * @File         lang_editor.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Language pack editor: edits a single key/value pair, with a PHP 8 path-traversal check on the target file.
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
$msg   = '';
$error = '';

if (!isset($dir_list[$sel_subdir])) {
    $error = L('Illegal_Module_Parameter');
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

//安全校验（PHP8，str_starts_with原生可用）
$safe_base_real = realpath($current_real_dir);
$safe_target_real = realpath($target_full_path);
$is_safe_file = ($safe_target_real && str_starts_with($safe_target_real, $safe_base_real));

$lang_data = [];
$file_sign = $sel_subdir . '|' . $sel_file;
if($is_safe_file)
{
    if(!is_file($target_full_path) || filesize($target_full_path) === 0){
        $lang_data = [];
        $error = L('Lang_File_Empty_Auto_Init_Empty');
    }else{
        $raw_text = file_get_contents($target_full_path);
        $has_php_tag = str_contains($raw_text, '<?php');
        $has_return_array = preg_match('/return\s*\[/',$raw_text);
        if(!$has_php_tag || !$has_return_array){
            $lang_data = [];
            $error = L('Lang_File_Syntax_Corrupted_Disallow');
        }else{
            $tmp_data = include $target_full_path;
            if(!is_array($tmp_data)){
                $lang_data = [];
                $error = L('Lang_File_Not_Standard_Array_Auto_Fix');
            }else{
                $lang_data = $tmp_data;
            }
        }
    }
    $_SESSION['lang_origin_data'] = $lang_data;
    $_SESSION['lang_filekey'] = $file_sign;
}

$post_lang       = $_POST['lang_item'] ?? [];
$del_keys        = $_POST['del_key'] ?? [];
$new_lang_keys   = $_POST['new_lang']['key'] ?? [];
$new_lang_vals   = $_POST['new_lang']['val'] ?? [];

//保存处理
if(isset($_POST['save_lang']) && $is_safe_file)
{
    $backup_filename = $sel_file . '.bak_' . date('Ymd_His');
    if(is_file($target_full_path)){
        copy($target_full_path, $current_real_dir . $backup_filename);
    }

    $save_arr = $post_lang;

    foreach($del_keys as $dk){
        unset($save_arr[$dk]);
    }

    foreach($new_lang_keys as $idx => $nk){
        $nk_trim = trim($nk);
        $nv = $new_lang_vals[$idx] ?? '';
        if($nk_trim !== ''){
            $save_arr[$nk_trim] = $nv;
        }
    }

    $origin_raw = is_file($target_full_path) ? file_get_contents($target_full_path) : '';
    $header_block = '';
    if(preg_match('/^(<\?php.*?)(?=\breturn\s*\[)/s',$origin_raw,$match_head)){
        $header_block = rtrim($match_head[1]);
    }else{
        $header_block = '<?php' . PHP_EOL . '// QsisCMS Language File | '.$sel_file;
    }

    $out_lines = [];
    $out_lines[] = $header_block;
    $out_lines[] = 'return [';
    foreach ($save_arr as $key => $value)
    {
        $k_esc = addslashes($key);
        $v_esc = addslashes($value);
        $out_lines[] = "    '{$k_esc}' => '{$v_esc}',";
    }
    $out_lines[] = '];';
    $out_lines[] = '?>';
    $final_content = implode(PHP_EOL,$out_lines);

    $write_ret = file_put_contents($target_full_path,$final_content);
    if($write_ret !== false){
        $msg = sprintf(L('Lang_Saved_Backup_Generated'), $backup_filename);
        unset($_SESSION['lang_origin_data']);
        unset($_SESSION['lang_filekey']);
        $raw_new = file_get_contents($target_full_path);
        $tmp_data = include $target_full_path;
        $lang_data = is_array($tmp_data) ? $tmp_data : [];
        $_SESSION['lang_origin_data'] = $lang_data;
    }else{
        $error = sprintf(L('Lang_Write_Failed_Check_Dir_Writable_Path'), $current_real_dir);
    }
}
?>
<div class="admin_page_name">
	<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    <a href="lang_editor.php" <?php echo $page_file == 'lang_editor.php' ? 'class="current"' : ''; ?>><?= L('Lang_Key_Value_Editor'); ?></a>
    <a href="lang_overview.php" <?php echo $page_file == 'lang_overview.php' ? 'class="current"' : ''; ?>><?= L('Lang_File_Overview_Edit'); ?></a>
</div>

    <style>
        *{box-sizing:border-box;}
        .wrap{margin:24px;max-width:1280px;}
        .select-bar{display:flex;gap:14px;align-items:center;margin-bottom:18px;flex-wrap:wrap;}
        .lang-row{display:flex;gap:10px;margin:10px 0;align-items:center;padding:6px 4px;border-radius:4px;}
        .lang-row.changed{background-color:#fff7cc;}
        .lang-row.changed .val-col{
    background-color:#fee;
    border:1px solid #dc3545;
    color:#b02a37;
}
        .key-col{width:280px;background:#f4f5f7;padding:8px;font-family:Consolas,monospace;border:1px solid #d2d2d2;overflow-x:auto;}
        .val-col{flex:1;padding:9px;border:1px solid #d2d2d2;font-size:14px;}
        .btn-submit{padding:10px 28px;background:#2563eb;color:#fff;border:none;border-radius:3px;font-size:15px;cursor:pointer;margin-top:12px;}
        .btn-add-line{padding:7px 14px;background:#009944;color:#fff;border:none;border-radius:4px;cursor:pointer;margin-bottom:10px;}
        .btn-del-line{padding:4px 8px;background:#dc3545;color:#fff;border:none;border-radius:3px;cursor:pointer;white-space:nowrap;}
        .box-success{background:#e8f4ea;border:1px solid #72c982;padding:12px;margin:12px 0;}
        .box-error{background:#fee;border:1px solid #f28482;padding:12px;margin:12px 0;}
        .tip-text{color:#606266;font-size:13px;margin-bottom:12px;}
        .hint-change{color:#c27803;font-size:12px;margin-left:8px;}
        .add-block{margin-top:24px;padding:16px;border:1px dashed #aaa;border-radius:6px;}
        .add-block h4{margin-top:0;margin-bottom:12px;}
        .add-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:8px 0;}
        .inp-new-key{width:280px;padding:8px;border:1px solid #ccc;}
        .inp-new-val{flex:1;padding:8px;border:1px solid #ccc;}
    </style>
<div id="fenye">
 <ta style="display:block">
    <div class="container">
    <div class="tip-text">

    </div>
    <?php if($msg):?><div class="box-success"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
    <?php if($error):?><div class="box-error"><?php echo htmlspecialchars($error);?></div><?php endif;?>

    <div class="select-bar">
        <form method="get">
            <label><?= L('Language_Module'); ?>:</label>
            <select name="subdir" onchange="this.form.submit()">
                <?php foreach(array_keys($dir_list) as $dname):?>
                <option value="<?php echo $dname;?>" <?php echo $sel_subdir===$dname?'selected':'';?>>
                    <?php echo $dname; ?>
                </option>
                <?php endforeach;?>
            </select>
            <label><?= L('language_file'); ?>:</label>
            <select name="file" onchange="this.form.submit()">
                <?php foreach ($lang_file_list as $fname):?>
                <option value="<?php echo htmlspecialchars($fname);?>"
                    <?php echo $sel_file === $fname ? 'selected' : '';?>>
                    <?php echo htmlspecialchars($fname);?>
                </option>
                <?php endforeach;?>
            </select>
        </form>
    </div>

    <?php if($is_safe_file):?>
    <form method="post">
        <?php
        $origin_data = $_SESSION['lang_origin_data'] ?? [];
        foreach($lang_data as $lang_key => $lang_val):
            $show_val = array_key_exists($lang_key,$post_lang) ? $post_lang[$lang_key] : $lang_val;
            $is_changed = (array_key_exists($lang_key,$post_lang) && ($post_lang[$lang_key] !== $lang_val));
        ?>
        <div class="lang-row <?php if($is_changed) echo 'changed'; ?>">
            <div class="key-col"><?php echo htmlspecialchars($lang_key); ?></div>
            <input class="val-col" type="text" name="lang_item[<?php echo htmlspecialchars($lang_key);?>]"
                   value="<?php echo htmlspecialchars($show_val);?>">
            <label><input type="checkbox" name="del_key[]" value="<?php echo htmlspecialchars($lang_key);?>"><?= L('Delete'); ?></label>
            <?php if($is_changed): ?><span class="hint-change"><?= L('Marked_Modified'); ?></span><?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- 多条新增区域 JQ动态追加行 -->
        <div class="add-block">
            <h4><?= L('Batch_Add_Translation_Entries'); ?></h4>
            <button type="button" class="btn-add-line" id="btnAddLine"><?= L('Add_New_Translation_Row'); ?></button>
            <div id="newLinesWrap">
                <!-- 默认预生成1行 -->
                <div class="add-row">
                    <input class="inp-new-key" placeholder="<?= L('Enter_Key_Name_Placeholder'); ?>" type="text" name="new_lang[key][]">
                    <input class="inp-new-val" placeholder="<?= L('Enter_Translation_Text_Placeholder'); ?>" type="text" name="new_lang[val][]">
                    <button type="button" class="btn-del-line btn-remove-row"><?= L('Delete'); ?></button>
                </div>
            </div>
        </div>

        <button class="btn-submit" type="submit" name="save_lang"><?= L('Backup_And_Save_Language'); ?></button>
    </form>
    <?php else:?>
        <div class="box-error"><?= L('Lang_Dir_No_Php_File'); ?></div>
    <?php endif;?>
</div>
</ta>
</div>
<script>
$(function(){
    //新增一行
    $('#btnAddLine').on('click',function(){
        const tpl = `
        <div class="add-row">
            <input class="inp-new-key" placeholder="<?= L('Enter_Key_Name_Placeholder'); ?>" type="text" name="new_lang[key][]">
            <input class="inp-new-val" placeholder="<?= L('Enter_Translation_Text_Placeholder'); ?>" type="text" name="new_lang[val][]">
            <button type="button" class="btn-del-line btn-remove-row"><?= L('Delete'); ?></button>
        </div>
        `;
        $('#newLinesWrap').append(tpl);
    });
    //删除本行
    $(document).on('click','.btn-remove-row',function(){
        $(this).closest('.add-row').remove();
    });
});
</script>

<?php require 'foot.php'; ?>
