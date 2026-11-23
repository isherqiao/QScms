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
 * @File         setimageup.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Settings image upload handler: stores the site logo and the default post image.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$upMsgs = [];
$upErr = false;
$baseAllowedImgTypes = ["jpg", "png", "apng", "jpeg", "gif", "webp", "ico", "avif", "svg"];

//文章默认图片
if ($_FILES['image']['size'] > 0)
{
    $targetDir = "../upload/images/";
    $oriName = basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($oriName, PATHINFO_EXTENSION));
    $baseName = pathinfo($oriName, PATHINFO_FILENAME);
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = $oriName;
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));

    $uploadOk = 1;
    if (isset($_POST["save"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $upMsgs[] = L('Image_Not_Valid_File');
            $upErr = true;
        }
    }
    if ($_FILES["image"]["size"] > 1024000) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Exceeds_1MB');
        $upErr = true;
    }
    if (!in_array(strtolower($imageFileType), $baseAllowedImgTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $qs_config['defaultpostimage'] = "upload/images/".$tryFilename;
            $upMsgs[] = sprintf(L('Image_Uploaded_Successfully'), $tryFilename);
        }else{
            $upMsgs[] = L('Image_Write_Server_Failed');
            $upErr = true;
        }
    }
}

//网站Logo图片
if ($_FILES['logo']['size'] > 0)
{
    $targetDir = "../upload/images/";
    $oriName = basename($_FILES["logo"]["name"]);
    $imageFileType = strtolower(pathinfo($oriName, PATHINFO_EXTENSION));
    $baseName = pathinfo($oriName, PATHINFO_FILENAME);
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = $oriName;
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));

    $uploadOk = 1;
    if (isset($_POST["save"])) {
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $upMsgs[] = L('Logo_Not_Valid_File');
            $upErr = true;
        }
    }
    if ($_FILES["logo"]["size"] > 1024000) {
        $uploadOk = 0;
        $upMsgs[] = L('Logo_Exceeds_1MB');
        $upErr = true;
    }
    if (!in_array(strtolower($imageFileType), $baseAllowedImgTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Logo_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["logo"]["tmp_name"], $targetFile)) {
            $qs_config['sitelogo'] = "upload/images/".$tryFilename;
            $upMsgs[] = sprintf(L('Logo_Uploaded_Successfully'), $tryFilename);
        }else{
            $upMsgs[] = L('Logo_Write_Server_Failed');
            $upErr = true;
        }
    }
}

//网站icon图片
//网站icon图片
if ($_FILES['ico']['size'] > 0)
{
    $targetDir = "../upload/images/";
    $oriName = basename($_FILES["ico"]["name"]);
    $imageFileType = strtolower(pathinfo($oriName, PATHINFO_EXTENSION));
    $baseName = pathinfo($oriName, PATHINFO_FILENAME);
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = $oriName;
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));
    $uploadOk = 1;
    if (isset($_POST["save"])) {
        if($imageFileType !== 'svg'){
            $check = getimagesize($_FILES["ico"]["tmp_name"]);
            if ($check === false) {
                $uploadOk = 0;
                $upMsgs[] = L('Ico_Not_Valid_File');
                $upErr = true;
            }
        }
    }
    if ($_FILES["ico"]["size"] > 1024000) {
        $uploadOk = 0;
        $upMsgs[] = L('Ico_Exceeds_1MB');
        $upErr = true;
    }
    if (!in_array(strtolower($imageFileType), $baseAllowedImgTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Ico_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["ico"]["tmp_name"], $targetFile)) {
            $qs_config['ico'] = "upload/images/".$tryFilename;
            $upMsgs[] = sprintf(L('Ico_Uploaded_Successfully'), $tryFilename);
        }else{
            $upMsgs[] = L('Ico_Write_Server_Failed');
            $upErr = true;
        }
    }
}

//网站背景图 site_backgrouppic 支持图片+mp4视频
if ($_FILES['site_backgrouppic']['size'] > 0) {
    $imageFileType = strtolower(pathinfo($_FILES["site_backgrouppic"]["name"], PATHINFO_EXTENSION));
    $targetDir = "../upload/images/";
    $baseName = "default";
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = "{$baseName}.{$imageFileType}";
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));

    $uploadOk = 1;
    if (isset($_POST["save"])) {
        $check = getimagesize($_FILES["site_backgrouppic"]["tmp_name"]);
        if($imageFileType !== 'mp4' && $check === false){
            $uploadOk = 0;
            $upMsgs[] = L('Bg_Not_Valid_File');
            $upErr = true;
        }
    }
    if ($_FILES["site_backgrouppic"]["size"] > 4096000) {
        $uploadOk = 0;
        $upMsgs[] = L('Bg_Exceeds_4MB');
        $upErr = true;
    }
    $bgAllowedTypes = array_merge($baseAllowedImgTypes, ["mp4"]);
    if (!in_array(strtolower($imageFileType), $bgAllowedTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Bg_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["site_backgrouppic"]["tmp_name"], $targetFile)) {
            $qs_config['site_backgrouppic'] = "upload/images/".$tryFilename;
            $upMsgs[] = sprintf(L('Bg_Uploaded_Successfully'), $tryFilename);
        }else{
            $upMsgs[] = L('Bg_Write_Server_Failed');
            $upErr = true;
        }
    }
}

//收款码图片 - 支付宝
if ($_FILES['payment_alipay_qr']['size'] > 0)
{
    $targetDir = "../upload/images/";
    $oriName = basename($_FILES["payment_alipay_qr"]["name"]);
    $imageFileType = strtolower(pathinfo($oriName, PATHINFO_EXTENSION));
    $baseName = "alipay_qr";
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = "{$baseName}.{$imageFileType}";
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));

    $uploadOk = 1;
    if (isset($_POST["save"])) {
        $check = getimagesize($_FILES["payment_alipay_qr"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $upMsgs[] = L('Image_Not_Valid_File');
            $upErr = true;
        }
    }
    if ($_FILES["payment_alipay_qr"]["size"] > 2097152) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Exceeds_1MB');
        $upErr = true;
    }
    if (!in_array(strtolower($imageFileType), $baseAllowedImgTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["payment_alipay_qr"]["tmp_name"], $targetFile)) {
            $qs_config['payment_alipay_qr'] = "upload/images/".$tryFilename;
            $upMsgs[] = L('Payment_Alipay_QR_Uploaded');
        }else{
            $upMsgs[] = L('Image_Write_Server_Failed');
            $upErr = true;
        }
    }
}

//收款码图片 - 微信
if ($_FILES['payment_wechat_qr']['size'] > 0)
{
    $targetDir = "../upload/images/";
    $oriName = basename($_FILES["payment_wechat_qr"]["name"]);
    $imageFileType = strtolower(pathinfo($oriName, PATHINFO_EXTENSION));
    $baseName = "wechat_qr";
    $idx = 0;
    do {
        if($idx === 0){
            $tryFilename = "{$baseName}.{$imageFileType}";
        }else{
            $tryFilename = "{$baseName}_{$idx}.{$imageFileType}";
        }
        $targetFile = $targetDir . $tryFilename;
        $idx++;
    } while (file_exists($targetFile));

    $uploadOk = 1;
    if (isset($_POST["save"])) {
        $check = getimagesize($_FILES["payment_wechat_qr"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $upMsgs[] = L('Image_Not_Valid_File');
            $upErr = true;
        }
    }
    if ($_FILES["payment_wechat_qr"]["size"] > 2097152) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Exceeds_1MB');
        $upErr = true;
    }
    if (!in_array(strtolower($imageFileType), $baseAllowedImgTypes)) {
        $uploadOk = 0;
        $upMsgs[] = L('Image_Unsupported_Format');
        $upErr = true;
    }
    if ($uploadOk != 0) {
        if (@move_uploaded_file($_FILES["payment_wechat_qr"]["tmp_name"], $targetFile)) {
            $qs_config['payment_wechat_qr'] = "upload/images/".$tryFilename;
            $upMsgs[] = L('Payment_WeChat_QR_Uploaded');
        }else{
            $upMsgs[] = L('Image_Write_Server_Failed');
            $upErr = true;
        }
    }
}
?>
