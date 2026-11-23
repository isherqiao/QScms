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
 * @File         api_post.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Post API endpoint: returns published post data for the remote content site and checks the access token.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// api_post.php｜A源站只读API【仅publish索引，不读取data正文】
require_once 'head.php';
header('Content-Type:application/json;charset=utf-8');

$api_mode = $qs_Adv_config['post_api'] ?? 'close';
$secret_hash = $qs_config['postapi_site_secret'] ?? '';

// 鉴权
if ($api_mode !== 'open' || empty($secret_hash)) {
    exit(json_encode(['code'=>403,'msg'=>'PostAPI本地查询模式未启用或密钥未配置']));
}
$client_secret = $_SERVER['HTTP_X_QSIS_SECRET'] ?? '';
if(hash('sha256', $client_secret) !== $secret_hash){
    exit(json_encode(['code'=>401,'msg'=>'鉴权失败']));
}

$action = $_GET['action'] ?? 'list';
$aid = trim($_GET['id'] ?? '');
$publishFile = __DIR__ . '/files/posts/index/publish.php';

if(!file_exists($publishFile)){
    exit(json_encode(['code'=>500,'msg'=>'publish索引文件不存在']));
}
include $publishFile;

// 1、索引列表：仅state=publish
if($action === 'list'){
    $list = [];
    foreach($qs_posts as $pid => $p){
        if( ($p['state'] ?? '') !== 'publish' ) continue;
        $list[] = [
            'id'      => $p['id'],
            'title'   => $p['title'],
            'date'    => $p['date'] ?? '',
            'time'    => $p['time'] ?? '',
            'tags'    => $p['tags'] ?? []
        ];
    }
    // 时间倒序
    usort($list,function($a,$b){
        $t1 = strtotime($a['date'].' '.$a['time']);
        $t2 = strtotime($b['date'].' '.$b['time']);
        return $t2 - $t1;
    });
    exit(json_encode(['code'=>200,'data'=>$list]));
}
// 2、详情：直接返回publish内该id整条元数据，不去读data
elseif($action === 'detail' && !empty($aid)){
    if(!isset($qs_posts[$aid])){
        exit(json_encode(['code'=>404,'msg'=>'文章不存在']));
    }
    exit(json_encode(['code'=>200,'data'=>$qs_posts[$aid]]));
}
exit(json_encode(['code'=>400,'msg'=>'参数错误']));
