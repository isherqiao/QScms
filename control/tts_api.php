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
 * @File         tts_api.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  TTS endpoint: public synthesis used by the front-end reader, plus an admin-only test action.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * QsCms 语音朗读公开代理端点
 * -------------------------------------------------------------
 * 供前台 JS 调用（同源，密钥留在服务端，不暴露给浏览器）：
 *   action=synth : 按句合成音频并返回（带本地缓存）
 *   action=test  : 后台「测试合成」按钮调用，返回 JSON 结果
 *
 * 用法：
 *   POST ?action=synth  key=... text=... rate=...  => 音频流
 *   POST ?action=test   key=... text=... rate=...  => {ok,error,size,mime}
 */

require_once __DIR__ . '/core.php';

// 前台朗读合成（action=synth）不强制登录：前端需要调用，且仅允许启用的 TTS key。
// 后台「测试合成」（action=test）只允许管理员，防止公开接口被利用做 SSRF/信息探测。
$isTest = (($_POST['action'] ?? $_GET['action'] ?? '') === 'test');
if ($isTest) {
    if (!(qs_Pass() || qs_UserPass())) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => '无权执行测试（需管理员登录）']);
        exit;
    }
}
qs_tts_handle_api();
