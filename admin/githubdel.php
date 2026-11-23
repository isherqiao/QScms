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
 * @File         githubdel.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  GitHub cleanup tool: keeps the files of the previous year and earlier, deletes the rest from the synced repository.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// github_clean_old_files.php
// 功能：保留去年及更早的文件，删除其他所有文件

// 开启错误报告用于调试
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '-1');

// 检查是否为 AJAX 清理请求
if (isset($_POST['action']) && $_POST['action'] === 'start_clean') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    header('Connection: keep-alive');
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_implicit_flush(true);
    
    require 'head.php'; 
    
    // 验证配置
    $required = ['github_token', 'github_owner', 'github_repo', 'github_branch', 'github_sync_dir'];
    foreach ($required as $key) {
        if (empty($qs_config[$key])) {
            echo json_encode(['type'=>'log','msg'=>sprintf(L('Error_Missing_Config'), $key),'isSuccess'=>false])."\n";
            echo json_encode(['type'=>'summary','total'=>0,'kept'=>0,'deleted'=>0,'fail'=>1])."\n";
            flush();
            exit;
        }
    }

    $encryptedToken = $qs_config['github_token'];
    $key = $sp_config['user_pass'];
    $token = Crypto::decrypt($encryptedToken, $key);
    $owner = $qs_config['github_owner'];
    $repo = $qs_config['github_repo'];
    $branch = $qs_config['github_branch'];
    $syncDir = $qs_config['github_sync_dir'];
    $commitMsg = !empty($qs_config['github_commit_msg']) ? $qs_config['github_commit_msg'] : 'Clean up old files';
    $commitMsg .= ' · ' . date('Y-m-d H:i:s');
    
    // 检查同步目录
    if (!is_dir($syncDir)) {
        echo json_encode(['type'=>'log','msg'=>sprintf(L('Error_Sync_Dir_Not_Exist'), $syncDir),'isSuccess'=>false])."\n";
        echo json_encode(['type'=>'summary','total'=>0,'kept'=>0,'deleted'=>0,'fail'=>1])."\n";
        flush();
        exit;
    }

    // 预扫描所有文件
    $allFiles = [];
    if ($handle = opendir($syncDir)) {
        closedir($handle);
        $dirIterator = new RecursiveDirectoryIterator($syncDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $relativePath = str_replace($syncDir . DIRECTORY_SEPARATOR, '', $filePath);
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                $fileMtime = $file->getMTime();
                $allFiles[] = [
                    'full_path' => $filePath, 
                    'relative_path' => $relativePath,
                    'mtime' => $fileMtime
                ];
            }
        }
    }
    $totalFiles = count($allFiles);

    // 定义内部函数
    function initCurl($url, $token, $method = 'GET', $postData = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: token {$token}",
            "User-Agent: QSIS-SSG-Clean",
            "Content-Type: application/json"
        ]);
        if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        }
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
        return $ch;
    }

    function deleteFile($relativePath, $owner, $repo, $branch, $token, $commitMsg) {
        $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}/contents/{$relativePath}";

        // 1. 检查文件是否存在并获取 SHA
        $ch = initCurl($apiUrl, $token);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status' => 'fail', 'msg' => sprintf(L('Curl_Error_Path'), $relativePath, $curlError)];
        }

        $sha = null;
        if ($code == 200) {
            $data = json_decode($res, true);
            $sha = $data['sha'] ?? null;
        } elseif ($code == 404) {
            return ['status' => 'success', 'msg' => sprintf(L('File_Not_Exist_No_Delete'), $relativePath)];
        } else {
            return ['status' => 'fail', 'msg' => sprintf(L('Query_Failed_Http'), $relativePath, $code)];
        }

        if (!$sha) {
            return ['status' => 'fail', 'msg' => sprintf(L('Get_SHA_Failed'), $relativePath)];
        }

        // 2. 删除文件
        $post = ['message'=>$commitMsg, 'sha'=>$sha, 'branch'=>$branch];
        $ch = initCurl($apiUrl, $token, 'DELETE', $post);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status'=>'fail','msg'=>sprintf(L('Delete_Curl_Error'), $relativePath, $curlError)];
        }
        
        if($code == 200) {
            return ['status'=>'success', 'msg'=>sprintf(L('Delete_Success_Path'), $relativePath)];
        }
        
        $errData = json_decode($res, true);
        $err = $errData['message'] ?? L('Unknown_Error');
        return ['status'=>'fail', 'msg'=>sprintf(L('Delete_Failed_Http'), $relativePath, $err, $code)];
    }

    // 开始执行清理
    $kept = $deleted = $fail = $current = 0;
    $currentYear = date('Y');
    $lastYear = $currentYear - 1; // 去年

    echo json_encode(['type'=>'progress','current'=>0,'total'=>$totalFiles,'msg'=>sprintf(L('Start_Cleaning_Keep_Old'), $lastYear)])."\n";
    flush();
    ob_flush();

    foreach ($allFiles as $f) {
        $current++;
        $relPath = $f['relative_path'];
        $fileYear = date('Y', $f['mtime']);
        $needDelete = ($fileYear > $lastYear); // 大于去年 → 删除

        $statusText = $needDelete ? L('Delete') : L('Keep');
        $progressData = json_encode(['type'=>'progress','current'=>$current,'total'=>$totalFiles,'msg'=>sprintf(L('Status_Path_Mtime'), $statusText, $relPath, date('Y-m-d H:i:s', $f['mtime']))])."\n";
        echo $progressData;
        flush();
        ob_flush();

        if (!$needDelete) {
            $kept++;
            $logData = json_encode(['type'=>'log','msg'=>sprintf(L('Kept_Path_Mtime'), $relPath, date('Y-m-d H:i:s', $f['mtime'])),'isSuccess'=>true])."\n";
            echo $logData;
            flush();
            ob_flush();
            continue;
        }

        // 执行删除
        $startTime = time();
        $res = deleteFile($relPath, $owner, $repo, $branch, $token, $commitMsg);
        
        if (time() - $startTime > 30) {
            $res = ['status'=>'fail', 'msg'=>sprintf(L('Delete_Timeout'), $relPath)];
        }
        
        if($res['status'] == 'success') {
            $deleted++;
        } else {
            $fail++;
        }

        $logData = json_encode(['type'=>'log','msg'=>$res['msg'],'isSuccess'=>$res['status']=='success'])."\n";
        echo $logData;
        flush();
        ob_flush();
        
        usleep(50000); // 50ms 避免API限流
    }
    
    $summaryData = json_encode(['type'=>'summary','total'=>$totalFiles,'kept'=>$kept,'deleted'=>$deleted,'fail'=>$fail])."\n";
    echo $summaryData;
    flush();
    ob_flush();
    
    exit;
}

// --- 以下为普通访问 (显示界面) ---

require 'head.php';

if (empty($qs_config)) {
    die("<div style='color:red;'>".L('Error_Config_Not_Loaded')."</div>");
}

$required = ['github_token', 'github_owner', 'github_repo', 'github_branch', 'github_sync_dir'];
foreach ($required as $key) {
    if (empty($qs_config[$key])) {
        echo "<div style='color:red;padding:20px;'>".sprintf(L('Error_Missing_Config_Set_Settings'), $key)."</div>";
        require 'foot.php';
        exit;
    }
}

$syncDir = $qs_config['github_sync_dir'];
$owner = $qs_config['github_owner'];
$repo = $qs_config['github_repo'];
$branch = $qs_config['github_branch'];
$lastYear = date('Y') - 1;

// 检查同步目录
if (!is_dir($syncDir)) {
    echo "<div style='color:red;padding:20px;'>".sprintf(L('Error_Sync_Dir_Not_Exist'), $syncDir)."</div>";
    require 'foot.php';
    exit;
}

// 预扫描文件总数
$totalFiles = 0;
$willKeep = 0;
$willDelete = 0;
if (is_dir($syncDir)) {
    $dirIterator = new RecursiveDirectoryIterator($syncDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $totalFiles++;
            $fileYear = date('Y', $file->getMTime());
            if ($fileYear <= $lastYear) {
                $willKeep++;
            } else {
                $willDelete++;
            }
        }
    }
}
?>

<style>
    .clean-container{max-width:1000px;margin:20px auto;padding:20px;background:#f8f9fa;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
    .clean-header{margin-bottom:20px;padding-bottom:10px;border-bottom:1px solid #dee2e6;}
    .clean-header h2{color:#dc3545;margin-bottom:10px;}
    .info-row{margin:8px 0;color:#495057;font-size:14px;}
    .warning-box{padding:12px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:4px;color:#856404;margin:15px 0;}
    .progress-container{width:100%;height:24px;background:#e9ecef;border-radius:12px;margin:20px 0;overflow:hidden;position:relative;}
    .progress-bar{height:100%;background:linear-gradient(90deg,#dc3545,#ff7f7f);border-radius:12px;width:0%;transition:width 0.3s ease;}
    .progress-text{position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;color:#333;}
    .log-container{max-height:400px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;padding:10px;background:#fff;margin:15px 0;}
    .log-item{padding:4px 0;font-size:13px;border-bottom:1px solid #f1f3f5;}
    .log-success{color:#28a745;}.log-fail{color:#dc3545;}
    .summary{margin-top:20px;padding:15px;background:#e9f5e9;border-radius:6px;font-size:14px;}
    .summary.fail{background:#fdf2f2;}.summary strong{font-size:16px;}
    .waiting{padding:20px;text-align:center;color:#6c757d;font-size:16px;}
    .waiting span{color:#dc3545;font-weight:bold;}
</style>

<div class="clean-container">
    <div class="clean-header">
        <h2><?= L('GitHub_Pages_Old_File_Cleanup'); ?></h2>
        <div class="info-row">?? <?= L('Target_Directory'); ?><?php echo htmlspecialchars($syncDir); ?></div>
        <div class="info-row">?? <?= L('Target_Repository'); ?><?php echo htmlspecialchars($owner.'/'.$repo.' ('.$branch.')'); ?></div>
        <div class="info-row">?? <?= L('Clean_Rule_Label'); ?><strong><?= sprintf(L('Clean_Rule'), $lastYear, $lastYear+1); ?></strong></div>
        <div class="info-row">?? <?= sprintf(L('Stats_Summary'), $totalFiles, $willKeep, $willDelete); ?></div>
        
        <div class="warning-box">
            ?? <strong><?= L('Warning_Irreversible'); ?></strong> <?= L('Warning_Delete_GitHub_File'); ?>
        </div>
    </div>
    
    <div class="waiting" id="waitingBox"><?= L('Page_Loaded_Prefix'); ?><?= sprintf(L('Seconds_Start_Cleaning'), '<span id="countdown">5</span>'); ?></div>
    
    <div class="progress-container" id="progressContainer" style="display:none">
        <div class="progress-bar" id="progressBar"></div>
        <div class="progress-text" id="progressText">0% (0/<?php echo $totalFiles; ?>)</div>
    </div>
    
    <h4 id="logTitle" style="display:none">?? <?= L('Clean_Details'); ?></h4>
    <div class="log-container" id="log" style="display:none"></div>
    
    <div class="summary" id="summary" style="display:none">
        <div id="sumCon"></div>
    </div>
</div>

<script>
function updatePro(c,t){
    let p = t > 0 ? Math.round(c/t*100) : 0;
    document.getElementById('progressBar').style.width = p + '%';
    document.getElementById('progressText').innerText = p + '% (' + c + '/' + t + ')';
}

function addLog(m, s){
    let d = document.getElementById('log');
    let i = document.createElement('div');
    i.className = 'log-item ' + (s ? 'log-success' : 'log-fail');
    i.innerText = m;
    d.appendChild(i);
    d.scrollTop = d.scrollHeight;
}

function showSum(t, k, d, f){
    let e = document.getElementById('summary');
    let c = document.getElementById('sumCon');
    let h = `<strong><?php echo L('Clean_Completed'); ?></strong><br><br><?php echo sprintf(L('Summary_Line'), '${t}', '${k}', '${d}', '${f}'); ?>`;
    if(f > 0){
        e.classList.add('fail');
        h += '<br><small>' + <?php echo json_encode(L('Some_Failed_Check_Log_API'), JSON_UNESCAPED_UNICODE); ?> + '</small>';
    } else {
        h += '<br><small>' + <?php echo json_encode(L('All_Success_Page_Refresh'), JSON_UNESCAPED_UNICODE); ?> + '</small>';
    }
    c.innerHTML = h;
    e.style.display = 'block';
}

function startClean(){
    document.getElementById('waitingBox').style.display = 'none';
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('logTitle').style.display = 'block';
    document.getElementById('log').style.display = 'block';

    let x = new XMLHttpRequest();
    let received = '';
    
    x.open('POST', location.href, true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.responseType = 'text';
    x.setRequestHeader('Cache-Control', 'no-cache');
    
    x.onreadystatechange = function() {
        if (x.readyState === 3) {
            let newData = x.responseText.substring(received.length);
            if (newData) {
                received += newData;
                processData(received);
            }
        } else if (x.readyState === 4) {
            let newData = x.responseText.substring(received.length);
            if (newData) {
                received += newData;
                processData(received);
            }
            if (x.status !== 200) {
                addLog(<?php echo json_encode(L('Request_Failed_Http'), JSON_UNESCAPED_UNICODE); ?>.replace('%s', x.status), false);
            }
        }
    };
    
    function processData(data) {
        let lines = data.split('\n');
        let incomplete = '';
        
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            if (i === lines.length - 1 && !line.endsWith('}')) {
                incomplete = line;
                continue;
            }
            if (!line) continue;
            
            try {
                let d = JSON.parse(line);
                switch(d.type){
                    case 'progress':
                        updatePro(d.current, d.total);
                        break;
                    case 'log':
                        addLog(d.msg, d.isSuccess);
                        break;
                    case 'summary':
                        showSum(d.total, d.kept, d.deleted, d.fail);
                        break;
                }
            } catch(e) {
                console.error('JSON解析错误:', e, '原始数据:', line);
            }
        }
        received = incomplete;
    }
    
    x.onerror = function() {
        addLog(<?php echo json_encode(L('Network_Error_Interrupted'), JSON_UNESCAPED_UNICODE); ?>, false);
    };
    x.ontimeout = function() {
        addLog(<?php echo json_encode(L('Request_Timeout'), JSON_UNESCAPED_UNICODE); ?>, false);
    };
    x.timeout = 300000;
    
    x.send('action=start_clean');
}

window.onload = function(){
    let t = 5;
    let e = document.getElementById('countdown');
    let timer = setInterval(function(){
        t--;
        if(e) e.innerText = t;
        if(t <= 0){
            clearInterval(timer);
            startClean();
        }
    }, 1000);
};
</script>

<?php require 'foot.php'; ?>