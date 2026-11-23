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
 * @File         privacy.php
 * @CreatedTime  15/11/26 22:45:00
 * @LastEditTime 15/11/26 23:15:00
 * @Description  Privacy policy page for the dynamic site; the content comes from the backend "Footer & Privacy" settings.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// files/privacy.php —— 隐私政策页（动态站）
// 具体渲染在 control/footer_privacy_lib.php 中，与 SSG 静态生成共用同一套样式。

if (!function_exists('qs_privacy_html')) {
    require_once __DIR__ . '/../control/footer_privacy_lib.php';
}

echo qs_privacy_html();
