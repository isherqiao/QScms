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
 * @File         post-edit.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 04/09/26 17:39:56
 * @Description  Post editor: rich-text editing with xhEditor/Quill, AI translation prompt, image upload and saving into data/posts.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
require __DIR__.'/keywords/filter.php';
$blocked_hits = [];
$aiPromptFile = $datapaths . $set_ai_prompt;
//翻译Prompt内置默认模板，可被数据文件里的 prompt_translate 覆盖
//占位符：{{target_lang}} 目标语言，{{input_text}} 待翻译正文
$qs_ai_trans_prompt_default = "翻译成{{target_lang}}语言的文章，让文章更适合{{target_lang}}当地人和欧美国家人阅读\n1. 将全部收到的中文文本完整翻译成{{target_lang}}，输出结果只能是目标语言，禁止保留任何中文；\n2. 原文里所有换行、段落位置必须原样保留；\n3. 不要输出开场白、总结、解释、备注,不要在结尾询问下一步工作；\n4. 不要添加任何参考文献、百分比数据、框架模型；\n5. 只返回译文。\n\n待翻译：\n{{input_text}}";
$qs_ai_defaults = [
    'prompt_summary'   => '',
    'prompt_format'    => '',
    'prompt_translate' => $qs_ai_trans_prompt_default
];
//文章翻译模块总开关（高级功能 → 文章翻译），关闭时整块不渲染、后端动作也拒绝
$post_translate_on = (($qs_adv_config['post_translate'] ?? 'close') === 'open');
//后台界面是否中文：决定语言下拉里是「中文」还是「其它语言」的取舍
$qs_ui_lang_hint = (string) ($qs_config['language_file'] ?? '');
if ($qs_ui_lang_hint === '') {
    $qs_ui_lang_hint = (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en');
}
$qs_ui_is_zh = (stripos($qs_ui_lang_hint, 'zh') !== false);
$qs_ai_set = $qs_ai_defaults;
if (file_exists($aiPromptFile)) {
    include $aiPromptFile;
    if (!is_array($qs_ai_set)) {
        $qs_ai_set = [];
    }
    $qs_ai_set = array_merge($qs_ai_defaults, $qs_ai_set);
}

$action = $_POST['action'] ?? '';

//函数移到最外层，全局可用！！！
function callAiApi(string $prompt, int $timeout = 40, ?int $pickIndex = null): array
{
    global $qs_config;
    $ai_api_list = $qs_config['ai_api_list'] ?? [];
    if (empty($ai_api_list)) {
        return [false, 'no_config'];
    }

    // 指定下标：只调用这一个通道，不走fallback
    if($pickIndex !== null && isset($ai_api_list[$pickIndex])){
        $oneCfg = $ai_api_list[$pickIndex];
        $sf_api_key = trim($oneCfg['apikey'] ?? '');
        $sf_site    = trim($oneCfg['site'] ?? '');
        $sf_model   = trim($oneCfg['model'] ?? '');
        if (empty($sf_api_key) || empty($sf_site) || empty($sf_model)) {
            return [false, 'fail'];
        }
        $apiUrl = rtrim($sf_site, '/') . '/v1/chat/completions';
        $reqData = json_encode([
            "model" => $sf_model,
            "messages" => [["role" => "user", "content" => $prompt]],
            "stream" => false
        ]);
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $sf_api_key
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        curl_close($ch);
        $respJson = json_decode($resp, true);
        if (isset($respJson['choices'][0]['message']['content'])) {
            $raw = trim($respJson['choices'][0]['message']['content']);
            $raw = preg_replace('/^```html\s*/i', '', $raw);
            $raw = preg_replace('/```\s*$/i', '', $raw);
            $banPrefix = ['user', 'assistant', '请继续', '下面是处理', '处理后的html', '处理结果：', '输出：', '以下是'];
            foreach ($banPrefix as $badStr) {
                if (stripos($raw, $badStr) === 0) {
                    $raw = preg_replace('/^.*?' . preg_quote($badStr, '/') . '.*?[:：]\s*/is', '', $raw);
                }
            }
            $raw = trim($raw);
            return [true, $raw];
        }
        return [false, 'fail'];
    }

    // 原有自动轮询逻辑
    $result = '';
    foreach ($ai_api_list as $oneCfg) {
        $sf_api_key = trim($oneCfg['apikey'] ?? '');
        $sf_site    = trim($oneCfg['site'] ?? '');
        $sf_model   = trim($oneCfg['model'] ?? '');
        if (empty($sf_api_key) || empty($sf_site) || empty($sf_model)) {
            continue;
        }
        $apiUrl = rtrim($sf_site, '/') . '/v1/chat/completions';
        $reqData = json_encode([
            "model" => $sf_model,
            "messages" => [["role" => "user", "content" => $prompt]],
            "stream" => false
        ]);
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $sf_api_key
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
if (PHP_VERSION_ID < 80000) {
    curl_close($ch);
}
        $respJson = json_decode($resp, true);
        if (isset($respJson['choices'][0]['message']['content'])) {
            $raw = trim($respJson['choices'][0]['message']['content']);
            $raw = preg_replace('/^```html\s*/i', '', $raw);
            $raw = preg_replace('/```\s*$/i', '', $raw);
            $banPrefix = ['user', 'assistant', '请继续', '下面是处理', '处理后的html', '处理结果：', '输出：', '以下是'];
            foreach ($banPrefix as $badStr) {
                if (stripos($raw, $badStr) === 0) {
                    $raw = preg_replace('/^.*?' . preg_quote($badStr, '/') . '.*?[:：]\s*/is', '', $raw);
                }
            }
            $raw = trim($raw);
            $result = $raw;
            break;
        }
    }
    if ($result !== '') {
        return [true, $result];
    }
    return [false, 'fail'];
}

//每个action独立if判断，命中之后ob_clean()->header()->echo json->exit

if ($action === 'gen_summary') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $pickAiIndex = isset($_POST['pick_ai_index']) && $_POST['pick_ai_index'] !== '' ? intval($_POST['pick_ai_index']) : null;
    //业务逻辑
    $post_content = $_POST['post_content'] ?? '';
    $post_content = trim($post_content);
    if (empty($post_content)) {
        echo json_encode(["code" => 400, "msg" => L('Article_Body_Cannot_Empty')]);
        exit;
    }
    $input_text = mb_substr($post_content, 0, 4000, 'utf-8');
    $prompt = str_replace('{{input_text}}',$input_text,$qs_ai_set['prompt_summary']);
    [$ok, $summary] = callAiApi($prompt, 40);
    if (!$ok) {
        if ($summary === 'no_config') {
            echo json_encode(["code" => 400, "msg" => L('AI_API_List_Not_Configured')]);
        } else {
            echo json_encode(["code" => 500, "msg" => L('All_API_Call_Failed')]);
        }
        exit;
    }
    $banWords = ["文章探讨了", "文中指出", "作者认为", "本文提到", "这篇文章"];
    foreach ($banWords as $w) {
        $summary = str_replace($w, '', $summary);
    }
    $summary = trim($summary);
    echo json_encode([
        "code" => 200,
        "data" => ["summary" => $summary]
    ]);
    exit;
}

if ($action === 'ai_format_content') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $pickAiIndex = isset($_POST['pick_ai_index']) && $_POST['pick_ai_index'] !== '' ? intval($_POST['pick_ai_index']) : null;
    $post_html = $_POST['post_html'] ?? '';
    $post_html = trim($post_html);
    if (empty($post_html)) {
        echo json_encode(["code" => 400, "msg" => L('Body_Content_Cannot_Empty')]);
        exit;
    }
    $input_text = mb_substr($post_html, 0, 6000, 'utf-8');
    $prompt = str_replace('{{input_text}}',$input_text,$qs_ai_set['prompt_format']);
    [$ok, $htmlResult] = callAiApi($prompt, 45);
    if (!$ok) {
        if ($htmlResult === 'no_config') {
            echo json_encode(["code" => 400, "msg" => L('AI_API_List_Not_Configured')]);
        } else {
            echo json_encode(["code" => 500, "msg" => L('All_API_Call_Failed')]);
        }
        exit;
    }
    echo json_encode([
        "code" => 200,
        "data" => ["html" => $htmlResult]
    ]);
    exit;
}

if ($action === 'save_ai_prompt') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    // 页面上没有对应输入框时（例如小说模式没有翻译Prompt框），沿用已有值，避免被清空
    $new_prompt_summary = array_key_exists('prompt_summary', $_POST) ? $_POST['prompt_summary'] : ($qs_ai_set['prompt_summary'] ?? '');
    $new_prompt_format  = array_key_exists('prompt_format', $_POST) ? $_POST['prompt_format'] : ($qs_ai_set['prompt_format'] ?? '');
    $new_prompt_translate = array_key_exists('prompt_translate', $_POST) ? $_POST['prompt_translate'] : ($qs_ai_set['prompt_translate'] ?? '');

    $aiPromptFile = $datapaths . $set_ai_prompt;

    //组装完整php配置内容，直接完整覆盖写入，永远只存在一份$qs_ai_set，不再正则追加
    $saveData = [
        'prompt_summary'   => $new_prompt_summary,
        'prompt_format'    => $new_prompt_format,
        'prompt_translate' => $new_prompt_translate
    ];
    $exportStr = var_export($saveData, true);
    //标准php返回数组格式
    $writeContent = "<?php\n\$qs_ai_set = {$exportStr};\n?>";

    //file_put_contents 文件不存在自动创建；FILE_USE_INCLUDE_PATH不使用，直接真实路径
    $writeOk = file_put_contents($aiPromptFile, $writeContent);

    if ($writeOk !== false) {
        //写完立刻重新加载进当前php内存，保证本页面后续渲染立刻生效
        $qs_ai_set = $saveData;
        echo json_encode(['code'=>200,'msg'=>L('Save_Success')]);
    } else {
        echo json_encode(['code'=>500,'msg'=>L('Write_Config_Failed_Permission')]);
    }
    exit;
}

if ($action === 'ai_trans_content') {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    if (!$post_translate_on) {
        echo json_encode(["code" => 403, "msg" => L('Post_Translate_Disabled')]);
        exit;
    }
    $post_html = $_POST['post_html'] ?? '';
    $target_lang = trim($_POST['target_lang'] ?? '');
    $post_html = trim($post_html);
    //标题、关键词也一起翻（可选传参，空则跳过，省调用）
    $src_title = trim((string) ($_POST['post_title'] ?? ''));
    $src_tags  = trim((string) ($_POST['post_tags'] ?? ''));
    if (empty($post_html)) {
        echo json_encode(["code" => 400, "msg" => L('Body_Content_Cannot_Empty')]);
        exit;
    }
    if (empty($target_lang)) {
        echo json_encode(["code" => 400, "msg" => L('Please_Select_Target_Lang')]);
        exit;
    }

    // 彻底清除全部HTML标签，纯净原文文本，不再做任何字符截断限制
    $cleanText = strip_tags($post_html);
    $input_text = $cleanText;

    // 使用可编辑的翻译Prompt模板；为空时回退到内置默认模板
    $transTpl = trim($qs_ai_set['prompt_translate'] ?? '');
    if ($transTpl === '') {
        $transTpl = $qs_ai_trans_prompt_default;
    }
    //模板里若删掉了 {{input_text}}，自动补上，避免正文丢失
    if (strpos($transTpl, '{{input_text}}') === false) {
        $transTpl .= "\n\n待翻译：\n{{input_text}}";
    }

    //目标语言是中文时，"禁止保留任何中文"这条会和目标冲突，单独纠正一下
    $zh_target = (mb_stripos($target_lang, '中文') !== false) || (stripos($target_lang, 'chinese') !== false);
    $zh_note = $zh_target
        ? "【本文目标语言为中文】下面模板里“禁止保留任何中文”的要求本次不适用（目标语言本身就是中文）；只输出简体中文译文，不要输出其它语言。\n\n"
        : '';

    $prompt = $zh_note . str_replace(['{{target_lang}}', '{{input_text}}'], [$target_lang, $input_text], $transTpl);

    [$ok, $transResult] = callAiApi($prompt, 50);
    if (!$ok) {
        if ($transResult === 'no_config') {
            echo json_encode(["code" => 400, "msg" => L('AI_API_List_Not_Configured')]);
        } else {
            echo json_encode(["code" => 500, "msg" => L('All_API_Call_Failed')]);
        }
        exit;
    }

    //兜底：删掉译文中意外残留的中文字符（目标语言是中文时不能删）
    if (!$zh_target) {
        $transResult = preg_replace('/[\x{4e00}-\x{9fa5}]+/u','',$transResult);
    }
    $transResult = preg_replace('/^```(?:html)?\s*/i', '', trim($transResult));
    $transResult = preg_replace('/\s*```$/', '', $transResult);

    // ---- 标题翻译（失败不影响正文） ----
    $trans_title = '';
    if ($src_title !== '') {
        $title_prompt = $zh_note . str_replace(['{{target_lang}}', '{{input_text}}'], [$target_lang, $src_title], $transTpl);
        [$ok_t, $title_out] = callAiApi($title_prompt, 30);
        if ($ok_t && is_string($title_out)) {
            if (!$zh_target) {
                $title_out = preg_replace('/[\x{4e00}-\x{9fa5}]+/u', '', $title_out);
            }
            $title_out = preg_replace('/^```(?:html)?\s*/i', '', trim($title_out));
            $title_out = preg_replace('/\s*```$/', '', $title_out);
            $title_out = trim($title_out, " \t\n\r\0\x0B\"'“”‘’");
            $trans_title = trim(preg_replace('/\s+/u', ' ', $title_out));
        }
    }

    // ---- 关键词翻译 ----
    $trans_tags = '';
    if ($src_tags !== '') {
        $tags_prompt = $zh_note . str_replace(['{{target_lang}}', '{{input_text}}'], [$target_lang, $src_tags], $transTpl);
        [$ok_g, $tags_out] = callAiApi($tags_prompt, 30);
        if ($ok_g && is_string($tags_out)) {
            if (!$zh_target) {
                $tags_out = preg_replace('/[\x{4e00}-\x{9fa5}]+/u', '', $tags_out);
            }
            $tags_out = preg_replace('/^```(?:html)?\s*/i', '', trim($tags_out));
            $tags_out = preg_replace('/\s*```$/', '', $tags_out);
            //统一成英文逗号分隔，去掉空项
            $tags_out = str_replace(['，', '、', '；', ';'], ',', $tags_out);
            $parts = array_filter(array_map('trim', explode(',', $tags_out)), 'strlen');
            $trans_tags = implode(',', $parts);
        }
    }

    echo json_encode([
        "code" => 200,
        "data" => [
            "trans_html"  => $transResult,
            "trans_title" => $trans_title,
            "trans_tags"  => $trans_tags
        ]
    ]);
    exit;
}

//=====加载广告列表，用于下拉选择=====
$adsFile = $datapaths . $adconfig;
$adSelectList = [];
if(file_exists($adsFile)){
    $allAds = require $adsFile;
    foreach($allAds as $aid=>$item){
        //只列出启用的广告给编辑器选择
        if($item['enable']){
            $adSelectList[$aid] = $item['name'].' ('.$aid.')';
        }
    }
}

$post_id          = '';
$post_state       = '';
$post_title       = '';
$user             = '';
$bookname         = ''; 
$bookpic          = ''; 
$bookgenres       = ''; 
$post_video       = '';
$post_summary     = '';
$post_content     = '';
$post_down        = '';
$post_down_size   = '';
$post_tags        = array();
//====附属翻译初始化====
$post_title_2nd   = '';
$post_tags_2nd    = array();
$post_content_2nd = '';
//====================
$post_date        = '';
$post_time        = '';
$post_passwd      = '';
$post_can_comment = '';
$error_msg        = '';
$succeed          = false;
if (isset($_POST['save'])) {

    if ($qs_config['sitemode'] == 'novel') {
        if (isset($_FILES['newbookpic']) && $_FILES['newbookpic']['size'] > 0) {
            $_FILES['bookpic'] = $_FILES['newbookpic'];
        } else if (isset($_FILES['bookpic']) && $_FILES['bookpic']['size'] > 0) {
            $_FILES['bookpic'] = $_FILES['bookpic'];
        } else if (isset($_FILES['changebookpic']) && $_FILES['changebookpic']['size'] > 0) {
            $_FILES['bookpic'] = $_FILES['changebookpic'];
        }

        if (isset($_FILES['bookpic'])) {
            if (!is_dir('../upload/bookpic/'))
                mkdir('../upload/bookpic/');

            $targetDir = "../upload/bookpic/";
            $targetFile = $targetDir.basename($_FILES["bookpic"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if (isset($_POST["save"])) {
                $check = getimagesize($_FILES["bookpic"]["tmp_name"]);
                if ($check !== false) {
                    $uploadOk = 1;
                } else {
                    echo L('This_is_not_valid_file');
                    $uploadOk = 0;
                }
            }
            if (file_exists($targetFile)) {
                echo L('Image_replaced_with_same_name_on_server');
                $uploadOk = 1;
            }
            if ($_FILES["bookpic"]["size"] > 1024000) {
                echo L('A9B');
                $uploadOk = 0;
            }
            $allowedFileTypes = array("jpg", "png", "apng", "jpeg", "gif", "webp");

            if (!in_array(strtolower($imageFileType), $allowedFileTypes)) {
                echo L('A9A');
                $uploadOk = 0;
            }
            if ($uploadOk == 0) {
                echo L('Pic_update_Failed');
            } else {
                if (move_uploaded_file($_FILES["bookpic"]["tmp_name"], $targetFile)) {
                    $bookpic = "../upload/bookpic/".$_FILES["bookpic"]["name"];
                } else {
                    echo L('Pic_update_Failed');
                }
            }
        }

        if (isset($_POST['newbookname']) && $_POST['newbookname'] !== '') {
            $_POST['bookname'] = trim($_POST['newbookname']);
        }

        if (isset($_POST['bookname']) && $_POST['bookname'] !== '') {
            $bookname = trim($_POST['bookname']);
        }

        if (isset($_POST['bookpic']) && $_POST['bookpic'] !== '' && !isset($_FILES['bookpic'])) {
            $bookpic = trim($_POST['bookpic']);
        } else if (isset($_POST['newbookpic']) && $_POST['newbookpic'] !== '') {
            $bookpic = trim($_POST['newbookpic']);
        }

        $bookgenres = isset($_POST['bookgenres']) ? trim($_POST['bookgenres']) : '';

    }
if (!empty($_SESSION['Qsis_user_name'])) {
    // 先用isset判断键存在，不存在直接取会话值
    $user = isset($_POST['user']) && $_POST['user'] === $_SESSION['Qsis_user_name']  
        ? $_POST['user']
        : $_SESSION['Qsis_user_name']  ;
} elseif (!empty($_SESSION['Qsis_adminname'])) {
    $user = isset($_POST['user']) ? trim($_POST['user']) : $sp_config['admin_displayname'];
}

    $post_id = $_POST['id'];
    $post_state = $_POST['state'];
    $post_title = trim($_POST['title']);

    if ($qs_config['sitemode'] == 'video' || $qs_config['sitemode'] == 'game') {
        $post_video = $_POST['video'];
    }

    if ($qs_config['sitemode'] == 'article' || $qs_config['sitemode'] == 'novel') {
        $post_summary = stripslashes(trim($_POST['summary']));
        $post_summary = str_replace(['<p><br></p>'], '', $post_summary);

    }

$post_tags = preg_split('/[,#\x{ff0c} ]/u', trim((string)($_POST['tags'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);
//====新增：接收附属翻译POST数据====
$post_title_2nd    = trim((string)($_POST['title_2nd'] ?? ''));
$post_content_2nd  = stripslashes(trim((string)($_POST['content_2nd'] ?? '')));
$post_tags_2nd = preg_split('/[,#\x{ff0c} ]/u', trim((string)($_POST['tags_2nd'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);
//================================

    if (isset($_POST['post_passwd']) && $_POST['post_passwd'] == '' && isset($_POST['old_passwd']) && $_POST['old_passwd'] != '') {
        $post_passwd = $_POST['old_passwd'];
        $post_content = stripslashes(trim($_POST['content']));
        if ($qs_config['sitemode'] == 'download' || $qs_config['sitemode'] == 'game') {
            $post_down = stripslashes(trim($_POST['download']));
        }
    } else if ($_POST['post_passwd'] != '' && $_POST['old_passwd'] != '') {
        $post_passwd = md5($_POST['post_passwd']);
        $post_content = stripslashes(trim($_POST['content']));
        if ($qs_config['sitemode'] == 'download' || $qs_config['sitemode'] == 'game') {
            $post_down = isset($_POST['download']) ? stripslashes(trim($_POST['download'])) : '';
        }
    } else if ($_POST['post_passwd'] != '' && $_POST['old_passwd'] == '') {
        $post_passwd = md5($_POST['post_passwd']);
        $post_content = base64_encode(stripslashes(trim($_POST['content'])));
        if ($qs_config['sitemode'] == 'download') {
            $post_down = base64_encode(stripslashes(trim($_POST['download'])));
        }
    } else {
        $post_content = stripslashes(trim($_POST['content']));
        if ($qs_config['sitemode'] == 'download' || $qs_config['sitemode'] == 'game') {
            $post_down_size = isset($_POST['download_size']) ? stripslashes(trim($_POST['download_size'])) : '';
            $post_down = isset($_POST['download']) ? stripslashes(trim($_POST['download'])) : '';
        }
    }

    $post_date = date("Y-m-d");
    $post_time = date("H:i:s");
    $post_can_comment = $_POST['can_comment'];
    if ($_POST['year'] != '')
        $post_date = substr_replace($post_date, $_POST['year'], 0, 4);
    if ($_POST['month'] != '')
        $post_date = substr_replace($post_date, $_POST['month'], 5, 2);
    if ($_POST['day'] != '')
        $post_date = substr_replace($post_date, $_POST['day'], 8, 2);
    if ($_POST['hourse'] != '')
        $post_time = substr_replace($post_time, $_POST['hourse'], 0, 2);
    if ($_POST['minute'] != '')
        $post_time = substr_replace($post_time, $_POST['minute'], 3, 2);
    if ($_POST['second'] != '')
        $post_time = substr_replace($post_time, $_POST['second'], 6, 2);
    $post_tags_count = count($post_tags);
    for ($i = 0; $i < $post_tags_count; $i++) {
        $trim = trim($post_tags[$i]);
        if ($trim == '') {
            unset($post_tags[$i]);
        } else {
            $post_tags[$i] = $trim;
        }
    }
    reset($post_tags);
    if ($post_title == '') {
        $error_msg = L('Article_title_cannot_empty');
    } elseif (!empty($blocked_hits = qs_keyword_check($post_title, $post_content))) {
        $error_msg = sprintf(L('Article_Has_Blocked_Keywords'), implode('、', array_map(fn($c, $w) => $w . '（' . $c . '）', $blocked_hits, array_keys($blocked_hits))));
    } else {
        if ($post_id == '') {
            $file_names = shorturl($post_title);
            foreach($file_names as $file_name) {
            	$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
                $file_path = $realPostRoot.'data/'.$file_name.'.php';
                if (!is_file($file_path)) {
                    $post_id = $file_name;
                    break;
                }
            }
        } else {
        	
        	$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
        	  $file_path = $realPostRoot.'data/'.$post_id.'.php';
            $file_content = file_get_contents($file_path);
            $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
            $file_content = preg_replace('/\?>$/', '', $file_content);
            $data = unserialize($file_content);
            $post_old_state = $data['state'];
            if ($post_old_state != $post_state) {
                $index_file = $realPostRoot.'index/'.$post_old_state.'.php';
                require $index_file;
                unset($qs_posts[$post_id]);
                file_put_contents($index_file,
                    "<?php\n\$qs_posts=".var_export($qs_posts, true).
                    "\n?>"
                );
            }
        }

$data = array(
    'id' => $post_id,
    'state' => $post_state,
    'title' => $post_title,
    'title_2nd' => $post_title_2nd,
    'tags' => $post_tags,
    'tags_2nd' => $post_tags_2nd,
    'date' => $post_date,
    'time' => $post_time,
    'user' => $user,
    'can_comment' => $post_can_comment,
);

        if ($qs_config['sitemode'] == "novel") {
            $data['bookname'] = $bookname;
            $data['bookgenres'] = $bookgenres;
            $data['bookpic'] = $bookpic;
        }
        $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
        $index_file = $realPostRoot.'index/'.$post_state.'.php';
        require $index_file;
        $qs_posts[$post_id] = $data;
        uasort($qs_posts, "post_sort");
        if ($qs_config['sitemode'] == "novel") {
            foreach($qs_posts as $key => $post) {
                if ($post['bookname'] === $bookname) {
                    $qs_posts[$key]['bookpic'] = $bookpic;
                    $qs_posts[$key]['user'] = $user;
                }
            }
        }
        file_put_contents($index_file,
            "<?php\n\$qs_posts=".var_export($qs_posts, true)."\n?>");

        if ($qs_config['sitemode'] == "novel") {
            $data['bookname'] = $bookname;
            $data['bookgenres'] = $bookgenres;
            $data['bookpic'] = $bookpic;
        }
        $data['user'] = $user;
        $data['video'] = $post_video;
        $data['summary'] = $post_summary;
        $data['content'] = $post_content;
        $data['content_2nd'] = $post_content_2nd;
        $data['download_size'] = $post_down_size;
        $data['download'] = $post_down;
        $data['post_passwd'] = $post_passwd;
        $data = "<?php //".serialize($data). "?>";
        file_put_contents($file_path, $data);
        $succeed = true;
        if ($succeed && file_exists(__DIR__ . '/qs_feeds.php')) {
            require_once __DIR__ . '/qs_feeds.php';
            qs_feeds_regenerate();
        }
      if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'post_save', L('Log_Article_Manage'), sprintf(L('Log_Article_Save_ID'), $post_id));
      }
    }
} else if (isset($_POST['base64decode'])) {
	  $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
    $file_path = $realPostRoot .'data/'.$_POST['id'].'.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);

        $data = unserialize($file_content);
        $post_id = $data['id'];
        $post_state = $data['state'];
        $user = $data['user'];
        $post_title = $data['title'];
        $post_video = $data['video'];
        $post_summary = $data['summary'];
        $post_passwd = $data['post_passwd'];

if ($qs_config['sitemode'] == "novel") {
    $bookname = $data['bookname'] ?? '';
    $bookgenres = $data['bookgenres'] ?? '';
    $bookpic = $data['bookpic'] ?? '';
}
        $code_passwd = md5($_POST['post_passwd']);

        if ($code_passwd == $post_passwd) {
            $post_content = base64_decode($_POST['content']);
        } else {
            $post_content = $data['content'];
        }
        $post_down_size = $data['download_size'];
        $post_down = $data['download'];
        $post_tags = $data['tags'];
        $post_date = $data['date'];
        $post_time = $data['time'];
    }
    if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
        write_operate_log(qs_get_current_admin_name(), 'post_base64_decode', L('Log_Article_Manage'), L('Log_Base64_Decode'));
    }
} else if (isset($_POST['base64encode'])) {
$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
    $file_path = $realPostRoot .'data/'.$_POST['id'].
    '.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $data = unserialize($file_content);
        $post_id = $data['id'];
        $post_state = $data['state'];
        $post_title = $data['title'];
        $user = $data['user'];
        if ($qs_config['sitemode'] == "novel") {
            $bookname = $data['bookname'];
            $bookgenres = $data['bookgenres'];
            $bookpic = $data['bookpic'];
        }

        $post_video = $data['video'];
        $post_summary = $data['summary'];
        $post_content = base64_encode($_POST['content']);
        $post_down = $data['download'];
        $post_passwd = $data['post_passwd'];
        $code_passwd = md5($_POST['post_passwd']);
        $post_tags = $data['tags'];
        $post_date = $data['date'];
        $post_time = $data['time'];
        $post_can_comment = isset($data['can_comment']) ? $data['can_comment'] : '1';
    }
if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
        write_operate_log(qs_get_current_admin_name(), 'post_base64_encode', L('Log_Article_Manage'), L('Log_Base64_Encode'));
    }
} else if (isset($_GET['id'])) {
    if ($qs_config['sitemode'] == "novel") {
        $bookpic = $_GET['bookpic'] ?? '';
        $bookname = $_GET['bookname'] ?? '';
        $bookgenres = $_GET['bookgenres'] ?? '';
    }
    
$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
    $file_path = $realPostRoot .'data/'.$_GET['id'].'.php';
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);

        $data = unserialize($file_content);
        $post_id = $data['id'];
        $user = isset($data['user']) ? $data['user'] : '';
        $post_state = isset($data['state']) ? $data['state'] : '';
        $post_title = isset($data['title']) ? $data['title'] : '';
        //====新增附属翻译字段回显读取====
        $post_title_2nd = isset($data['title_2nd']) ? $data['title_2nd'] : '';
        $post_video = isset($data['video']) ? $data['video'] : '';
        $post_summary = isset($data['summary']) ? $data['summary'] : '';
        $post_content = $data['content'];
        $post_content_2nd = isset($data['content_2nd']) ? $data['content_2nd'] : '';
        $post_down_size = isset($data['download_size']) ? $data['download_size'] : '';
        $post_down = isset($data['download']) ? $data['download'] : '';
        $post_passwd = $data['post_passwd'] ?? '';
        $post_tags = $data['tags'];
        $post_tags_2nd  = isset($data['tags_2nd']) ? $data['tags_2nd'] : [];
        $post_date = $data['date'];
        $post_time = $data['time'];
        $post_can_comment = isset($data['can_comment']) ? $data['can_comment'] : '1';
        if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
            write_operate_log(qs_get_current_admin_name(), 'edit_article', L('Log_Article_Manage'), sprintf(L('Log_Open_Article_Edit_ID'), $post_id));
        }
    } else {
        Header("Location:");
    }

}
?>

<?php 
$site_name = $qs_config['site_name'];
if ($post_id == '') {
    $tempid = L('New_Article');
    $autsaveid = $site_name.'-'.$tempid;
} else {
    $tempid = $post_id;
    $autsaveid = $site_name.'-'.$tempid;
}
?>
  
<form action=""  name="<?php echo $tempid; ?>" id="<?php echo $autsaveid; ?>" method="post" enctype="multipart/form-data">
<script src="marked.min.js"></script>
<script src="markdown-support.js"></script>
<script language="javascript" src="xheditor/jquery/jquery-1.11.2.min.js"></script> 
<script language="javascript" src="sisyphus.min.js"></script>
  <div class="admin_page_name">
  	<?php 
  	$sitemode = $qs_config['sitemode']; 
  	$url_ends = $qs_config['url_ends']; 
  	?>
<?php if ($post_id == '') echo L('Write_Article'); else echo L('Edit_Article'); ?> 
<input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>   
<?php if ($post_id != ''): ?>
    <a href=../?<?php echo $sitemode; ?>/<?php echo $tempid.$url_ends; ?> target=_blank><?php echo L('View_Article'); ?></a>
    <?php echo $tempid; ?> 
    <a href=post.php?delete=<?php echo $tempid; ?>><?php echo L('Delete'); ?></a>
<?php endif; ?>

  <div style="float:right"><?= L('System_Mode'); ?>：
 <?php 
$mode = ($qs_config['sitemode'] == "game") ? L('Game_System') : 
        (($qs_config['sitemode'] == "business") ? L('Business_System') : 
        (($qs_config['sitemode'] == "post") ? L('Blog_System') : 
        (($qs_config['sitemode'] == "video") ? L('Video_System') : 
        (($qs_config['sitemode'] == "novel") ? L('Novel_System') : 
        (($qs_config['sitemode'] == "article") ? L('Literature_System') : 
        (($qs_config['sitemode'] == "download")? L('Download_System') : ""))))));
        
        echo $mode;
?>
 </div>
  </div>

<?php if (!empty($error_msg)): ?>
  <div style="margin:12px 0;padding:12px 14px;border:1px solid #dc3545;border-radius:4px;background:#fff5f5;color:#dc3545;font-weight:bold;">
    <?= htmlspecialchars($error_msg); ?>
  </div>
<?php endif; ?>

<?php
$aiApiList = $qs_config['ai_api_list'] ?? [];
?>
<div style="margin:8px 0 14px 0;padding:8px 12px;background:#f7f9fa;border:1px solid #e2e5e9;border-radius:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <label><strong>🤖 <?= L('Global_AI_Channel'); ?></strong></label>
    <select id="globalAiSelector" style="padding:4px 6px;min-width:260px;">
        <option value=""><?= L('Auto_Poll_Global_Config'); ?></option>
        <?php foreach($aiApiList as $idx=>$cfg):?>
        <?php $disp = htmlspecialchars(trim($cfg['model']??'').' | '.trim($cfg['site']??''));?>
        <option value="<?php echo $idx; ?>"><?php echo $disp; ?></option>
        <?php endforeach;?>
    </select>
    <span style="font-size:12px;color:#666;"><?= L('AI_Channel_Selector_Desc'); ?></span>
</div>





    <?php if ($succeed) { ?>
  <?php if ($post_state == 'publish') { ?>
  <div class="updated"><?= L('Published'); ?></div>
  <?php } else { ?>
  <div class="updated"><?= L('Article_has_been_Drafts'); ?> <a href="post.php?state=draft"><?= L('Drafts_folder'); ?></a></div>
  <?php } ?>
  <?php } ?>
  
  <div style="margin-bottom:20px;">
    <input name="title" id="title" placeholder="<?= L('Enter_Article_Title_Here'); ?>" type="text" class="edit_textbox" value="<?php
    if (!$post_title == "") {
      echo htmlspecialchars($post_title);
    }
    ?>"/>
  </div>
  
   <?php
   $sitemode = $qs_config['sitemode'];
if ($sitemode == "video" || $sitemode == "game") {
?>
  <div style="margin-bottom:20px;">
<?php $videoVal = !empty($post_video) ? $post_video : L('Video_url_in_here'); ?>

<input 
    name="video"
    id="video"
    type="text"
    class="edit_textbox"
    value="<?= htmlspecialchars($videoVal) ?>"
    style="color:#888;"
    onfocus="empty_textbox_focus(this)"
    onblur="empty_textbox_blur(this)"
/>
      </div>
<?php
}
?>
<?php
   $sitemode = $qs_config['sitemode'];
if (in_array($sitemode, ['blog','business','video','article','game','download'], true)) {
?>

<?= L('Outline_Abstract_Summary'); ?>

<button type="button" onclick="openImageLibrary(event, 'summary')"><?= L('Insert_Server_Image'); ?></button>
<button type="button" id="btnGenSummary" onclick="aiGenSummaryClick()">🤖 <?= L('AI_Generate_Summary'); ?></button>
<span id="aiGenTip" style="margin-left:8px;color:#666;"></span>
<button type="button" id="btnEditPromptSummary"><?= L('Edit_Prompt'); ?></button>
<!-- 摘要Prompt编辑区 默认隐藏 -->
<div id="wrapPromptSummary" style="display:none;margin:8px 0;padding:10px;border:1px solid #ccc;">
    <p><?= L('Prompt_Placeholder_Desc_Summary'); ?></p>
    <textarea id="taPromptSummary" rows="8" style="width:100%;"><?=htmlspecialchars($qs_ai_set['prompt_summary'])?></textarea>
    <div style="margin-top:6px;">
        <button type="button" id="btnSavePromptSummary"><?= L('Save_Prompt'); ?></button>
        <span id="tipPromptSummary" style="margin-left:8px;"></span>
    </div>
</div>

<?php
if (isset($qs_config['edittools'])){
$edittools = $qs_config['edittools'];
} else {
$edittools = "xheditor";
}

if ($edittools == "xheditor" ) {
?>
	<script language="javascript" src="xheditor/xheditor.js"></script>
    <script language="javascript" src="xheditor/xheditor_lang/zh-cn.js"></script>
  <div style="margin-bottom:20px;">
    <script language="javascript">
  $(pageInit);
  function pageInit(){
  $('#summary').xheditor({tools:'full',internalScript:true,inlineScript:true,cleanPaste:2,urlType:'rel',html5Upload:false,upBtnText:"<?= L('Upload'); ?>",upMultiple:99,upLinkUrl:'supldaa.php',upLinkExt:'zip,rar,txt',upImgUrl:'supldaa.php',upImgExt:'jpg,jpeg,gif,png',upMediaUrl:'supldaa.php',upMediaExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',upVideoUrl:'supldaa.php',upVideoExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',emotPath:'xheditor/xheditor_emot/',onUpload:insertUpload});
    }
  function insertUpload(arrMsg){
  var i,msg;
  for(i=0;i<arrMsg.length;i++){
  msg=arrMsg[i];
  $("#uploadList").append('<option value="'+msg.id+'">'+msg.localname+'</option>');
    }
  }
 </script>
 <textarea id="summary" name="summary" style="width:100%; height:150px; color:#888;" ><?php echo htmlspecialchars($post_summary); ?></textarea>

 <?php
} elseif ($edittools == "qseditor") {
?>
<textarea id="summary" name="summary" style="display: none;"><?php echo htmlspecialchars($post_summary); ?></textarea>
<link href="./qseditor/qseditor.css" rel="stylesheet">
<script src="qseditor/qseditor.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    QSEditor.init('summary', { placeholder: '请输入摘要...' });
});
</script>
 <?php
} else {
?>
<textarea id="summary" name="summary" style="display: none;"><?php echo htmlspecialchars($post_summary); ?></textarea>
<div id="summaryQuill"></div>
<link href="./quill/quill.snow.css" rel="stylesheet">
<script src="quill/quill.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        var editorsummary = document.getElementById('summary').value;
        var summaryQuill = document.getElementById('summaryQuill');
        var quill = new Quill(summaryQuill, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['undo', 'redo'],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    ['link', 'image', 'video'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        quill.clipboard.dangerouslyPasteHTML(editorsummary);

        quill.on('text-change', function(){
            $('#summary').val(quill.root.innerHTML);
        });
        window.quill_summary = quill; 

    });
</script>
 <?php
} 
}
?>


<div style="margin-bottom:20px;" class="Post-div">
<div class="Post-div-left">
	

<div style="margin-bottom:10px;">
<?= L('Main_Body'); ?>:
    <button type="button" id="btnAiFormat" onclick="aiFormatContentClick()">🤖 <?= L('AI_Format_Content'); ?></button>
    <button type="button" id="btnZhConvert" onclick="convertZhTwCn()">🔤 <?= L('Toggle_Traditional_Simplified'); ?></button>
    <span id="aiFormatTip" style="margin-left:8px;color:#666;"></span>
    <span id="zhConvertTip" style="margin-left:8px;color:#666;"></span>
    <button type="button" id="btnEditPromptFormat"><?= L('Edit_Prompt'); ?></button>
    <!-- 排版Prompt编辑区 默认隐藏 -->
<div id="wrapPromptFormat" style="display:none;margin:8px 0;padding:10px;border:1px solid #ccc;">
    <p><?= L('Prompt_Placeholder_Desc_Format'); ?></p>
    <textarea id="taPromptFormat" rows="8" style="width:100%;"><?=htmlspecialchars($qs_ai_set['prompt_format'])?></textarea>
    <div style="margin-top:6px;">
        <button type="button" id="btnSavePromptFormat"><?= L('Save_Prompt'); ?></button>
        <span id="tipPromptFormat" style="margin-left:8px;"></span>
    </div>
</div>
</div>
<?php
if (isset($qs_config['edittools'])){
$edittools = $qs_config['edittools'];
} else {
$edittools = "xheditor";
}
if ($edittools == "xheditor" ) {
?>
<script language="javascript" src="xheditor/xheditor.js"></script>
<script language="javascript" src="xheditor/xheditor_lang/zh-cn.js"></script>
<script language="javascript">
$(document).ready(function(){
    $('#contentbox').xheditor({
        tools:'full',
        internalScript:true,
        inlineScript:true,
        cleanPaste:2,
        onPaste:function(h){ return (window.qsMarkdown && qsMarkdown.processPastedHtml) ? qsMarkdown.processPastedHtml(h) : h; },
        urlType:'rel',
        html5Upload:false,
        upBtnText:"<?= L('Upload'); ?>",
        upMultiple:99,
        upLinkUrl:'supldaa.php',
        upLinkExt:'zip,rar,txt',
        upImgUrl:'supldaa.php',
        upImgExt:'jpg,jpeg,gif,png',
        upMediaUrl:'supldaa.php',
        upMediaExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',
        upVideoUrl:'supldaa.php',
        upVideoExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',
        emotPath:'xheditor/xheditor_emot/',
        onUpload:insertUpload
    });
    updateWordCount(); 
    setInterval(updateWordCount, 1000);
});

function insertUpload(arrMsg){
    var i,msg;
    for(i=0;i<arrMsg.length;i++){
        msg=arrMsg[i];
        $("#uploadList").append('<option value="'+msg.id+'">'+msg.localname+'</option>');
    }
}

function updateWordCount(){
    var content = $('#contentbox').val();
    var wordCount = content.length;
    $('#wordCount').text(wordCount + L('Words_number'));
}
</script>

<textarea id="contentbox" name="content" style="width: 100%; height: 450px;"><?php echo htmlspecialchars($post_content); ?></textarea>
<span id="wordCount"></span>

   <?php
} elseif ($edittools == "qseditor") {
?>
<textarea id="contentbox" name="content" style="display: none;"><?php echo htmlspecialchars($post_content); ?></textarea>
<link href="./qseditor/qseditor.css" rel="stylesheet">
<script src="qseditor/qseditor.js"></script>
<div style="display:flex; align-items:center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
    <?= L('Words_number'); ?>：
    <div id="word-count" style="margin-right: 10px;"></div>
    <label><input type="checkbox" id="pasteImageMode" checked> <?= L('Paste_Image_Auto_Upload'); ?></label>
</div>
<script>
window.addEventListener('DOMContentLoaded', function () {
    var ed = QSEditor.init('contentbox', { placeholder: '请输入正文...' });
    window.qsEditorMain = ed;
    // 字数统计同步
    if (ed) {
        var origSync = ed.sync;
        ed.sync = function () {
            origSync();
            var wc = document.getElementById('word-count');
            if (wc) {
                var text = ed.body.innerText || '';
                wc.textContent = text.trim().length + ' ' + "<?= L('Word_Char'); ?>";
            }
        };
        ed.sync();
    }
});
</script>
 <?php
} elseif ($edittools == "Quill" )  {
?>
<link href="./quill/quill.snow.css" rel="stylesheet">
 <script src="quill/quill.js"></script>
<textarea id="contentbox" name="content" style="display: none;"><?php echo htmlspecialchars($post_content); ?></textarea>
<div id="editor" name="content"></div>
<div style="display:flex; align-items:center; flex-wrap: wrap; gap: 10px;">
    <?= L('Words_number'); ?>：
    <div id="word-count" style="margin-right: 10px;">0</div>
    <div id="file-Input"></div>
<button type="button" onclick="openImageLibrary(event, 'quillMain')"><?= L('Insert_Server_Image'); ?></button>
<label style="margin-left:10px;">
    <input type="checkbox" id="pasteImageMode" checked>
    <?= L('Paste_Image_Auto_Upload'); ?>
</label>
</div>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        var editorContent = document.getElementById('contentbox').value;
        var editor = document.getElementById('editor');
        var quill = new Quill(editor, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['undo', 'redo'],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    ['link', 'image', 'video'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        if (window.qsMarkdown) {
            qsMarkdown.enableQuill(quill);
        }
        if (window.qsMarkdown && qsMarkdown.isLikely(editorContent)) {
            editorContent = qsMarkdown.toHtml(editorContent);
        }

        if (editorContent.trim() !== '') {
            quill.clipboard.dangerouslyPasteHTML(editorContent);
        }

        <?php echo qs_keyword_highlight_script($blocked_hits, 'quill'); ?>



        quill.root.addEventListener('paste', function (e) {
            const isUploadMode = document.getElementById('pasteImageMode')?.checked;
            if (!isUploadMode) return;

            const clipboardData = e.clipboardData || window.clipboardData;
            const items = clipboardData?.items;
            if (!items) return;

            let hasImage = false;
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    hasImage = true;
                    break;
                }
            }

            if (hasImage) {
                e.preventDefault();
                e.stopPropagation();
            }

            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    const file = item.getAsFile();
                    if (file) {
                        uploadImageAndInsert(file);
                        break;
                    }
                }
            }
        }, true);

        // 【关键修复】统一变量名，让上传函数能找到编辑器
        window.quillMain = quill;

        function uploadImageAndInsert(file) {
            // 这里的判断现在能正确执行了
            if (!file || !window.quillMain) return;
            const formData = new FormData();
            formData.append('filedata', file);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'supldaa.php', true);
            xhr.onload = function () {
                if (xhr.status !== 200) return;
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.err) { alert("<?= L('Upload_Failed_With_Error'); ?>" + res.err); return; }
                    const url = res.msg.url;
                    // 【修复】使用正确的编辑器实例
                    const range = window.quillMain.getSelection();
                    window.quillMain.insertEmbed(range ? range.index : window.quillMain.getLength(), 'image', url);
                } catch (e) {
                    console.error('上传错误:', e);
                }
            };
            xhr.onerror = function() {
                alert("<?= L('Upload_Request_Failed_Check_Server'); ?>");
            };
            xhr.send(formData);
        }

        quill.on('text-change', function () {
            setTimeout(() => {
                const text = quill.getText();
                const wordCount = text.trim().length - 1;
                document.getElementById('word-count').textContent = wordCount + ' ' + "<?= L('Word_Char'); ?>";
            }, 0);
            document.getElementById('contentbox').value = quill.root.innerHTML;
        });

        // 移除原来的错误赋值
        // window.quill_quillMain = quill;
    });
</script>
 <?php
} 
?>






<?php if ($qs_config['sitemode'] !== "novel" && $post_translate_on): ?>
<fieldset style="margin-top:24px;padding:14px;border:1px solid #bbbbbb;border-radius:4px;">
    <legend>🌐 <?= L('Aux_Translation_Versions'); ?>
        <select id="transTargetLang">
            <?php if (!$qs_ui_is_zh): ?>
            <option value="简体中文"><?= L('Lang_Chinese') ?></option>
            <?php endif; ?>
            <option value="<?= L('Lang_English'); ?>"><?= L('Lang_English'); ?></option>
            <option value="<?= L('Lang_Traditional_Chinese'); ?>"><?= L('Lang_Traditional_Chinese'); ?></option>
            <option value="<?= L('Lang_Japanese'); ?>"><?= L('Lang_Japanese'); ?></option>
            <option value="<?= L('Lang_Korean'); ?>"><?= L('Lang_Korean'); ?></option>
            <option value="<?= L('Lang_French'); ?>"><?= L('Lang_French'); ?></option>
            <option value="<?= L('Lang_German'); ?>"><?= L('Lang_German'); ?></option>
        </select>
        <button type="button" id="btnAiTranslate">🤖 <?= L('AI_Translate_Main_Content'); ?></button>
        <span id="aiTransTip" style="margin-left:8px;color:#666;"></span>
    </legend>
    <!-- 翻译Prompt编辑区 默认隐藏 -->
    <div style="margin:8px 0;">
        <button type="button" id="btnEditPromptTranslate"><?= L('Edit_Prompt'); ?></button>
        <div id="wrapPromptTranslate" style="display:none;margin:8px 0;padding:10px;border:1px solid #ccc;">
            <p>{{target_lang}} = 目标语言，{{input_text}} = 待翻译正文</p>
            <textarea id="taPromptTranslate" rows="8" style="width:100%;"><?=htmlspecialchars($qs_ai_set['prompt_translate'] !== '' ? $qs_ai_set['prompt_translate'] : $qs_ai_trans_prompt_default)?></textarea>
            <div style="margin-top:6px;">
                <button type="button" id="btnSavePromptTranslate"><?= L('Save_Prompt'); ?></button>
                <span id="tipPromptTranslate" style="margin-left:8px;"></span>
            </div>
        </div>
    </div>
    <div style="margin:10px 0;">
        <label><?= L('Title'); ?></label><br>
        <input type="text" name="title_2nd" style="width:100%;padding:6px;" value="<?=htmlspecialchars($post_title_2nd) ?>">
    </div>
    <div style="margin:10px 0;">
        <label><?= L('Tags_Comma_Separated'); ?></label><br>
        <input  type="text" name="tags_2nd" id="tags_2nd"  autocomplete="tags" placeholder="<?= L('Article_Tags_Keywords'); ?>" value="<?php echo htmlspecialchars(implode(',', $post_tags_2nd)); ?>" class="edit_textbox" /></input>
    </div>
    <div style="margin:10px 0;">
        <label><?= L('Translation'); ?></label><br>
        <textarea name="content_2nd" id="taContent2nd" rows="16" style="width:100%;padding:6px;font-family:monospace;"><?=htmlspecialchars($post_content_2nd) ?></textarea>
    </div>
</fieldset>
<?php endif; ?>

</div>



<?php
$sitemode = $qs_config['sitemode'];
if ($sitemode == "novel") {
?>
<div class="Post-div-right">
<div>
    <?= L('Author');?>:
    <?php if ($_SESSION['admin_level'] == "0") {  ?>
 <input type="text" name="user" value="<?php
        if (!empty($user)) {
            echo htmlspecialchars($user, ENT_QUOTES);
        } elseif (!empty($_SESSION['editor']) && !empty($_SESSION['Qsis_user_name']  )) {
            echo htmlspecialchars($_SESSION['Qsis_user_name']  , ENT_QUOTES);
        }
    ?>">
    <?php
     } else {
        if (!empty($user)) {
            echo htmlspecialchars($user, ENT_QUOTES);
        } elseif (!empty($_SESSION['editor']) && !empty($_SESSION['Qsis_user_name']  )) {
            echo htmlspecialchars($_SESSION['Qsis_user_name']  , ENT_QUOTES);
        }
    ?>
    <input type="text" name="user" hidden value="<?php
        if (!empty($user)) {
            echo htmlspecialchars($user, ENT_QUOTES);
        } elseif (!empty($_SESSION['editor']) && !empty($_SESSION['Qsis_user_name']  )) {
            echo htmlspecialchars($_SESSION['Qsis_user_name']  , ENT_QUOTES);
        }
    ?>">
  <?php  } ?>
    <hr>
</div>

<?php

$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
require_once $realPostRoot .'index/publish.php';  	

$allEmpty = true;
$seenBooknames = array(); 
$bookpic = isset($_GET['bookpic']) ? urldecode($_GET['bookpic']) : '';
$bookname = isset($_GET['bookname']) ? $_GET['bookname'] : '';
$bookgenres = isset($_GET['bookgenres']) ? $_GET['bookgenres'] : '';

foreach ($qs_posts as $post) {
    if (!isset($post['bookname']) || $post['bookname'] === "" || in_array($post['bookname'], $seenBooknames)) {
        continue;
    }
    $seenBooknames[] = $post['bookname'];
    $allEmpty = false;
}

if ($allEmpty) {
?>
<?= L('New_Novel_Name'); ?>：
<input name="newbookname" id="newbookname" type="text" class="edit_textbox" value="" style="color: rgb(136, 136, 136);" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)">

<?= L('New_Book_Cover'); ?>：
<input type="file" name="newbookpic" style="width:100%" id="newbookpic">
<img id="new_preview" height="auto" style="width:100%" src="" />
<script>
document.getElementById('newbookpic').addEventListener('change', function() {
  var file = this.files[0];
  if (file) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('new_preview').setAttribute('src', e.target.result);
    };
    reader.readAsDataURL(file);
  }
});
</script>
<?php
} else if (!$allEmpty){
?>
<?= L('Novel_Name'); ?>：
<select name="bookname" id="booknameSelect"  onchange="toggleTextbox(this)">
	<option value=""></option>
  <?php foreach ($seenBooknames as $bookname): ?>
    <?php
        $selected = isset($_GET['bookname']) && $_GET['bookname'] == $bookname ? 'selected' : '';
    ?>
    <option value="<?php echo $bookname; ?>" <?php echo $selected; ?>><?php echo $bookname; ?></option>
  <?php endforeach; ?>
</select>

<img id="preview" height="auto" style="width:100%" src="<?php echo $bookpic; ?>"><br>
<input type="hidden" name="bookpic" id="bookpic"  value="<?php echo $bookpic; ?>">
<div style="margin: 10px 0;">
    <button type="button" onclick="openImageLibrary()" style="padding: 5px 10px; cursor: pointer; background: #f0f0f0; border: 1px solid #ccc;">
        📁 <?= L('Select_From_Server_Image_Library'); ?>
    </button>
    <span id="selectedImagePath" style="margin-left: 10px; color: #666; font-size: 12px;">
        <?php echo $bookpic ? sprintf(L('Current_Bookpic_Name'), basename($bookpic)) : L('No_Image_Selected'); ?>
    </span>
</div>
<div id="changebookpic_div"><?= L('UC_Change_Pic'); ?>：
<input type="file" name="changebookpic"  id="changebookpic" style="width:100%">
</div>
<div id="booknameTextbox" style="display: none;">
<hr>
<?= L('Create_New_Novel'); ?><br>
<?= L('New_Novel_Name'); ?>：
<input name="newbookname" id="bookname" type="text" class="edit_textbox" value="" style="color: rgb(136, 136, 136);" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)">

<?= L('New_Book_Cover'); ?>：
<input type="file" name="newbookpic" style="width:100%">
<img id="new_preview" height="auto" style="width:100%" src="" />
</div>

<script>
window.onload = function() {
    var booknameSelect = document.getElementById('booknameSelect');
    if (booknameSelect.value !== "") {
        booknameSelect.dispatchEvent(new Event('change'));
    }
};
	
document.getElementById('changebookpic').addEventListener('change', function() {
  var file = this.files[0];
  if (file) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('preview').setAttribute('src', e.target.result);
    };
    reader.readAsDataURL(file);
  }
});

function toggleTextbox(select) {
  var booknameTextbox = document.getElementById('booknameTextbox');
  var previewImg = document.getElementById('preview');
  if (select.value === "") {
    booknameTextbox.style.display = 'block';
    changebookpic_div.style.display = 'none';
    previewImg.style.display = 'none';
  } else {
    booknameTextbox.style.display = 'none';
    changebookpic_div.style.display = 'block';
    previewImg.style.display = 'block';
  }
}

var bookPics = {
    <?php foreach ($qs_posts as $post): ?>
        <?php if (isset($post['bookname'])): ?>
            '<?php echo $post['bookname']; ?>': '<?php echo $post['bookpic']; ?>',
        <?php endif; ?>
    <?php endforeach; ?>
};

document.getElementById('booknameSelect').addEventListener('change', function() {
    var selectedBookname = this.value;
    var bookpic = bookPics[selectedBookname];
    var previewImg = document.getElementById('preview');
    var bookpicInput = document.getElementById('bookpic');

    if (bookpic) {
        previewImg.setAttribute('src', bookpic);
        bookpicInput.value = bookpic;
    } else {
        previewImg.setAttribute('src', '');
        bookpicInput.value = '';
    }
});
</script>
<?php } ?>
<div>&nbsp;</div>
<div>
	<?= L('Bookgenres'); ?>:<br>
<select id="BookgenresSelect" name="bookgenres" >
<?php
if (file_exists($datapaths.$qs_config['bookgenreslist'])) {
    $options = file($datapaths.$qs_config['bookgenreslist'], FILE_IGNORE_NEW_LINES);
    foreach ($options as $option) {
    	   $selected = ($option === $bookgenres) ? ' selected' : '';
        echo "<option value=\"$option\"$selected>$option</option>";
    }
}
?>
    </select>
  </div>
</div>
<?php 
}
?>
</div> 

  
  <?php
   $sitemode = $qs_config['sitemode'];
if ($sitemode == "novel") {
	 echo L('Novel_Next_Preview').":";
	 echo '<div style="margin-bottom:20px;">';

if (isset($qs_config['edittools'])){
$edittools = $qs_config['edittools'];
} else {
$edittools = "xheditor";
}
if ($edittools == "xheditor" ) {
?>
    <script language="javascript">
  $(pageInit);
  function pageInit(){
  $('#summary').xheditor({tools:'full',internalScript:true,inlineScript:true,cleanPaste:2,urlType:'rel',html5Upload:false,upBtnText:"<?= L('Upload'); ?>",upMultiple:99,upLinkUrl:'supldaa.php',upLinkExt:'zip,rar,txt',upImgUrl:'supldaa.php',upImgExt:'jpg,jpeg,gif,png',upMediaUrl:'supldaa.php',upMediaExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',upVideoUrl:'supldaa.php',upVideoExt:'wmv,avi,mp4,wma,mov,gcc,mp3,mid,m4a,aac,ape,wav,flac',emotPath:'xheditor/xheditor_emot/',onUpload:insertUpload});
    }
  function insertUpload(arrMsg){
  var i,msg;
  for(i=0;i<arrMsg.length;i++){
  msg=arrMsg[i];
  $("#uploadList").append('<option value="'+msg.id+'">'+msg.localname+'</option>');
    }
  }
 </script>
<textarea id="summary" name="summary" style="width:100%; height:150px; color:#888;" ><?php echo htmlspecialchars($post_summary); ?></textarea>
</div>
<?php
} elseif ($edittools == "qseditor") {
?>
<link href="./qseditor/qseditor.css" rel="stylesheet">
<script src="qseditor/qseditor.js"></script>
<textarea id="summary" name="summary" style="display: none;"><?php echo htmlspecialchars($post_summary); ?></textarea>
<script>
window.addEventListener('DOMContentLoaded', function () {
    QSEditor.init('summary', { placeholder: '请输入摘要...' });
});
</script>
<?php
} elseif ($edittools == "Quill" )  {
?>
<link href="./quill/quill.snow.css" rel="stylesheet">
<script src="quill/quill.js"></script>
<textarea id="summary" name="summary" style="display: none;"><?php echo htmlspecialchars($post_summary); ?></textarea>
<div id="summaryQuill"></div>
<link href="./quill/quill.snow.css" rel="stylesheet">

<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        var editorsummary = document.getElementById('summary').value;
        var summaryQuill = document.getElementById('summaryQuill');
        var quill = new Quill(summaryQuill, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['undo', 'redo'],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    ['link', 'image', 'video'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });
        quill.clipboard.dangerouslyPasteHTML(editorsummary);

        quill.on('text-change', function(){
            $('#summary').val(quill.root.innerHTML);
        });
        window.quill_summary = quill;
    });
</script>
</div>
<?php
}
}
?>

<?php if (($qs_adv_config['ads'] ?? '') === 'open'): ?>
<div style="background:#f7f7f7;padding:12px;border:1px solid #ddd;margin-bottom:15px;">
    <div style="margin-bottom:8px;"><strong><?= L('Ad_Insert_Tool'); ?></strong></div>
    <!-- 手动插入广告区域 -->
    <div style="margin-bottom:10px;">
        <label><?= L('Manually_Select_Ad'); ?></label>
        <select id="manualAdSelect">
            <option value=""><?= L('Please_Select_Ad'); ?></option>
            <?php foreach($adSelectList as $aid=>$name):?>
            <option value="<?php echo htmlspecialchars($aid); ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach;?>
        </select>
        <button type="button" onclick="insertAdManual()"><?= L('Insert_At_Cursor_Position'); ?></button>
    </div>
    <!-- 自动添加区域 -->
    <div>
        <label>
            <input type="checkbox" id="autoAdCheck"> <?= L('Enable_Auto_Segment_Ad'); ?>
        </label>
        <select id="autoAdSelect">
            <option value=""><?= L('Please_Select_Auto_Ad'); ?></option>
            <?php foreach($adSelectList as $aid=>$name):?>
            <option value="<?php echo htmlspecialchars($aid); ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach;?>
        </select>
        <button type="button" onclick="runAutoInsertAd()"><?= L('Execute_Auto_Ad_Insert'); ?></button>
        <div style="font-size:12px;color:#666;margin-top:4px;">
        <?= L('Auto_Ad_Rule_Desc'); ?>
        </div>
    </div>
</div>
<?php endif; ?>


   <?php
if ($sitemode == "download" || $sitemode == "game") {
?>
<div style="margin-bottom:20px;">
<?= L('Post_Download_size'); ?>:<input placeholder="" value="<?php echo $post_down_size; ?>" name="download_size" type="text" class="edit_download_size"/></input>
</div>
<div style="margin-bottom:20px;">
	<?= L('Post_Download'); ?><span class="help-icon" title="<?= L('Download_Links_Comma_Tip'); ?>"></span>:<input placeholder="https://xx.com/a,https://xx.com/b" value="<?php echo htmlspecialchars($post_down); ?>" name="download" type="text" class="edit_download"/></input>
</div>
<?php
}
?>
    <script>
        function addTagToInput(tag) {
            var inputField = document.getElementById('tags');
            var currentValue = inputField.value;
            if (currentValue) {
                currentValue += ',' + tag;
            } else {
                currentValue = tag;
            }
            inputField.value = currentValue;
        }
    </script>
<?php
$tags = preg_split('/[,;\x{ff0c}；]/u', $qs_config['tags_list'], -1, PREG_SPLIT_NO_EMPTY);
    ?>
  <div style="margin-bottom:20px;">
  	    <div><?= L('tag_list'); ?> <span class="help-icon" title="<?= L('Quick_Tags_Setting_Path_Tip'); ?>"></span>:
        <?php foreach ($tags as $tag): ?>
            <button type="button" class="tag-button" onclick="addTagToInput('<?php echo $tag; ?>')"><?php echo $tag; ?></button>
        <?php endforeach; ?>
    </div>
    <input autocomplete="tags" placeholder="<?= L('Article_Tags_Keywords'); ?>" value="<?php echo htmlspecialchars(implode(',', $post_tags)); ?>" name="tags" id="tags" type="text" class="edit_textbox"/></input>
  </div>
  <div class="st-w">
    <div class="st-w-l">
  <?= L('Date_Time'); ?>：
    <select name="year" >
      <option value=""></option>
<?php $year = substr($post_date, 0, 4); for ($i = 2010; $i <= 2120; $i ++) { ?>
      <option value="<?php echo $i; ?>" <?php if ($year == $i) echo 'selected="selected";' ?>><?php echo $i; ?></option>
<?php } ?>
    </select> -
    <select name="month" >
      <option value=""></option>
<?php $month = substr($post_date, 5, 2); for ($i = 1; $i <= 12; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($month == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> -
    <select name="day" >
      <option value=""></option>
<?php $day = substr($post_date, 8, 2); for ($i = 1; $i <= 31; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($day == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select>&nbsp;
    <select name="hourse" >
      <option value=""></option>
<?php $hourse = substr($post_time, 0, 2); for ($i = 0; $i <= 23; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($hourse == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> :
    <select name="minute" >
      <option value=""></option>
<?php $minute = substr($post_time, 3, 2); for ($i = 0; $i <= 59; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($minute == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> :
    <select name="second" >
      <option value=""></option>
<?php $second = substr($post_time, 6, 2); for ($i = 0; $i <= 59; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($second == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select><?= L('Leave_blank_current_time'); ?>
    </div>

  <div class="st-w">
    <div class="st-w-l">
  <?php if ($post_passwd != '') { ?>
    <?= L('Password_already_used') ?>：
    <input type="checkbox" checked="checked" name="old_passwd" value="<?= htmlspecialchars($post_passwd) ?>">
    <input type="password" name="post_passwd" value="" placeholder="<?= L('Passwd_or_repasswd') ?>">
    <input type="submit" name="base64decode" style="margin:5px;" value="<?= L('Decrypt_Password') ?>">
    |
    <input type="submit" name="base64encode" style="margin:5px;" value="<?= L('Convert_To_Ciphertext'); ?>">
<?php } else { ?>
    <?= L('Password') ?>：
    <input type="password" name="post_passwd" value="" autocomplete="new-password">
    <?= L('After_adding_password') ?>
<?php } ?>
   </div>
  </div>  
   <?php if ($post_id != '') echo '<input type="hidden" name="id" value="'.$tempid.'">'; ?> 
   <div  class="st-w">
 <div  class="st-w-l">
    <?= L('Status'); ?>：
    <select name="state" style="width:100px;" >
      <option value="publish" <?php if ($post_state == 'publish') echo 'selected="selected"'; ?>><?= L('Publish'); ?></option>
      <option value="draft" <?php if ($post_state == 'draft') echo 'selected="selected"'; ?>><?= L('Drafts'); ?></option>
    </select>
    | <?= L('Comment'); ?>：<select name="can_comment">
      <option value="1" <?php if ($post_can_comment == '1') echo 'selected="selected";'; ?>><?= L('Allow'); ?></option>
      <option value="0" <?php if ($post_can_comment == '0') echo 'selected="selected";'; ?>><?= L('Disable'); ?></option>
    </select>
    </div>
    </div>
    <div style="clear:both;"></div>
  </div>
  <div style="text-align:right">
    <input type="hidden" name="id" value="<?php echo $post_id; ?>"/>
    <input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>
  </div>
</form>

<script>
$(function() {
    var formId = "#<?php echo $autsaveid; ?>";
    $(formId).sisyphus({
        timeout: 3,
        excludeFields: ['input[type="file"]', '#file-Input']
    });
});

// 获取编辑器正文内容
function getArticleContent() {
    if (window.quillMain) {
        return window.quillMain.root.innerHTML;
    } else {
        var box = document.getElementById('contentbox');
        return box ? box.value : '';
    }
}

function setSummaryHtml(html) {
    if (window.quill_summary) {
        window.quill_summary.clipboard.dangerouslyPasteHTML(html);
    } else {
        var summaryDom = document.getElementById('summary');
        if(summaryDom){
            summaryDom.value = html;
        }
    }
}

async function aiGenSummaryClick(){
    const btn = document.getElementById('btnGenSummary');
    const tip = document.getElementById('aiGenTip');
    const content = getArticleContent();

    if (!content.trim()) {
        tip.textContent = "⚠️ <?= L('Please_Fill_Article_Body'); ?>";
        return;
    }

    btn.disabled = true;
    tip.textContent = "⏳ <?= L('AI_Generating_Please_Wait'); ?>";

    try{
        const fd = new FormData();
        fd.append('action','gen_summary');
fd.append('pick_ai_index', getGlobalAiPickIndex());
        fd.append('post_content',content);

        const res = await fetch('post-edit.php',{
            method:'POST',
            body:fd
        });

        //先拿到原始文本，控制台打印，排查是不是HTML
        const rawText = await res.text();
        console.log("后端返回原始内容：",rawText);

        //容错判断：如果返回开头是 <!DOCTYPE 直接报错
        if(rawText.trim().startsWith('<!DOCTYPE')){
            throw new Error("后端返回网页HTML，不是JSON");
        }

        const json = JSON.parse(rawText);

        if(json.code===200){
            setSummaryHtml(json.data.summary);
            tip.textContent = "✅ <?= L('Summary_Generated_Check_Save'); ?>";
        }else{
            tip.textContent = "❌"+json.msg;
        }
    }catch(err){
        tip.textContent = "❌ <?= L('Network_Request_Error'); ?>";
        console.error(err);
    }finally{
        btn.disabled = false;
    }
}





async function aiFormatContentClick(){
    const btn = document.getElementById('btnAiFormat');
    const tip = document.getElementById('aiFormatTip');
    //获取编辑器原始html
    const contentHtml = getArticleContent();
    if (!contentHtml.trim()) {
        tip.textContent = "⚠️ <?= L('Editor_Body_Cannot_Empty'); ?>";
        return;
    }
    btn.disabled = true;
    tip.textContent = "⏳ <?= L('AI_Formatting_Please_Wait'); ?>";
    try{
        const fd = new FormData();
        fd.append('action','ai_format_content');
        fd.append('pick_ai_index', getGlobalAiPickIndex());
        fd.append('post_html',contentHtml);
        const res = await fetch('post-edit.php',{
            method:'POST',
            body:fd
        });
        const rawText = await res.text();
        console.log("AI排版返回原始：",rawText);
        if(rawText.trim().startsWith('<!DOCTYPE')){
            throw new Error("后端返回HTML页面，不是JSON");
        }
        const json = JSON.parse(rawText);
        if(json.code===200){
            //填入弹窗文本框，弹出窗口，不修改原编辑器！
            document.getElementById('aiFormatResultText').value = json.data.html;
            openAiFormatModal();
            tip.textContent = "✅ <?= L('Format_Done_Check_Modal'); ?>";
        }else{
            tip.textContent = "❌"+json.msg;
        }
    }catch(err){
        tip.textContent = "❌ <?= L('Request_Error'); ?>";
        console.error(err);
    }finally{
        btn.disabled = false;
    }
}

//繁简转换全局标记 true=当前简体 false=当前繁体
let isNowSimplified = true;

async function convertZhTwCn(){
    const tipDom = document.getElementById('zhConvertTip');
    const btnDom = document.getElementById('btnZhConvert');
    let htmlContent = getEditorHtml();
    if(!htmlContent.trim()){
        tipDom.textContent = "⚠️ <?= L('Body_Content_Empty'); ?>";
        setTimeout(()=>tipDom.textContent='',2000);
        return;
    }
    //检查库是否加载成功
    if(typeof OpenCC === "undefined"){
        tipDom.textContent = "❌ <?= L('OpenCC_Lib_Load_Failed_Refresh'); ?>";
        setTimeout(()=>tipDom.textContent='',2500);
        return;
    }

    btnDom.disabled = true;
    tipDom.textContent = "⏳ <?= L('Converting'); ?>";
    try{
        let converter;
        if(isNowSimplified){
            //简体 → 繁体(tw台湾字形)
            converter = OpenCC.Converter({from:'cn', to:'tw'});
            htmlContent = converter(htmlContent);
            tipDom.textContent = "✅ <?= L('Converted_To_Traditional'); ?>";
        }else{
            //繁体 → 简体
            converter = OpenCC.Converter({from:'tw', to:'cn'});
            htmlContent = converter(htmlContent);
            tipDom.textContent = "✅ <?= L('Converted_To_Simplified'); ?>";
        }
        //回填编辑器（兼容Quill / Xheditor）
        setEditorHtml(htmlContent);
        isNowSimplified = !isNowSimplified;
    }catch(err){
        console.error("繁简转换异常：",err);
        tipDom.textContent = "❌ <?= L('Convert_Failed'); ?>";
    }finally{
        btnDom.disabled = false;
        setTimeout(()=>tipDom.textContent='',2200);
    }
}

//打开弹窗
function openAiFormatModal(){
    document.getElementById('aiFormatModal').style.display = 'block';
    document.getElementById('copyTip').textContent = '';
}
//关闭弹窗
function closeAiFormatModal(){
    document.getElementById('aiFormatModal').style.display = 'none';
}
//复制弹窗内AI排版结果
async function copyAiFormatResult(){
    const text = document.getElementById('aiFormatResultText').value;
    const copyTip = document.getElementById('copyTip');
    try{
        await navigator.clipboard.writeText(text);
        copyTip.textContent = "✅ <?= L('Copy_Success_Paste_Editor'); ?>";
    }catch(e){
        //降级复制
        let ta = document.getElementById('aiFormatResultText');
        ta.select();
        document.execCommand('copy');
        copyTip.textContent = "✅ <?= L('Copied'); ?>";
    }
    setTimeout(()=>{copyTip.textContent=''},2500);
}
//点击遮罩层关闭弹窗
document.getElementById('aiFormatModal').addEventListener('click',function(e){
    if(e.target === this){
        closeAiFormatModal();
    }
})
</script>


<!-- AI排版结果弹窗 -->
<div id="aiFormatModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.6);">
    <div style="background:#fff;width:80%;max-width:850px;margin:40px auto;padding:20px;border-radius:6px;max-height:85vh;display:flex;flex-direction:column;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="margin:0;"><?= L('AI_Format_Result'); ?></h3>
            <button type="button" onclick="closeAiFormatModal()" style="padding:4px 10px;">× <?= L('close'); ?></button>
        </div>
        <div style="flex:1;overflow:auto;border:1px solid #ccc;padding:10px;background:#fafafa;">
            <textarea id="aiFormatResultText" readonly style="width:100%;height:380px;border:0;background:transparent;resize:none;"></textarea>
        </div>
        <div style="margin-top:12px;text-align:right;">
            <button type="button" id="btnCopyAiResult" onclick="copyAiFormatResult()" style="padding:6px 16px;">📋 <?= L('Copy_Format_Result'); ?></button>
            <span id="copyTip" style="margin-left:8px;color:#28a745;"></span>
            <button type="button" onclick="closeAiFormatModal()" style="padding:6px 16px;margin-left:10px;"><?= L('Close_Window'); ?></button>
        </div>
    </div>
</div>

<?php require 'image-library.php'; ?>
<script>
	
	
	//Prompt编辑区通用：取值（元素不存在返回空串）
function qsPromptVal(id){
    const el = document.getElementById(id);
    return el ? el.value : '';
}
//展开/收起
function qsBindPromptEdit(btnId, wrapId){
    const btn = document.getElementById(btnId);
    if(!btn) return;
    btn.addEventListener('click',function(){
        const wrap = document.getElementById(wrapId);
        if(!wrap) return;
        wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
    });
}
//保存（三处Prompt一起提交，互不覆盖）
function qsBindPromptSave(btnId, tipId){
    const btn = document.getElementById(btnId);
    if(!btn) return;
    btn.addEventListener('click',async function(){
        const tip = document.getElementById(tipId);
        if(tip) tip.textContent = "<?= L('Saving'); ?>";
        const fd = new FormData();
        fd.append('action','save_ai_prompt');
        //只提交页面上真实存在的Prompt框，缺失的由后端沿用旧值
        if(document.getElementById('taPromptSummary')) fd.append('prompt_summary', qsPromptVal('taPromptSummary'));
        if(document.getElementById('taPromptFormat'))  fd.append('prompt_format', qsPromptVal('taPromptFormat'));
        if(document.getElementById('taPromptTranslate')) fd.append('prompt_translate', qsPromptVal('taPromptTranslate'));
        try{
            const res = await fetch('post-edit.php',{method:'POST',body:fd});
            const raw = await res.text();
            let json;
            try{
                json = JSON.parse(raw);
            }catch(e){
                if(tip) tip.textContent = "❌ <?= L('Return_Not_JSON'); ?>";
                return;
            }
            if(tip) tip.textContent = (json.code===200) ? ("✅ <?= L('Saved'); ?>") : ('❌'+json.msg);
            setTimeout(()=>{ if(tip) tip.textContent=''; },2500);
        }catch(e){
            if(tip) tip.textContent = '❌'+e.message;
        }
    });
}
qsBindPromptEdit('btnEditPromptSummary','wrapPromptSummary');
qsBindPromptSave('btnSavePromptSummary','tipPromptSummary');
qsBindPromptEdit('btnEditPromptFormat','wrapPromptFormat');
qsBindPromptSave('btnSavePromptFormat','tipPromptFormat');
qsBindPromptEdit('btnEditPromptTranslate','wrapPromptTranslate');
qsBindPromptSave('btnSavePromptTranslate','tipPromptTranslate');




// 获取编辑器原始HTML内容
function getEditorHtml(){
    //判断当前编辑器
    if(window.quillMain){
        return window.quillMain.root.innerHTML;
    }else{
        //xheditor
        return $('#contentbox').val();
    }
}
// 设置编辑器HTML
function setEditorHtml(html){
    if(window.quillMain){
        window.quillMain.clipboard.dangerouslyPasteHTML(html);
    }else{
        $('#contentbox').val(html);
    }
}

//====手动插入广告标签到光标位置====
function insertAdManual(){
    var aid = document.getElementById('manualAdSelect').value;
    if(!aid){
        alert("<?= L('Please_Select_Ad_First'); ?>");
        return;
    }
    var tag = '{{ad:'+aid+'}}';
    if(window.quillMain){
        var range = window.quillMain.getSelection();
        if(!range) range = {index: window.quillMain.getLength()};
        window.quillMain.insertText(range.index,tag);
    }else{
        //xheditor光标插入
        var ed=$('#contentbox').xheditor();
        ed.pasteHTML(tag);
    }
}





//====自动投放广告核心函数====
function runAutoInsertAd(){
    var autoEnable = document.getElementById("autoAdCheck").checked;
    var aid = document.getElementById("autoAdSelect").value;
    if(!autoEnable){
        alert("<?= L('Please_Check_Auto_Ad_Checkbox'); ?>");
        return;
    }
    if(!aid){
        alert("<?= L('Please_Select_Auto_Ad_Item'); ?>");
        return;
    }
    const adTag = '<p>{{ad:'+aid+'}}</p>';
    let html = getEditorHtml();

    //1.提取所有p段落
    const parser = new DOMParser();
    let doc = parser.parseFromString(html,"text/html");
    let paragraphs = Array.from(doc.querySelectorAll('p'));
    let totalChar = 0;
    let lastInsertPos = 0;
    const firstGap = 300;
    const nextGap = 600;

    for(let p of paragraphs){
        let text = p.innerText.trim();
        let len = text.length;
        totalChar += len;

        //判断是否到达投放阈值
        if(lastInsertPos===0 && totalChar >= firstGap){
            //在当前段落后面插入广告
            p.insertAdjacentHTML('afterend',adTag);
            lastInsertPos = totalChar;
        }else if(lastInsertPos>0 && (totalChar - lastInsertPos)>=nextGap){
            p.insertAdjacentHTML('afterend',adTag);
            lastInsertPos = totalChar;
        }
    }

    let newHtml = doc.body.innerHTML;
    setEditorHtml(newHtml);
    alert("<?= L('Auto_Ad_Inserted_Success'); ?>");
}




//AI翻译：正文 + 标题 + 关键词一并翻进附属翻译字段
(function(){
    var btnTrans = document.getElementById('btnAiTranslate');
    if(!btnTrans){ return; }   //高级功能里关掉文章翻译时，整块不渲染，这里直接跳过
    btnTrans.addEventListener('click', async function(){
        const btn = this;
        const tip = document.getElementById('aiTransTip');
        const targetLang = document.getElementById('transTargetLang').value;
        const mainHtml = getEditorHtml();
        if(!mainHtml.trim()){
            tip.textContent = "⚠️ <?= L('Main_Editor_Body_Cannot_Empty'); ?>";
            setTimeout(()=>tip.textContent='',2500);
            return;
        }
        btn.disabled = true;
        tip.textContent = "⏳ <?= L('AI_Translating_Please_Wait'); ?>";
        try{
            const fd = new FormData();
            fd.append('action','ai_trans_content');
            fd.append('pick_ai_index', getGlobalAiPickIndex());
            fd.append('post_html', mainHtml);
            fd.append('target_lang', targetLang);
            fd.append('post_title', (document.getElementById('title') || {value:''}).value);
            fd.append('post_tags', (document.getElementById('tags') || {value:''}).value);
            const res = await fetch('post-edit.php',{
                method:'POST',
                body:fd
            });
            const rawText = await res.text();
            console.log("AI翻译返回原始：",rawText);
            if(rawText.trim().startsWith('<!DOCTYPE')){
                throw new Error("后端返回HTML页面，不是JSON");
            }
            const json = JSON.parse(rawText);
            if(json.code===200){
                let htmlOut = json.data.trans_html || '';
                // 移除首尾markdown代码块标记
                htmlOut = htmlOut.replace(/^```(html)?\s*/gi, '');
                htmlOut = htmlOut.replace(/\s*```$/g, '');
                document.getElementById('taContent2nd').value = htmlOut;

                // 标题译文
                const title2nd = document.querySelector('input[name="title_2nd"]');
                if(title2nd && json.data.trans_title){
                    title2nd.value = json.data.trans_title;
                }
                // 关键词译文
                const tags2nd = document.getElementById('tags_2nd');
                if(tags2nd && json.data.trans_tags){
                    tags2nd.value = json.data.trans_tags;
                }

                tip.textContent = "✅ <?= L('Translation_Done_Filled'); ?>";
            }else{
                tip.textContent = "❌"+json.msg;
            }
        }catch(err){
            tip.textContent = "❌ <?= L('Request_Error'); ?>";
            console.error(err);
        }finally{
            btn.disabled = false;
            setTimeout(()=>tip.textContent='',3000);
        }
    });
})();







// 全局AI选择器会话记忆
(function(){
    const sel = document.getElementById('globalAiSelector');
    // 读取会话存储
    const storedVal = sessionStorage.getItem('postedit_global_ai_pick');
    if(storedVal !== null){
        sel.value = storedVal;
    }
    // 变更时保存到sessionStorage
    sel.addEventListener('change',function(){
        sessionStorage.setItem('postedit_global_ai_pick', this.value);
    });

    // 封装获取当前选中的下标值
    window.getGlobalAiPickIndex = function(){
        return sel.value === '' ? '' : sel.value;
    };
})();

</script>

<script src="/files/vendor/opencc-js/1.0.5/full.js"></script>

<?php require 'foot.php' ?>