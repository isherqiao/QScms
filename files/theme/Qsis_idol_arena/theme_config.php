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
 * @Description  Template of the theme "Qsis_idol_arena": option schema and effective values.
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
        'default' => 'IDOL ARENA / WORLD TOUR',
        'help'    => '显示在首屏主标题上方，一行演唱会编号。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '全场一起喊我名字',
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
        'default' => '看巡演场次',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '亮起灯牌',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '主色（应援红）',
        'type'    => 'color',
        'default' => '#e04a35',
    ),
    'color_accent' => array(
        'label'   => '点缀色（灯牌金）',
        'type'    => 'color',
        'default' => '#f7d9a8',
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
        'default' => "场馆|中心体育场\n开演|19:30\n区域|内场 B 区\n巡演|第 30 站",
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
        'default' => "240|演出场次\n2100万|到场观众\n48|巡演城市\n100%|场场售罄",
        'help'    => '每行一项，格式：数值|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '列表 标题',
        'type'    => 'text',
        'default' => '人群里的记录',
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
        'default' => '想让灯牌连成一片海',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '把想喊的名字写在灯牌上，我会在安可时看见它。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '举起灯牌',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);