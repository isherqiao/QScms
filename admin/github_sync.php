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
 * @File         github_sync.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  GitHub Pages sync panel: connection test, full or incremental sync and copy of the static output to the repository.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

opcache_reset();
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('default_socket_timeout', 3600);
ignore_user_abort(false);
$lockFile ='git_sync.lock';
if (isset($_POST['action']) && $_POST['action'] === 'start_git_sync') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    while (ob_get_level()) ob_end_clean();
    ob_implicit_flush(true);
    require 'head.php';
    putenv('GIT_TERMINAL_PROMPT=0');
    
    $token = Crypto::decrypt($qs_config['github_token'], $sp_config['user_pass']);
    $owner = $qs_config['github_owner'];
    $repo  = $qs_config['github_repo'];
    $branch= $qs_config['github_branch'] ?: 'main';
    $localDir = realpath(rtrim($qs_config['github_sync_dir'], '/'));
    $commitMsg = !empty($qs_config['github_commit_msg']) ? $qs_config['github_commit_msg'] : "isher Sync";
    $commitMsg .= " · ".date('Y-m-d H:i:s');
    $repoUrl = "https://$token@github.com/$owner/$repo.git";
    // 接收全量同步开关
    $isFullSync = !empty($_POST['full_sync']);
    if ($isFullSync) {
        $commitMsg = "[全量强制上传] ".$commitMsg;
    }
    putenv('GIT_TERMINAL_PROMPT=0');
    putenv('GIT_ASKPASS=');
    putenv('SSH_ASKPASS=');
    $log = function($msg, $ok = true) {
        echo json_encode(["type" => "log", "msg" => $msg, "isSuccess" => $ok]) . "\n";
        flush();
        
        if (connection_aborted()) {
            @unlink($GLOBALS['lockFile']);
            exit;
        }
    };
// ========== GitHub 测试 + 单行倒计时（不刷屏） ==========
$testGithubSuccess = false;
while (!$testGithubSuccess) {
    $ch = curl_init("https://github.com");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "curl-test");
    $res = curl_exec($ch);
    $err = curl_error($ch);
    // PHP < 8.0 才执行 curl_close
if (PHP_VERSION_ID < 80000) {
    curl_close($ch);
}
    $log(L('Php_GitHub_Test_Result'));
    $log(sprintf(L('Error_Message_Label'), $err));
    $log(L('Is_Success_Label').($res ? L('Success') : L('Failed')));
    if ($res) {
        $testGithubSuccess = true;
        $log(L('GitHub_Connectivity_Passed_Start_Sync'));
        
    } else {
        $wait = 3;
        echo json_encode(["type"=>"countdown","num"=>$wait]) . "\n";
        flush();
        
        while ($wait > 0) {
            sleep(1);
            $wait--;
            echo json_encode(["type"=>"countdown","num"=>$wait]) . "\n";
            flush();
            if (connection_aborted()) {
                @unlink($lockFile);
                exit;
            }
        }
        $log(L('Retest_GitHub_Connection'));
    }
}
// ======================================================
function run($cmd, $log) {
    $log(sprintf(L('Executing_Command'), $cmd));
    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"],
    ];
    $process = proc_open($cmd, $descriptorspec, $pipes);
    if (!is_resource($process)) {
        throw new Exception(sprintf(L('Cannot_Start_Command'), $cmd));
    }
    stream_set_blocking($pipes[1], 1);
    stream_set_blocking($pipes[2], 1);
    $output = '';
    $start = time();
    while (true) {
        if (time() - $start > 200) {
            proc_terminate($process);
            throw new Exception(L('Command_Execution_Timeout'));
        }
        $line = fgets($pipes[1]);
        if ($line) {
            $output .= $line;
            $log(trim($line));
        }
        $err = fgets($pipes[2]);
        if ($err) {
            $output .= $err;
            if (!str_contains($err, 'hint:')) {
                $log(trim($err), false);
            }
        }
        if (feof($pipes[1]) && feof($pipes[2])) break;
        usleep(200000);
    }
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    if ($code !== 0) {
        throw new Exception(sprintf(L('Command_Execution_Failed_Exit_Code'), $code));
    }
}
    try {
        if (file_exists($lockFile)) {
            $lockTime = file_get_contents($lockFile);
            if (time() - $lockTime > 60) {
                @unlink($lockFile);
            } else {
                throw new Exception(L('Task_Running_Please_Wait'));
            }
        }
        file_put_contents($lockFile, time());
        register_shutdown_function(fn() => @unlink($lockFile));
        if (!$localDir || !is_dir($localDir)) {
            throw new Exception(sprintf(L('Directory_Not_Exist'), $qs_config['github_sync_dir']));
        }
        chdir($localDir);
        $log(L('Start_Git_Sync'));
        // ---------- 新增：检测.git，不存在自动init初始化仓库 ----------
        $gitMetaDir = $localDir . DIRECTORY_SEPARATOR . '.git';
        if (!is_dir($gitMetaDir)) {
            $log(L('No_Git_Meta_Dir_Run_Git_Init'));
            run("git init", $log);
            run("git branch -M {$branch}", $log);
        }
        // 只有存在.git仓库时才执行 --local 参数配置，避免致命报错
        if(is_dir($gitMetaDir)){
            run("git config --local core.fileMode false", $log);
            run("git config --local core.trustctime false", $log);
        }
        run("git config core.quotepath false", $log);
        run("git config i18n.logoutputencoding utf-8", $log);
        run("git config gui.encoding utf-8", $log);
        run("git remote remove origin 2>/dev/null || true", $log);
        run("git remote add origin $repoUrl", $log);
        run("git config core.preloadindex true", $log);
        run("git config core.fscache true", $log);
        $gitName = escapeshellarg($owner);
        run("git config user.name {$gitName}", $log);
        $gitMail = escapeshellarg('qiaoshuo@gmail.com');
        run("git config user.email {$gitMail}", $log);
        $log(L('Sync_Remote_Status'));
        exec("git rev-parse --verify $branch", $out, $code);
        if ($code !== 0) {
            run("git checkout -b $branch", $log);
        } else {
            run("git checkout $branch", $log);
        }
        $log(L('Detect_File_Changes'));
        exec("git status --porcelain", $rawList);
        $changedList = [];
        foreach ($rawList as $row) {
            $row = trim($row);
            if ($row === '') continue;
            $realPath = substr($row, 2);
            $changedList[] = trim($realPath);
        }
        $changedList = array_unique($changedList);
        // 核心逻辑：全量模式跳过空变更拦截
        if (!$isFullSync) {
            if (empty($changedList)) {
                $log(L('SSG_No_File_Changes_No_Push'), true);
                write_operate_log(qs_get_current_admin_name(), L('Log_SSG_No_File_Changes'), L('Log_Target_GitHub_Sync_Msg'), 'github_rsync.php');
                exit;
            }
        }
        // 分两种add逻辑
        if ($isFullSync) {
            $log(L('Full_Sync_Add_All_Reset_Baseline'));
            // 全量模式：先清空git索引（不动磁盘文件），再重新add全部文件，强制生成变更集
            run("git rm --cached -r .", $log);
            run("git add .", $log);
        } else {
            $log(L('Normal_Incremental_Mode_Changed_Only'));
            foreach ($changedList as $filePath) {
                $filePath = trim($filePath);
                $escapedPath = escapeshellarg($filePath);
                run("git add {$escapedPath}", $log);
            }
        }

        $safeCommitMsg = escapeshellarg($commitMsg);
        // commit 单独处理：兼容 nothing to commit 场景，不抛异常
        $cmdCommit = "git commit -m {$safeCommitMsg} 2>&1";
        $log(sprintf(L('Executing_Command'), $cmdCommit));
        exec($cmdCommit, $commitOutput, $commitCode);
        foreach($commitOutput as $line){
            $log(trim($line));
        }

        $nothingToCommit = false;
        foreach ($commitOutput as $line) {
            if (stripos($line, 'nothing to commit') !== false) {
                $nothingToCommit = true;
                break;
            }
        }

        if ($commitCode !== 0 && !$nothingToCommit) {
            throw new Exception(sprintf(L('Commit_Command_Failed_Exit_Code'), $commitCode));
        }

        if ($nothingToCommit) {
            $log(L('Full_Mode_No_Diff_Empty_Commit'), true);
            // 空提交，用来触发push动作
            run("git commit --allow-empty -m {$safeCommitMsg}", $log);
            $log(L('Push_To_GitHub'));
            run("git push origin {$branch} --force", $log);
        } else {
            $log(L('Push_To_GitHub'));
            run("git push origin {$branch} --force", $log);
            $log(L('Sync_Completed'));
        }

        if ($isFullSync) {
            $log(L('Full_Upload_Finished_Next_Incremental'));
            write_operate_log(qs_get_current_admin_name(), L('Log_GitHub_Full_Sync'), L('Log_Target_GitHub_Sync_Msg'), 'github_rsync.php');
        $log(L('Sync_Completed'));
        }

// 记录ssg静态化发布操作日志
write_operate_log(qs_get_current_admin_name(), L('Log_GitHub_Normal_Sync'), L('Log_Target_GitHub_Sync_Msg'), 'github_rsync.php');
    } catch (Exception $e) {
        $log(sprintf(L('Error_Prefix'), $e->getMessage()), false);
    }
    @unlink($lockFile);
    $log(L('Progress_Finished'));
    exit;
}
require 'head.php';
?>
<style>
.sync-container{max-width:1000px;margin:20px auto;padding:20px;background:#f8f9fa;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);} 
.log-container{max-height:500px;overflow-y:auto;border:1px solid #333;padding:15px;background:#1e1e1e;color:#d4d4d4;margin:15px 0;font-family:Consolas, monospace;font-size:12px;line-height:1.6;} 
.log-item{padding:3px 0;border-bottom:1px solid #2d2d2d;} 
.log-success{color:#75beff;} 
.log-fail{color:#f48771;font-weight:bold;}
.start-btn{padding:12px 30px;background:#007cba;color:white;border:0;border-radius:6px;cursor:pointer;font-weight:bold;}
.start-btn:disabled{background:#666;cursor:not-allowed;}
.check-line{margin:10px 0;font-size:14px;}
</style>
<div class="sync-container">
    <h2><?= L('GitHub_Git_Sync_Title'); ?></h2>
    <div class="check-line">
        <input type="checkbox" id="fullSyncCheck">
        <label for="fullSyncCheck"><?= L('Full_Force_Upload_Desc'); ?></label>
    </div>
    <button class="start-btn" id="startBtn" onclick="startSync()"><?= L('Start_Sync_Button'); ?></button>
    <div class="log-container" id="log"></div>
</div>
<script>
let logBox = document.getElementById('log');
let btn = document.getElementById('startBtn');
let countLine = null;
const fullSyncCheck = document.getElementById('fullSyncCheck');
function addLog(msg, ok){
    let d = document.createElement('div');
    d.className = 'log-item ' + (ok ? 'log-success' : 'log-fail');
    d.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
    logBox.appendChild(d);
    logBox.scrollTop = logBox.scrollHeight;
    countLine = null;
}
function updateCount(num) {
    let text = '[' + new Date().toLocaleTimeString() + '] ' + <?= json_encode(L('GitHub_Connect_Failed_Retry_Seconds')); ?>.replace('%s', num);
    if(!countLine){
        countLine = document.createElement('div');
        countLine.className = 'log-item log-fail';
        logBox.appendChild(countLine);
    }
    countLine.textContent = text;
    logBox.scrollTop = logBox.scrollHeight;
}
function startSync(){
    btn.disabled = true;
    logBox.innerHTML = '';
    countLine = null;
    let xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.timeout = 0;
    // 携带全量参数
    let postData = 'action=start_git_sync';
    if(fullSyncCheck.checked){
        postData += '&full_sync=1';
    }
    let offset = 0;
    xhr.onprogress = function(){
        let newData = xhr.responseText.substring(offset);
        let lines = newData.split('\n');
        lines.forEach(line => {
            if(line.trim()){
                try {
                    let j = JSON.parse(line);
                    if(j.type === 'log') addLog(j.msg, j.isSuccess);
                    if(j.type === 'countdown') updateCount(j.num);
                } catch(e) {}
            }
        });
        offset = xhr.responseText.length;
    };
    xhr.onload = function(){
        btn.disabled = false;
        addLog(L('Sync_Ended'), true);
    };
    xhr.send(postData);
}
</script>
<?php require 'foot.php'; ?>
