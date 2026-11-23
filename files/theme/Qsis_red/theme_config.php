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
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         theme_config.php
 * @Description  Template of the theme "Qsis_red": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_kicker' => array(
        'label'   => '首屏小字',
        'type'    => 'text',
        'default' => '观世事 · 悟本质 · 思有度 · 行有矩',
        'help'    => '显示在站名上方的一行小字，建议 6–18 字；留空则整行不显示。',
    ),
    'hero_title' => array(
        'label'   => '首屏大标题',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动使用站点名称（系统设置里的站点名）。',
    ),
    'hero_sub' => array(
        'label'   => '首屏副文案',
        'type'    => 'text',
        'default' => '',
        'help'    => '显示在大标题下方，一句话即可；留空则不显示。',
    ),
    'hero_bg' => array(
        'label'   => '首屏背景图',
        'type'    => 'image',
        'default' => 'image.jpg',
        'help'    => '铺在首屏右侧，左侧会自动压深红渐变保证文字可读。建议横图、宽度 1600px 以上。',
    ),
    'sidebar_img' => array(
        'label'   => '侧栏主图',
        'type'    => 'image',
        'default' => 'logo.jpg',
        'help'    => '侧栏第一块显示的方图，建议 240×240；留空则不显示这一块。',
    ),
    'sidebar_img_link' => array(
        'label'   => '侧栏主图链接',
        'type'    => 'text',
        'default' => '/?qiaoshuo/',
        'help'    => '点图片跳转的地址，留空则不可点。',
    ),
    'color_main' => array(
        'label'   => '主色',
        'type'    => 'color',
        'default' => '#9d1210',
        'help'    => '首屏底色、菜单底线、按钮与当前页码的颜色。',
    ),
    'color_deep' => array(
        'label'   => '主色（深）',
        'type'    => 'color',
        'default' => '#7d1512',
        'help'    => '页脚底色、正文标题颜色，一般比主色再深一档。',
    ),
    'color_accent' => array(
        'label'   => '点缀色',
        'type'    => 'color',
        'default' => '#c69a52',
        'help'    => '首屏小字、灯笼穗、分隔线与悬停高亮的颜色。',
    ),
    'show_lantern' => array(
        'label'   => '首屏灯笼',
        'type'    => 'switch',
        'default' => '1',
    ),
    'show_breadcrumb' => array(
        'label'   => '显示面包屑',
        'type'    => 'switch',
        'default' => '1',
        'help'    => '关闭后列表与标签页顶部不再显示「当前位置」。',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
