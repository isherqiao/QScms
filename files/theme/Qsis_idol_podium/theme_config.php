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
 * @Description  Template of the theme "Qsis_idol_podium": option schema and effective values.
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
        'default' => 'IDOL FORUM / TALK 2026',
        'help'    => '显示在首屏主标题上方，一行演唱会编号。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '把话说给一万个人听',
        'help'    => '建议 5 到 14 个字，居中大字最有海报感。',
    ),
    'hero_lead' => array(
        'label' => '首屏副文案',
        'type'  => 'text',
        'default' => '',
        'help'  => '留空则自动取站点描述（超过 96 字截断）。',
    ),
    'btn1_text' => array(
        'label'   => '按钮一 文字',
        'type'    => 'text',
        'default' => '看演讲日程',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '留下提问',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '主色（会场青）',
        'type'    => 'color',
        'default' => '#25b3a3',
    ),
    'color_accent' => array(
        'label'   => '点缀色（冷雾白）',
        'type'    => 'color',
        'default' => '#d7f2ec',
        'help'    => '用于编号、细线、追光与悬停高亮。',
    ),
    'hud_title' => array(
        'label'   => '场次信息 标题',
        'type'    => 'text',
        'default' => '本场信息',
    ),
    'hud_items' => array(
        'label'   => '场次信息 条目',
        'type'    => 'textarea',
        'default' => "场馆|国际会展中心\n开讲|14:00\n席位|A 区 3 排\n场次|第 8 场",
        'help'    => '每行一项，格式：名称|状态。最多 6 行。',
    ),
    'metrics_show' => array(
        'label'   => '显示数字刻度条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'metrics' => array(
        'label'   => '数字刻度条 内容',
        'type'    => 'textarea',
        'default' => "92|演讲场次\n560万|到场听众\n21|巡回城市\n96%|场场满座",
        'help'    => '每行一项，格式：数值|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '列表 标题',
        'type'    => 'text',
        'default' => '讲台下的记录',
    ),
    'feed_count' => array(
        'label'   => '列表条数',
        'type'    => 'number',
        'default' => '8',
    ),
    'cta_show' => array(
        'label'   => '显示底部行动条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'cta_title' => array(
        'label'   => '行动条 标题',
        'type'    => 'text',
        'default' => '想递上一张问题卡',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '把想问的问题写下来，我会在台上点开它。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '递上问题卡',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);