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
 * @File         blocked_keywords.php
 * @CreatedTime  05/11/26 23:05:52
 * @LastEditTime 05/11/26 23:05:52
 * @Description  Preset data: default blocked-keyword library (categorised, chunk-aware), copied into control/data during installation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 屏蔽关键词配置（分类结构，由 admin/keywords/keywords_manage.php 维护；enabled 1=参与过滤 0=不参与）
// 词库较大时自动切割为 blocked_keywords_N.php 分片，主文件通过 chunks 引用
$qs_blocked_keywords = array (
  'sexy' => 
  array (
    'name' => '色情',
    'enabled' => 1,
    'words' => 
    array (
      0 => '原味',
      1 => '裸聊',
      2 => '伴游',
      3 => '上门服务',
      4 => '私密按摩',
      5 => '包夜',
      6 => '一夜情',
      7 => '约炮',
      8 => '情趣用品',
      9 => '成人网站',
      10 => '情色',
      11 => '黄色视频',
    ),
  ),
  'gamble' => 
  array (
    'name' => '赌博',
    'enabled' => 1,
    'words' => 
    array (
      0 => '老虎机',
      1 => '打鱼机',
      2 => '博彩',
      3 => '上分',
      4 => '下分',
      5 => '千术',
      6 => '赌球',
      7 => '棋牌外挂',
      8 => '网赌',
      9 => '彩票作弊',
      10 => '赌码',
      11 => '时时彩',
    ),
  ),
  'violence' => 
  array (
    'name' => '暴恐',
    'enabled' => 1,
    'words' => 
    array (
      0 => '砍杀',
      1 => '爆炸物',
      2 => '人体炸弹',
      3 => '纵火',
      4 => '袭击教程',
      5 => '恐怖袭击',
      6 => '枪支弹药',
      7 => '管制刀具',
      8 => '炸弹配方',
      9 => '袭击目标',
    ),
  ),
  'drug' => 
  array (
    'name' => '违禁药品',
    'enabled' => 1,
    'words' => 
    array (
      0 => '冰毒',
      1 => '摇头丸',
      2 => '大麻',
      3 => '罂粟',
      4 => '止咳水',
      5 => '上头',
      6 => '白粉',
      7 => '海洛因',
      8 => '制毒',
      9 => '吸毒工具',
      10 => '迷幻药',
      11 => '精神药品滥用',
    ),
  ),
  'traffic' => 
  array (
    'name' => '违规引流',
    'enabled' => 1,
    'words' => 
    array (
      0 => '加微信',
      1 => '+v',
      2 => '私聊发链接',
      3 => 'telegram',
      4 => '电报群',
      5 => '私我拿资源',
      6 => '扫码进群',
      7 => '外链',
      8 => '留联系方式',
      9 => '引流到站外',
      10 => '私域导流',
    ),
  ),
  'abuse' => 
  array (
    'name' => '低俗辱骂',
    'enabled' => 1,
    'words' => 
    array (
      0 => '垃圾',
      1 => '脑残',
      2 => '去死',
      3 => '蠢货',
      4 => '废物',
      5 => '贱人',
      6 => '地域黑',
      7 => '开盒',
      8 => '挂人',
      9 => '人肉',
      10 => '喷子',
      11 => '狗东西',
      12 => '臭傻逼',
      13 => '王八蛋',
      14 => '狗操的',
      15 => '臭逼',
      16 => '小瘪三',
      17 => '小刺老',
    ),
  ),
);
?>
