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
 * @File         side2.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_red": secondary sidebar block.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<?php
if (!isset($theme_options)) {
    include __DIR__ . '/theme_config.php';
}
$ny_sb_img  = isset($theme_options['sidebar_img']) ? trim((string)$theme_options['sidebar_img']) : '';
$ny_sb_url  = $ny_sb_img !== '' ? qs_theme_opts_img_url(__DIR__, $ny_sb_img) : '';
$ny_sb_link = trim((string)(isset($theme_options['sidebar_img_link']) ? $theme_options['sidebar_img_link'] : ''));
?>    <div id="side">

<?php if ($ny_sb_url !== '') { ?>
    <div class="sidebox">
        <?php if ($ny_sb_link !== '') { ?><a href="<?php echo htmlspecialchars($ny_sb_link, ENT_QUOTES, 'UTF-8'); ?>" title="about"><?php } ?>
        <img src="<?php echo htmlspecialchars($ny_sb_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php qs_site_name(); ?>">
        <?php if ($ny_sb_link !== '') { ?></a><?php } ?>
    </div>
<?php } ?>
    <div class="about"><?php qs_site_about(); ?></div>

    <div class="clear"></div>

  </div>