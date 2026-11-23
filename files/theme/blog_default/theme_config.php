<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/**
 * QsCms 项目文件
 *
 * @File         theme_config.php
 * @Description  Template of the theme "blog_default": theme option schema.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_eyebrow' => array(
        'label'   => '首屏小字',
        'type'    => 'text',
        'default' => 'JOURNAL',
        'help'    => '首屏上方那行小字，留空则不显示。',
    ),
    'hero_title' => array(
        'label'   => '首屏大标题',
        'type'    => 'text',
        'default' => '记录日常，也记录方法',
        'help'    => '建议 8 到 18 个字。',
    ),
    'hero_text' => array(
        'label'   => '首屏副文案',
        'type'    => 'text',
        'default' => '随手写下的观察、经验与结论，想到就写。',
        'help'    => '留空则取站点描述。',
    ),
    'side_about' => array(
        'label'   => '侧栏「关于」内容',
        'type'    => 'textarea',
        'default' => '',
        'help'    => '留空则用站点描述。',
    ),
    'color_brand' => array(
        'label'   => '主色',
        'type'    => 'color',
        'default' => '#2f6f5e',
        'help'    => '站名、按钮、链接用色。',
    ),
    'color_accent' => array(
        'label'   => '强调色',
        'type'    => 'color',
        'default' => '#c98b3c',
        'help'    => '小标题、标签用色。',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
        'help'    => '开启后滚动条跟随主色。',
    ),
);
