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
 * @File         en.php
 * @CreatedTime  15/11/26 03:10:00
 * @LastEditTime 15/11/26 03:10:00
 * @Description  Cookie consent language template: English.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie consent component: English copy
// Placeholders: %s = site name, {manage} = manage-preferences link, {privacy} = privacy-policy link

return array(
    'name'  => 'English',
    'html_lang' => 'en',
    'dir'   => 'ltr',

    'banner_title'  => '%s values your privacy',
    'banner_text'   => 'This site and third-party providers use cookies to store unique IDs and personal information to provide, maintain and improve services and advertising. If you agree, personalised content and ads may be displayed. You may select “Accept all” to consent to all purposes, or {manage} to review options. You may withdraw your consent at any time in preferences. {privacy}',

    'link_manage'        => 'Manage preferences',
    'link_manage_cookie' => 'Manage Cookie settings',
    'link_privacy'       => 'Privacy Policy',

    'btn_accept'  => 'Accept all',
    'btn_reject'  => 'Reject all',
    'btn_ignore'  => 'Dismiss',
    'btn_confirm' => 'Confirm my choices',

    'modal_title' => 'About your privacy',
    'modal_intro' => 'We process your data to deliver content and ads, measure campaign performance and share information with partners based on legitimate interests.',

    'cat_storage_name'   => 'Store or access information on your device',
    'cat_storage_desc'   => 'Store or access information on your device such as unique identifiers and cookies.',
    'cat_ads_name'       => 'Personalised ads and content',
    'cat_ads_desc'       => 'Show personalised ads and content based on your interests, visits and device information.',
    'cat_measure_name'   => 'Measurement of personalised content and ad performance',
    'cat_measure_desc'   => 'Collect visit statistics for content and ads to measure performance and improve services.',
    'cat_geo_name'       => 'Use precise geolocation data',
    'cat_geo_desc'       => 'Use precise geolocation data to deliver relevant services upon your consent.',
    'cat_analytics_name' => 'Third-party analytics',
    'cat_analytics_desc' => 'Use third-party analytics to understand website traffic and user behaviour.',
    'cat_social_name'    => 'Social media cookies',
    'cat_social_desc'    => 'Used for social-media features, share components and related login services.',

    'always_on'     => 'Always on',
    'required_hint' => 'Required, cannot be switched off',

    'saved'       => 'Your choices have been recorded',
    'save_failed' => 'Could not save your choices, please try again later',
    'reopen'      => 'Manage Cookie settings',

    'version_label' => 'Version',
    'updated_label' => 'Last updated',
);
