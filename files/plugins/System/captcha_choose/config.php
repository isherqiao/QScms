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
 * @File         config.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Captcha provider handler: sanitises the provider path, falls back to the built-in captcha and saves the choice.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (isset($_POST['save'])) {
    $captcha_provider = $_POST['captcha_provider'] ?? 'System/captcha';
    // 仅允许字母/数字/下划线/斜杠/短横，防止路径穿越
    $captcha_provider = preg_replace('#[^A-Za-z0-9_/\-]#', '', $captcha_provider);
    if ($captcha_provider === '') {
        $captcha_provider = 'System/captcha';
    }
    $plugin_Content .= "\$captcha_provider = '" . addslashes($captcha_provider) . "';\n";
}
