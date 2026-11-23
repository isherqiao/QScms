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
 * @Description  Template of the theme "Cn_newyear": home page listing of the latest posts.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 include('header.php'); ?>

<!-- 详情页面开始 -->
<!-- 主菜单显示 -->
<?php include('menu.php'); ?>
<!-- 主菜单结束 -->

<div id="content_box">
  <?php if (qs_is_post()) { ?>
    <div class="post">
      <h1 class="title">
        <?= qs_the_link() ?>
<?php if (qs_Pass()) { ?>
            <div class="post_edit"><a href="<?= qs_postedit_link() ?>">编辑</a></div>
        <?php
          }
        ?>
      </h1>
      <div class="content">
<?php
    //调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'读速:');?>
    <div id="article-content"><?= qs_the_content() ?></div> <!-- 输出文章内容 -->
      </div>
      <div class="post_meta">
        <div class="post_date">⏰<?= qs_the_date() ?></div>
        <div class="post_tag">#<?= qs_the_tags('', '', '') ?></div>
      </div>
      <?php if (function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')) { ?>
        <div id="comm"><?php if (function_exists('qs_comment_code')) qs_comment_code(); ?></div>
      <?php } ?>
    </div>

  <?php } else if (qs_is_page()) { ?>
    <div class="post">
      <h1 class="title">
        <?= qs_page_title() ?>
      </h1>
      <div class="content">
    <?php
    // 调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'读速:');?>

    <div id="article-content"><?= qs_the_content() ?></div> <!-- 输出文章内容 -->

      </div>
      <?php if (function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')) { ?>
        <div id="comm"><?php if (function_exists('qs_comment_code')) qs_comment_code(); ?></div>
      <?php } ?>
    </div>

  <?php } else if (qs_is_archive()) { ?>
    <div class="post">
      <h1 class="title">文章存档</h1>
      <div class="content">

    <div class="date_list">
        <h2>月份</h2>
        <ul id="month_list">
<li><?= qs_date_list() ?></li>
</ul>
</div>
 <div class="tag_list">
        <h2>标签</h2>
        <ul id="tag_list">
<li><?= qs_tag_list() ?></li>
</ul>
 </div>
</div>
</div>
  <?php } else { ?>

    <?php if (qs_is_tag()) { ?>
      <div id="page_info"><span>当前位置：<?= qs_site_name() ?>-><?= qs_tag_name() ?></span></div>
    <?php } else if (qs_is_date()) { ?>
      <div id="page_info"><span>根据年月日显示：<?= qs_date_name() ?></span></div>
    <?php } ?>

    <?php include('index_box.php'); ?>

    <div id="page_bar">
共 <?php echo $qs_post_count; ?> 篇文章<br>
<?= qs_pagination() ?>
</div>

    <?php while (qs_next_post()) { ?>
      <div class="post">
        <h1 class="title">
          <?= qs_the_link() ?>
            <?php if (qs_Pass()) { ?>
                <div class="post_edit">
                    <a href="<?= qs_postedit_link() ?>">编辑</a>
                </div>
            <?php } ?>
        </h1>
        <div class="content">
 <div class="image-container">
 <img src="<?php echo qs_the_content_pic(); ?>" alt="Post Image">
</div>
<div class="content_post">
	<?= qs_the_content() ?>
</div>
          
        </div>
        <div class="post_meta">
          <div class="post_date">⏰<?= qs_the_date() ?></div>
          <div class="post_tag">#<?= qs_the_tags('', '', '') ?></div>
        </div>
      </div>
    <?php } ?>

    <div class="clear"></div>

    <div id="page_bar">
共 <?php echo $qs_post_count; ?> 篇文章<br>
<?= qs_pagination() ?>
</div>

  <?php } ?>
</div>
</div>
<?php include('side.php'); ?>
<?php include('side2.php'); ?>
<?php include('footer.php'); ?>
