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
 * @LastEditTime 17/11/26
 * @Description  Template of the theme "Qsis_game": sidebar.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<style>

	/* Sidebar Styles */
.sidebar-column {
    position: relative;
    overflow: visible;
    box-sizing: border-box;
    min-height: 1px;
   float: right;
}

/* Search Widget Styles */
.widget_search {
    margin-bottom: 20px;
}

.search-fields {
    display: flex;
    margin-bottom: 10px;
}

.search {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Resource Information Styles */
.widget.ripro-v2-widget-shop-down {
    margin-bottom: 20px;
}

.price {
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.pricing-options {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pricing-options li {
    margin-bottom: 5px;
}

.async-shop-down {
    text-align: center;
}

.down-info {
    border-top: 1px solid #ccc;
    padding-top: 10px;
    margin-top: 10px;
}

.down-info h5 {
    margin-bottom: 10px;
}

.down-info img {
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
}

.down-help {
    margin-top: 10px;
    color: #777;
}

</style>
<div class="sidebar-column col-lg-3" >
    <!-- #secondary -->
    <aside id="secondary" class="widget-area">
        <div id="search-3" class="widget widget_search">
            <div class="search-fields">
                <input type="text" placeholder="输入游戏关键字" autocomplete="off" class="search" name="search" id="gm-side-search" value="">
                <button class="" type="button" onclick="qsGameSearch(this)"><i class="search-icon"></i></button>
            </div>
        </div>
        <?php if (qs_is_post()) {
            global $qs_data;
            $gm_size = trim((string)($qs_data['download_size'] ?? ''));
            $gm_down = trim((string)($qs_data['download'] ?? ''));
            $gm_pic  = (string)qs_the_content_pic();
        ?>
        <div id="ripro_v2_shop_down-3" class="widget ripro-v2-widget-shop-down">
            <div class="row">

  <div class="col d-none d-lg-block price">
 <h3><i class="fas fa-cloud-download-alt mr-1"></i>资源介绍</h3>
                    </div>

                    <div class="col d-none d-lg-block price">
                     <h3><?php qs_postedit('✏️编辑'); ?></h3>
                      </div>

            </div>
            <ul class="pricing-options">
                <li><span>普通：</span><b>免费</b></li>
                <li><span>会员：</span><b>免费</b></li>
                <li><span>永久会员：</span><b>免费<small class="badge badge-danger-lighten ml-2">推荐</small></b></li>
            </ul>

            <div class="down-info">
                <h5>资源介绍</h5>
                <div class="infos">
                    <img class="gm-side-cover" src="<?php echo htmlspecialchars($gm_pic); ?>" alt="<?= qs_the_title() ?>" onerror="this.style.display='none'">
                    <?php if ($gm_size !== '') { ?>
                    <div class="gm-side-row">
                        <span class="data-label">游戏大小：</span>
                        <span class="info"><?php echo htmlspecialchars($gm_size); ?></span>
                    </div>
                    <?php } ?>
                </div>
                <div class="async-shop-down text-center">
                    <?php if ($gm_down !== '') { ?>
                    <button type="button"  onclick="openPopup()" class="btn btn-block btn-primary mb-3 click-pay-post">免费下载</button>
                    <?php } else { ?>
                    <button type="button" class="btn btn-block btn-secondary mb-3" disabled>暂无下载</button>
                    <?php } ?>
                </div>
              <!-- 弹出窗口 -->

    <div id="popup" class="popup-biankuang">
    	<div class="popup">
        <button class="close-btn" onclick="closePopup()">×</button>
        <?= qs_the_content('download') ?>
       </div>
    </div>

    <!-- 遮罩层 -->
    <div id="overlay" class="overlay" onclick="closePopup()"></div>

    <script>
        function openPopup() {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
            <div class="down-help mt-2 small text-muted">
                <strong>本站提供[天翼云盘下载]、[百度网盘下载]，最近更新游戏增加[阿里云盘下载]</strong>
            </div>
            </div>
        </div>
<?php }?>
    </aside>
</div>
