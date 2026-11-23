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
 * @File         tag.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_novel": tag cloud block.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><div class="row">
<?php while (qs_next_post()) { ?>
<div class="tag_post" role="tabpanel">
	<small class="entry-title"><strong>
<?php
$nv_book = qs_book('name');
$nv_genre = qs_book('bookgenres');
if ($nv_book !== '') { echo htmlspecialchars($nv_book); }
elseif ($nv_genre !== '') { echo htmlspecialchars($nv_genre); }
else { echo '单篇'; }
?></strong></small>
<?php // White content area ?>

<div class="container">
	<a target="_blank" href="<?php qs_post_link(); ?>" title="<?= qs_the_title() ?>" rel="nofollow noopener noreferrer">
<span class="nv-cover"><img src="<?php echo function_exists('qs_book') && qs_book('pic') ? qs_book('pic') : qs_the_content_pic(); ?>" alt="" onerror="this.parentNode.classList.add('nv-nocover');this.parentNode.removeChild(this);"></span>
</a>
<?= qs_the_link() ?>
</div>
<div class="post-note" role="alert">

<?php
if (qs_Pass()) {
?>
<div class="edit">
<button class="go-star-btn btn btn-sm btn-white"><i class="far fa-star"></i> <a href="<?php qs_postedit_link(); ?>">编辑</a></button>
</div>
<?php
}
?>
                </div>
						</div>
				
			<?php } ?>
				</div>						<div id="page_bar">
<?php qs_pagination(); ?>
</div>
</div>	
<!--?php include('side.php'); ?-->
</div>
</div>
