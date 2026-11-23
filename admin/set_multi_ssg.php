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
 * @File         set_multi_ssg.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 01/09/26 17:39:56
 * @Description  Multi-site static-generation settings: selects which sites the SSG run should publish.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

?>

<div class="admin_page_name">
	<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
<a href=set_multi_ssg.php  <?php echo $page_file == 'set_multi_ssg.php' ? 'class="current"' : ''; ?>><?= L('Total_Config_Items'); ?></a>
<a href=set_multi_site.php  <?php echo $page_file == 'set_multi_site.php' ? 'class="current"' : ''; ?>><?= L('multi_site_manage'); ?></a>
</div>
 		<div class="clear"></div>
 		 <div class="small_form small_form2">
<div class="clear"></div>

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

<!-- static_theme_dir 配置项 -->
<div class="field">
<div class="label">
<?php echo L('static_theme_dir'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('static_theme_dir'); ?>" type="text" name="static_theme_dir" value="<?php echo htmlspecialchars($static_theme_dir ?? $static_config['static_theme_dir'] ?? 'goto core.php set'); ?>" />
</div>
<div class="info">
<?php echo L('static_theme_dir'); ?>
</div>
<div class="clear"></div>
</div>

<?php require 'foot.php'; ?>
