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
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_novel": single post detail page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><div id="content_box">
<div class="content-area">
   <div id="post" class="post">
            <!-- 文章头部信息 -->
<div class="post-top">书名：<a href="<?php echo qs_book('link') ?>"><?= qs_book('name') ?></a>
<?php
if (qs_Pass()) {
?>
<div class="edit">
<i class="far fa-star"></i> <a href="<?php qs_postedit_link(); ?>">编辑</a>
</div>
<?php
}
 ?>
</div>
            <div class="entry-title"><h1><strong><?= qs_the_link() ?></strong></h1></div>

            <!-- 文章内容 -->
            <div class="entry-wrapper">
      <div class="content">
<?php
    //调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'读速:');?>
    <div id="article-content"><?= qs_the_content() ?></div> <!-- 输出文章内容 -->
      </div>
            <div class="entry-meta">
              <small>关键词：  <?= qs_the_tags('','','') ?></small>
            </div>
                            <!-- 文章底部声明 -->
                <div class="post-note" role="alert">

                  <small><strong>声明：</strong>本站所有内容均为原创，未经授权禁止转载和/或抄袭。</small>
                </div>
          </div>
        </div>
</div>
  <!-- 侧边栏 -->
  <?php include('side.php'); ?>
</div></div>

<!-- 底部小部件区域 -->
<div id="ripro_v2_widget_down_top-2" class="widget ripro_v2-widget-downtop">
  <!-- 可添加其他小部件内容 -->
</div>
