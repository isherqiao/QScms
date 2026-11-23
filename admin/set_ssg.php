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
 * @File         set_ssg.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 30/08/26 17:39:56
 * @Description  Static site generation settings: output path, site URL and generation options.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>

 		<div class="clear"></div>
 		 <div class="small_form small_form2">
 		 	<b><?= L('QS_Ssg_Site_Settings'); ?> </b>
	<b><a href="ssg_work.php" target="_blank" class="button"  name=""  value="" style="padding:10px 20px; font-size:16px; cursor:pointer;" />Static_Site_Generator</a>
<a href="set.php?action=clear" class="button" style="padding:10px 20px; font-size:16px; cursor:pointer;" >🧹<?= L('Clear_Static_Directory'); ?></a>
<a href="set_ssg_menu.php" class="button" style="padding:10px 20px; font-size:16px; cursor:pointer;" >Static Site Menu Settings</a>

<div class="clear"></div>
<div style="margin:10px 0; line-height:2">
    <label><input type="checkbox" name="switch[robots]" <?php if(!empty($qs_config['switch']['robots'])) echo 'checked' ?>> <?= L('Generate_Robots_Txt'); ?></label><br>
    <label><input type="checkbox" name="switch[sitemap]" <?php if(!empty($qs_config['switch']['sitemap'])) echo 'checked' ?>> <?= L('Generate_Sitemap_Xml'); ?></label><br>
    <label><input type="checkbox" name="switch[rss]" <?php if(!empty($qs_config['switch']['rss'])) echo 'checked' ?>> <?= L('Generate_Rss_Xml'); ?></label><br>
    <label><input type="checkbox" name="switch[search_index]" <?php if(!empty($qs_config['switch']['search_index'])) echo 'checked' ?>> <?= L('Generate_Search_Index_Json'); ?></label><br>
    <label><input type="checkbox" name="switch[page_404]" <?php if(!empty($qs_config['switch']['page_404'])) echo 'checked' ?>> <?= L('Generate_Page_404_Html'); ?></label><br>
    <label><input type="checkbox" name="switch[urls_txt]" <?php if(!empty($qs_config['switch']['urls_txt'])) echo 'checked' ?>> <?= L('Generate_Urls_Txt'); ?></label><br>
    <label><input type="checkbox" name="switch[cname]" <?php if(!empty($qs_config['switch']['cname'])) echo 'checked' ?>> <?= L('Generate_Cname'); ?></label><br>
<label><input type="checkbox" name="switch[IndexNow]" <?php if(!empty($qs_config['switch']['IndexNow'])) echo 'checked' ?>> <?= L('Generate_IndexNow'); ?></label><br>
</div>

<!-- root 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_root_url'); ?>
</div>
<div class="price">
	
<input class="input" placeholder="<?php echo L('root_directory'); ?>" type="text" name="static_root_url" value="<?= htmlspecialchars($static_root_url ?? $root_url ?? 'https://qsis.cn'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- output 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_output'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('output_directory'); ?>" type="text" name="static_output" value="<?php echo htmlspecialchars($static_output ?? $output ?? '../static_site'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- upload_dir 配置项 -->
<div class="field">
<div class="label">
<?php echo L('local_upload_dir'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('local_upload_dir'); ?>" type="text" name="local_upload_dir" value="<?php echo htmlspecialchars($local_upload_dir ?? '../upload'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- upload_dir 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_upload_dir'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_upload_dir'); ?>" type="text" name="static_upload_dir" value="<?php echo htmlspecialchars($static_upload_dir ?? $upload_dir ?? '/upload/'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- data_posts 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_data_posts'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('posts_data_directory'); ?>" type="text" name="static_data_posts" value="<?php echo htmlspecialchars($static_data_posts ?? $data_posts ?? '../files/posts/data'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- data_pages 配置项 -->
<div class="field">
<div class="label">
<?php echo L('data_pages'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('pages_data_directory'); ?>" type="text" name="static_data_pages" value="<?php echo htmlspecialchars($static_data_pages ?? $data_pages ?? '../files/pages/data'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- publish_posts 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_publish_posts'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('publish_posts_file'); ?>" type="text" name="static_publish_posts" value="<?php echo htmlspecialchars($static_publish_posts ?? $publish_posts ?? '../files/posts/index/publish.php'); ?>" />
</div>
<div class="info">
<?php echo L('publish_article_id_file'); ?>
</div>
<div class="clear"></div>
</div>

<!-- publish_pages 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_publish_pages'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('publish_pages_file'); ?>" type="text" name="static_publish_pages" value="<?php echo htmlspecialchars($static_publish_pages ?? $publish_pages ?? '../files/pages/index/publish.php'); ?>" />
</div>
<div class="info">
<?php echo L('publish_page_id_file'); ?>
</div>
<div class="clear"></div>
</div>
<!-- static_sitelogo 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_sitelogo'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_sitelogo'); ?>" type="text" name="static_sitelogo" value="<?php echo htmlspecialchars($static_sitelogo ?? '/'.$sitelogo ?? '/upload/images/logo.jpg'); ?>" />
</div>
<div class="info">
<?php echo L('static_sitelogo'); ?>
</div>
<div class="clear"></div>
</div>
<!-- static_ico 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_ico'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_ico'); ?>" type="text" name="static_ico" value="<?php echo htmlspecialchars($static_ico ?? '/'.$ico); ?>" />
</div>
<div class="info">
<?php echo L('static_ico'); ?>
</div>
<div class="clear"></div>
</div>
<!-- static_theme_dir 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_theme_dir'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_theme_dir'); ?>" type="text" name="static_theme_dir" value="<?php echo htmlspecialchars($static_config['static_theme_dir'] ?? $static_theme_dir); ?>" />
</div>
<div class="info">
<?php echo L('static_theme_dir'); ?>
</div>
<div class="clear"></div>
</div>

<!-- theme_name 配置项 -->
<?php
$theme_list = [];
$canScan = true;

if(is_dir($static_theme_dir)){
    $folders = glob($static_theme_dir . '*', GLOB_ONLYDIR);
    if($folders !== false){
        foreach($folders as $fullpath){
            $theme_list[] = basename($fullpath);
        }
    }else{
        // glob扫描失败
        $canScan = false;
    }
} else {
	echo L('static_theme_dir') . ': ' . htmlspecialchars($static_theme_dir) . ' — Theme directory does not exist. 主题目录不存在，请确认 files/theme 目录存在。';
	}

// 容错补充：没有识别到任何主题文件夹，预置默认值
if(empty($theme_list)){
    $theme_list[] = ($static_config['static_theme_name'] ?? 'Qsis_business');
}

// 当前选中主题
$current_theme = $static_theme_name ?? ($static_config['static_theme_name'] ?? ($theme_list[0] ?? 'Qsis_business'));
?>

<!-- theme_name 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_theme_name'); ?>
</div>
<div class="price">
<?php if($canScan): ?>
    <select class="input" name="static_theme_name">
        <?php foreach ($theme_list as $t): ?>
        <option value="<?=htmlspecialchars($t)?>" <?= ($t === $current_theme) ? 'selected' : '' ?>>
            <?=htmlspecialchars($t)?>
        </option>
        <?php endforeach; ?>
    </select>
<?php else: ?>
    <!-- 目录读取受限，降级为手动输入 -->
    <input class="input" placeholder="<?php echo L('theme_name_placeholder'); ?>" type="text" name="static_theme_name" value="<?php echo htmlspecialchars($current_theme); ?>" />
<?php endif; ?>
</div>
<div class="info">
<?php echo L('theme_name_desc'); ?>
</div>
<div class="clear"></div>
</div>

<!-- per_page 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_per_page'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('per_page_placeholder'); ?>" type="number" min="1" name="static_per_page" value="<?php echo htmlspecialchars($static_per_page ?? $per_page ?? '10'); ?>" />
</div>
<div class="info">
<?php echo L('per_page_desc'); ?>
</div>
<div class="clear"></div>
</div>

<!-- box_post_number 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_box_post_number'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_box_post_numbere_placeholder'); ?>" type="number" min="1" name="static_box_post_number" value="<?php echo htmlspecialchars($static_box_post_number ?? $box_post_number ?? '8'); ?>" />
</div>
<div class="info">
<?php echo L('static_box_post_number_desc'); ?>
</div>
<div class="clear"></div>
</div>

<!-- url_ends 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_url_ends'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('url_ends_placeholder'); ?>" type="text" name="static_url_ends" value="<?php echo htmlspecialchars($static_url_ends ?? $url_ends ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- footer_code 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_footer_code'); ?>
</div>
<div class="price">
<textarea class="input" placeholder="<?php echo L('footer_code_placeholder'); ?>" name="static_footer_code" rows="3"><?php echo htmlspecialchars($static_footer_code ?? $footer_code ?? ''); ?></textarea>
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- other_code 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_other_code'); ?>
</div>
<div class="price">
<textarea class="input" placeholder="<?php echo L('other_code_placeholder'); ?>" name="static_other_code" rows="3"><?php echo htmlspecialchars($static_other_code ?? $other_code ?? ''); ?></textarea>
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- head_code 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_head_code'); ?>
</div>
<div class="price">
<textarea class="input" placeholder="<?php echo L('head_code_placeholder'); ?>" name="static_head_code" rows="5"><?php echo htmlspecialchars($static_head_code ?? $head_code ?? ''); ?></textarea>
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- site_tags 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_site_tags'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('site_tags_placeholder'); ?>" type="text" name="static_site_tags" value="<?php echo htmlspecialchars($static_site_tags ?? $site_tags ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- language_name 配置项 -->
<div class="field">
<div class="label">
<?php echo L('language_name'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('language_name_placeholder'); ?>" type="text" name="static_language_name" value="<?php echo htmlspecialchars($static_language_name ?? $language_name ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- language_file 配置项 -->
<div class="field">
<div class="label">
<?php echo L('language_file'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('language_file_placeholder'); ?>" type="text" name="static_language_file" value="<?php echo htmlspecialchars($static_language_file ?? $language_file ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- robots.txt 配置项 -->
<div class="field">
<div class="label">
<?php echo L('IndexNow'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('IndexNow'); ?>" type="text" name="IndexNow" value="<?php echo htmlspecialchars($IndexNow ?? $IndexNow ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- ads.txt 配置项 -->
<?php if(isset($qs_adv_config['ads']) == 'open' ): ?>
<div class="field">
<div class="label">
<?php echo L('google_ads'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('google_ads'); ?>" type="text" name="google_ads" value="<?php echo htmlspecialchars($google_ads ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>
<?php endif; ?>

<!-- robots.txt 配置项 -->
<div class="field">
<div class="label">
<?php echo L('robots'); ?>
</div>
<div class="price">
	<textarea class="input" placeholder="<?php echo L('robots'); ?>" name="static_robots" rows="5"><?php echo htmlspecialchars($static_robots ?? $robots ?? ''); ?></textarea>
<div class="info"></div>
<div class="clear"></div>
</div>
</div>
</div>
<div class="clear"></div>