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
 * @Description  Template of the theme "Qsis_novel": home page listing of the latest posts.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 include('header.php'); ?>
<!-- 详情页面开始 -->
<div class="empty-line">

</div>
    <?php if (qs_is_post()) { ?>
<?php include('post.php'); ?>

    <?php } else if  (qs_is_page()) { ?>
<?php include('page.php'); ?>
<!-- 详情页面结束 -->
    <?php if (function_exists('qs_can_comment') && qs_can_comment() && function_exists('qs_comment_code')) { ?>
    <div id="comm"><?php if (function_exists('qs_comment_code')) qs_comment_code(); ?></div>
    <?php } ?>
</div>
    <?php } else if (qs_is_archive()) { ?>
    <div class="post">
        <h1 class="title">文章存档</h1>
        <div class="clear"></div>
        <div class="article_content">
            <table class="archivetar">
                <tbody>
                    <tr>
                        <td width="100%" style="vertical-align:top;">
                            <h1 class="date_list">月份</h1>
                            <ul id="list"><?php qs_date_list(); ?></ul>
                        </td>
                        </tr>
                        <tr>
                        <td width="100%" style="vertical-align:top;">
                            <h1 class="tag_list">标签</h1>
                            <ul id="list"><?php qs_tag_list(); ?></ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } else if (qs_is_index()) { ?>
<?php include('first.php'); ?>

<?php } else if (qs_is_search()) { ?>
<?php  include('search.php'); ?>

<?php } else if (qs_is_bookname()) { ?>
<div class="nav">
<div id="page_info"><span>书名：
<?php qs_tag_name(); ?></span></div>
</div>
<?php  include('tag.php'); ?>

<?php } else if (qs_is_tag()) { ?>
<div class="nav">
<div id="page_info"><span>标签：
<?php qs_tag_name(); ?></span></div>
</div>
<?php  include('tag.php'); ?>
    
    <?php } else if (qs_is_date()) { ?>
    <main id="main" role="main" class="site-content" style="transform: none;">
<div class="container" style="transform: none;">
	<div class="row" style="transform: none;">
		<div class="content-column col-lg-9" style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
			
			<div class="content-area">

<div class="single-download-nav">
  <ul class="nav nav-pills" id="pills-tab" role="tablist"> <!--蓝色标签-->

    <li class="nav-item" role="presentation">
      <a class="nav-link active" id="pills-details-tab" data-toggle="pill" href="#pills-details" role="tab" aria-controls="pills-details" aria-selected="true">
      	<i class="far fa-file-alt mr-1"></i><div id="page_info"><span>根据年月日显示：<?php qs_date_name(); ?></span></div></a>
    </li>

  </ul>
</div>
    
    <?php } else {
    	    	include('other.php');
    }
    ?>
</div>

<div id="ylink">
	     	链接：<?php qs_links(); ?>
	</div>
<?php include('footer.php'); ?>
