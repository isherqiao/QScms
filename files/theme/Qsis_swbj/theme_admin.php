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
 * @File         theme_admin.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_swbj": theme options panel shown in the backend.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 主题自己的管理页，必须校验管理员权限，防止外人访问
// 后台目录名由 core.php 的 $controldata 决定，换目录不用改这里
require_once __DIR__.'/../../../control/core.php';
require_once $controldata.'head.php';

$configFile = __DIR__.'/theme_config.php';
// 文件版：主题配置统一存到 control/data/Qsis_swbj/
$storeFile = SITE_ROOT . '/control/data/Qsis_swbj/theme_config.php';
$message = '';

// 处理提交保存
if(isset($_POST['save'])){
    $inBlocks = $_POST['theme_tag_block'] ?? [];
    $outBlocks = [];
    foreach($inBlocks as $one){
        $outBlocks[] = [
            'enable'    => !empty($one['enable']) ? true : false,
            'tag_name'  => trim($one['tag_name'] ?? ''),
            'tag_desc'  => trim($one['tag_desc'] ?? ''),
            'limit'     => (isset($one['limit']) && $one['limit'] !== '') ? intval($one['limit']) : null
        ];
    }
    // 首屏轮播：固定 3 条
    $inBanners = $_POST['theme_banner'] ?? [];
    $outBanners = [];
    for($i = 0; $i < 3; $i++){
        $one = $inBanners[$i] ?? [];
        $outBanners[] = [
            'enable' => !empty($one['enable']) ? true : false,
            'img'    => trim($one['img'] ?? ''),
            'text'   => trim($one['text'] ?? ''),
            'link'   => trim($one['link'] ?? ''),
        ];
    }
    $storeDir = dirname($storeFile);
    if(!is_dir($storeDir)){
        @mkdir($storeDir, 0755, true);
    }
    $export = var_export($outBlocks,true);
    $exportBanner = var_export($outBanners,true);
    $code = "<?php\nif (!defined('SITE_ROOT')) exit;\n\$theme_tag_blocks = ".$export.";\n\$theme_banners = ".$exportBanner.";\n?>";
    $rs = @file_put_contents($storeFile,$code);
    if($rs!==false){
        @chmod($storeFile, 0644);
        $message = '✅保存成功';
    }else{
        $message = '❌保存失败，请检查 control/data/Qsis_swbj 目录的写入权限';
    }
}

// 读取现有配置：先取默认，再用已保存值覆盖
$theme_tag_blocks = [];
$theme_banners = [];
if(file_exists($configFile)){
    include $configFile;
}
if(file_exists($storeFile)){
    include $storeFile;
}
if(!is_array($theme_tag_blocks)) $theme_tag_blocks = [];
if(!is_array($theme_banners)) $theme_banners = [];
// 轮播固定补齐到 3 条，便于表单展示
for($i = count($theme_banners); $i < 3; $i++){
    $theme_banners[$i] = ['enable'=>true,'img'=>'','text'=>'','link'=>''];
}
$theme_banners = array_slice($theme_banners, 0, 3);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>主题标签区块配置</title>
<style>
.box{background:#fff;padding:16px;border-radius:6px;box-shadow:0 1px 3px #0002;}
.item{border:1px #ccc solid;padding:12px;margin:10px 0;border-radius:4px;}
.row{margin:6px 0;}
label{display:inline-block;width:90px;}
input{padding:4px 6px;width:320px;}
.btn{padding:6px 12px;margin:4px;border:0;border-radius:4px;cursor:pointer;}
.btn-del{background:#dc3545;color:#fff;}
.msg{padding:8px;margin:8px 0;border-radius:4px;}
.success{background:#d4edda;color:#155724;}
.error{background:#f8d7da;color:#721c24;}
.section-title{margin:22px 0 8px;padding-top:14px;border-top:1px dashed #ccd3da;font-size:16px;font-weight:600;}
</style>
</head>
<body>
<div class="box">
    <h2>主题标签区块配置</h2>
    <?php if($message):?>
    <div class="msg <?=str_starts_with($message,'✅')?'success':'error'?>"><?=htmlspecialchars($message)?></div>
    <?php endif;?>
    <form method="post">
        <div id="blockList">
        <?php foreach ($theme_tag_blocks as $idx=>$item): ?>
        <div class="item">
            <div class="row">
                <label>启用区块</label>
                <input type="checkbox" name="theme_tag_block[<?=$idx?>][enable]" value="1" <?php echo !empty($item['enable']) ? 'checked' : '';?>>
            </div>
            <div class="row">
                <label>标签名</label>
                <input type="text" name="theme_tag_block[<?=$idx?>][tag_name]" value="<?=htmlspecialchars($item['tag_name']??'')?>">
            </div>
            <div class="row">
                <label>标签描述</label>
                <input type="text" name="theme_tag_block[<?=$idx?>][tag_desc]" value="<?=htmlspecialchars($item['tag_desc']??'')?>">
            </div>
            <div class="row">
                <label>显示条数</label>
                <input type="number" name="theme_tag_block[<?=$idx?>][limit]" value="<?=$item['limit']??''?>" placeholder="留空使用系统per_page">
            </div>
            <div class="row">
                <button type="button" class="btn btn-del" onclick="this.closest('.item').remove()">删除本区块</button>
            </div>
        </div>
        <?php endforeach;?>
        </div>
        <div class="section-title">首屏轮播（3 张大图，按顺序从左到右轮播）</div>
        <div id="bannerList">
        <?php foreach ($theme_banners as $bidx => $b): ?>
        <div class="item">
            <div class="row">
                <label>启用</label>
                <input type="checkbox" name="theme_banner[<?=$bidx?>][enable]" value="1" <?php echo !empty($b['enable']) ? 'checked' : '';?>>
            </div>
            <div class="row">
                <label>图片地址</label>
                <input type="text" name="theme_banner[<?=$bidx?>][img]" value="<?=htmlspecialchars($b['img'] ?? '')?>" placeholder="如 /upload/2026/xxx.jpg">
            </div>
            <div class="row">
                <label>标题文字</label>
                <input type="text" name="theme_banner[<?=$bidx?>][text]" value="<?=htmlspecialchars($b['text'] ?? '')?>" placeholder="留空则不显示文字">
            </div>
            <div class="row">
                <label>跳转链接</label>
                <input type="text" name="theme_banner[<?=$bidx?>][link]" value="<?=htmlspecialchars($b['link'] ?? '')?>" placeholder="留空则图片不可点">
            </div>
        </div>
        <?php endforeach;?>
        </div>
        <div style="margin-top:12px;">
            <button type="button" class="btn" onclick="addItem()">+新增区块</button>
            <button type="submit" name="save" class="btn">保存配置</button>
        </div>
    </form>
    <div style="margin-top:16px;">
        <a href="../control/set.php">← 返回系统设置</a>
    </div>
</div>

<script>
let index = <?=count($theme_tag_blocks)?>;
function addItem(){
    const wrap = document.getElementById('blockList');
    const div = document.createElement('div');
    div.className = 'item';
    div.innerHTML = `
<div class="row">
    <label>启用区块</label>
    <input type="checkbox" name="theme_tag_block[\${index}][enable]" value="1" checked>
</div>
<div class="row">
    <label>标签名</label>
    <input type="text" name="theme_tag_block[\${index}][tag_name]" value="">
</div>
<div class="row">
    <label>标签描述</label>
    <input type="text" name="theme_tag_block[\${index}][tag_desc]" value="">
</div>
<div class="row">
    <label>显示条数</label>
    <input type="number" name="theme_tag_block[\${index}][limit]" value="" placeholder="留空使用系统per_page">
</div>
<div class="row">
    <button type="button" class="btn btn-del" onclick="this.closest('.item').remove()">删除本区块</button>
</div>
    `;
    wrap.appendChild(div);
    index++;
}
</script>
<?php require_once $controldata.'foot.php'; ?>