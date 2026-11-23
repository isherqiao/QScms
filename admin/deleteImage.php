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
 * @File         deleteImage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Image deletion endpoint: verifies the requested path stays inside the upload directory before unlinking the file.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 安全删除图片
if (!isset($_POST['path']) || empty($_POST['path'])) {
    echo L('Parameter_Error');
    exit;
}

$path = $_SERVER['DOCUMENT_ROOT'] . $_POST['path'];
$path = realpath($path);

// 安全限制：只允许删除 upload 目录下的图片
if ($path === false || strpos($path, realpath($_SERVER['DOCUMENT_ROOT'] . '/upload/')) !== 0) {
    echo L('Illegal_Path');
    exit;
}

if (is_file($path) && unlink($path)) {
    echo 'ok';
} else {
    echo L('File_Not_Exist_Or_No_Permission');
}
?>