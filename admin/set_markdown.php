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
 * @File         set_markdown.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Markdown import/export panel: parses front-matter, converts posts to Markdown and drives the export action.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 include 'head.php'; ?>
<?php
$post_api = $qs_adv_config['post_api'] ?? 'close';
global $qs_config,$qs_dev_config;
// set_markdown.php Markdown导入导出面板
// 复用原有获取索引文件函数
function get_index_file($state) {
    global $qs_config;
    if ($state == 'draft') {
        return $qs_config['qs_post_dir'] . 'index/draft.php';
    } else if ($state == 'delete') {
        return $qs_config['qs_post_dir'] . 'index/delete.php';
    } else {
        return $qs_config['qs_post_dir'] . 'index/publish.php';
    }
}
$postDataDir = $qs_config['qs_post_dir'].'data/';
if (!is_dir($postDataDir)) {
    mkdir($postDataDir, 0755, true);
}
$tmpDir = __DIR__.'/../temp_md_import/';
if(!is_dir($tmpDir)) mkdir($tmpDir,0755,true);
if(!file_exists($tmpDir.'index.php')) file_put_contents($tmpDir.'index.php','<?php exit;');
/**
 * 解析markdown front‑matter
 * ---
 * title:文章标题
 * date:2026-08-26
 * tags:认知心理,人生成长
 * ---
 * 增强：无front‑matter时自动读取第一行 # 一级标题
 */
function parseMdFrontMatter(string $rawMd):array
{
    $art = [
        'title'=>L('Untitled_Title'),
        'tags'=>[],
        'date'=>date('Y-m-d'),
        'content'=>$rawMd
    ];
    //优先解析front‑matter
    if(preg_match('/^---\r?\n(.*?)\r?\n---\r?\n(.*)$/s',$rawMd,$m)){
        $fmText = $m[1];
        $body = $m[2];
        $art['content'] = $body;
        preg_match('/title:\s*(.+)/',$fmText,$mt);
        if($mt) $art['title']=trim($mt[1]);
        preg_match('/date:\s*(\d{4}-\d{2}-\d{2})/',$fmText,$md);
        if($md) $art['date']=trim($md[1]);
        preg_match('/tags:\s*(.+)/',$fmText,$mtag);
        if($mtag){
            $art['tags'] = array_filter(array_map('trim',explode(',',$mtag[1])));
        }
        //front‑matter没有title，再去正文第一行读取#标题
        if($art['title'] === L('Untitled_Title')){
            $firstLine = '';
            $lines = explode("\n",$body);
            foreach($lines as $line){
                $trimLine = trim($line);
                if($trimLine === '') continue;
                $firstLine = $trimLine;
                break;
            }
            if(str_starts_with($firstLine,'# ')){
                $art['title'] = trim(substr($firstLine,2));
            }
        }
    }else{
        //无front‑matter，直接扫描全文第一行非空行读取一级标题
        $firstLine = '';
        $lines = explode("\n",$rawMd);
        foreach($lines as $line){
            $trimLine = trim($line);
            if($trimLine === '') continue;
            $firstLine = $trimLine;
            break;
        }
        if(str_starts_with($firstLine,'# ')){
            $art['title'] = trim(substr($firstLine,2));
        }
    }
    return $art;
}
/**
 * 根据标题生成唯一post_id，复用系统shorturl，查重逻辑和post.php一致
 */
function genUniquePostId(string $title,string $dataDir):string
{
    $file_names = shorturl($title);
    foreach($file_names as $file_name) {
        $file_path = $dataDir.$file_name.'.php';
        if (!is_file($file_path)) {
            return $file_name;
        }
    }
    // 极端情况全部id被占用，追加随机后缀
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $suffix = '';
    for($i=0;$i<4;$i++) $suffix .= $chars[rand(0,strlen($chars)-1)];
    return end($file_names).$suffix;
}
/**
 * 保存一篇文章 + 更新索引，完全复刻post.php保存逻辑
 */
function saveNewPost(array $parsedMd,string $adminName,string $postState='publish'):void
{
    global $qs_config;
    $pid = genUniquePostId($parsedMd['title'],$GLOBALS['postDataDir']);
    $nowTime = date('H:i:s');
    //基础元数据
    $data = [
        'id' => $pid,
        'state' => $postState,
        'title' => $parsedMd['title'],
        'tags' => $parsedMd['tags'],
        'date' => $parsedMd['date'],
        'time' => $nowTime,
        'user' => $adminName,
        'can_comment' => '1',
        'video'=>'',
        'summary'=>'',
        'content'=>$parsedMd['content'],
        'download_size'=>'',
        'download'=>'',
        'post_passwd'=>''
    ];
    //小说模式补齐字段
    if ($qs_config['sitemode'] == "novel") {
        $data['bookname'] = '';
        $data['bookgenres'] = '';
        $data['bookpic'] = '';
    }
    //写入data文件，格式 <?php //serialize
    $file_path = $GLOBALS['postDataDir'].$pid.'.php';
    $writeContent = "<?php //".serialize($data). "?>";
    file_put_contents($file_path,$writeContent);
    //更新索引
    $index_file = get_index_file($postState);
    require $index_file;
    $qs_posts[$pid] = [
        'id' => $pid,
        'state' => $postState,
        'title' => $parsedMd['title'],
        'tags' => $parsedMd['tags'],
        'date' => $parsedMd['date'],
        'time' => $nowTime,
        'user' => $adminName,
        'can_comment' => '1'
    ];
    // 执行排序，和后台文章列表排序保持一致
    uasort($qs_posts, "post_sort");
    file_put_contents($index_file,
        "<?php\n\$qs_posts=".var_export($qs_posts, true)."\n?>"
    );
    //操作日志
    if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
        write_operate_log(qs_get_current_admin_name(), 'post_save', L('Log_Markdown_Batch_Import'), sprintf(L('Log_Add_Article_ID'), $pid));
    }
}
//读取session闪存消息，读完立刻清除
$msg = '';
if(!empty($_SESSION['md_import_msg'])){
    $msg = $_SESSION['md_import_msg'];
    unset($_SESSION['md_import_msg']);
}
//===== 导入动作 =====
if(isset($_POST['md_import_submit'])){
    $currentAdmin = qs_get_current_admin_name();
    $importOk = 0;
    foreach ($_FILES['md_files']['name'] as $k=>$fname){
        $err = $_FILES['md_files']['error'][$k];
        if($err!==UPLOAD_ERR_OK) continue;
        $tmpUpload = $_FILES['md_files']['tmp_name'][$k];
        $ext = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
        if($ext === 'md'){
            $mdRaw = file_get_contents($tmpUpload);
            $p = parseMdFrontMatter($mdRaw);
            saveNewPost($p, $currentAdmin,'publish');
            $importOk++;
        }elseif($ext === 'zip'){
            $zip = new ZipArchive();
            if($zip->open($tmpUpload)===true){
                for($i=0;$i<$zip->numFiles;$i++){
                    $stat = $zip->statIndex($i);
                    $inName = $stat['name'];
                    if(substr($inName,-1)==='/') continue;
                    if(strpos($inName,'__MACOSX')!==false) continue;
                    if(strtolower(pathinfo($inName,PATHINFO_EXTENSION))!=='md') continue;
                    $mdRaw = $zip->getFromIndex($i);
                    $p = parseMdFrontMatter($mdRaw);
                    saveNewPost($p, $currentAdmin,'publish');
                    $importOk++;
                }
                $zip->close();
            }
        }
    }
    //存入会话消息，页面原地刷新一次
    $_SESSION['md_import_msg'] = sprintf(L('Import_Finished_Success_Count'), $importOk);
}
//===== 导出动作，先生成zip临时文件，跳转纯净下载页 =====
if(isset($_POST['md_export_submit']) && !empty($_POST['export_ids'])){
    $exportIds = $_POST['export_ids'];
    $zip = new ZipArchive();
    $tempZipPath = $tmpDir.'export_'.uniqid().'.zip';
    $openRes = $zip->open($tempZipPath,ZipArchive::CREATE);
    if($openRes !== true){
        die(sprintf(L('Zip_Open_Failed_Code'), $openRes));
    }
    foreach($exportIds as $pid){
        $pid = basename($pid);
        $artFile = $postDataDir.$pid.'.php';
        if(!file_exists($artFile)) continue;
        $file_content = file_get_contents($artFile);
        $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
        $file_content = preg_replace('/\?>$/', '', $file_content);
        $a = unserialize($file_content);
        $fm  = "---\ntitle: {$a['title']}\ndate: {$a['date']}\ntags: ".implode(',',$a['tags'])."\n---\n";
        $mdText = $fm.$a['content'];
        //安全文件名，使用普通减号
        $safeName = preg_replace('/[^\w\x80-\xff]/','_',$a['title']).'.md';
        $zip->addFromString($safeName,$mdText);
    }
    $zip->close();
    //把临时zip路径存入session，跳转下载入口
    $_SESSION['md_download_file'] = $tempZipPath;
    // 刷新到自身页面带下载参数，防止表单重提交
    header("Location: ?md_download=1");
    exit;
}
//===== 纯净下载分支，单独处理zip输出 =====
if(isset($_GET['md_download']) && !empty($_SESSION['md_download_file'])){
    $file = $_SESSION['md_download_file'];
    unset($_SESSION['md_download_file']);
    if(file_exists($file)){
        //清空所有缓冲区，杜绝任何前置文字输出污染zip
        while(ob_get_level()) ob_end_clean();
        // 修复：全部改为英文普通短横杠 -
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="qsis_export_md.zip"');
        readfile($file);
        unlink($file);
        exit;
    }
}
//读取文章列表用于导出勾选
$qs_posts = [];
$publishIndexPath = get_index_file('publish');
if(file_exists($publishIndexPath)) include $publishIndexPath;

// ========== 新增分页逻辑开始 =========
$pageSize = 20;     // 每一页显示条数，可自行修改
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$total = count($qs_posts);
$totalPage = $total > 0 ? (int)ceil($total / $pageSize) : 1;
$offset = ($page - 1) * $pageSize;

//数组切片，保留key（pid不能丢）
$slicePosts = array_slice($qs_posts, $offset, $pageSize, true);
// ========== 新增分页逻辑结束 =========
?>

<div class="admin_page_name">
<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    <a href="set_markdown.php"  <?php echo $page_file == 'set_markdown.php' ? 'class="current"' : ''; ?>><?= L('Markdown_Import_Export'); ?></a>
</div>
<div id="fenye">
 <ta style="display:block">
    <div class="container">
    <h2></h2>
    
    <?php if($msg):?><div class="updated"><?=htmlspecialchars($msg)?></div><?php endif;?>
    <div style="border:1px solid #ccc;padding:15px;margin:10px 0;">
        <h3><?= L('Markdown_Import'); ?></h3>
        <p><?= L('Markdown_Import_Desc'); ?></p>
        <form method="post" enctype="multipart/form-data">
            <input type="file" multiple accept=".md,.zip" name="md_files[]">
            <button type="submit" name="md_import_submit"><?= L('Start_Import'); ?></button>
        </form>
    </div>
    <div style="border:1px solid #ccc;padding:15px;margin:10px 0;">
        <h3><?= L('Markdown_Export'); ?></h3>
        <p><?= L('Markdown_Export_Desc'); ?><br>
        <?= sprintf(L('Export_Page_Info'), $page, $totalPage, $total); ?>；<strong><?= L('Export_Page_Check_Warning'); ?></strong>
        </p>
        <form method="post">
            <?php foreach($slicePosts as $pid=>$meta):?>
                <label style="display:block">
                    <input type="checkbox" name="export_ids[]" value="<?=htmlspecialchars($pid)?>">
                    <?=htmlspecialchars($meta['title'])?>
                </label>
            <?php endforeach;?>
            <br>
            <button type="submit" name="md_export_submit"><?= L('Export_Selected_Articles'); ?></button>
        </form>

        <!-- 分页导航 -->
        <div style="margin-top:12px;">
            <?php if($totalPage>1):?>
                <?php if($page>1):?>
                    <a href="?page=<?=$page-1?>"><?= L('Previous_Page'); ?></a>
                <?php endif;?>
                <?php
                //简单页码输出
                for($i=1;$i<=$totalPage;$i++){
                    if(abs($i-$page) >4) continue;
                    if($i==$page){
                        echo '<strong style="margin:0 4px;">'.$i.'</strong>';
                    }else{
                        echo '<a href="?page='.$i.'" style="margin:0 4px;">'.$i.'</a>';
                    }
                }
                ?>
                <?php if($page < $totalPage):?>
                    <a href="?page=<?=$page+1?>"><?= L('Next_Page'); ?></a>
                <?php endif;?>
            <?php endif;?>
        </div>
    </div>
    </ta>
</div>
<?php include 'foot.php'; ?>
