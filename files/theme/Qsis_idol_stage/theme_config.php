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
 * @Description  Template of the theme "Qsis_idol_stage": option schema and effective values.
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
        'default' => 'IDOL STAGE / LIVE 2026',
        'help'    => '显示在首屏主标题上方，一行演唱会编号。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '灯亮起的那一秒',
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
        'default' => '看巡演日程',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '留下应援',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '主色（舞台紫）',
        'type'    => 'color',
        'default' => '#7c5cff',
    ),
    'color_accent' => array(
        'label'   => '点缀色（追光白）',
        'type'    => 'color',
        'default' => '#e6d8ff',
        'help'    => '用于编号、细线、追光与悬停高亮。',
    ),
    'hud_title' => array(
        'label'   => '场次信息 标题',
        'type'    => 'text',
        'default' => '今晚场次',
    ),
    'hud_items' => array(
        'label'   => '场次信息 条目',
        'type'    => 'textarea',
        'default' => "场馆|国家体育馆\n开演|20:00\n座位|内场 A 区\n巡演站|第 12 站",
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
        'default' => "186|演出场次\n1280万|应援人次\n36|巡演城市\n99.2%|场场售罄",
        'help'    => '每行一项，格式：数值|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '列表 标题',
        'type'    => 'text',
        'default' => '台下的记录',
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
        'default' => '想收到一张后台通行证',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '把想说的话写在应援牌上，我会在谢幕时看见它。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '递上应援牌',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);