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
 * @File         other.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 17/11/26
 * @Description  Template of the theme "Qsis_game": tag / date / archive list.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><div class="container">
	<div class="content-column col-lg-9">
		<div class="content-area">
			<?php if (empty($qs_posts)) { ?>
			<div class="gm-empty"><i class="fas fa-inbox"></i><p>这里还没有内容。</p></div>
			<?php } ?>

			<div class="gm-list">
			<?php while (qs_next_post()) {
			    $gm = qs_card_media(); ?>
			<article class="gm-list-item">
				<a class="gm-list-media" href="<?php qs_post_link(); ?>" title="<?= qs_the_title() ?>">
				<?php if ($gm['type'] === 'video') { ?>
				<video muted playsinline preload="metadata" poster="<?php echo htmlspecialchars($gm['poster']); ?>"><source src="<?php echo htmlspecialchars($gm['src']); ?>" type="<?php echo $gm['mime']; ?>"></video>
				<span class="gm-play"><i class="fas fa-play"></i></span>
				<?php } else { ?>
				<img src="<?php echo htmlspecialchars($gm['src']); ?>" alt="<?= qs_the_title() ?>" loading="lazy" onerror="var w=this.parentNode; if(w){w.className+=' gm-noimg';} this.remove();">
				<?php } ?>
				</a>
				<div class="gm-list-body">
					<h2 class="entry-title"><?= qs_the_link() ?></h2>
					<p class="gm-list-excerpt"><?= qs_the_content() ?></p>
					<div class="gm-list-meta">
						<span><i class="fa fa-clock-o"></i> <?= qs_the_date() ?></span>
						<?php if (game_opt('show_tags', '1') === '1') { ?><span class="meta-category-dot"><?= qs_the_tags('', '', '') ?></span><?php } ?>
					</div>
				</div>
			</article>
			<?php } ?>
			</div>

			<div class="row"><div class="col-12"><?php qs_pagination(); ?></div></div>
		</div>
	<?php include('side.php'); ?>
	</div>
</div>
