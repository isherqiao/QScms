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
 * @File         index.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_redhead": home page listing of the latest posts.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 文章 / 单页走独立模板 post.php（静态生成时 SSG 直接渲染该文件）
if (qs_is_post() || qs_is_page()) {
    include __DIR__ . '/post.php';
    return;
}

 include('header.php'); ?>

<!-- 详情页面开始 -->

<div id="content_box">
  <?php if (qs_is_archive()) { ?>
    <div class="post">
      <h1 class="title">文章存档</h1>
      <div class="content">
        <table width="280" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:30px auto;">
          <tbody>
            <tr>
              <td width="140" style="vertical-align:top;">
                <h1 class="date_list">月份</h1>
                <ul id="list"><?php qs_date_list(); ?></ul>
              </td>
              <td width="140" style="vertical-align:top;">
                <h1 class="tag_list">标签</h1>
                <ul id="list"><?php qs_tag_list(); ?></ul>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  <?php } else { ?>

    <?php if (qs_is_tag()) { ?>
      <div id="page_info"><span>当前位置：<?php qs_site_name(); ?> -> <?php qs_tag_name(); ?></span></div>
    <?php } else if (qs_is_date()) { ?>
      <div id="page_info"><span>根据年月日显示：<?php qs_date_name(); ?></span></div>
    <?php } ?>

    <?php while (qs_next_post()) { ?>
      <div class="post default">
        <h1 class="title">
          <?= qs_the_link() ?>
<?php if (qs_Pass()) { ?>
              <div class="post_edit"><a href="<?php qs_postedit_link(); ?>">编辑</a></div>
          <?php
          }
          ?>
        </h1>
        <div class="content">
          <?= qs_the_content() ?>
        </div>
        <div class="post_meta">
          <div class="post_date"><?= qs_the_date() ?></div>
          <div class="seal"></div>
          <div class="post_tag">@Tag: <?= qs_the_tags('', '', '') ?></div>
        </div>
      </div>
    <?php } ?>

    <div class="clear"></div>
<div id="page_bar">
共 <?php echo $qs_post_count; ?> 篇文章<br>
<?php qs_pagination(); ?>
</div>
  <?php } ?>
</div>
<div class="topbar"></div>
<?php include('footer.php'); ?>

<!-- 主菜单显示 -->

<!-- 主菜单结束 -->
