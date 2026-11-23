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
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         theme_admin.php
 * @Description  Template of the theme "Qsis_red": backend options panel.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__ . '/../../../control/core.php';
require_once $controldata . 'head.php';
require_once __DIR__ . '/../../../control/theme_options_lib.php';

include __DIR__ . '/theme_config.php';

$message = qs_theme_opts_save(__DIR__, $theme_schema);
if ($message !== '') {
    $theme_options = qs_theme_opts_load(__DIR__, $theme_schema);
}

echo qs_theme_opts_form(
    $theme_schema,
    $theme_options,
    'Qsis_red 主题设置',
    $message,
    qs_admin_base() . '/set.php',
    __DIR__
);

require_once $controldata . 'foot.php';
