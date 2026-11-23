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
 * @File         side.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_novel": sidebar block.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<div class="sidebar-column" >
    <!-- #secondary -->
<div id="search-3" class="widget widget_search">
            <div class="search-fields">
                <input type="text" placeholder="输入关键字" autocomplete="off" class="search" name="search" id="text" value="">
                <button class="search-button search-icon" type="submit" onclick="Search()"></button>
            </div>
</div>
<?php
if (qs_is_post()) { ?>

<div id="search-3" class="widget widget_search">
<div class="info">
<h3>简介:</h3>
<div class="entry-info">书名：<a href="<?php echo qs_book('link') ?>"><?php
$nv_book = qs_book('name');
$nv_genre = qs_book('bookgenres');
if ($nv_book !== '') { echo htmlspecialchars($nv_book); }
elseif ($nv_genre !== '') { echo htmlspecialchars($nv_genre); }
else { echo '单篇'; }
?></a><br>
作者：<?php echo $qs_config['user_nick'] ?><br>
时间：<?php echo qs_the_date();?>
</div>

<span class="nv-cover nv-side"><img src="<?php echo function_exists('qs_book') && qs_book('pic') ? qs_book('pic') : qs_the_content_pic(); ?>" alt="" onerror="this.parentNode.classList.add('nv-nocover');this.parentNode.removeChild(this);"></span>

</div>
<?php } ?>
    </div>
</div>