<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 */

/**
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         theme_admin.php
 * @Description  Template of the theme "Qsis_business": backend options panel.
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
    'Qsis_business 主题设置',
    $message,
    qs_admin_base() . '/set.php',
    __DIR__
);

require_once $controldata . 'foot.php';
