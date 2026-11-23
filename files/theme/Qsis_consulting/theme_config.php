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
 * @Description  Template of the theme "Qsis_consulting": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'intro_image' => array(
        'label'   => '首屏右侧大图',
        'type'    => 'image',
        'default' => '',
        'help'    => '留空则用主题自带的配图（intro.jpg）。上传后替换首屏右侧的大图，建议竖图（4:5 或 1:1）。',
    ),
    'intro_image_caption' => array(
        'label'   => '右侧图下方注脚',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则不显示。',
    ),
    'intro_label' => array(
        'label'   => '引言顶部小字',
        'type'    => 'text',
        'default' => '独立顾问 · 长期合作',
    ),
    'intro_title' => array(
        'label'   => '引言大标题',
        'type'    => 'text',
        'default' => '把判断做对，再把事情做完',
    ),
    'intro_text' => array(
        'label'   => '引言正文',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动取站点描述（超过 96 字截断）。',
    ),
    'link1_text' => array(
        'label'   => '主链接 文字',
        'type'    => 'text',
        'default' => '预约一次沟通',
    ),
    'link1_href' => array(
        'label'   => '主链接 地址',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'link2_text' => array(
        'label'   => '次链接 文字',
        'type'    => 'text',
        'default' => '看看我们做过什么',
    ),
    'link2_href' => array(
        'label'   => '次链接 地址',
        'type'    => 'text',
        'default' => '#practice',
    ),
    'color_accent' => array(
        'label'   => '点缀色',
        'type'    => 'color',
        'default' => '#b5651d',
        'help'    => '用于小标题、分隔线、分页选中。',
    ),
    'color_ink' => array(
        'label'   => '正文色',
        'type'    => 'color',
        'default' => '#262b2e',
    ),
    'practice_title' => array(
        'label'   => '三件事 标题',
        'type'    => 'text',
        'default' => '我们在做的三件事',
    ),
    'practice_note' => array(
        'label'   => '三件事 说明',
        'type'    => 'text',
        'default' => '不是所有问题都需要推倒重来。多数时候，先把判断做对，比加法更重要。',
    ),
    'service_items' => array(
        'label'   => '三件事 条目',
        'type'    => 'textarea',
        'default' => "判断与诊断|把模糊的困扰拆成可回答的问题，给出书面判断，而不是一句\"再看看\"。\n方案与取舍|方案的价值在于取舍。我们给出两到三条路径，说明代价，再一起选一条。\n陪伴与复盘|阶段性复盘，把过程中的经验沉淀成可复用的方法，而不是一次性结论。",
        'help'    => '每行一条，格式：标题|描述。最多 5 行。',
    ),
    'notes_title' => array(
        'label'   => '笔记区标题',
        'type'    => 'text',
        'default' => '最近写下的内容',
    ),
    'feed_count' => array(
        'label'   => '笔记条数',
        'type'    => 'number',
        'default' => '7',
    ),
    'quote_show' => array(
        'label'   => '显示结尾引句',
        'type'    => 'switch',
        'default' => '1',
    ),
    'quote_text' => array(
        'label'   => '引句内容',
        'type'    => 'text',
        'default' => '把不确定留给自己，把确定交给你。',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
        'help'    => '开启后滚动条会按主题配色显示（仅现代浏览器生效）。',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
