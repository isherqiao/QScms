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
 * @File         bookgenres_process.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Book genre helper endpoint: returns the genre options used by the book editor.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once '../control/core.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $option = $_POST['option'];

    if (file_exists($datapaths.$qs_config['bookgenreslist'])) {
        $options = file($datapaths.$qs_config['bookgenreslist'], FILE_IGNORE_NEW_LINES);
    } else {
        $options = [];
    }

    if ($action === 'add' && !in_array($option, $options)) {
        $options[] = $option;
        file_put_contents($datapaths.$qs_config['bookgenreslist'], implode(PHP_EOL, $options));
    } elseif ($action === 'delete' && in_array($option, $options)) {
        $options = array_diff($options, [$option]);
        file_put_contents($datapaths.$qs_config['bookgenreslist'], implode(PHP_EOL, $options));
    }

    foreach ($options as $opt) {
        echo "<option value=\"$opt\">$opt</option>";
    }
}