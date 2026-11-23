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
 * @File         first.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_novel": lead item block on the home page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<div id="main" role="main" class="site-content">
<div class="update">
<?php
updatenew('title');
updatenew('bookname');
updatenew('bookpic');
$nv_new_title = isset($title) ? $title : '';
$nv_new_book  = isset($bookname) ? $bookname : '';
$nv_new_pic   = isset($bookpic) ? $bookpic : '';
if ($nv_new_pic !== '') {
    $nv_new_path = ltrim(preg_replace('#^\.\./#', '', $nv_new_pic), '/');
    echo '<img class="nv-new-cover" src="' . htmlspecialchars(rtrim(qs_site_link(false), '/') . '/' . $nv_new_path) . '" alt="" onerror="this.style.display=\'none\'">';
}
?>
</div>

<div class="row">
<?php while (qs_next_post()) { ?>
<div class="tag_post" role="tabpanel">
<div class="container">
<a target="_blank" href="<?php qs_post_link(); ?>" title="<?= qs_the_title() ?>" rel="nofollow noopener noreferrer">
<span class="nv-cover"><img src="<?php echo function_exists('qs_book') && qs_book('pic') ? qs_book('pic') : qs_the_content_pic(); ?>" alt="" onerror="this.parentNode.classList.add('nv-nocover');this.parentNode.removeChild(this);"></span>
 <div class="entry-wrapper"></a>
<small class="entry-title"><strong>
<a href="<?php echo qs_book('link') ?>"><?php
$nv_book = qs_book('name');
$nv_genre = qs_book('bookgenres');
if ($nv_book !== '') { echo htmlspecialchars($nv_book); }
elseif ($nv_genre !== '') { echo htmlspecialchars($nv_genre); }
else { echo '单篇'; }
?></a></strong></small>

<div class="entry-footer">
<span class="first-title"><?= qs_the_link() ?></span>	    
</div>
	    </div>
	</div>
</div>
<?php } ?>
</div>
<div id="page_bar">
<?php qs_pagination(); ?>
</div>

</div>

</div>