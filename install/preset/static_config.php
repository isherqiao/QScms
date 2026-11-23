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
 * @File         static_config.php
 * @CreatedTime  05/11/26 23:05:52
 * @LastEditTime 05/11/26 23:05:52
 * @Description  Preset data: default static-generation configuration, copied into control/data during installation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Static site config (独立ssg存储 static_ 配置)
$static_config = array (
  'static_root_url' => 'http://127.0.0.1:90',
  'local_upload_dir' => '../upload',
  'static_output' => '../static_site',
  'static_data_posts' => '../data/posts/data',
  'static_data_pages' => '../data/pages/data',
  'static_publish_posts' => '../data/posts/index/publish.php',
  'static_publish_pages' => '../data/pages/index/publish.php',
  'static_theme_name' => 'Qsis_swbj',
  'static_per_page' => '10',
  'static_url_ends' => '.qshtml',
  'static_upload_dir' => '/upload/',
  'static_site_theme' => 'Qsis_swbj',
  'static_sitelogo' => '',
  'static_ico' => 'files/images/btn.jpg',
  'static_site_backgrouppic' => '',
  'static_footer_code' => 'Powered by <a href="https://cms.qsis.cn" target="_blank">QScms</a>. Copyright © 2026 QScms Demo All Rights Reserved.',
  'static_other_code' => '',
  'static_head_code' => '',
  'static_site_tags' => 'QScms,建站系统,静态站,内容管理,示例站点',
  'static_language_name' => '简体中文',
  'static_language_file' => 'lang_zh-CN.php',
  'static_robots' => 'User-agent: *
Disallow: /?
Allow: /sitemap.xml
Allow: /sitemap.txt

Sitemap: http://127.0.0.1:90/sitemap.xml
Sitemap: http://127.0.0.1:90/sitemap.txt',
  'static_box_post_number' => '8',
  'IndexNow' => '',
  'static_theme_dir' => '../files/theme/',
  'google_ads' => '',
);
?>