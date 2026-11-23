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
 * @Description  Template of the theme "Qsis_corp": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'hero_eyebrow' => array(
        'label'   => '首屏小标题',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则显示站点名称。',
    ),
    'hero_title' => array(
        'label'   => '首屏主标题',
        'type'    => 'text',
        'default' => '把复杂的事，做成可靠的结果',
    ),
    'hero_lead' => array(
        'label'   => '首屏副文案',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动取站点描述（超过 96 字截断）。',
    ),
    'hero_btn1_text' => array(
        'label'   => '按钮一 文字',
        'type'    => 'text',
        'default' => '联系我们',
    ),
    'hero_btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '/contact.html',
        'help'    => '支持以 / 开头的站内路径，或 #services 这样的锚点。',
    ),
    'hero_btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '了解能力',
    ),
    'hero_btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '#services',
    ),
    'color_brand' => array(
        'label'   => '主色（深色）',
        'type'    => 'color',
        'default' => '#1b3a57',
        'help'    => '用于标题、导航选中、数据条背景等。',
    ),
    'color_accent' => array(
        'label'   => '点缀色（亮色）',
        'type'    => 'color',
        'default' => '#b8844b',
        'help'    => '用于按钮、小标题、卡片左边线。',
    ),
    'services_title' => array(
        'label'   => '能力区标题',
        'type'    => 'text',
        'default' => '我们提供的核心能力',
    ),
    'services' => array(
        'label'   => '能力卡片',
        'type'    => 'textarea',
        'default' => "01|需求与方案|先听懂问题，再给出可执行的方案。用清晰的边界和节奏，减少反复与返工。\n02|实施与交付|按阶段推进，每阶段可验证、可回溯。交付即上手，附带说明与培训。\n03|长期运维|上线不是终点。持续巡检、响应与优化，让系统在真实流量里稳得住。",
        'help'    => '每行一张卡片，格式：编号|标题|描述。最多 6 行。',
    ),
    'metrics_show' => array(
        'label'   => '显示数据条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'metrics' => array(
        'label'   => '数据条内容',
        'type'    => 'textarea',
        'default' => "276|已发布内容\n10+|行业覆盖\n24h|响应时效\n99.9%|稳定运行",
        'help'    => '每行一项，格式：数字|说明。最多 4 行。',
    ),
    'feed_title' => array(
        'label'   => '动态区标题',
        'type'    => 'text',
        'default' => '最新动态',
    ),
    'feed_count' => array(
        'label'   => '动态条数',
        'type'    => 'number',
        'default' => '6',
        'help'    => '首页显示的动态条数，建议 4 到 8。',
    ),
    'cta_show' => array(
        'label'   => '显示底部行动条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'cta_title' => array(
        'label'   => '行动条 标题',
        'type'    => 'text',
        'default' => '准备好开始一次合作了吗',
    ),
    'cta_lead' => array(
        'label'   => '行动条 副文案',
        'type'    => 'text',
        'default' => '把您的需求告诉我们，我们会在一个工作日内给出初步判断。',
    ),
    'cta_btn' => array(
        'label'   => '行动条 按钮文字',
        'type'    => 'text',
        'default' => '发起咨询',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
        'help'    => '开启后滚动条会按主题配色显示（仅现代浏览器生效）。',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
