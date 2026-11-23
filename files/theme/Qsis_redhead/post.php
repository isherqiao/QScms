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
 * @File         post.php
 * @Description  Template of the theme "Qsis_redhead": single post / page layout.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

include('header.php'); ?>

<!-- 详情页面开始 -->

<div id="content_box">
<?php if (qs_is_page()) { ?>
    <div class="post default">
      <h1 class="title"><?php qs_page_title(); ?></h1>
      <div class="content">
                                        <?php
    //调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'读速:');?>
<!-- 输出文章内容 -->
    <div id="article-content"><?= qs_the_content() ?></div> 
      </div>
      <?php if (function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')) { ?>
        <div id="comm"><?php if (function_exists('qs_comment_code')) qs_comment_code(); ?></div>
      <?php } ?>
    </div>

<?php } else { ?>
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
       <?php
    //调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'读速:');?>
<!-- 输出文章内容 -->
    <div id="article-content"><?= qs_the_content() ?></div> 
      </div>
        <div class="post_meta">
        <div class="post_date"><?php qs_user_nick(); ?></div>
          <div class="seal"></div>
          <div class="post_tag">@Tag: <?= qs_the_tags('', '', '') ?></div>
        </div>
      </div>
      <?php if (function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')) { ?>
        <div id="comm"><?php if (function_exists('qs_comment_code')) qs_comment_code(); ?></div>
      <?php } ?>
    </div>

<?php } ?>
</div>
<div class="topbar"></div>

<?php include('footer.php'); ?>

<!-- 主菜单显示 -->

<!-- 主菜单结束 -->
