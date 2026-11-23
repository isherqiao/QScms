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
 * @File         ja.php
 * @CreatedTime  15/11/26 03:10:00
 * @LastEditTime 15/11/26 03:10:00
 * @Description  Cookie consent language template: Japanese.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie 同意コンポーネント：日本語テキスト
// プレースホルダー：%s = サイト名、{manage} = 設定管理リンク、{privacy} = プライバシーポリシーリンク

return array(
    'name'  => '日本語',
    'html_lang' => 'ja',
    'dir'   => 'ltr',

    'banner_title'  => '%s はプライバシーを重視しています',
    'banner_text'   => '本サイトおよび第三者プロバイダーはCookieを使用し、一意のIDや個人情報を保存し、サービス・広告の提供、維持、改善に利用します。同意すると、カスタマイズされたコンテンツや広告が表示されます。「すべて承諾」で全ての目的に同意するか、{manage} からオプションを確認できます。設定からいつでも同意を取り消せます。{privacy}',

    'link_manage'        => '設定を管理',
    'link_manage_cookie' => 'Cookie設定を管理',
    'link_privacy'       => 'プライバシーポリシー',

    'btn_accept'  => 'すべて承諾',
    'btn_reject'  => 'すべて拒否',
    'btn_ignore'  => '閉じる',
    'btn_confirm' => '選択を確認',

    'modal_title' => 'プライバシーについて',
    'modal_intro' => 'コンテンツ・広告配信、効果測定、正当な利益に基づくパートナーとの情報共有のためデータを処理します。',

    'cat_storage_name'   => 'デバイス上の情報の保存・参照',
    'cat_storage_desc'   => '一意の識別子、Cookieなどの情報をデバイスに保存または参照します。',
    'cat_ads_name'       => 'カスタマイズ広告とコンテンツ',
    'cat_ads_desc'       => '興味、閲覧履歴、端末情報に基づきカスタマイズされた広告・コンテンツを表示します。',
    'cat_measure_name'   => 'カスタマイズコンテンツ・広告効果測定',
    'cat_measure_desc'   => 'コンテンツ・広告のアクセス情報を収集し効果測定とサービス改善に活用します。',
    'cat_geo_name'       => '高精度位置情報の使用',
    'cat_geo_desc'       => '同意を得た上で高精度位置情報をサービス提供に利用します。',
    'cat_analytics_name' => '第三者分析',
    'cat_analytics_desc' => '第三者分析ツールによりサイトアクセスとユーザー行動を把握します。',
    'cat_social_name'    => 'ソーシャルメディアCookie',
    'cat_social_desc'    => 'SNS機能、共有ボタン、ログイン関連機能に使用されます。',

    'always_on'     => '常に有効',
    'required_hint' => '必須項目です。オフにできません',

    'saved'       => '選択内容を記録しました',
    'save_failed' => '保存に失敗しました。しばらくしてから再試行してください',
    'reopen'      => 'Cookie設定を管理',

    'version_label' => 'バージョン',
    'updated_label' => '更新日',
);
