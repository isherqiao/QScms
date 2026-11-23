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
 * @LastEditTime 30/08/26 17:39:56
 * @Description  Template of the theme "Qsis_swbj": home page listing of the latest posts.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<?php include 'header.php'; ?>

<?php
// 加载本主题配置，只新增这一段，不改动别的
$theme_config_file = __DIR__ . '/theme_config.php';
$theme_tag_blocks = [];
if (file_exists($theme_config_file)) {
    include $theme_config_file;
}
$render_blocks = [];
if(!empty($theme_tag_blocks)){
    foreach ($theme_tag_blocks as $item) {
        if (!empty($item['enable'])) {
            $render_blocks[] = $item;
        }
    }
}
?>

<?php if (qs_is_post()) { 
include 'post.php';
 } else if (qs_is_page()) { 
include 'post.php';
 } else if (qs_is_tags()) { 
include 'tags.php';
 } else if (qs_is_tag()) { 
include 'list.php';
 } else {
?>

<?php
// 首屏轮播：读主题设置里的 3 张大图，不再走系统「扩展」里的 carousel_config
$qs_banner_items = [];
if (!empty($theme_banners) && is_array($theme_banners)) {
    foreach ($theme_banners as $one) {
        if (!empty($one['enable']) && !empty($one['img'])) {
            $qs_banner_items[] = $one;
        }
    }
}
?>
<?php if ($qs_banner_items): ?>
<!-- 轮播 -->
<div class="banner">
  <div class="swiper-container">
    <div class="swiper-wrapper">
      <?php foreach ($qs_banner_items as $one): ?>
      <div class="swiper-slide">
        <?php if (!empty($one['link'])): ?>
        <a href="<?php echo htmlspecialchars($one['link']); ?>"><img src="<?php echo htmlspecialchars($one['img']); ?>" alt=""></a>
        <?php else: ?>
        <img src="<?php echo htmlspecialchars($one['img']); ?>" alt="">
        <?php endif; ?>
        <?php if (!empty($one['text'])): ?>
        <div class="banner-text"><?php echo htmlspecialchars($one['text']); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
  </div>
</div>
<?php endif; ?>

<div class="new-list">
  <div class="container">
    <!-- 左侧：最新发布 -->
    <div class="left-news"> 

<div class="law-category">
  <div class="container">
   
    <div class="category-grid">
<?php
if (!empty($render_blocks)) {
    foreach ($render_blocks as $item) {
        $show_limit = $item['limit'] ?? $qs_config['per_page'];
        qs_block_tag_posts($item['tag_name'], $item['tag_desc'], $show_limit);
    }
}
?>
      </div>
  </div>
</div>

    <div class="left-news">
   <?php 
    $pageTitle = '文章列表';
    if (qs_is_tag()) {
        $pageTitle = '<a href=/tags/>专题</a>：' . qs_tag_name((false));
    } 
    ?>
   
      <h3 class="list-title"><?php echo $pageTitle; ?></h3>
      <div class="list-items">
        <?php if (qs_next_post()): ?>
          <?php do { ?>
            <div class="list-item">
              <div class="title">
                <a href="<?php 
                  if (qs_is_bookname()) {
                      $id = $qs_config['current_data']['id'] ?? 'unknown';
                      echo './' . $id . '.html';
                  } else {
                      qs_post_link(); 
                  }
                ?>"><?= qs_the_title() ?></a>
              </div>
              <div class="date"><?= qs_the_date() ?></div>
            </div>
          <?php } while (qs_next_post()); ?>
        <?php else: ?>
          <p class="no-content">暂无内容。</p>
        <?php endif; ?>
      </div>
      <div class="pagination">
  <?php qs_pagination(); ?>
</div>
    </div>
    </div>

    <!-- 右侧 -->
<div class="right-intro">
	
	  <h3 class="intro-title">Logo</h3>
  <div class="intro-content">
    <a href="/shenwubiji.html" title="关于深悟笔记和QSIS.CMS">
      <img src="<?php echo qs_site_link(false).$qs_config['sitelogo']; ?>">
    </a>
  </div>
  <h3 class="intro-title">深悟笔记介绍</h3>
  <div class="intro-content">
<?php echo $qs_config['site_about']; ?>
  </div>
<div class="sidebar-ad">

</div>
</div>

</div>
<!-- 分页 -->

</div>
<?php
}
?>
<?php include 'footer.php'; ?>