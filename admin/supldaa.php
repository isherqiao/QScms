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
 * @File         supldaa.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Editor upload endpoint: validates type and size, then stores the file under the upload directory.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

//supidaa.php
// 引入语言配置
ob_clean();
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);

// 登录校验：未登录一律拒绝（后台编辑器与图片库都带会话，不受影响）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../control/core.php';
if (!qs_Pass() && !qs_UserPass()) {
    echo json_encode([
        'err' => '未登录或登录已失效，请重新登录后台',
        'msg' => ['url' => '', 'localname' => '', 'id' => 0],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($language_dir)) $language_dir = dirname(__DIR__) . '/files/language/admin/';

$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$dir_url_http = dirname($url) . '/';

$inputName = 'filedata';
$attachDir = 'upload';
$dirType = 2; // 1:按天 2:按月 3:按扩展名
$maxAttachSize = 20971520; // 20MB
$upExt = 'webp,txt,rar,zip,jpg,jpeg,gif,png,fla,swf,flv,wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac,tar,tar.gz,ipk,apk,7z';
$msgType = 2;
$immediate = isset($_GET['immediate']) ? $_GET['immediate'] : 0;
date_default_timezone_set('Asia/Shanghai');

$err = "";
$msg = [];
$tempPath = '../' . $attachDir . '/' . date("YmdHis") . mt_rand(10000, 99999) . '.tmp';
$localName = '';

// 处理上传
if (isset($_SERVER['HTTP_CONTENT_DISPOSITION']) && preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i', $_SERVER['HTTP_CONTENT_DISPOSITION'], $info)) {
    file_put_contents($tempPath, file_get_contents("php://input"));
    $localName = urldecode($info[2]);
} else {
    $upfile = @$_FILES[$inputName];
    if (!isset($upfile)) {
        $err = L('Filename_Error');
    } elseif (!empty($upfile['error'])) {
        switch ($upfile['error']) {
            case 1: $err = L('Size_Exceeds_Server_Limit'); break;
            case 2: $err = L('Size_Exceeds_Form_Limit'); break;
            case 3: $err = L('Partial_Upload'); break;
            case 4: $err = L('No_File_Uploaded_Err'); break;
            case 6: $err = L('Temp_Dir_Missing'); break;
            case 7: $err = L('File_Write_Failed'); break;
            default: $err = L('Unknown_Upload_Error');
        }
    } elseif (empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none') {
        $err = L('No_File_Uploaded_Err');
    } else {
        move_uploaded_file($upfile['tmp_name'], $tempPath);
        $localName = $upfile['name'];
    }
}

// 验证文件
if ($err == '') {
$fileInfo = pathinfo($localName);
$extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';
    if (preg_match('/^(' . str_replace(',', '|', $upExt) . ')$/i', $extension)) {
        $bytes = filesize($tempPath);
        if ($bytes > $maxAttachSize) {
            $err = sprintf(L('Size_Exceeds_Max'), formatBytes($maxAttachSize));
        } else {
            // ---- 外部图床：接了图床就先传图床，成功即返回外链 ----
            $ibUrl = '';
            $ibKeepLocal = false;
            $ibLib = __DIR__ . '/../control/imagebed_lib.php';
            if (is_file($ibLib)) {
                require_once $ibLib;
                if (function_exists('qs_imagebed_enabled') && qs_imagebed_enabled()) {
                    $ibCfg = qs_imagebed_config();
                    $ibMime = function_exists('mime_content_type') ? (string)@mime_content_type($tempPath) : '';
                    $ibRes  = qs_imagebed_upload($tempPath, $localName, $ibMime);
                    if (!empty($ibRes['ok']) && $ibRes['url'] !== '') {
                        $ibUrl = (string)$ibRes['url'];
                        $ibKeepLocal = ((string)($ibCfg['keep_local'] ?? '0') === '1');
                        if (!$ibKeepLocal) {
                            @unlink($tempPath);
                            echo json_encode([
                                'err' => '',
                                'msg' => ['url' => $ibUrl, 'localname' => $localName, 'id' => 1, 'imagebed' => $ibRes['driver']],
                            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    } elseif ((string)($ibCfg['on_fail'] ?? 'local') === 'error') {
                        @unlink($tempPath);
                        echo json_encode([
                            'err' => '图床上传失败：' . $ibRes['err'],
                            'msg' => ['url' => '', 'localname' => ''],
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    // 其余情况（失败但策略是回落本地）：继续走本地落盘
                }
            }

            // 按类型创建目录
            switch ($dirType) {
                case 1: $attachSubDir = 'day_' . date('ymd'); break;
                case 2: $attachSubDir = 'month_' . date('ym'); break;
                case 3: $attachSubDir = 'ext_' . $extension; break;
            }
            $attachFullDir = '../' . $attachDir . '/' . $attachSubDir;
            if (!is_dir($attachFullDir)) {
                @mkdir($attachFullDir, 0777, true);
                @fopen($attachFullDir . '/index.htm', 'w');
            }
            // 生成文件名
            $newFilename = date("YmdHis") . mt_rand(1000, 9999) . '.' . $extension;
            $targetPath = $attachFullDir . '/' . $newFilename;
            rename($tempPath, $targetPath);
            @chmod($targetPath, 0755);
            // 转为前端可用的路径（去掉../）
            $realDest = realpath($targetPath);
$rootPath = realpath($_SERVER['DOCUMENT_ROOT']);
$webPath = str_replace($rootPath, '', $realDest);
$webPath = '/' . ltrim($webPath, '/');
            // 构建返回数据
            $msg = [
                'url' => ($ibUrl !== '' ? $ibUrl : $webPath),
                'localname' => $localName,
                'id' => 1
            ];
        }
    } else {
        $err = sprintf(L('Unsupported_File_Type'), $upExt);
    }
    @unlink($tempPath);
}

// 输出标准JSON
if (!isset($msg['url'])) {
    $msg = ['url' => '', 'localname' => ''];
}

echo json_encode([
    'err' => $err,
    'msg' => $msg
]);

// 辅助函数
function formatBytes($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824 * 100) / 100 . 'GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576 * 100) / 100 . 'MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024 * 100) / 100 . 'KB';
    } else {
        return $bytes . 'Bytes';
    }
}
?>