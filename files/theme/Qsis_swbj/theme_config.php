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
 * @File         theme_config.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_swbj": theme metadata and default option values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;
$theme_tag_blocks = array (
  0 => 
  array (
    'enable' => true,
    'tag_name' => '认知心理',
    'tag_desc' => '认知逻辑与心理洞察',
    'limit' => NULL,
  ),
  1 => 
  array (
    'enable' => true,
    'tag_name' => '实操方略',
    'tag_desc' => '人文思考与社会观察',
    'limit' => NULL,
  ),
  2 => 
  array (
    'enable' => true,
    'tag_name' => '格物思理',
    'tag_desc' => '观世事悟本质',
    'limit' => NULL,
  ),
  3 => 
  array (
    'enable' => true,
    'tag_name' => '人生成长',
    'tag_desc' => '自我提升与人生感悟',
    'limit' => NULL,
  ),
);

// 首屏轮播：主题设置里可编辑，固定 3 条（默认用主题自带的 3 张大图）
$theme_banners = array (
  0 => 
  array (
    'enable' => true,
    'img' => '/files/theme/Qsis_swbj/banner/banner1.jpg',
    'text' => '',
    'link' => '',
  ),
  1 => 
  array (
    'enable' => true,
    'img' => '/files/theme/Qsis_swbj/banner/banner2.jpg',
    'text' => '',
    'link' => '',
  ),
  2 => 
  array (
    'enable' => true,
    'img' => '/files/theme/Qsis_swbj/banner/banner3.jpg',
    'text' => '',
    'link' => '',
  ),
);

// 后台保存的自定义配置优先：存在则覆盖上面的默认值
// 文件版统一把主题配置存在 control/data/<主题目录名>/ 下
$qs_swbj_store = SITE_ROOT . '/control/data/Qsis_swbj/theme_config.php';
if (is_file($qs_swbj_store)) {
    include $qs_swbj_store;
}
?>