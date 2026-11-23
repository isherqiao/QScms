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
 * @File         submit_se.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Search-engine submission: pushes URLs to the Baidu and Sogou index APIs.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * 多搜索引擎URL推送工具（断点续推+配额中断版）
 * 读取 sitemap.txt 每行一条url
 * 支持：百度普通收录、搜狗、必应
 * 👉 仅成功推送的URL记录偏移；配额耗尽立即停止本轮
 */

if (!function_exists('L')) {
    require_once __DIR__ . '/../control/functions.php';
    if (!isset($language_dir)) $language_dir = dirname(__DIR__) . '/files/language/admin/';
}

// ====================== 配置区 ======================
$config = [
    // 百度普通收录 https://ziyuan.baidu.com/
    'baidu' => [
        'enable'  => true,
        'site'    => 'swbj.qsis.cn', //裸域名，不带http/https
        'token'   => 'UIHUDdZfDIKGNlgl',
        'api'     => 'http://data.zz.baidu.com/urls'
    ],
    // 搜狗收录推送 https://zhanzhang.sogou.com/
    'sogou' => [
        'enable'  => false, //拿到密钥再改成true
        'site'    => 'xxx.com',
        'key'     => '你的搜狗推送key',
        'api'     => 'http://zhanzhang.sogou.com/api/sitemap.php'
    ],
    // 必应Bing 推送 https://www.bing.com/webmasters/
    'bing' => [
        'enable'  => false, //拿到密钥再改成true
        'api_key' => '你的Bing API Key',
        'api'     => 'https://ssl.bing.com/webmaster/api.svc/pox/SubmitUrlBatch?op=SubmitUrlBatch'
    ],
    'sitemap_file' => __DIR__ . '/../static_site/sitemap.txt',
    'offset_file'  => __DIR__ . '/push_offset.txt', //记录已成功推送偏移量
    'batch_size'   => 10, //百度硬性最大10条/批
];
// ====================================================

$log = [];
$urls_all = [];

//读取 sitemap.txt
if (!file_exists($config['sitemap_file']) && !str_starts_with($config['sitemap_file'],'http')) {
    $log[] = sprintf(L('Error_Local_File_Not_Found'), $config['sitemap_file']);
} else {
    if(str_starts_with($config['sitemap_file'],'http')){
        $raw = @file_get_contents($config['sitemap_file']);
        if($raw===false){
            $log[] = L('Remote_Sitemap_Fetch_Failed');
        }
    }else{
        $raw = file_get_contents($config['sitemap_file']);
    }

    if(!empty($raw)){
        $lines = explode("\n", $raw);
        foreach ($lines as $line) {
            $u = trim($line);
            if (!empty($u) && (str_starts_with($u, 'http://') || str_starts_with($u, 'https://'))) {
                $urls_all[] = $u;
            }
        }
        $log[] = sprintf(L('Links_Total_Read'), count($urls_all));
    }
}

// 读取上次【已成功推送】偏移位置
$start_offset = 0;
if(file_exists($config['offset_file'])){
    $start_offset = intval(file_get_contents($config['offset_file']));
}
$log[] = sprintf(L('Start_From_Offset'), $start_offset+1);
$total_url = count($urls_all);

if($start_offset >= $total_url){
    $log[] = L('All_Links_Pushed_Reset_Offset');
    file_put_contents($config['offset_file'], 0);
}else{
    $wait_push_urls = array_slice($urls_all, $start_offset);
    $batches = array_chunk($wait_push_urls, $config['batch_size']);
    $success_count_round = 0; //本轮成功条数

    /**
     * CURL POST通用请求
     */
    function curl_post($url, $body, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        return [
            'code'=>$code,
            'body'=>$resp,
            'curl_err'=>$err
        ];
    }

    // ---------------------- 百度提交 ----------------------
    if ($config['baidu']['enable'] && count($wait_push_urls)>0){
        $log[] = "\n" . L('Baidu_Api_Section');
        $stop_baidu = false;
        foreach ($batches as $idx => $batch){
            if($stop_baidu) break;
            $post_body = implode("\n", $batch);
            $req_url = $config['baidu']['api'] . "?site={$config['baidu']['site']}&token={$config['baidu']['token']}";
            $header = ['Content-Type: text/plain'];
            $ret = curl_post($req_url, $post_body, $header);

            $log[] = sprintf(L('Batch_Http_Code'), $idx+1, $ret['code']);
            $log[] = sprintf(L('Response_Content'), $ret['body']);
            if(!empty($ret['curl_err'])) $log[] = sprintf(L('Curl_Error_Msg'), $ret['curl_err']);

            //判断是否配额耗尽
            if(str_contains($ret['body'],'over quota')){
                $log[] = L('Baidu_Quota_Exhausted');
                $stop_baidu = true;
            }
            //200状态码才算本批次成功，累加成功数量
            if($ret['code'] === 200){
                $success_count_round += count($batch);
            }
        }
    }

    // ---------------------- 搜狗提交 ----------------------
    if ($config['sogou']['enable'] && count($wait_push_urls)>0){
        $log[] = "\n" . L('Sogou_Section');
        $stop_sogou = false;
        foreach ($batches as $idx => $batch){
            if($stop_sogou) break;
            $post_body = implode("\n", $batch);
            $req_url = $config['sogou']['api'] . "?siteurl={$config['sogou']['site']}&key={$config['sogou']['key']}";
            $header = ['Content-Type:text/plain'];
            $ret = curl_post($req_url, $post_body, $header);

            $log[] = sprintf(L('Batch_Http_Code'), $idx+1, $ret['code']);
            $log[] = sprintf(L('Response_Content'), $ret['body']);
            if(!empty($ret['curl_err'])) $log[] = sprintf(L('Curl_Error_Msg'), $ret['curl_err']);

            //搜狗如返回额度不足可在此加判断
            if($ret['code'] != 200){
                $log[] = L('Sogou_API_Error');
                $stop_sogou = true;
            }
            if($ret['code'] === 200){
                $success_count_round += count($batch);
            }
        }
    }

    // ---------------------- 必应Bing提交 ----------------------
    if ($config['bing']['enable'] && count($wait_push_urls)>0){
        $log[] = "\n" . L('Bing_Section');
        $stop_bing = false;
        foreach ($batches as $idx => $batch){
            if($stop_bing) break;
            $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<SubmitUrlBatch xmlns="http://schemas.datacontract.org/2004/07/Microsoft.Bing.Webmaster.Api">
  <apiKey>'.$config['bing']['api_key'].'</apiKey>
  <siteUrl>'.$batch[0].'</siteUrl>
  <urls>';
            foreach($batch as $bu){
                $xml_data .= '<string>'.htmlspecialchars($bu).'</string>';
            }
            $xml_data .= '</urls></SubmitUrlBatch>';

            $header = ['Content-Type: application/xml; charset=utf-8'];
            $ret = curl_post($config['bing']['api'], $xml_data, $header);

            $log[] = sprintf(L('Batch_Http_Code'), $idx+1, $ret['code']);
            $log[] = sprintf(L('Response_Content'), $ret['body']);
            if(!empty($ret['curl_err'])) $log[] = sprintf(L('Curl_Error_Msg'), $ret['curl_err']);

            if($ret['code'] != 200){
                $log[] = L('Bing_API_Error');
                $stop_bing = true;
            }
            if($ret['code'] === 200){
                $success_count_round += count($batch);
            }
        }
    }

    //只更新【本轮成功】后的偏移
    $new_offset = $start_offset + $success_count_round;
    file_put_contents($config['offset_file'], $new_offset);
    $log[] = "\n" . sprintf(L('Round_Pushed_Offset'), $success_count_round, $new_offset);
    $log[] = L('Failed_Links_Retry_Next');
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title><?= L('Search_Engine_URL_Batch_Push_Tool'); ?></title>
    <style>
        pre{background:#111;color:#0f0;padding:16px;font-size:13px;white-space:pre-wrap;}
        body{font-family:system-ui;max-width:1200px;margin:20px auto;padding:0 10px;}
    </style>
</head>
<body>
<h2><?= L('Multi_Engine_URL_Push_Log'); ?></h2>
<pre><?php echo htmlspecialchars(implode("\n",$log)); ?></pre>
</body>
</html>