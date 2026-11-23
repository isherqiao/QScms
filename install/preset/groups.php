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
 * @File         groups.php
 * @CreatedTime  05/11/26 23:05:52
 * @LastEditTime 05/11/26 23:05:52
 * @Description  Preset data: default user groups, copied into control/data during installation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 用户组配置（由 user/group_manage.php 维护）
$qs_groups = array (
  '超级管理员' => 
  array (
    'perms' => 
    array (
      0 => '*',
    ),
    'desc' => '拥有后台全部功能',
  ),
  '作者' => 
  array (
    'perms' => 
    array (
      0 => 'post_manage',
      1 => 'set_articles',
      2 => 'set_markdown',
      3 => 'set_bookgenres',
      4 => 'picmanage',
      5 => 'copyright',
    ),
    'desc' => '可创作与管理文章、图片',
  ),
  '编辑' => 
  array (
    'perms' => 
    array (
      0 => 'post_manage',
      1 => 'page_manage',
      2 => 'set_articles',
      3 => 'set_account',
      4 => 'set_extensions',
      5 => 'set_menu',
      6 => 'set_link',
      7 => 'set_markdown',
      8 => 'set_bookgenres',
      9 => 'picmanage',
      10 => 'mail_setting',
      11 => 'messages',
      12 => 'user_set',
      13 => 'copyright',
    ),
    'desc' => '可管理文章、网页与站点结构',
  ),
  '用户' => 
  array (
    'perms' => 
    array (
      0 => 'user_set',
      1 => 'copyright',
    ),
    'desc' => '注册默认分组，登录后仅能看到个人信息',
  ),
  '备份运维管理员' => 
  array (
    'perms' => 
    array (
      0 => 'backup_system',
      1 => 'backup_verify',
      2 => 'set_backup',
      3 => 'log_operate',
      4 => 'messages',
      5 => 'set_github',
      6 => 'set_ssg',
      7 => 'set_remote',
      8 => 'set_api',
      9 => 'user_set',
      10 => 'copyright',
    ),
    'desc' => '负责备份与系统运维',
  ),
);
?>