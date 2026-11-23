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
 * @File         tags.php
 * @CreatedTime  18/11/26 22:20:00
 * @LastEditTime 18/11/26 22:20:00
 * @Description  Template of the theme "Qsis_red": tags overview page (SSG)
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */
include __DIR__ . '/header.php';
?>
<div class="post">
  <h1 class="title">标签总览</h1>
  <div class="content">
    <ul class="qs-tag-cloud">
<?php if (function_exists('qs_tag_cloud')) { qs_tag_cloud('<li>', '', '</li>', 0); } ?>
    </ul>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>