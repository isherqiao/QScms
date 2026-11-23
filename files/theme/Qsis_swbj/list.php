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
 * @File         list.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_swbj": category / paged list page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 if (qs_is_tag()) { 
?>
<div class="new-list">
  <div class="container">
    <div class="left-news">
      <h3 class="list-title"><?php qs_tag_name(); ?>文章列表</h3>
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

    <!-- 右侧：个人介绍 -->
<div class="right-intro">
  <h3 class="intro-title">乔硕 介绍</h3>
  <div class="intro-content">
    <a href="/qiaoshuo.html" title="乔硕">
      <img src="<?php echo $qs_config['custom_side_photo']; ?>">
    </a>
  </div>

  <h3 class="intro-title" style="margin-top:20px;">Logo</h3>
  <div class="intro-content">
    <a href="/swbj.html" title="关于深悟笔记和QSIS.CMS">
      <img src="<?php echo $static_config['static_root_url'].$static_config['static_sitelogo']; ?>">
    </a>
  </div>
</div>
</div>
<!-- 分页 -->
</div>
<?php } ?>