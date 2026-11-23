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
 * @Description  Template of the theme "Qsis_game": single post detail page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><div class="container">
  <div class="content-column col-lg-9" style="float: left;">
    <div class="content-area">
      <!-- 导航标签 -->
      <div class="single-download-nav">
        <ul class="nav nav-pills" id="pills-tab" role="tablist">
          <!-- 蓝色标签 -->
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="pills-details-tab" data-toggle="pill" href="#pills-details" role="tab" aria-controls="pills-details" aria-selected="true">
              <i class="far fa-file-alt mr-1"></i>详情介绍</a>
          </li>
        </ul>
      </div>

      <!-- 内容区域 -->
      <div class="container">
      <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade active show" id="pills-details" role="tabpanel" aria-labelledby="pills-details-tab">
          <!-- 文章内容 -->
          <div id="post" class="article-content post-65417 post type-post status-publish format-standard has-post-thumbnail hentry category-actgame category-hotgame">
            <!-- 文章头部信息 -->
            <h1 class="entry-title"><?= qs_the_link() ?></h1>
            <div class="entry-meta">
              <span class="post_meta meta-category-dot">
                <?= qs_the_tags('','','') ?>
              </span>
    <?php
    // 调用语音合成函数，自动根据后台配置选择合成方法
    qs_speakArticle($qs_config,'speed:'); ?>
            </div>

            <!-- 文章内容 -->
            <div class="entry-wrapper">
              <div id="article-content" class="entry-content u-text-format u-clearfix">
                <?php
                $gm = qs_card_media();
                qs_the_video('', $gm['poster'], 'gm-post-video');
                if ($gm['type'] === 'image' && $gm['src'] !== '') { ?>
                <div class="gm-post-cover"><img src="<?php echo htmlspecialchars($gm['src']); ?>" alt="<?= qs_the_title() ?>" onerror="var w=this.parentNode; if(w){w.className+=' gm-noimg';} this.remove();"></div>
                <?php } ?>
                <?= qs_the_content() ?>
                </div>
                <!-- 文章底部声明 -->
                <div class="post-note alert alert-warning mt-2" role="alert">
                  <small><strong>声明：</strong>本站所有资源均来自用户分享和网络收集，仅供学习与参考，请勿用于商业用途，如果损害了您的权利，请联系网站客服，我们核实后会立即删除。</small>
                </div>

                <!-- 文章分享与编辑按钮 -->

            </div>
          </div>
        </div>
      </div>
       </div>
    </div>
  </div>

  <!-- 侧边栏 -->
  <?php include('side.php'); ?>
</div>

<!-- 底部小部件区域 -->
<div id="ripro_v2_widget_down_top-2" class="widget ripro_v2-widget-downtop">
  <!-- 可添加其他小部件内容 -->
</div>
