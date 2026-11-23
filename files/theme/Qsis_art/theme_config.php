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
 * @File         theme_config.php
 * @Description  Template of the theme "Qsis_art": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_eyebrow' => array(
        'label'   => '首屏小标签',
        'type'    => 'text',
        'default' => '软软的，但很可靠',
        'help'    => '留空则显示站点名称。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '慢慢来，也能走到很远',
        'help'    => '建议 10 到 18 个字。',
    ),
    'hero_lead' => array(
        'label'   => '首屏副文案',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动取站点描述（超过 96 字截断）。',
    ),
    'btn1_text' => array(
        'label'   => '按钮一 文字',
        'type'    => 'text',
        'default' => '从这里开始',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '关于这里',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '正文色',
        'type'    => 'color',
        'default' => '#3a2b31',
    ),
    'color_accent' => array(
        'label'   => '点缀色',
        'type'    => 'color',
        'default' => '#ff8a71',
        'help'    => '用于按钮、标签和小装饰。',
    ),
    'cards_title' => array(
        'label'   => '卡片区标题',
        'type'    => 'text',
        'default' => '这里有什么',
    ),
    'cards' => array(
        'label'   => '卡片内容',
        'type'    => 'textarea',
        'default' => "日常记录|不宏大，但都是真实发生过的\n方法拆解|把做过的事写成能复用的步骤\n慢慢更新|不追速度，只保证每篇都值得看",
        'help'    => '每行一张卡，格式：标题|说明。最多 6 行。',
    ),
    'feed_title' => array(
        'label'   => '内容区标题',
        'type'    => 'text',
        'default' => '最近写下的',
    ),
    'feed_count' => array(
        'label'   => '内容条数',
        'type'    => 'number',
        'default' => '6',
    ),
    'cta_show' => array(
        'label'   => '显示底部关注条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'cta_title' => array(
        'label'   => '关注条 标题',
        'type'    => 'text',
        'default' => '想第一时间看到更新',
    ),
    'cta_lead' => array(
        'label'   => '关注条 副文案',
        'type'    => 'text',
        'default' => '收藏这个页面，或者直接留个言，都会看到。',
    ),
    'cta_btn' => array(
        'label'   => '关注条 按钮文字',
        'type'    => 'text',
        'default' => '留个言',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
