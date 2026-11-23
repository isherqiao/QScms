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
 * @File         search.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_novel": search form / results block.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<div id="content_box">
<div class="content-area">

	<div class="single-download-nav">
<div id="page_info"><span>当前搜索关键词为：
<?php qs_search_name(); ?></span></div>
</div>

<div id="main">
			<?php while (qs_next_post()) { ?>
					<div class="post" role="tabpanel">
							<?php // White content area ?>
							<h1 class="entry-title">
								<?= qs_the_link() ?><?php
if (qs_Pass()) {
?>
<div class="edit">
<button class="go-star-btn btn btn-sm btn-white"><i class="far fa-star"></i> <a href="<?php qs_postedit_link(); ?>">编辑</a></button>
</div>
                      <?php
                            }
                      ?>
							</h1>
							
						<div class="container">
								<?php $nv_p = qs_book('pic'); if ($nv_p !== '') { ?>
									<span class="nv-cover nv-inline"><img src="<?php echo htmlspecialchars($nv_p); ?>" alt="" onerror="this.parentNode.classList.add('nv-nocover');this.parentNode.removeChild(this);"></span>
									<?php } ?>
								<p><?= qs_the_content() ?></p>
							</div>
            <div class="entry-meta">
              <small>关键词：  <?= qs_the_tags('','','') ?></small>
            </div>
                            <div class="post-note" role="alert">

                  <small><strong>声明：</strong>本站所有内容均为原创，未经授权禁止转载和/或抄袭。</small>
                </div>
						</div>
				
			<?php } ?>
				</div>						<div id="page_bar">
    <?php if (qs_has_new()) { ?>
        <?php qs_goto_new('«上一页'); ?>
    <?php } ?>
    <?php if (qs_has_old()) { ?>
        <?php qs_goto_old('下一页»'); ?>
    <?php } ?>
</div>
</div>	

</div>
</div>
