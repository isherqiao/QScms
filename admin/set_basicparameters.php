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
 * @File         set_basicparameters.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Basic site parameters: site name, URL, keywords and the logo/post image pickers fed by the upload directory listing.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 获取upload/images目录已有的图片文件，用于下拉选择
function getUploadImageOptions(){
    $dir = '../upload/images/';
    $allowExt = ['jpg','png','apng','jpeg','gif','webp','ico','avif','svg','mp4'];
    $opts = '<option value="">' . L('No_Existing_File_Option') . '</option>';
    if(!is_dir($dir)) return $opts;
    $files = scandir($dir);
    foreach ($files as $f){
        if($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f,PATHINFO_EXTENSION));
        if(!in_array($ext,$allowExt)) continue;
        $relPath = "upload/images/{$f}";
        $opts .= '<option value="'.htmlspecialchars($relPath).'">'.htmlspecialchars($f).'</option>';
    }
    return $opts;
}
$imageOptions = getUploadImageOptions();
?>
<b><?= L('basic_settings'); ?></b>
<div class="small_form small_form2">

<div class="field">
<div class="label">
<?= L('A0'); ?>
</div>
<div class="price">
    <select class="select-1" name="site_close" id="site_close_sel">
        <option value="1" <?php if($qs_config['site_close'] == '1') echo 'selected'; ?>><?= L('open'); ?></option>
        <option value="0" <?php if($qs_config['site_close'] == '0') echo 'selected'; ?>><?= L('close'); ?></option>
    </select>

    <div id="site_close_wrap" style="margin-top:8px; <?php echo ($qs_config['site_close']!='0'?'display:none;':''); ?>">
        <!-- 站点关闭说明 支持HTML -->
        <textarea class="textbox" name="site_close_desc" rows="6" placeholder="<?= L('Site‑Closed Description'); ?><?= L('Supports_HTML_Marker'); ?>"><?= htmlspecialchars($qs_config['site_close_desc'] ?? '') ?></textarea>
        <div style="font-size:14px;color:#666;margin-top:4px;margin-bottom:10px;">
            <?= L('Site_Closed_Desc_Tip'); ?>
        </div>

        <!-- 关闭跳转链接 -->
        <input class="input" type="text" name="site_close_redirect" placeholder="<?= L('Site_Close_Redirect_Placeholder'); ?>"
               value="<?= htmlspecialchars($qs_config['site_close_redirect'] ?? '') ?>">
        <div style="font-size:14px;color:#666;margin-top:4px;">
            <?= L('Site_Close_Redirect_Hint'); ?>
        </div>
    </div>
</div>
<div class="info">
<?= L('A1'); ?>
</div>
</div>

<script>
const sel = document.getElementById('site_close_sel');
const wrap = document.getElementById('site_close_wrap');
function toggleCloseArea(){
    if(sel.value === '0'){
        wrap.style.display = 'block';
    }else{
        wrap.style.display = 'none';
    }
}
sel.addEventListener('change', toggleCloseArea);
</script>

<div class="clear"></div>
<div class="field">
<div class="label">
    <?= L('Title'); ?>
</div>
<div class="price">
<input class="input" type="text" name="site_name" value="<?php echo htmlspecialchars($site_name);?>" />
</div>
<div class="info">
<?= L('A1'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
 <?= L('Title_Description'); ?>
</div>
<div class="price">
<input class="input" placeholder="" type="text" id="nick_code" name="nick_code" value="<?php echo htmlspecialchars($nick_code); ?>" />

</div>
<div class="info">
 <?= L('A2'); ?>
    <?= L('A2A'); ?>：<?php echo htmlspecialchars($nick_code);
?>
</div>
</div>
<div class="clear"></div>
<div class="field">
<div class="label">
	 <?= L('Keywords'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?= L('Homepage_Keywords'); ?>" type="text" name="site_tags" value="<?php echo htmlspecialchars($site_tags);
?>" />
   </div>
<div class="info">
	<?= L('A3'); ?>
</div>
</div>
<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('Description'); ?>
</div>
<div class="price">
<input class="input" type="text" name="site_desc" value="<?php echo htmlspecialchars($site_desc);?>" />
</div>
<div class="info">
	<?= L('A4'); ?>
</div>
</div>
   <div class="clear"></div>
<div class="field">
<div class="label">
<?= L('Site_Address'); ?><br>
</div>
<div class="price">
<input class="input" placeholder="/" type="text" name="site_link" value="<?php echo ($site_link);?>" />
</div>
<div class="info">
<?= L('A5'); ?>
</div>
</div>
<div class="clear"></div>
<div class="field">
<div class="label">
	<?= L('Virtual_Directory'); ?><br>
</div>
<div class="price">
<input class="input" placeholder="/xxx/" type="text" name="site_directory" value="<?php echo htmlspecialchars($site_directory);
?>" />
</div>
<div class="info">
<?= L('A6'); ?>
</div>
</div>
<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('Picture_Bed_Address'); ?><br>
</div>
<div class="price">
<input class="input" placeholder="http://" type="text" name="imagehost" value="<?php echo htmlspecialchars($imagehost);
?>" />
</div>
<div class="info">
	<?= L('A7'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('System_Mode'); ?>
</div>
<div class="price" >
<?php SitemodeSelect($sitemode);?>
</div>
<div class="info">
<?= L('A8A'); ?>
</div>
</div>
<div class="clear"></div>

<?php if( isset($qs_adv_config['enable_post_page_storage']) && $qs_adv_config['enable_post_page_storage'] == 'open' ): ?>

<div class="field">
<div class="label">
<?= L('qs_post_dir'); ?>
</div>
<div class="price" >
<input class="input" placeholder="data/posts/" type="text" name="qs_post_dir" value="<?php echo htmlspecialchars($qs_post_dir ?? 'data/posts/'); ?>" />
</div>
<div class="info">
<?= L('A8A'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('qs_page_dir'); ?>
</div>
<div class="price" >
<input class="input" placeholder="data/pages/" type="text" name="qs_page_dir" value="<?php echo htmlspecialchars($qs_page_dir ?? 'data/pages/'); ?>" />
</div>
<div class="info">
<?= L('A8A'); ?>
</div>
</div>

<?php endif ?>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('LOGO'); ?>
</div>
<div class="price" >
<!-- 1.本地上传 -->
<input class="input" type="file" name="logo" id="logo"><br>
<!-- 2.选择服务器已有图片 -->
<select name="sel_logo" style="margin:4px 0; min-width:260px;">
    <?= $imageOptions ?>
</select>
<br>
<!--3.外部URL输入框 -->
<input class="input" type="text" name="url_logo" placeholder="<?= L('Image_URL_Placeholder'); ?>" value="<?= htmlspecialchars($qs_config['sitelogo'] ?? '') ?>" style="margin:4px 0;">
<br>
<img width="100px" height="auto" src="../<?php echo $sitelogo; ?>" />
</div>
<div class="info">
<?= L('A9'); ?><br>
<?= L('A9A'); ?>
<br><?= L('Upload_Priority_Hint'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('favicon.ico'); ?>
</div>
<div class="price" >
<input class="input" type="file" name="ico" id="ico"><br>
<select name="sel_ico" style="margin:4px 0; min-width:260px;">
    <?= $imageOptions ?>
</select>
<br>
<input class="input" type="text" name="url_ico" placeholder="<?= L('Image_URL_Placeholder'); ?>" value="<?= htmlspecialchars($qs_config['ico'] ?? '') ?>" style="margin:4px 0;">
<br>
<img width="100px" height="auto" src="../<?php echo $ico; ?>" />
</div>
<div class="info">
<?= L('A9'); ?><br>
<?= L('A10'); ?>
<br><?= L('Upload_Priority_Hint'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('theme'); ?>
</div>
<div class="price">
<?php  
global $theme;
$theme = $site_theme;
?>
<input id="site_theme" hidden name="site_theme" value="<?php echo $site_theme; ?>">
<!-- 需要添加这个隐藏字段 ↓ -->
<input id="site_theme_now" hidden name="site_theme_now" value="<?php echo $site_theme; ?>">

<?php
/**
 * 取主题的中文分类名：优先 theme_label.txt，其次 theme_admin.php 里的「主题设置（…）」括号内容
 * 只做文本解析，不 include，避免执行主题代码
 */
function qs_theme_label($theme_dir, $name) {
    $f1 = $theme_dir . '/' . $name . '/theme_label.txt';
    if (is_file($f1)) {
        $t = trim((string)file_get_contents($f1));
        if ($t !== '') { return $t; }
    }
    $f2 = $theme_dir . '/' . $name . '/theme_admin.php';
    if (is_file($f2)) {
        $c = (string)file_get_contents($f2);
        if (preg_match('/主题设置（(.+?)）/u', $c, $m)) {
            $t = trim($m[1]);
            if ($t !== '') { return $t; }
        }
    }
    return '';
}

function themetree($directory) {
    $dirs = array();
    $labels = array();
    $mydir = opendir($directory);
    while ($file = readdir($mydir)) {
        if ((is_dir("$directory/$file")) && ($file != ".") && ($file != "..")) {
            $dirs[] = $file;
            $labels[$file] = qs_theme_label($directory, $file);
        }
    }
    closedir($mydir);

    // 按分类分组：同一分类的主题归到一个 optgroup 下，一眼能看出是什么类型
    $groups = array();
    foreach ($dirs as $d) {
        $g = ($labels[$d] !== '') ? $labels[$d] : '未分类';
        $groups[$g][] = $d;
    }
    ksort($groups, SORT_STRING);
    foreach ($groups as $g => $names) {
        usort($names, 'strnatcasecmp');
        $groups[$g] = $names;
    }

    echo '<select class="select-1" name="site_theme_select" id="site_theme_select" onchange="changeIt()">';
    $cur = $GLOBALS['theme'];
    $curLabel = qs_theme_label($directory, $cur);
    echo '<option value="' . htmlspecialchars($cur, ENT_QUOTES, 'UTF-8') . '">';
    echo L('Current_theme');
    echo htmlspecialchars($curLabel !== '' ? $curLabel . ' — ' . $cur : $cur, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach ($groups as $g => $names) {
        echo '<optgroup label="' . htmlspecialchars($g, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($names as $n) {
            echo '<option value="' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        echo '</optgroup>';
    }
    echo "</select>";
}
themetree($theme_dir)
?>
<script language="JavaScript" type="text/javascript">
function changeIt() {
    // 修改这里的变量引用方式 ↓
    var siteThemeSelect = document.getElementById('site_theme_select');
    var tags = document.getElementById('tags');
    var siteTheme = document.getElementById('site_theme');
    var siteThemeNow = document.getElementById('site_theme_now');
    
    var url = "<?= qs_admin_base() ?>/files/theme/";
    tags.src = url + siteThemeSelect.value + "/image.jpg";
    siteTheme.value = siteThemeSelect.value;
    // 修改这里直接使用隐藏字段的值 ↓
    siteThemeNow.value = "<?php echo $site_theme; ?>";
}
</script>
<br> <img id="tags" src='<?= qs_admin_base() ?>/files/theme/<?= htmlspecialchars($site_theme, ENT_QUOTES, 'UTF-8') ?>/image.jpg' width="100px" height="100px">

<?php
$themeDataFile = SITE_ROOT . "/files/theme/{$site_theme}/theme_config.php";
$themeAdminUrl = qs_admin_base() . "/files/theme/{$site_theme}/theme_admin.php";
$hasThemeConfig = file_exists($themeDataFile);
?>
<div style="margin:8px 0;">
<?php if ($hasThemeConfig): ?>
    <a target="_blank" href="<?php echo htmlspecialchars($themeAdminUrl); ?>" class="button"><?= L('Theme_Custom_Config_Link'); ?></a>
<?php else: ?>
    <span style="color:#999;"><?= L('No_Theme_Config_Entry'); ?></span>
<?php endif; ?>
</div>

</div>
<div class="info">
<?= L('A11'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('backgroup_img'); ?>
</div>
<div class="price">
<input class="input" type="file" name="site_backgrouppic" id="site_backgrouppic"><br><img width="100px" height="auto" src="../<?php echo $site_backgrouppic; ?>" />
</div>
<div class="info">
	<?= L('A12'); ?>
</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">
<?= L('personalization_url_ends'); ?>
</div>
<div class="price">
	<input class="input" placeholder=".qs" type="text" name="url_ends" value="<?php echo ($url_ends);?>" />
</div>
<div class="info">
	
</div>
</div>

<div class="clear"></div>
<div class="field">
<span id="nick_code_chanage1" style="display:none; color:#ff0000"><?= L('A100'); ?></span>
</div>
</div>