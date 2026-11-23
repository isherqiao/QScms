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
 * @LastEditTime 17/11/26
 * @Description  Template of the theme "Qsis_game": search result list.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>	<main id="main" role="main" class="site-content">
		<div id="rizhuti_v2_module_search-2" class="section rizhuti_v2-widget-search-bg">
			<div class="container d-none d-lg-block">
<div class="module search-bg" >
          <div class="search-bg-overlay"></div>
            <div class="container">

                <form class="search-form" method="get" action="<?php echo rtrim(qs_site_link(false), '/'); ?>/" onsubmit="qsGameSearch(this.querySelector('input')); return false;">
                 <div class="search-form-index">
                      <div class="search-select" >
                        搜索
                        </div>

                        <div class="search-fields">
                          <input type="text"  placeholder="<?php echo htmlspecialchars(game_opt('search_placeholder', '关键字全站搜索')); ?>" autocomplete="off" class="search" name="search" id="text" value="<?php qs_search_name(); ?>">
                          <button class="" type="submit"><i class="search-icon"></i></button>
                        </div>
                    </div>
                </form>
 </div>
        </div></div><div id="rizhuti_v2_module_dynamic-2" class="section rizhuti_v2-widget-dynamic"></div><div id="rizhuti_v2_module_lastpost_item-2" class="section rizhuti_v2-widget-lastpost"><div class="container">
        <div class="row">
            <div class="col-lg col-sm-12">
                <h3 class="section-title d-flex align-items-center mb-lg-4 mb-2">当前搜索关键词为：<em class="gm-kw"><?php qs_search_name(); ?></em></h3>
            </div>

        </div>

        <?php if (empty($qs_posts)) { ?>
        <div class="gm-empty">
          <i class="fas fa-search"></i>
          <p>没有找到相关资源，换个关键词试试。</p>
        </div>
        <?php } ?>

        <div class="module posts-wrapper grid">
            <div class="row posts-wrapper scroll">
                <?php while (qs_next_post()) {
                    $gm = qs_card_media(); ?>
<div class="col-lg-5ths col-lg-3 col-md-4 col-6">

	<article id="post-<?php echo htmlspecialchars((string)$qs_post_id); ?>" class="post post-grid gm-card">

		<span class="meta-post-type"><i class="fas fa-play-circle"></i></span>
	    <div class="entry-media"><div class="placeholder gm-media"><a target="_blank" href="<?php qs_post_link(); ?>" title="<?= qs_the_title() ?>" rel="nofollow noopener noreferrer">
	    <?php if ($gm['type'] === 'video') { ?>
	    <video class="gm-card-video" muted playsinline preload="metadata" poster="<?php echo htmlspecialchars($gm['poster']); ?>"><source src="<?php echo htmlspecialchars($gm['src']); ?>" type="<?php echo $gm['mime']; ?>"></video>
	    <span class="gm-play"><i class="fas fa-play"></i></span>
	    <?php } else { ?>
	    <img class="gm-card-img" src="<?php echo htmlspecialchars($gm['src']); ?>" alt="<?= qs_the_title() ?>" loading="lazy" onerror="var w=this.parentNode; if(w){w.className+=' gm-noimg';} this.remove();">
	    <?php } ?></a></div></div>
	    <div class="entry-wrapper">


	    	<header class="entry-header">
	    		<h2 class="entry-title"><?= qs_the_link() ?></h2>	    	</header>

	      	<div class="entry-footer">
			          <div class="entry-meta">

                          <span class="meta-date">
                  <time datetime="<?= qs_the_date() ?>">
                    <i class="fa fa-clock-o"></i>

<?= qs_the_date() ?>

       </time>
              </span>
                            <span class="meta-views"><i class="fa fa-eye"></i></span>
            <span class="meta-shhop-icon"><i class="far fa-gem"></i></span>

          </div>
          			<?php if (game_opt('show_tags', '1') === '1') { ?><span class="meta-category-dot"><?= qs_the_tags('','','') ?></span><?php } ?>
        			</div>
	    </div>
	</article>

</div>
<?php } ?>
            </div>
        </div>

        <div class="row"><div class="col-12"><?php qs_pagination(); ?></div></div>
</div>
</div>
</main>
