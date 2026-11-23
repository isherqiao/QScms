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
 * @File         captcha_image.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Captcha image endpoint: generates the random code, draws it with the bundled Noto Sans font and streams a PNG.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

session_start();

$_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

// 创建一个空白的图片
$image = imagecreatetruecolor(120, 40);

// 为图片分配背景颜色
$bgColor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgColor);

// 为验证码生成随机颜色
$textColor = imagecolorallocate($image, 0, 0, 0);

// 生成验证码文本
$captchaCode = $_SESSION['captcha_code'];
$font = dirname(__FILE__).'/NotoSans-Regular.ttf';

// 在图片上绘制验证码
$font1 = imagettftext($image, 20, 0, 20, 30, $textColor, $font, $captchaCode);

// 输出图片
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>