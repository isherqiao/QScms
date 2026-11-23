<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 */

/**
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         theme_config.php
 * @Description  Template of the theme "Qsis_game": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'logo_mode' => array(
        'label'   => '顶部标识 显示方式',
        'type'    => 'text',
        'default' => 'image',
        'help'    => '填 image：显示站点 Logo 图片 + 站点名；填 text：只显示下面的文字标题（图片缺失时切到 text 更干净）。',
    ),
    'logo_text' => array(
        'label'   => '文字标题',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动使用站点名称。',
    ),
    'logo_color' => array(
        'label'   => '文字标题颜色',
        'type'    => 'color',
        'default' => '#ff3131',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '单机游戏下载库',
    ),
    'hero_sub' => array(
        'label'   => '首屏副文案',
        'type'    => 'text',
        'default' => '本站所有资源均为高质量资源，各种姿势下载。',
        'help'    => '留空则不显示这一行。',
    ),
    'hero_bg1' => array(
        'label'   => '首屏背景 起色',
        'type'    => 'color',
        'default' => '#1b2a41',
    ),
    'hero_bg2' => array(
        'label'   => '首屏背景 止色',
        'type'    => 'color',
        'default' => '#0d1420',
    ),
    'hero_bg_image' => array(
        'label'   => '首屏背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '上传后铺满首屏，并在其上压深色遮罩保证文字可读。留空则只用上面的渐变色。',
    ),
    'search_placeholder' => array(
        'label'   => '搜索框提示文字',
        'type'    => 'text',
        'default' => '输入游戏名称、类型或关键词',
    ),
    'search_bg' => array(
        'label'   => '搜索框底色',
        'type'    => 'color',
        'default' => '#ffffff',
    ),
    'page_bg' => array(
        'label'   => '列表区底色',
        'type'    => 'color',
        'default' => '#eef2f7',
        'help'    => '首屏下面卡片区、列表页的整块背景色。',
    ),
    'card_bg' => array(
        'label'   => '卡片底色',
        'type'    => 'color',
        'default' => '#ffffff',
    ),
    'accent' => array(
        'label'   => '强调色',
        'type'    => 'color',
        'default' => '#ff7a45',
        'help'    => '播放徽标、关键词高亮、按钮等点缀色。',
    ),
    'text_color' => array(
        'label'   => '卡片标题颜色',
        'type'    => 'color',
        'default' => '#111826',
        'help'    => '卡片底色改深色时，把这里同时改成浅色，标题才看得清。',
    ),
    'muted_color' => array(
        'label'   => '日期/标签 文字颜色',
        'type'    => 'color',
        'default' => '#6b7a90',
    ),
    'show_tags' => array(
        'label'   => '卡片显示标签',
        'type'    => 'switch',
        'default' => '1',
    ),
    'per_page' => array(
        'label'   => '每页条数',
        'type'    => 'number',
        'default' => '10',
        'help'    => '留 0 或留空则沿用系统设置。',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);

/* 模板里取值的小工具，避免每处都写 isset 判断 */
if (!function_exists('game_opt')) {
    function game_opt($key, $default = '')
    {
        global $theme_options;
        if (!is_array($theme_options)) {
            return $default;
        }
        if (!isset($theme_options[$key])) {
            return $default;
        }
        $v = is_scalar($theme_options[$key]) ? (string)$theme_options[$key] : '';
        return ($v === '') ? $default : $v;
    }
}
?>
