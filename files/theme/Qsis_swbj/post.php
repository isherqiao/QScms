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
 * @Description  Template of the theme "Qsis_swbj": single post detail page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><!-- 文章双栏主体 -->
<div class="container">
  <div class="article-row">
    <!-- 文章内容 -->
    <div class="article-wrapper">
      <h1 class="article-title">
<?= qs_the_title() ?>
      	</h1>
      <div class="article-info">
        <span><?= qs_the_date() ?></span>
<?php $author = qs_the_user(false); ?>
<?php if (!empty($author) && !qs_is_page()): ?>
深悟笔记 作者：<?= qs_the_user() ?>
<?php endif; ?>
      </div>
      
<?php if (qs_is_post()) { ?>
<?php qs_speakArticle($qs_config, '语速：'); ?>
<span class="qstts-help" title="阅读功能依靠调用 TTS 系统开放接口，支持安卓、iOS、Windows、Linux 几乎所有系统，鸿蒙系统无法语音阅读请联系手机售后。"> ❓ </span>
<?php } ?>
      <div id="article-content" class="article-content">
 <?= qs_the_content() ?>
      </div>
<?php if (qs_is_post()) { ?>
<?= qs_the_tags(' 标签：', '', ' ') ?>
<?php } ?>
      <!-- 上一篇 / 下一篇 -->
      <div class="article-page">
     <a href="<?php qs_site_link(); ?>/" class="btn">返回首页</a>
      </div>
    </div>

    <!-- ========== 右侧边栏（post-sidebar） ========== -->
    <div class="post-sidebar">

                  <!-- RSS -->
      <div class="sidebar-box">
<?php echo $qs_config['custom_RSS']; ?>
       </div>

            <!-- 二维码 -->
      <div class="sidebar-box">
      	<div class="sidebar-title">本篇文章二维码</div>
        		   <?php
                // 获取当前文章链接
                if (qs_is_bookname()) {
                    $id = $qs_config['current_data']['id'] ?? 'unknown';
                    $post_url = './' . $id . '.html';
                } elseif (qs_is_page()) {
                    global $qs_post;
                    $post_url = './' . $qs_post['path'] . '/';
                } else {
                    $post_url = qs_post_link(null, false);
                }
            ?>
        		<div class="qrcode" data-url="<?php echo htmlspecialchars($post_url); ?>"></div>
        		您可以通过手机语音助手指令“扫描二维码”，进行扫描并打开浏览器进行阅读，也可以通过微信扫一扫阅读文章。
       </div>

                  <!-- 简介内容 -->
<?php if (qs_is_post()) { ?>
 <?php if (qs_the_content_pic() || qs_the_summary()): // 只要有图 OR 有摘要就显示 ?>
      <div class="sidebar-box">
        <div class="sidebar-title">文章摘要</div>
    <?php if (qs_has_content_pic()): ?>
        <img src="<?php echo qs_the_content_pic(); ?>" alt="<?= qs_the_title() ?>" class="book-cover">
    <?php endif; ?>
        <!-- 简介内容（支持截取字数，不改你原有写法） -->
    <?php if (qs_the_summary()): ?>
            <?php echo qs_the_summary(200);  ?>
    <?php endif; ?>
          </div>
<?php endif; ?>
<?php } ?>

<div class="sidebar-box">
<div class="sidebar-ad">

</div>
</div>

<div class="sidebar-box">
	<div class="sidebar-title">本文作者</div>
	
	<a href="/qiaoshuo.html" title="乔硕">
		<img src="<?php echo $qs_config['custom_side_photo']; ?> ">
		</a>
</div>

<div class="sidebar-box"><a href="/swbj.html" title="关于深悟笔记和QSIS.CMS"><img src="<?php echo  $qs_config['site_link'].$qs_config['sitelogo']; ?> "></a></div>

      <!-- 文章分类 
      <div class="sidebar-box">
        <h3 class="sidebar-title">文章分类</h3>
        <ul class="sidebar-cat">
          <li>认知心理</li>
          <li>格物思理</li>
          <li>人文笔记</li>
          <li>人生成长</li>
        </ul>
      </div>-->

    </div>
  </div>
</div>

<!-- 底部 -->
