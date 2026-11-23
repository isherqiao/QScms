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
 * @File         tts.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Text-to-speech engine library: provider adapters, voice/format parameters and synthesis helpers.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * QsCms 语音朗读引擎 (TTS)
 * -------------------------------------------------------------
 * 由 isher 基于通用 TTS 调用模型编写。
 * 统一入口：qs_speakArticle()（前端朗读控件输出）
 *           qs_tts_synth()（后端合成，被 tts_api.php 调用）
 *
 * 支持的调用方式（handler）：
 *   web         浏览器原生 Web Speech API（零配置，默认）
 *   openai      OpenAI 兼容 /v1/audio/speech（JSON + Bearer，返回音频流）
 *   baidu       百度短语音合成（OAuth token + form，返回音频流）
 *   tencent     腾讯云基础语音合成（TC3-HMAC-SHA256 签名）
 *   xunfei      讯飞在线语音合成（WebSocket + HMAC-SHA256）
 *   generic_http 通用 HTTP 模板（覆盖 edge 远程/小米/自建/其他公开 TTS）
 *   local_cmd   本地命令行 TTS（espeak-ng / festival / pico2wave / edge-tts CLI）
 *
 * 兼容 PHP 8.3。
 */

if (!defined('SITE_ROOT')) {
    return; // 未通过 core.php 加载则静默退出
}

/* ============================================================
 * 一、内置 handler 清单
 * ============================================================ */
function qs_tts_handlers(): array
{
    return [
        'web'          => ['name' => '浏览器原生朗读 (Web Speech API)', 'note' => '零配置，浏览器自带，无需 API Key'],
        'openai'       => ['name' => 'OpenAI 兼容 TTS', 'note' => 'POST {api_url}，JSON，Authorization: Bearer，返回音频流'],
        'baidu'        => ['name' => '百度短语音合成', 'note' => 'OAuth token + form(text2audio)，返回音频流'],
        'tencent'      => ['name' => '腾讯云基础语音合成', 'note' => 'TC3-HMAC-SHA256 签名，返回 JSON 内 base64'],
        'xunfei'       => ['name' => '讯飞在线语音合成', 'note' => 'WebSocket v2/tts + HMAC-SHA256，返回 base64'],
        'edge'         => ['name' => 'edge-tts（本地 CLI）', 'note' => '需服务器安装 Python + edge-tts'],
        'espeak'       => ['name' => 'espeak-ng（本地 CLI）', 'note' => '需服务器安装 espeak-ng'],
        'festival'     => ['name' => 'Festival text2wave（本地 CLI）', 'note' => '需服务器安装 festival'],
        'pico'         => ['name' => 'pico2wave（本地 CLI）', 'note' => '需服务器安装 libttspico-utils'],
        'xiaomi'       => ['name' => '小米 MiMo TTS', 'note' => 'POST api.xiaomimimo.com，api-key 头，JSON，返回 base64'],
        'generic_http' => ['name' => '通用 HTTP 模板（自定义/其他 TTS）', 'note' => '可配方法/请求头/请求体模板/响应取音频方式'],
        'local_cmd'    => ['name' => '本地命令模板（自定义）', 'note' => '任意可合成 wav/mp3 的命令，含 {text}{voice}{rate}{outfile} 占位符'],
    ];
}

function qs_tts_handler_label(string $h): string
{
    $map = qs_tts_handlers();
    return $map[$h]['name'] ?? $h;
}

/**
 * 后台 TTS 行 HTML（服务端回显，字段结构与前端 JS 一致）
 */
function qs_tts_row_html(array $p): string
{
    $e = function ($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES); };
    $key = $p['key'] ?? '';
    $name = $p['name'] ?? '';
    $handler = $p['handler'] ?? 'web';
    $enabled = ($p['enabled'] ?? '1') !== '0';
    $voice = $p['voice'] ?? '';
    $lang = $p['lang'] ?? '';
    $rate = $p['rate'] ?? '1';
    $method = strtoupper($p['method'] ?? 'POST');
    $apiUrl = $p['api_url'] ?? '';
    $apiKey = $p['api_key'] ?? '';
    $apiSecret = $p['api_secret'] ?? '';
    $extra = $p['extra'] ?? '';
    $cmd = $p['cmd'] ?? '';
    $cmdWorkdir = $p['cmd_workdir'] ?? '';

    $handlerOptions = '';
    foreach (qs_tts_handlers() as $hk => $hv) {
        $handlerOptions .= '<option value="' . $e($hk) . '"' . ($hk === $handler ? ' selected' : '') . '>' . $e($hv['name']) . '</option>';
    }
    $note = qs_tts_handlers()[$handler]['note'] ?? '';

    $r = '<div class="tts-row" style="border:1px solid #e5e5e5;border-radius:6px;padding:10px;margin:8px 0;background:#fcfcfc;">';
    $r .= '<div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">';
    $r .= '<input type="text" name="tts_key[]" value="' . $e($key) . '" placeholder="唯一Key（英文/数字）" style="flex:1;min-width:140px;">';
    $r .= '<input type="text" name="tts_name[]" value="' . $e($name) . '" placeholder="显示名称" style="flex:1;min-width:120px;">';
    $r .= '<select name="tts_handler[]" class="input" onchange="qsTtsHandlerNote(this)">' . $handlerOptions . '</select>';
    $r .= '<label style="white-space:nowrap;"><input type="hidden" name="tts_enabled[]" value="' . ($enabled ? '1' : '0') . '"><input type="checkbox" class="tts-enabled-box"' . ($enabled ? ' checked' : '') . ' onclick="qsTtsEnabled(this)"> 启用</label>';
    $r .= '<button type="button" class="button" onclick="qsTtsTest(this)">测试合成</button>';
    $r .= '<button type="button" class="button" onclick="this.closest(\'.tts-row\').remove()">删除</button>';
    $r .= '<span class="tts-test-result" style="color:#333;"></span>';
    $r .= '</div>';
    $r .= '<div class="tts-help" style="margin-bottom:6px;color:#888;font-size:12px;">' . $e($note) . '</div>';
    $r .= '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">';
    $r .= '<label>音色 <input type="text" name="tts_voice[]" value="' . $e($voice) . '" style="width:120px;"></label>';
    $r .= '<label>语言 <input type="text" name="tts_lang[]" value="' . $e($lang) . '" style="width:90px;"></label>';
    $r .= '<label>默认语速 <input type="text" name="tts_rate[]" value="' . $e($rate) . '" style="width:60px;"></label>';
    $r .= '<label>请求方式 <select name="tts_method[]" class="input" style="width:90px;">' .
        '<option value="POST"' . ($method === 'POST' ? ' selected' : '') . '>POST</option>' .
        '<option value="GET"' . ($method === 'GET' ? ' selected' : '') . '>GET</option></select></label>';
    $r .= '</div>';
    $r .= '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">';
    $r .= '<label style="flex:1;min-width:220px;">API地址 <input type="text" name="tts_api_url[]" value="' . $e($apiUrl) . '" style="width:100%;"></label>';
    $r .= '<label>API Key <input type="text" name="tts_api_key[]" value="' . $e($apiKey) . '" style="width:140px;"></label>';
    $r .= '<label>Secret <input type="text" name="tts_api_secret[]" value="' . $e($apiSecret) . '" style="width:140px;"></label>';
    $r .= '</div>';
    $r .= '<div style="margin-bottom:6px;">额外参数(JSON) <textarea name="tts_extra[]" rows="2" style="width:100%;">' . $e($extra) . '</textarea></div>';
    $r .= '<div style="margin-bottom:6px;">本地命令模板 <input type="text" name="tts_cmd[]" value="' . $e($cmd) . '" style="width:100%;" placeholder="本地 CLI 专用，如 espeak-ng -v {voice} -s {rate} -w {outfile} {text}"></div>';
    $r .= '<div>工作目录 <input type="text" name="tts_cmd_workdir[]" value="' . $e($cmdWorkdir) . '" style="width:100%;"></div>';
    $r .= '</div>';
    return $r;
}

/* ============================================================
 * 二、内置预设（一键填充，apikey 留空，填写获取方式说明）
 * 返回数组：['cfg' => 预填字段, 'help' => 获取方式说明]
 * ============================================================ */
function qs_tts_presets(): array
{
    return [
        'web' => [
            'cfg' => [
                'handler' => 'web', 'name' => '浏览器原生朗读', 'enabled' => '1',
                'voice' => '', 'lang' => 'zh-CN', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '无需申请，浏览器自动朗读（Chrome/Edge 效果最佳）。'
        ],
        'openai' => [
            'cfg' => [
                'handler' => 'openai', 'name' => 'OpenAI 兼容 TTS', 'enabled' => '1',
                'voice' => 'alloy', 'lang' => 'zh-CN', 'rate' => '1',
                'api_url' => 'https://api.openai.com/v1/audio/speech', 'method' => 'POST',
                'api_key' => '', 'api_secret' => '', 'extra' => '{"model":"tts-1","response_format":"mp3"}',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'Key 获取：OpenAI 控制台(platform.openai.com) 生成；或任何 OpenAI 兼容代理(自建/中转)填其接口地址+Key。音色: alloy/echo/fable/onyx/nova/shimmer。'
        ],
        'baidu' => [
            'cfg' => [
                'handler' => 'baidu', 'name' => '百度短语音合成', 'enabled' => '1',
                'voice' => '0', 'lang' => 'zh', 'rate' => '5',
                'api_url' => 'https://tsn.baidu.com/text2audio', 'method' => 'POST',
                'api_key' => '', 'api_secret' => '', 'extra' => '',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'Key 获取：百度智能云-语音技术-短语音合成，创建应用得到 API Key(api_key) 与 Secret Key(api_secret)。语速 voice 对应 per 参数: 0女声/1男声/3情感/4情感/5情感。'
        ],
        'tencent' => [
            'cfg' => [
                'handler' => 'tencent', 'name' => '腾讯云基础语音合成', 'enabled' => '1',
                'voice' => '101001', 'lang' => 'zh-CN', 'rate' => '0',
                'api_url' => 'https://tts.tencentcloudapi.com', 'method' => 'POST',
                'api_key' => '', 'api_secret' => '', 'extra' => '{"Codec":"mp3","SampleRate":16000}',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'Key 获取：腾讯云控制台-访问管理-API密钥管理，SecretId(api_key)+SecretKey(api_secret)。voice 为 VoiceType 音色ID(默认101001女声，见音色列表文档)。'
        ],
        'xunfei' => [
            'cfg' => [
                'handler' => 'xunfei', 'name' => '讯飞在线语音合成', 'enabled' => '1',
                'voice' => 'xiaoyan', 'lang' => 'zh-CN', 'rate' => '50',
                'api_url' => 'tts-api.xf-yun.com', 'method' => 'POST',
                'api_key' => '', 'api_secret' => '', 'extra' => '{"aue":"lame","tte":"UTF8"}',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'Key 获取：讯飞开放平台-在线语音合成，创建应用得 AppID/APIKey/APISecret；api_key 填 AppID，api_secret 填 APIKey，额外参数 extra 内填 APISecret(见示例)。'
        ],
        'edge' => [
            'cfg' => [
                'handler' => 'edge', 'name' => 'edge-tts 本地', 'enabled' => '1',
                'voice' => 'zh-CN-YunxiNeural', 'lang' => 'zh-CN', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '无需 Key。需服务器安装 Python3 + pip install edge-tts。音色如 zh-CN-XiaoxiaoNeural(晓晓女)/zh-CN-YunxiNeural(云希男)。'
        ],
        'espeak' => [
            'cfg' => [
                'handler' => 'espeak', 'name' => 'espeak-ng 本地', 'enabled' => '1',
                'voice' => 'cmn', 'lang' => 'zh', 'rate' => '160',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '无需 Key。需服务器安装 espeak-ng。voice 填语言(如 cmn 中文)，rate 为语速(如 160)。'
        ],
        'festival' => [
            'cfg' => [
                'handler' => 'festival', 'name' => 'Festival 本地', 'enabled' => '1',
                'voice' => '', 'lang' => 'zh', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '无需 Key。需服务器安装 festival。中文支持需额外中文语音库，默认英文可用。'
        ],
        'pico' => [
            'cfg' => [
                'handler' => 'pico', 'name' => 'pico2wave 本地', 'enabled' => '1',
                'voice' => 'zh-CN', 'lang' => 'zh-CN', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '无需 Key。需服务器安装 libttspico-utils。voice 填语言如 zh-CN/en-US。'
        ],
        'xiaomi' => [
            'cfg' => [
                'handler' => 'xiaomi', 'name' => '小米 MiMo TTS', 'enabled' => '1',
                'voice' => 'mimo_default', 'lang' => 'zh-CN', 'rate' => '1',
                'api_url' => 'https://api.xiaomimimo.com/v1/chat/completions', 'method' => 'POST',
                'api_key' => '', 'api_secret' => '', 'extra' => '{"model":"mimo-v2.5-tts","audio_field":"data.0.audio","audio_type":"base64"}',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'Key 获取：小米开放平台(mimo.mi.com)申请 API Key，填 api_key。extra 中 audio_field 为响应取音频字段(默认 data.0.audio base64)，如不一致按接口文档调整。'
        ],
        'custom_http' => [
            'cfg' => [
                'handler' => 'generic_http', 'name' => '自定义 HTTP TTS', 'enabled' => '1',
                'voice' => '', 'lang' => '', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '{"body_template":"{\\\"text\\\":\\\"{text}\\\",\\\"voice\\\":\\\"{voice}\\\"}", "auth_mode":"header_bearer", "resp_mode":"audio_stream"}',
                'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => '适用于其他任意公开 TTS。字段含义见下：auth_mode=none/header_bearer/query_key/form_key；resp_mode=audio_stream(直接音频流)/json_base64(JSON内base64)/json_url(JSON内音频URL)/json_text(字段存文本后再次GET)。body_template 用 {text}{voice}{rate}{key} 占位符。'
        ],
        'local_cmd' => [
            'cfg' => [
                'handler' => 'local_cmd', 'name' => '自定义本地命令 TTS', 'enabled' => '1',
                'voice' => '', 'lang' => '', 'rate' => '1',
                'api_url' => '', 'method' => 'POST', 'api_key' => '', 'api_secret' => '',
                'extra' => '{"outfile_ext":"wav"}', 'cmd' => '', 'cmd_workdir' => '',
            ],
            'help' => 'cmd 支持占位符: {text}文本 {voice}音色 {rate}语速 {outfile}输出音频路径 {workdir}工作目录。示例(espeak-ng): espeak-ng -v {voice} -s {rate} -w {outfile} "{text}"'
        ],
    ];
    // 让每个预设带一个建议 key（如 openai），便于后台添加时直接使用
    foreach ($presets as $pk => $pv) {
        if (!isset($pv['cfg']['key'])) {
            $presets[$pk]['cfg']['key'] = $pk;
        }
    }
    return $presets;
}

/* ============================================================
 * 三、配置读取
 * ============================================================ */
function qs_tts_get_provider(string $key = ''): ?array
{
    global $qs_config;
    $list = $qs_config['speak_tts_list'] ?? [];
    if (!is_array($list)) {
        $list = [];
    }
    foreach ($list as $p) {
        if (is_array($p) && ($p['key'] ?? '') === $key && ($p['enabled'] ?? '1') !== '0') {
            return $p;
        }
    }
    return null;
}

function qs_tts_active_provider(): ?array
{
    global $qs_config;
    $sel = $qs_config['speakArticle'] ?? '';
    $p = qs_tts_get_provider($sel);
    if ($p) {
        return $p;
    }
    // 兜底：第一个启用的
    $list = $qs_config['speak_tts_list'] ?? [];
    if (is_array($list)) {
        foreach ($list as $pp) {
            if (is_array($pp) && ($pp['enabled'] ?? '1') !== '0') {
                return $pp;
            }
        }
    }
    // 列表为空：退回浏览器原生默认
    return [
        'key' => 'web', 'name' => '浏览器原生朗读', 'handler' => 'web', 'enabled' => '1',
        'voice' => '', 'lang' => 'zh-CN', 'rate' => '1', 'method' => 'POST',
        'api_url' => '', 'api_key' => '', 'api_secret' => '', 'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
    ];
}

/* ============================================================
 * 四、通用 HTTP 请求辅助 (cURL)
 * ============================================================ */
function qs_tts_http(string $url, string $method = 'GET', array $headers = [], $body = null, int $timeout = 30): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => '服务器未启用 PHP cURL 扩展，无法调用远程 TTS'];
    }
    $ch = curl_init($url);
    $h = [];
    foreach ($headers as $k => $v) {
        $h[] = $k . ': ' . $v;
    }
    if ($body !== null && $body !== '') {
        $h[] = 'Content-Type: ' . ($headers['Content-Type'] ?? 'application/octet-stream');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $h,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    if (strtoupper($method) === 'GET' && $body === null) {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    }
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $ct = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    if ($resp === false) {
        return ['ok' => false, 'error' => '请求失败: ' . $err];
    }
    return ['ok' => true, 'code' => (int)$code, 'body' => $resp, 'ctype' => (string)$ct];
}

function qs_tts_is_audio_ctype(string $ctype): bool
{
    return (bool)preg_match('#^(audio|application/ogg|application/mp3|video/mpeg)#i', $ctype);
}

/* ============================================================
 * 五、各 handler 合成实现
 * 统一返回 ['ok'=>bool, 'audio'=>string(binary) , 'mime'=>string, 'error'=>?string]
 * ============================================================ */

// ---- OpenAI 兼容 ----
function qs_tts_openai(array $c, string $text, string $rate): array
{
    $url = $c['api_url'] ?? '';
    $key = $c['api_key'] ?? '';
    if ($url === '') {
        return ['ok' => false, 'error' => '未配置接口地址 api_url'];
    }
    $extra = qs_tts_json($c['extra'] ?? '');
    $model = $extra['model'] ?? 'tts-1';
    $fmt = $extra['response_format'] ?? 'mp3';
    $body = json_encode([
        'model' => $model,
        'input' => $text,
        'voice' => $c['voice'] ?? 'alloy',
        'response_format' => $fmt,
        'speed' => (float)($rate ?: 1),
    ]);
    $headers = ['Content-Type' => 'application/json'];
    if ($key !== '') {
        $headers['Authorization'] = 'Bearer ' . $key;
    }
    $r = qs_tts_http($url, 'POST', $headers, $body);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    if ($r['code'] >= 400) {
        return ['ok' => false, 'error' => "HTTP {$r['code']}: " . substr($r['body'], 0, 500)];
    }
    if (qs_tts_is_audio_ctype($r['ctype'])) {
        return ['ok' => true, 'audio' => $r['body'], 'mime' => ($fmt === 'wav' ? 'audio/wav' : 'audio/mpeg')];
    }
    // 有些兼容服务返回 JSON(base64)
    $j = json_decode($r['body'], true);
    if (is_array($j)) {
        foreach (['audio', 'audio_base64', 'data'] as $f) {
            if (!empty($j[$f]) && is_string($j[$f])) {
                return ['ok' => true, 'audio' => base64_decode($j[$f]), 'mime' => 'audio/mpeg'];
            }
        }
    }
    // 纯音频二进制兜底
    return ['ok' => true, 'audio' => $r['body'], 'mime' => 'audio/mpeg'];
}

// ---- 百度短语音 ----
function qs_tts_baidu_token(array $c): string
{
    $key = $c['api_key'] ?? '';
    $secret = $c['api_secret'] ?? '';
    if ($key === '' || $secret === '') {
        return '';
    }
    $cache = SITE_ROOT . '/control/data/tts_cache/baidu_token.txt';
    if (is_file($cache)) {
        $data = @file_get_contents($cache);
        if ($data) {
            $arr = json_decode($data, true);
            if (is_array($arr) && isset($arr['token'], $arr['exp']) && (int)$arr['exp'] > time()) {
                return $arr['token'];
            }
        }
    }
    $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=' .
        rawurlencode($key) . '&client_secret=' . rawurlencode($secret);
    $r = qs_tts_http($url, 'POST', ['Content-Type' => 'application/json']);
    if ($r['ok']) {
        $j = json_decode($r['body'], true);
        if (isset($j['access_token'])) {
            if (!is_dir(dirname($cache))) {
                @mkdir(dirname($cache), 0755, true);
            }
            @file_put_contents($cache, json_encode(['token' => $j['access_token'], 'exp' => time() + (int)($j['expires_in'] ?? 2592000) - 300]));
            return $j['access_token'];
        }
    }
    return '';
}

function qs_tts_baidu(array $c, string $text, string $rate): array
{
    $tok = qs_tts_baidu_token($c);
    if ($tok === '') {
        return ['ok' => false, 'error' => '百度 access_token 获取失败，请检查 api_key/api_secret 是否正确'];
    }
    $url = ($c['api_url'] ?? '') ?: 'https://tsn.baidu.com/text2audio';
    $data = http_build_query([
        'tex' => $text,
        'tok' => $tok,
        'cuid' => 'qscms_tts',
        'ctp' => '1',
        'lan' => $c['lang'] ?? 'zh',
        'spd' => (int)($c['rate'] ?? $rate ?: 5),
        'pit' => (int)($c['extra_pit'] ?? 5),
        'vol' => (int)($c['extra_vol'] ?? 5),
        'per' => (int)($c['voice'] ?? 0),
        'aue' => 3, // 3=mp3
    ]);
    $r = qs_tts_http($url, 'POST', ['Content-Type' => 'application/x-www-form-urlencoded'], $data);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    if ($r['code'] >= 400) {
        return ['ok' => false, 'error' => "HTTP {$r['code']}: " . substr($r['body'], 0, 500)];
    }
    // 失败时 body 可能是 JSON 错误
    if (!qs_tts_is_audio_ctype($r['ctype']) && strpos(ltrim($r['body']), '{') === 0) {
        $j = json_decode($r['body'], true);
        if (is_array($j) && isset($j['err_msg'])) {
            return ['ok' => false, 'error' => $j['err_msg'] . ' (code:' . ($j['err_no'] ?? '') . ')'];
        }
    }
    return ['ok' => true, 'audio' => $r['body'], 'mime' => 'audio/mpeg'];
}

// ---- 腾讯云 TC3-HMAC-SHA256 ----
function qs_tts_tencent(array $c, string $text, string $rate): array
{
    $sid = $c['api_key'] ?? '';
    $skey = $c['api_secret'] ?? '';
    if ($sid === '' || $skey === '') {
        return ['ok' => false, 'error' => '未配置 SecretId(api_key) 与 SecretKey(api_secret)'];
    }
    $host = parse_url(($c['api_url'] ?? '') ?: 'https://tts.tencentcloudapi.com', PHP_URL_HOST) ?: 'tts.tencentcloudapi.com';
    $service = 'tts';
    $action = 'TextToSpeech';
    $version = '2019-08-23';
    $algo = 'TC3-HMAC-SHA256';
    $ts = time();
    $nonce = random_int(100000, 999999);
    $extra = qs_tts_json($c['extra'] ?? '');
    $payload = [
        'Text' => $text,
        'SessionId' => 'qscms-' . md5($text . time()),
        'VoiceType' => (int)($c['voice'] ?? 101001),
        'Codec' => $extra['Codec'] ?? 'mp3',
        'SampleRate' => (int)($extra['SampleRate'] ?? 16000),
    ];
    $body = json_encode($payload);
    $ct = 'application/json; charset=utf-8';
    $date = gmdate('Y-m-d', $ts);
    $credentialScope = $date . '/' . $service . '/tc3_request';
    $hashedPayload = hash('sha256', $body);
    $canonicalHeaders = "content-type:$ct\nhost:$host\nx-tc-action:" . strtolower($action) . "\n";
    $signedHeaders = 'content-type;host;x-tc-action';
    $canonicalRequest = "POST\n/\n\n$canonicalHeaders\n$signedHeaders\n$hashedPayload";
    $stringToSign = "$algo\n$ts\n$credentialScope\n" . hash('sha256', $canonicalRequest);
    $secretDate = hash_hmac('sha256', $date, 'TC3' . $skey, true);
    $secretService = hash_hmac('sha256', $service, $secretDate, true);
    $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
    $signature = hash_hmac('sha256', $stringToSign, $secretSigning);
    $auth = "$algo Credential=$sid/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";
    $headers = [
        'Authorization' => $auth,
        'Content-Type' => $ct,
        'Host' => $host,
        'X-TC-Action' => $action,
        'X-TC-Version' => $version,
        'X-TC-Timestamp' => (string)$ts,
        'X-TC-Nonce' => (string)$nonce,
    ];
    $r = qs_tts_http('https://' . $host, 'POST', $headers, $body);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    $j = json_decode($r['body'], true);
    if (isset($j['Response']['Error'])) {
        return ['ok' => false, 'error' => $j['Response']['Error']['Message'] . ' (Code:' . $j['Response']['Error']['Code'] . ')'];
    }
    $audio = $j['Response']['Audio'] ?? '';
    if ($audio === '') {
        return ['ok' => false, 'error' => '响应缺少 Audio 字段: ' . substr($r['body'], 0, 400)];
    }
    return ['ok' => true, 'audio' => base64_decode($audio), 'mime' => 'audio/mpeg'];
}

// ---- 讯飞在线语音合成 (WebSocket v2/tts) ----
function qs_tts_xunfei(array $c, string $text, string $rate): array
{
    $appid = $c['api_key'] ?? '';
    $apikey = $c['api_secret'] ?? '';
    $extra = qs_tts_json($c['extra'] ?? '');
    $apisecret = $extra['apisecret'] ?? '';
    if ($appid === '' || $apikey === '' || $apisecret === '') {
        return ['ok' => false, 'error' => '未配置 AppID(api_key)/APIKey(api_secret)/APISecret(extra内)'];
    }
    $host = ($c['api_url'] ?? '') ?: 'tts-api.xf-yun.com';
    $path = '/v2/tts';
    $date = gmdate('D, d M Y H:i:s \G\M\T');
    $origin = "host: $host\ndate: $date\nGET $path HTTP/1.1";
    $sig = base64_encode(hash_hmac('sha256', $origin, $apisecret, true));
    $authz = 'api_key="' . $apikey . '", algorithm="hmac-sha256", headers="host date request-line", signature="' . $sig . '"';
    $query = http_build_query(['authorization' => base64_encode($authz), 'host' => $host, 'date' => $date]);
    $wsUrl = 'wss://' . $host . $path . '?' . $query;

    // 组帧数据
    $voice = $c['voice'] ?? 'xiaoyan';
    $speed = (int)($rate ?: ($c['rate'] ?: 50));
    $aue = $extra['aue'] ?? 'lame';
    $sfl = in_array($aue, ['raw']) ? 0 : 1;
    $frame = json_encode([
        'common' => ['app_id' => $appid],
        'business' => ['aue' => $aue, 'sfl' => $sfl, 'tte' => $extra['tte'] ?? 'UTF8', 'vcn' => $voice, 'speed' => $speed, 'volume' => 50, 'pitch' => 50],
        'data' => ['status' => 2, 'text' => base64_encode($text)],
    ]);

    $sock = @stream_socket_client('ssl://' . $host . ':443', $errno, $errstr, 15);
    if (!$sock) {
        return ['ok' => false, 'error' => "无法连接讯飞: $errstr ($errno)"];
    }
    // 发起 WebSocket 握手
    $key = base64_encode(random_bytes(16));
    $req = "GET $path?$query HTTP/1.1\r\n" .
        "Host: $host\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Key: $key\r\n" .
        "Sec-WebSocket-Version: 13\r\n\r\n";
    fwrite($sock, $req);
    // 读握手响应头
    $hdr = '';
    while (!feof($sock)) {
        $hdr .= fread($sock, 4096);
        if (strpos($hdr, "\r\n\r\n") !== false) {
            break;
        }
    }
    if (strpos($hdr, ' 101 ') === false) {
        fclose($sock);
        return ['ok' => false, 'error' => '讯飞 WebSocket 握手失败: ' . substr($hdr, 0, 300)];
    }

    qs_tts_ws_send($sock, $frame);
    stream_set_timeout($sock, 30);

    $audio = '';
    $err = '';
    $done = false;
    $deadline = microtime(true) + 60;
    while (!$done && microtime(true) < $deadline && !feof($sock)) {
        $opcode = null;
        $data = qs_tts_ws_recv($sock, $opcode);
        if ($data === null) {
            break;
        }
        if ($opcode === 8) {
            break; // close
        }
        $j = json_decode($data, true);
        if (!is_array($j)) {
            continue;
        }
        $code = $j['code'] ?? 0;
        if ($code !== 0) {
            $err = $j['message'] ?? ("讯飞错误码 $code");
            break;
        }
        $audio64 = $j['payload']['tts']['audio'] ?? '';
        if ($audio64 !== '') {
            $audio .= base64_decode($audio64);
        }
        if (($j['data']['status'] ?? 2) === 2) {
            $done = true;
        }
    }
    fclose($sock);
    if ($err !== '') {
        return ['ok' => false, 'error' => $err];
    }
    if ($audio === '') {
        return ['ok' => false, 'error' => '讯飞未返回音频数据'];
    }
    return ['ok' => true, 'audio' => $audio, 'mime' => ($aue === 'raw' ? 'audio/wav' : 'audio/mpeg')];
}

// WebSocket 发送（客户端掩码帧）
function qs_tts_ws_send($sock, string $payload): void
{
    $len = strlen($payload);
    $mask = random_bytes(4);
    $header = "\x81";
    if ($len < 126) {
        $header .= chr(0x80 | $len);
    } elseif ($len < 65536) {
        $header .= chr(0x80 | 126) . pack('n', $len);
    } else {
        $header .= chr(0x80 | 127) . pack('J', $len);
    }
    $masked = $payload ^ str_repeat($mask, intdiv($len + 3, 4));
    $masked = substr($masked, 0, $len);
    fwrite($sock, $header . $mask . $masked);
}

function qs_tts_ws_recv($sock, ?int &$opcode): ?string
{
    $head = fread($sock, 2);
    if (strlen($head) < 2) {
        return null;
    }
    $b = ord($head[0]);
    $lenByte = ord($head[1]);
    $opcode = $b & 0x0F;
    $masked = ($lenByte & 0x80) !== 0;
    $len = $lenByte & 0x7F;
    if ($len === 126) {
        $len = unpack('n', fread($sock, 2))[1];
    } elseif ($len === 127) {
        $len = unpack('J', fread($sock, 8))[1];
    }
    $maskKey = $masked ? fread($sock, 4) : '';
    $data = $len > 0 ? fread($sock, $len) : '';
    if ($masked && $data !== '') {
        $data = $data ^ str_repeat($maskKey, intdiv($len + 3, 4));
        $data = substr($data, 0, $len);
    }
    return $data;
}

// ---- 本地命令行 TTS（通用） ----
function qs_tts_run_cmd(string $cmd): array
{
    $out = [];
    $code = 0;
    // 限制：禁止 shell 链式/变量替换类符号（防注入）；占位符已用 escapeshellarg 转义。
    // 允许 | < >（管道/重定向，供 festival 等模板使用）
    if (preg_match('#[;&`$(){}]#', $cmd)) {
        return ['ok' => false, 'error' => '命令包含非法字符(为避免注入，禁止 ; & ` $ ( ) { } 等字符)'];
    }
    // 屏蔽危险命令片段
    foreach (['rm ', 'del ', 'shutdown', 'format ', 'mkfs', ':(){', '> /dev/sd'] as $bad) {
        if (stripos($cmd, $bad) !== false) {
            return ['ok' => false, 'error' => '命令包含被禁止的危险操作'];
        }
    }
    exec($cmd . ' 2>&1', $out, $code);
    return ['ok' => ($code === 0), 'error' => ($code === 0 ? '' : '命令退出码 ' . $code . ': ' . implode("\n", $out)), 'output' => implode("\n", $out)];
}

function qs_tts_local_cmd(array $c, string $text, string $rate, string $voice): array
{
    $cmdTemplate = $c['cmd'] ?? '';
    if ($cmdTemplate === '') {
        return ['ok' => false, 'error' => '未配置 cmd 命令模板'];
    }
    if (!function_exists('exec')) {
        return ['ok' => false, 'error' => '服务器禁用了 exec()，无法使用本地命令行 TTS'];
    }
    $extra = qs_tts_json($c['extra'] ?? '');
    $ext = $extra['outfile_ext'] ?? 'wav';
    $dir = SITE_ROOT . '/control/data/tts_cache/cmd';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $outfile = $dir . '/cmd_' . md5($text . $rate . $voice) . '.' . $ext;
    $workdir = ($c['cmd_workdir'] ?? '') ?: SITE_ROOT;
    $cmd = str_replace(
        ['{text}', '{voice}', '{rate}', '{outfile}', '{workdir}'],
        [escapeshellarg($text), escapeshellarg((string)$voice), escapeshellarg((string)$rate), escapeshellarg($outfile), escapeshellarg($workdir)],
        $cmdTemplate
    );
    $r = qs_tts_run_cmd($cmd);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    if (!is_file($outfile)) {
        return ['ok' => false, 'error' => '命令执行成功但未生成音频文件，检查 outfile 路径/命令是否带 -w 等写文件参数。输出: ' . ($r['output'] ?? '')];
    }
    $audio = file_get_contents($outfile);
    @unlink($outfile);
    return ['ok' => true, 'audio' => $audio, 'mime' => ($ext === 'mp3' ? 'audio/mpeg' : 'audio/wav')];
}

// ---- 本地预设快捷实现（edge / espeak / festival / pico）----
function qs_tts_edge(array $c, string $text, string $rate, string $voice): array
{
    // edge-tts CLI: edge-tts --voice X --text "..." --write-media out.mp3 [--rate +10%]
    $voice = $voice ?: 'zh-CN-YunxiNeural';
    $c['cmd'] = 'edge-tts --voice {voice} --rate ' . (($rate && $rate != '1') ? ('+' . (int)((float)$rate * 100 - 100) . '%') : '+0%') . ' --text {text} --write-media {outfile}';
    $c['extra'] = '{"outfile_ext":"mp3"}';
    return qs_tts_local_cmd($c, $text, $rate, $voice);
}

function qs_tts_espeak(array $c, string $text, string $rate, string $voice): array
{
    $voice = $voice ?: 'cmn';
    $rate = $rate ?: 160;
    $c['cmd'] = 'espeak-ng -v {voice} -s {rate} -w {outfile} {text}';
    $c['extra'] = '{"outfile_ext":"wav"}';
    return qs_tts_local_cmd($c, $text, $rate, $voice);
}

function qs_tts_festival(array $c, string $text, string $rate, string $voice): array
{
    $c['cmd'] = 'echo {text} | text2wave -o {outfile}';
    $c['extra'] = '{"outfile_ext":"wav"}';
    return qs_tts_local_cmd($c, $text, $rate, $voice);
}

function qs_tts_pico(array $c, string $text, string $rate, string $voice): array
{
    $voice = $voice ?: 'zh-CN';
    $c['cmd'] = 'pico2wave -l {voice} -w {outfile} {text}';
    $c['extra'] = '{"outfile_ext":"wav"}';
    return qs_tts_local_cmd($c, $text, $rate, $voice);
}

// ---- 小米 MiMo ----
function qs_tts_xiaomi(array $c, string $text, string $rate, string $voice): array
{
    $url = $c['api_url'] ?? '';
    $key = $c['api_key'] ?? '';
    if ($url === '' || $key === '') {
        return ['ok' => false, 'error' => '未配置接口地址 api_url 与 api_key'];
    }
    $extra = qs_tts_json($c['extra'] ?? '');
    $model = $extra['model'] ?? 'mimo-v2.5-tts';
    $body = json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => $text]],
        'audio' => ['voice' => $voice ?: 'mimo_default'],
    ]);
    $r = qs_tts_http($url, 'POST', ['Content-Type' => 'application/json', 'api-key' => $key], $body);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    if ($r['code'] >= 400) {
        return ['ok' => false, 'error' => "HTTP {$r['code']}: " . substr($r['body'], 0, 500)];
    }
    $j = json_decode($r['body'], true);
    $field = $extra['audio_field'] ?? 'data.0.audio';
    $val = qs_tts_dot($j, $field);
    if (is_string($val) && $val !== '') {
        $audio = ($extra['audio_type'] ?? 'base64') === 'base64' ? base64_decode($val) : $val;
        return ['ok' => true, 'audio' => $audio, 'mime' => 'audio/mpeg'];
    }
    return ['ok' => false, 'error' => '未在响应中找到音频字段 ' . $field . '，请按接口文档调整 extra.audio_field。原始响应: ' . substr($r['body'], 0, 300)];
}

// ---- 通用 HTTP 模板 ----
function qs_tts_generic_http(array $c, string $text, string $rate, string $voice): array
{
    $url = $c['api_url'] ?? '';
    if ($url === '') {
        return ['ok' => false, 'error' => '未配置接口地址 api_url'];
    }
    $extra = qs_tts_json($c['extra'] ?? '');
    $method = strtoupper($c['method'] ?? 'POST');
    $key = $c['api_key'] ?? '';
    $secret = $c['api_secret'] ?? '';
    $authMode = $extra['auth_mode'] ?? 'header_bearer';
    $respMode = $extra['resp_mode'] ?? 'audio_stream';
    $bodyTemplate = $extra['body_template'] ?? '';
    $headersRaw = $extra['headers'] ?? '';
    $bodyTemplate = str_replace(['{text}', '{voice}', '{rate}', '{key}', '{secret}'], [$text, $voice, $rate, $key, $secret], $bodyTemplate);

    $url = str_replace(['{text}', '{voice}', '{rate}', '{key}', '{secret}'], [rawurlencode($text), rawurlencode($voice), rawurlencode($rate), rawurlencode($key), rawurlencode($secret)], $url);

    $headers = [];
    if (is_string($headersRaw) && $headersRaw !== '') {
        foreach (preg_split('/\r?\n/', $headersRaw) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }
            [$hk, $hv] = explode(':', $line, 2);
            $headers[trim($hk)] = trim($hv);
        }
    }
    // 认证
    $body = null;
    $queryAppend = '';
    if ($authMode === 'header_bearer' && $key !== '') {
        $headers['Authorization'] = 'Bearer ' . $key;
    } elseif ($authMode === 'query_key' && $key !== '') {
        $queryAppend = (strpos($url, '?') === false ? '?' : '&') . urlencode($key) . '=' . urlencode($secret !== '' ? $secret : '1');
    }
    if ($bodyTemplate !== '') {
        $body = $bodyTemplate;
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = ($extra['body_type'] ?? 'application/json');
        }
        if ($authMode === 'form_key') {
            $queryAppend = (strpos($url, '?') === false ? '?' : '&') . 'apikey=' . urlencode($key);
        }
    }
    $r = qs_tts_http($url . $queryAppend, $method, $headers, $body);
    if (!$r['ok']) {
        return ['ok' => false, 'error' => $r['error']];
    }
    if ($r['code'] >= 400) {
        return ['ok' => false, 'error' => "HTTP {$r['code']}: " . substr($r['body'], 0, 500)];
    }
    switch ($respMode) {
        case 'audio_stream':
            return ['ok' => true, 'audio' => $r['body'], 'mime' => ($r['ctype'] && qs_tts_is_audio_ctype($r['ctype']) ? $r['ctype'] : 'audio/mpeg')];
        case 'json_base64':
            $j = json_decode($r['body'], true);
            $field = $extra['audio_field'] ?? 'audio';
            $val = qs_tts_dot($j, $field);
            if (is_string($val) && $val !== '') {
                return ['ok' => true, 'audio' => base64_decode($val), 'mime' => 'audio/mpeg'];
            }
            return ['ok' => false, 'error' => '未在响应中找到音频字段 ' . $field . ': ' . substr($r['body'], 0, 300)];
        case 'json_url':
            $j = json_decode($r['body'], true);
            $field = $extra['audio_field'] ?? 'audio_url';
            $val = qs_tts_dot($j, $field);
            if (is_string($val) && $val !== '') {
                $r2 = qs_tts_http($val, 'GET');
                if ($r2['ok'] && $r2['code'] < 400) {
                    return ['ok' => true, 'audio' => $r2['body'], 'mime' => 'audio/mpeg'];
                }
                return ['ok' => false, 'error' => '拉取音频URL失败: ' . ($r2['error'] ?? ('HTTP ' . $r2['code']))];
            }
            return ['ok' => false, 'error' => '未在响应中找到音频URL字段 ' . $field . ': ' . substr($r['body'], 0, 300)];
        case 'json_text':
            // 字段存的是可再请求的文本/ID
            $j = json_decode($r['body'], true);
            $field = $extra['audio_field'] ?? 'text';
            $val = qs_tts_dot($j, $field);
            if (is_string($val) && $val !== '') {
                $u2 = str_replace(['{text}', '{voice}', '{rate}', '{key}'], [rawurlencode($val), rawurlencode($voice), rawurlencode($rate), rawurlencode($key)], $url);
                $r2 = qs_tts_http($u2, $method, $headers);
                if ($r2['ok'] && $r2['code'] < 400) {
                    return ['ok' => true, 'audio' => $r2['body'], 'mime' => 'audio/mpeg'];
                }
                return ['ok' => false, 'error' => '二次请求失败: ' . ($r2['error'] ?? ('HTTP ' . $r2['code']))];
            }
            return ['ok' => false, 'error' => '未找到 ' . $field . ': ' . substr($r['body'], 0, 300)];
        default:
            return ['ok' => true, 'audio' => $r['body'], 'mime' => 'audio/mpeg'];
    }
}

/* ============================================================
 * 六、辅助
 * ============================================================ */
function qs_tts_json($s)
{
    if (is_array($s)) {
        return $s;
    }
    if (!is_string($s) || trim($s) === '') {
        return [];
    }
    $j = json_decode($s, true);
    return is_array($j) ? $j : [];
}

function qs_tts_dot($arr, string $path)
{
    if (!is_array($arr)) {
        return null;
    }
    $cur = $arr;
    foreach (explode('.', $path) as $seg) {
        $seg = trim($seg);
        if (is_array($cur) && isset($cur[$seg])) {
            $cur = $cur[$seg];
        } else {
            return null;
        }
    }
    return $cur;
}

/* ============================================================
 * 七、合成主入口
 * 返回 ['ok'=>bool, 'audio'=>string|'', 'mime'=>string, 'error'=>?string]
 * ============================================================ */
function qs_tts_synth(array $c, string $text, string $rate = ''): array
{
    $handler = $c['handler'] ?? 'web';
    $voice = $c['voice'] ?? '';
    if ($rate === '') {
        $rate = $c['rate'] ?? '1';
    }
    switch ($handler) {
        case 'web':
            return ['ok' => false, 'error' => '浏览器原生模式无需服务端合成'];
        case 'openai':
            return qs_tts_openai($c, $text, $rate);
        case 'baidu':
            return qs_tts_baidu($c, $text, $rate);
        case 'tencent':
            return qs_tts_tencent($c, $text, $rate);
        case 'xunfei':
            return qs_tts_xunfei($c, $text, $rate);
        case 'edge':
            return qs_tts_edge($c, $text, $rate, $voice);
        case 'espeak':
            return qs_tts_espeak($c, $text, $rate, $voice);
        case 'festival':
            return qs_tts_festival($c, $text, $rate, $voice);
        case 'pico':
            return qs_tts_pico($c, $text, $rate, $voice);
        case 'xiaomi':
            return qs_tts_xiaomi($c, $text, $rate, $voice);
        case 'local_cmd':
            return qs_tts_local_cmd($c, $text, $rate, $voice);
        case 'generic_http':
        default:
            return qs_tts_generic_http($c, $text, $rate, $voice);
    }
}

/* ============================================================
 * 八、缓存
 * ============================================================ */
function qs_tts_cache_dir(): string
{
    $d = SITE_ROOT . '/control/data/tts_cache/audio';
    if (!is_dir($d)) {
        @mkdir($d, 0755, true);
    }
    return $d;
}

function qs_tts_cache_path(array $c, string $text, string $rate): string
{
    $key = md5(($c['key'] ?? '') . '|' . ($c['handler'] ?? '') . '|' . ($c['api_url'] ?? '') . '|' . ($c['voice'] ?? '') . '|' . ($rate ?: '1') . '|' . $text);
    return qs_tts_cache_dir() . '/' . $key . '.mp3';
}

/* ============================================================
 * 九、前端朗读控件输出 qs_speakArticle()（自包含：按钮+语速+脚本）
 * ============================================================ */
function qs_speakArticle($qs_config, $rate = '读速:')
{
    static $ttsInstance = 0;
    if (!isset($qs_config['openspeak']) || $qs_config['openspeak'] !== 'open') {
        return; // 全局开关关闭
    }
    $provider = qs_tts_active_provider();
    if (!$provider) {
        return;
    }
    $ttsInstance++;
    $uid = 'qstts' . $ttsInstance;
    $handler = $provider['handler'] ?? 'web';
    $lang = htmlspecialchars($provider['lang'] ?? 'zh-CN', ENT_QUOTES);
    $defaultRate = htmlspecialchars($provider['rate'] ?? '1', ENT_QUOTES);
    $proxy = WEB_BASE . 'control/tts_api.php';
    $pkey = htmlspecialchars($provider['key'] ?? '', ENT_QUOTES);
    $isWeb = ($handler === 'web');
    $rateLabel = htmlspecialchars($rate, ENT_QUOTES);
    ?>
    <div class="qstts-box" style="display:flex;align-items:center;gap:8px;margin:8px 0;flex-wrap:wrap;">
        <button type="button" id="<?= $uid; ?>-toggle" style="cursor:pointer;border:1px solid #ccc;background:#fff;border-radius:4px;padding:2px 8px;font-size:14px;">▶️</button>
        <label for="<?= $uid; ?>-rate"><?= $rateLabel; ?></label>
        <input type="range" id="<?= $uid; ?>-rate" min="0.1" max="2" step="0.1" value="1">
        <span id="<?= $uid; ?>-rate-val">1</span>
    </div>
    <script>
    (function () {
        function qsInit() {
        var btn = document.getElementById('<?= $uid; ?>-toggle');
        if (!btn) return;
        var art = document.getElementById('article-content');
        if (!art) {
            var box = btn.closest('.qstts-box');
            if (box) box.style.display = 'none';
            return;
        }
        var playing = false, idx = 0, sentences = [];
        var rateControl = parseFloat('<?= $defaultRate; ?>') || 1;
        var rateEl = document.getElementById('<?= $uid; ?>-rate');
        var rateVal = document.getElementById('<?= $uid; ?>-rate-val');
        var isWeb = <?= $isWeb ? 'true' : 'false'; ?>;
        var audio = isWeb ? null : new Audio();
        var proxy = '<?= $proxy; ?>';
        var providerKey = '<?= $pkey; ?>';
        var lang = '<?= $lang; ?>';

        function split() {
            var t = art.innerText || '';
            sentences = t.split(/([。！？!?；;\n])/).filter(function (s) { return s.trim() !== ''; });
            idx = 0;
        }
        function stop() {
            playing = false;
            if (isWeb) {
                if ('speechSynthesis' in window) speechSynthesis.cancel();
            } else {
                audio.pause();
                audio.removeAttribute('src');
            }
            if (btn) btn.textContent = '▶️';
        }
        function playNext() {
            if (idx >= sentences.length) { stop(); return; }
            var text = sentences[idx++];
            if (isWeb) {
                if (!('speechSynthesis' in window)) { stop(); return; }
                var u = new SpeechSynthesisUtterance(text);
                u.lang = lang;
                u.rate = rateControl;
                u.onend = function () { playNext(); };
                u.onerror = function () { stop(); };
                speechSynthesis.speak(u);
                return;
            }
            var fd = new FormData();
            fd.append('action', 'synth');
            fd.append('key', providerKey);
            fd.append('text', text);
            fd.append('rate', String(rateControl));
            var xhr = new XMLHttpRequest();
            xhr.open('POST', proxy, true);
            xhr.responseType = 'blob';
            xhr.onload = function () {
                if (xhr.status === 200 && xhr.response && xhr.response.size > 0) {
                    var url = URL.createObjectURL(xhr.response);
                    audio.src = url;
                    audio.onended = function () { URL.revokeObjectURL(url); playNext(); };
                    audio.play();
                } else {
                    var r = new FileReader();
                    r.onload = function () {
                        try { var j = JSON.parse(r.result); if (j && j.error) alert('朗读失败：' + j.error); } catch (e) {}
                        stop();
                    };
                    r.readAsText(xhr.response);
                }
            };
            xhr.onerror = function () { stop(); };
            xhr.send(fd);
        }
        if (btn) {
            btn.addEventListener('click', function () {
                if (playing) { stop(); }
                else { split(); playing = true; btn.textContent = '⏸️'; playNext(); }
            });
        }
        if (rateEl) {
            rateEl.addEventListener(isWeb ? 'input' : 'change', function () {
                rateControl = parseFloat(rateEl.value) || 1;
                if (rateVal) rateVal.textContent = rateControl;
                if (playing && !isWeb) { stop(); split(); playing = true; btn.textContent = '⏸️'; playNext(); }
            });
        }
        }
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', qsInit); }
        else { qsInit(); }
    })();
    </script>
    <?php
}

/* ============================================================
 * 十、代理端点处理（供 control/tts_api.php 调用）
 * ============================================================ */
function qs_tts_handle_api(): void
{
    $action = $_POST['action'] ?? ($_GET['action'] ?? 'synth');
    $key = $_POST['key'] ?? ($_GET['key'] ?? '');
    $rate = $_POST['rate'] ?? ($_GET['rate'] ?? '');

    $provider = null;
    $inline = (($_POST['inline'] ?? '') === '1' || ($_GET['inline'] ?? '') === '1');
    if ($inline) {
        // 后台测试：用表单提交的未保存配置
        $provider = [
            'key' => $key,
            'handler' => $_POST['handler'] ?? 'generic_http',
            'voice' => $_POST['voice'] ?? '',
            'lang' => $_POST['lang'] ?? '',
            'rate' => $_POST['rate'] ?? '1',
            'method' => $_POST['method'] ?? 'POST',
            'api_url' => $_POST['api_url'] ?? '',
            'api_key' => $_POST['api_key'] ?? '',
            'api_secret' => $_POST['api_secret'] ?? '',
            'extra' => $_POST['extra'] ?? '',
            'cmd' => $_POST['cmd'] ?? '',
            'cmd_workdir' => $_POST['cmd_workdir'] ?? '',
            'enabled' => '1',
        ];
    } else {
        // 前台/正式：按已保存 key 查找启用的配置
        $provider = qs_tts_get_provider($key);
        if (!$provider) {
            if ($action === 'test') {
                qs_tts_json_out(['ok' => false, 'error' => '未找到启用的 TTS 配置: ' . $key]);
            }
            http_response_code(400);
            exit('invalid tts key');
        }
    }

    if (($provider['handler'] ?? 'web') === 'web') {
        qs_tts_json_out(['ok' => false, 'error' => '浏览器原生模式无需服务端合成']);
    }

    if ($action === 'test') {
        $text = $_POST['text'] ?? '这是一段语音朗读测试，验证 TTS 配置是否可用。';
        $r = qs_tts_synth($provider, $text, $rate);
        if (!$r['ok']) {
            qs_tts_json_out(['ok' => false, 'error' => $r['error']]);
        }
        qs_tts_json_out(['ok' => true, 'size' => strlen($r['audio']), 'mime' => $r['mime']]);
    }

    // synth：带缓存
    $text = $_POST['text'] ?? '';
    $text = trim($text);
    if ($text === '') {
        http_response_code(400);
        exit('empty text');
    }
    $rate = ($rate !== '') ? $rate : ($provider['rate'] ?? '1');
    $cacheFile = qs_tts_cache_path($provider, $text, $rate);
    if (!is_file($cacheFile)) {
        $r = qs_tts_synth($provider, $text, $rate);
        if (!$r['ok']) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => $r['error']]);
            exit;
        }
        @file_put_contents($cacheFile, $r['audio']);
        @file_put_contents($cacheFile . '.mime', $r['mime'] ?? 'audio/mpeg');
    }
    $audio = file_get_contents($cacheFile);
    $mime = @file_get_contents($cacheFile . '.mime');
    $mime = ($mime !== false && $mime !== '') ? $mime : 'audio/mpeg';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($audio));
    echo $audio;
    exit;
}

function qs_tts_json_out(array $data): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
