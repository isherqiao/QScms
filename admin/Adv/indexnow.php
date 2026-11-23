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
 * @File         indexnow.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  IndexNow client: normalises the host and submits URLs to the IndexNow endpoint with the configured key.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>

<?php
//admin/indexnow.php

/*
==%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*=
==%%%%%#*+++++++*#%%%%%#*++++++++++*%##++%%#*++++++++++#%%*=
==%%%#+==+**##*+==+%%%*==+*********#%#*++%*==**####****#%%*=
==%%#===#%%%%%%%#===%%===%%%%%%%%%%%%#####==+%%%%%%%%%%%%%*=
==%%===#%%%%%%%%%*==*%+==*######%%%%%#*==#===*#%%%%%%%%%%%*=
==%%===%%%%%%%%%%#==+%#+==========+#%#*==%#+==========+%%%*=
==%%===#%%%%%%%%%#==*%%%%%######*===*#*==%%%%%######*===#%*=
==%%#==+%%%%%%%%#+==#%%%%%%%%%%%%#==+#*==%%%%%%%%%%%%*==*%*=
==%%%*===*#%%%#*==+##%%##########+==##*==%%######%%%#===%%*=
==%%%%%*+============##===========+*%#*==%*===========+#%%*=
==%%%%%%%##**********%#*********###%%##**%#********###%%%%*=
==%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*=
*/
?>
<?php

if(isset($_POST['action']) && $_POST['action'] === 'run_indexnow_push')
{
    set_time_limit(0);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    while (ob_get_level()) ob_end_clean();
    ob_implicit_flush(true);

    $log = function(string $msg, bool $isSuccess=true){
        echo json_encode(["type"=>"log","msg"=>$msg,"isSuccess"=>$isSuccess])."\n";
        flush();
        if(connection_aborted()) exit;
    };

    $key = trim($static_config['IndexNow'] ?? '');
    $hostFullUrl = rtrim($static_config['static_root_url'] ?? '', '/');
    $staticOutputRaw = rtrim($static_config['static_output'] ?? '', '/');
    $sccBase = realpath(__DIR__ . '/../');
    $staticRoot = $sccBase . DIRECTORY_SEPARATOR . ltrim($staticOutputRaw, '/');
    $staticRoot = rtrim($staticRoot, '/');

    if(empty($key)){
        $log(L('IndexNow_Key_Empty_Tip'),false);
        exit(1);
    }
    if(!is_dir($staticRoot)){
        $log(sprintf(L('IndexNow_Static_Root_Missing'), $staticRoot),false);
        exit(1);
    }

    function scanHtmlFiles(string $dir, string $baseLocal): array
    {
        $results = [];
        $files = scandir($dir);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $fullPath = $dir . DIRECTORY_SEPARATOR . $f;
            if(is_dir($fullPath)){
                $results = array_merge($results, scanHtmlFiles($fullPath,$baseLocal));
            }else{
                $ext = strtolower(pathinfo($f,PATHINFO_EXTENSION));
                if($ext === 'html'){
                    $rel = substr($fullPath,strlen($baseLocal));
                    $rel = str_replace(DIRECTORY_SEPARATOR,'/',$rel);
                    $results[] = $rel;
                }
            }
        }
        return $results;
    }

    $htmlRelPaths = scanHtmlFiles($staticRoot,$staticRoot);
    $urlList = [];
    foreach ($htmlRelPaths as $rel){
        $urlList[] = $hostFullUrl.$rel;
    }
    $urlList = array_values(array_unique($urlList));

    if(count($urlList)===0){
        $log(L('IndexNow_No_Html_Page_Tip'),true);
        exit(0);
    }
    $log(sprintf(L('IndexNow_Scanned_Page_Count'), count($urlList)),true);

    $apiUrl = 'https://api.indexnow.org/indexnow';
    //解析裸host，协议要求host字段只填域名，不带http
    $hostPure = parse_url($hostFullUrl,PHP_URL_HOST);
    if($hostPure === null){
        $hostPure = parse_url('https://'.$hostFullUrl,PHP_URL_HOST);
    }

    $batchSize = 10000; //官方上限10000，取保守值
    $batches = array_chunk($urlList,$batchSize);
    $allOk = true;

foreach ($batches as $idx=>$batchUrls){
    $payloadArr = [
        "host" => $hostPure,
        "key" => $key,
        "urlList" => $batchUrls
    ];

    if(!empty($key)){
        $keyLocation = rtrim($hostFullUrl,'/') . '/' . $key . '.txt';
        $payloadArr['keyLocation'] = $keyLocation;
        $log("🔑 keyLocation：".$keyLocation,true);
    }

$payload = json_encode($payloadArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // ==========日志写入 control/data/indexnow_log 绝对路径==========
    $logDir = $datapaths . 'indexnow_log';

    $log("🔍 SITE_ROOT = ".SITE_ROOT,true);
    $log("🔍 datapaths = ".$datapaths,true);
    $log(sprintf(L('IndexNow_Log_Dir_Tip'), $logDir),true);
    $dirExists = is_dir($logDir);
    $log(sprintf(L('IndexNow_Log_Dir_Exists_Tip'), var_export($dirExists,true)),true);

    //尝试递归创建目录
    if(!$dirExists){
        $mkdirRet = mkdir($logDir,0755,true);
        $log(sprintf(L('IndexNow_Mkdir_Return_Tip'), var_export($mkdirRet,true)),true);
        if(!$mkdirRet){
            $log(L('IndexNow_Mkdir_Failed_Perm'),false);
        }
    }

    $fileName = 'batch_'.date("Ymd_His")."_no".($idx+1).".json";
    $saveFile = $logDir . '/' . $fileName;
    $log(sprintf(L('IndexNow_Save_File_Path_Tip'), $saveFile),true);

    $writeRet = false;
    if(is_dir($logDir)){
        $writeRet = file_put_contents($saveFile,$payload);
    }
    $log(sprintf(L('IndexNow_Write_Return_Tip'), var_export($writeRet,true)),true);

    if($writeRet === false){
        $log(L('IndexNow_Batch_Log_Write_Failed'),false);
        $viewUrl = '';
    }else{
        $viewUrl = htmlspecialchars("show_indexnow_batch.php?f=".$fileName);
        $log(sprintf(L('IndexNow_Batch_Urls_Link'), $viewUrl, count($batchUrls)),true);
    }
    // =============================================================

    $log(sprintf(L('IndexNow_Batch_Submit_Progress'), $idx+1, count($batches), count($batchUrls)),true);

    $ch = curl_init($apiUrl);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
    curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json; charset=utf-8']);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,15);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($resp === false || !empty($curlErr)){
        $log(sprintf(L('IndexNow_Batch_Curl_Error'), $idx+1, $curlErr),false);
        $allOk = false;
    }else{
        $log(sprintf(L('IndexNow_Batch_Http_Response'), $idx+1, $httpCode, var_export($resp,true)),true);
    }
    sleep(1);
}

    if($allOk){
        $log(L('IndexNow_All_Batches_Done'),true);
    }else{
        $log(L('IndexNow_Some_Batches_Failed'),false);
        exit(1);
    }
    exit;
}

// ---------------- 以上为新增action，下面全部沿用你原来的页面，一行未改 ----------------
?>
<div class="admin_page_name">
  <a href="advset.php"><?= L('Advanced_Maintenance_Mode'); ?></a>
  <a href="indexnow.php"><?= L('IndexNow'); ?></a>
</div>

<div id="mail">
 <div id="fenye">
  <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
  	
 	<ta style="display: block;">
 		<div class="clear"></div>
 		 <div class="small_form small_form2">
 		 	<b><?= L('Tool_IndexNow_Full_Push'); ?></b>
<b><a href="indexnow_loglist.php"  style="padding:10px 20px; font-size:16px; cursor:pointer;" class="button"><?= L('Log_List'); ?></a></b>
	</div>

<div class="field">

<div class="small_form">
    <div style="padding:8px 0;color:#d00;">
<?= L('IndexNow_Prereq_Tip_Line1'); ?><br>
        <?= L('IndexNow_Prereq_Tip_Line2'); ?>
    </div>

    <div class="field">
        <button type="button" id="btnIndexNowPush" class="button"><?= L('IndexNow_Full_Push_Button'); ?></button>
    </div>

    <div id="indexnowLogBox" style="margin-top:10px;max-height:320px;overflow:auto;background:#111;color:#ccc;padding:10px;font-family:Consolas;font-size:12px;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const btn = document.getElementById('btnIndexNowPush');
    const logBox = document.getElementById('indexnowLogBox');
    if(!btn) return;

    btn.addEventListener('click',function(){
        btn.disabled = true;
        logBox.innerHTML = '';
        let offset = 0;
        const xhr = new XMLHttpRequest();
        xhr.open('POST','',true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');

        xhr.onprogress = function(){
            const chunk = xhr.responseText.substring(offset);
            offset = xhr.responseText.length;
            const lines = chunk.split("\n");
            lines.forEach(line=>{
                if(!line.trim()) return;
                try{
                    const j = JSON.parse(line);
                    const color = j.isSuccess ? '#75beff' : '#f48771';
                    logBox.innerHTML += `<div style="color:${color}">${j.msg}</div>`;
                    logBox.scrollTop = logBox.scrollHeight;
                }catch(e){}
            });
        };

        xhr.onload = function(){
            btn.disabled = false;
        };

        xhr.onerror = function(){
            logBox.innerHTML += '<div style="color:#f48771"><?php echo L('Network_Request_Failed'); ?></div>';
            btn.disabled = false;
        };

        xhr.send('action=run_indexnow_push');
    });
});
</script>

    </form>
     </div>

 </div>
</ta>
</div>
<?php require_once __DIR__.'/../foot.php'; ?>