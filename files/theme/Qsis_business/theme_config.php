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
 * @Description  Template of the theme "Qsis_business": option schema and effective values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

if (!function_exists('qs_theme_opts_load')) {
    require_once __DIR__ . '/../../../control/theme_options_lib.php';
}

$theme_schema = array(
    'banner_bg' => array(
        'label'   => '首屏横幅 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '上传一张横图（建议 1600×900 以上），会铺满首屏并在其上压深色遮罩保证文字可读。',
    ),
    'banner_title' => array(
        'label'   => '横幅主标题',
        'type'    => 'text',
        'default' => '规范流程，稳定交付',
    ),
    'banner_sub' => array(
        'label'   => '横幅副文案',
        'type'    => 'text',
        'default' => '',
        'help'    => '留空则自动取站点描述（超过 96 字截断）。',
    ),
    'banner_btn1_text' => array(
        'label'   => '按钮一 文字',
        'type'    => 'text',
        'default' => '获取方案',
    ),
    'banner_btn1_link' => array(
        'label'   => '按钮一 链接',
        'type'    => 'text',
        'default' => '/contact.html',
    ),
    'banner_btn2_text' => array(
        'label'   => '按钮二 文字',
        'type'    => 'text',
        'default' => '查看服务',
    ),
    'banner_btn2_link' => array(
        'label'   => '按钮二 链接',
        'type'    => 'text',
        'default' => '#services',
    ),
    'facts' => array(
        'label'   => '横幅右侧概览',
        'type'    => 'textarea',
        'default' => "方案设计\n工程实施\n验收交付\n长期运维",
        'help'    => '每行一项，最多 6 行。留空则不显示这一栏。',
    ),
    'color_brand' => array(
        'label'   => '主色（深色）',
        'type'    => 'color',
        'default' => '#1d3b34',
    ),
    'color_accent' => array(
        'label'   => '点缀色（亮色）',
        'type'    => 'color',
        'default' => '#a9803f',
    ),
    'services_title' => array(
        'label'   => '服务区标题',
        'type'    => 'text',
        'default' => '服务矩阵',
    ),
    'services_note' => array(
        'label'   => '服务区说明',
        'type'    => 'text',
        'default' => '按项目生命周期划分，每一段都有明确的输入、输出与验收标准。',
    ),
    'services' => array(
        'label'   => '服务矩阵',
        'type'    => 'textarea',
        'default' => "01|现状诊断|梳理现有系统与流程，定位瓶颈与风险点，形成书面诊断结论。\n02|方案设计|给出分层方案与实施路径，明确边界、投入与时间表。\n03|工程实施|按里程碑推进，阶段可验证，变更留痕，风险提前暴露。\n04|质量验收|对照验收清单逐项确认，交付文档与操作手册同步完成。\n05|培训移交|面向使用方与维护方分别培训，确保接手即可运转。\n06|持续运维|定期巡检与响应机制，问题闭环，性能持续优化。",
        'help'    => '每行一格，格式：编号|标题|描述。最多 6 行。这里的文字就是前台六张卡片显示的内容。',
    ),
    'svc1_bg' => array(
        'label'   => '服务卡 1 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 1 行（现状诊断）。留空则用纯色底。',
    ),
    'svc2_bg' => array(
        'label'   => '服务卡 2 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 2 行（方案设计）。',
    ),
    'svc3_bg' => array(
        'label'   => '服务卡 3 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 3 行（工程实施）。',
    ),
    'svc4_bg' => array(
        'label'   => '服务卡 4 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 4 行（质量验收）。',
    ),
    'svc5_bg' => array(
        'label'   => '服务卡 5 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 5 行（培训移交）。',
    ),
    'svc6_bg' => array(
        'label'   => '服务卡 6 背景图',
        'type'    => 'image',
        'default' => '',
        'help'    => '对应上面第 6 行（持续运维）。',
    ),
    'adv_show' => array(
        'label'   => '显示优势区',
        'type'    => 'switch',
        'default' => '1',
    ),
    'adv_title' => array(
        'label'   => '优势区标题',
        'type'    => 'text',
        'default' => '把不确定留给沟通，把确定交给流程',
    ),
    'adv_items' => array(
        'label'   => '优势条目',
        'type'    => 'textarea',
        'default' => "需求变更走书面确认，不靠口头传递\n每个阶段都有可核对的交付物\n问题响应有时限，处理结果可回溯\n不承诺做不到的事，承诺的按约完成",
        'help'    => '每行一条，最多 6 行。',
    ),
    'adv_caption' => array(
        'label'   => '优势区数字说明',
        'type'    => 'text',
        'default' => '已公开的案例与说明',
        'help'    => '数字取自站内已发布内容总数。',
    ),
    'feed_title' => array(
        'label'   => '案例与动态 标题',
        'type'    => 'text',
        'default' => '案例与动态',
    ),
    'feed_count' => array(
        'label'   => '动态条数',
        'type'    => 'number',
        'default' => '8',
    ),
    'contact_show' => array(
        'label'   => '显示底部联系条',
        'type'    => 'switch',
        'default' => '1',
    ),
    'contact_title' => array(
        'label'   => '联系条 标题',
        'type'    => 'text',
        'default' => '需要一份可执行的方案',
    ),
    'contact_sub' => array(
        'label'   => '联系条 副文案',
        'type'    => 'text',
        'default' => '告诉我们项目背景与目标，我们会给出初步判断与下一步建议。',
    ),
    'contact_btn' => array(
        'label'   => '联系条 按钮文字',
        'type'    => 'text',
        'default' => '联系我们',
    ),
    'custom_scrollbar' => array(
        'label'   => '自定义滚动条',
        'type'    => 'switch',
        'default' => '1',
        'help'    => '开启后滚动条会按主题配色显示（仅现代浏览器生效）。',
    ),
);

$theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
