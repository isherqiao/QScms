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
 * @Description  Template of the theme "Qsis_maruko": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_eyebrow' => array(
        'label'   => '首屏页签',
        'type'    => 'text',
        'default' => '手账 / 第 001 页',
        'help'    => '显示在首屏左上角，像贴上去的一张小标签。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '日子要慢慢过',
        'help'    => '建议 5 到 14 个字，短一点更像说话。',
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
        'default' => '翻开手账',
    ),
    'btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '#feed',
    ),
    'btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '写一句',
    ),
    'btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'color_main' => array(
        'label'   => '主色（樱桃红）',
        'type'    => 'color',
        'default' => '#df4a4a',
    ),
    'color_accent' => array(
        'label'   => '点缀色（奶油黄）',
        'type'    => 'color',
        'default' => '#ffcf5c',
        'help'    => '用于小圆点、分割装饰和悬停高亮。',
    ),
    'hud_title' => array(
        'label'   => '右栏卡片 标题',
        'type'    => 'text',
        'default' => '今日清单',
    ),
    'hud_items' => array(
        'label'   => '右栏卡片 条目',
        'type'    => 'textarea',
        'default' => "早饭|吃饱了\n作业|写完了\n散步|半小时\n心情|还不错",
        'help'    => '每行一项，格式：事项|状态。最多 6 行。',
    ),
    'metrics_show' => array(
        'label'   => '显示数字刻度条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'metrics' => array(
        'label'   => '数字刻度条 内容',
        'type'    => 'textarea',
        'default' => "276|记下的日子\n297|手账页数\n24h|回信时间\n99.9%|在线率",
        'help'    => '每行一项，格式：数值|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '手账列表 标题',
        'type'    => 'text',
        'default' => '最近写下的几页',
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
        'default' => '今天想写点什么',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '一句话也好，写下来就成了今天的一页。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '写一句',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
