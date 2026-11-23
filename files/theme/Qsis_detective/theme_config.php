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
 * @Description  Template of the theme "Qsis_detective": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_eyebrow' => array(
        'label'   => '首屏案卷编号',
        'type'    => 'text',
        'default' => 'CASE FILE / NO.001',
        'help'    => '显示在首屏左上角，像一页案卷的编号。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '真相藏在细节里',
        'help'    => '建议 6 到 16 个字，越克制越有侦探片的分量。',
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
        'default' => '翻看案卷',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '递线索',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '主色（领结红）',
        'type'    => 'color',
        'default' => '#b3171c',
    ),
    'color_accent' => array(
        'label'   => '点缀色（黄铜）',
        'type'    => 'color',
        'default' => '#c6a052',
        'help'    => '用于编号、细线、放大镜边框与悬停高亮。',
    ),
    'hud_title' => array(
        'label'   => '右栏卡片 标题',
        'type'    => 'text',
        'default' => '案件要点',
    ),
    'hud_items' => array(
        'label'   => '右栏卡片 条目',
        'type'    => 'textarea',
        'default' => "线索梳理|已归档\n逻辑推演|进行中\n证言核对|待确认\n结论|保留",
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
        'default' => "276|已归档\n297|案卷条目\n24h|响应窗口\n99.9%|在线率",
        'help'    => '每行一项，格式：数值|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '案卷列表 标题',
        'type'    => 'text',
        'default' => '最近归档的案卷',
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
        'default' => '有疑点，就从这里开始',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '把现象、时间线和已知条件写下来，线索会自己连成一条路。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '递一条线索',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
