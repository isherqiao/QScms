<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         zh-CN.php
 * @CreatedTime  15/11/26 03:10:00
 * @LastEditTime 15/11/26 03:10:00
 * @Description  Cookie consent language template: Simplified Chinese.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie 同意组件：简体中文文案
// 占位符：%s = 站点名称，{manage} = 管理首选项链接，{privacy} = 隐私政策链接

return array(
    'name'  => '中文',
    'html_lang' => 'zh-CN',
    'dir'   => 'ltr',

    'banner_title'  => '%s重视您的隐私',
    'banner_text'   => '本站和第三方供应商使用 Cookie 存储访问唯一 ID、个人信息，用于提供、维护、改进服务与广告。如果您同意，本站将个性化展示内容与广告。您可以选择“我接受”同意全部用途，或 {manage} 查看选项。您可随时在管理首选项中撤回同意。{privacy}',

    'link_manage'        => '管理首选项',
    'link_manage_cookie' => '管理 Cookie 设置',
    'link_privacy'       => '隐私政策',

    'btn_accept'  => '我接受',
    'btn_reject'  => '全部拒绝',
    'btn_ignore'  => '忽略',
    'btn_confirm' => '确认我的选择',

    'modal_title' => '关于你的隐私',
    'modal_intro' => '我们处理你的数据以提供内容和广告，衡量投放效果，并基于合法利益与合作伙伴共享信息。',

    'cat_storage_name'   => '在设备上存储或访问信息',
    'cat_storage_desc'   => '存储或访问设备上的信息，例如唯一标识符、Cookie 等。',
    'cat_ads_name'       => '个性化广告和内容',
    'cat_ads_desc'       => '根据您的兴趣、访问记录和设备信息展示个性化广告与内容。',
    'cat_measure_name'   => '个性化内容和广告效果衡量',
    'cat_measure_desc'   => '收集关于内容和广告的访问数据，用于衡量效果并改进服务。',
    'cat_geo_name'       => '使用精确的地理位置数据',
    'cat_geo_desc'       => '经您同意后，使用精确地理位置数据提供相关服务。',
    'cat_analytics_name' => '第三方分析',
    'cat_analytics_desc' => '使用第三方分析服务了解网站访问情况和用户行为。',
    'cat_social_name'    => '社交媒体 Cookie',
    'cat_social_desc'    => '用于社交媒体功能、分享组件和相关登录服务。',

    'always_on'     => '始终启用',
    'required_hint' => '必要项，不可关闭',

    'saved'       => '已记录你的选择',
    'save_failed' => '保存失败，请稍后重试',
    'reopen'      => '重新设置 Cookie 选项',

    'version_label' => '版本',
    'updated_label' => '更新日期',
);
