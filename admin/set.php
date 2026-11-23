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
 * @File         set.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 12/11/26 09:15:00
 * @Description  Backend settings hub: renders the tabbed settings shell, loads the individual set_* panels and merges local and remote indexes.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
require_once __DIR__ . '/menu_conf_lib.php';
?>
<?php
if(isset($_POST['do_fetch']) && $_POST['do_fetch'] === '1' && $is_admin){
    $remote_api_url = trim($qs_config['remote_api_url'] ?? '');
    $remote_api_key = trim($qs_config['remote_api_key'] ?? '');
    $indexFile = $qs_config['qs_post_publish'] ?? 'files/posts/index/publish.php';

    if(empty($remote_api_url) || empty($remote_api_key)){
        echo '<script>alert("' . L('Please_Configure_Remote_API_URL_Key') . '");</script>';
    }else{
        // 载入本地现有索引
        $localQcPosts = [];
        if(file_exists($indexFile)){
            include $indexFile;
            $localQcPosts = $qs_posts ?? [];
        }

        // 拉远程索引清单
        $ch = curl_init($remote_api_url.'?action=list');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,['X-Qsis-Secret: '.$remote_api_key]);
        curl_setopt($ch,CURLOPT_TIMEOUT,20);
        $res = curl_exec($ch);
        curl_close($ch);
        $list = json_decode($res,true);

        $importCount = 0;
        if($list && $list['code']===200 && !empty($list['data'])){
            foreach($list['data'] as $item){
                $aid = $item['id'];
                // 本地已有该id，直接跳过，不覆盖
                if(isset($localQcPosts[$aid])) continue;

                // 拉取该id完整元数据
                $ch2 = curl_init($remote_api_url.'?action=detail&id='.urlencode($aid));
                curl_setopt($ch2,CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch2,CURLOPT_HTTPHEADER,['X-Qsis-Secret: '.$remote_api_key]);
                curl_setopt($ch2,CURLOPT_TIMEOUT,15);
                $detailRes = curl_exec($ch2);
                curl_close($ch2);
                $detail = json_decode($detailRes,true);

                if(!$detail || $detail['code']!==200 || empty($detail['data'])) continue;
                $localQcPosts[$aid] = $detail['data'];
                $importCount++;
            }
            // 一次性重写本地publish索引
            $out = "<?php\n\$qs_posts = ".var_export($localQcPosts,true).";\n?>";
            file_put_contents($indexFile,$out);
        }
        echo '<script>alert("' . sprintf(L('Sync_Complete_Imported_Count'), $importCount) . '");</script>';
    }
}
?>

<?php
// ==========================================
// 初始化与配置加载
// ==========================================
$configFile = $datapaths . $config;
$staticFile = $datapaths . $staticconfig;
$message = '';
$messageType = ''; // 'success' or 'error'

$site_directory = htmlspecialchars(getTargetDirectory(), ENT_QUOTES);
$confContent = file_get_contents($configFile);
$confContent = preg_replace('/\'site_directory\'\s*=>\s*\'[^\']*\',/', "'site_directory' => '{$site_directory}',", $confContent);
file_put_contents($configFile, $confContent);

// 确保全局配置数组存在
if (!isset($qs_config)) $qs_config = [];
if (!isset($sp_config)) $sp_config = [];
if (!isset($qs_links)) $qs_links = [];
if (!isset($qs_menu)) $qs_menu = [];
if (!isset($qs_side_menu)) $qs_side_menu = [];
if (!isset($qs_custom_menus)) $qs_custom_menus = [];

if (isset($_POST['save'])) {
	    $snap_user_name = $sp_config['user_name'] ?? '';
    $snap_user_pass_hash = $sp_config['user_pass'] ?? '';
    $snap_github_token_enc = $qs_config['github_token'] ?? '';
    
    // --- A. 处理图片上传 ---
if (($_FILES['image']['size'] > 0) || ($_FILES['logo']['size'] > 0) || ($_FILES['ico']['size'] > 0) || ($_FILES['site_backgrouppic']['size'] > 0) || ($_FILES['payment_alipay_qr']['size'] > 0) || ($_FILES['payment_wechat_qr']['size'] > 0)) {
        $uploadDir = '../upload/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        include 'setimageup.php';
        // 移到花括号内部
        if(!empty($upMsgs)){
            $message .= ($message ? "；" : "") . implode("；", $upMsgs);
            if(!empty($upErr)){
                $messageType = 'error';
            }
        }
    }

// logo
if(empty($_FILES['logo']['size'])){
    $sel_logo = trim($_POST['sel_logo'] ?? '');
    $url_logo = trim($_POST['url_logo'] ?? '');
    if(!empty($sel_logo)){
        $qs_config['sitelogo'] = $sel_logo;
    }elseif(!empty($url_logo)){
        $qs_config['sitelogo'] = $url_logo;
    }
}

// favicon ico
if(empty($_FILES['ico']['size'])){
    $sel_ico = trim($_POST['sel_ico'] ?? '');
    $url_ico = trim($_POST['url_ico'] ?? '');
    if(!empty($sel_ico)){
        $qs_config['ico'] = $sel_ico;
    }elseif(!empty($url_ico)){
        $qs_config['ico'] = $url_ico;
    }
}

// site_backgrouppic背景图
if(empty($_FILES['site_backgrouppic']['size'])){
    $sel_bg = trim($_POST['sel_site_backgrouppic'] ?? '');
    $url_bg = trim($_POST['url_site_backgrouppic'] ?? '');
    if(!empty($sel_bg)){
        $qs_config['site_backgrouppic'] = $sel_bg;
    }elseif(!empty($url_bg)){
        $qs_config['site_backgrouppic'] = $url_bg;
    }
}

// 如果还有文章默认图片image配置，在这里再加一组

if(empty($_FILES['image']['size'])){
    $sel_img = trim($_POST['sel_image'] ?? '');
    $url_img = trim($_POST['url_image'] ?? '');
    if(!empty($sel_img)){
        $qs_config['defaultpostimage'] = $sel_img;
    }elseif(!empty($url_img)){
        $qs_config['defaultpostimage'] = $url_img;
    }
}

// 把消息追加到提示
if(!empty($upMsgs)){
    $message .= ($message ? "；" : "") . implode("；", $upMsgs);
}
// ===== end 新增图片选择&url逻辑 =====

    // --- B. 安全辅助函数 ---
    $getPost = function($key, $default = '') {
        if (!isset($_POST[$key])) return $default;
        return is_array($_POST[$key]) ? $_POST[$key] : trim($_POST[$key]);
    };
    
    $getInt = function($key, $default = 0) {
        return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
    };

// ==========================================
// 【重要】先保存所有旧数据！
// ==========================================
// 接收前端提交的数据
    $username      = $getPost('user_name');
    $new_user_pass = $getPost('user_pass');
    $old_user_name = $snap_user_name; // 修复日志未定义变量
    
    // ========== 密码变更时，自动迁移加密的Github Token（全局 + 所有多站点） ==========
    if ($new_user_pass !== '') {
        $newPassHash = hash_admin_password($new_user_pass);

        // -------------------- 1.迁移全局 github_token --------------------
        if (!empty($snap_github_token_enc) && !empty($snap_user_pass_hash)) {
            $plainGithubToken = Crypto::decrypt($snap_github_token_enc, $snap_user_pass_hash);
            if (!empty($plainGithubToken)) {
                $qs_config['github_token'] = Crypto::encrypt($plainGithubToken, $newPassHash);
            }
        }

        // -------------------- 2.读取并迁移 static_multisite_config.php 内所有站点token --------------------
        $multiSiteFile = $datapaths . $staticmultisites;
        if(file_exists($multiSiteFile)){
            include $multiSiteFile;
            if(!empty($multi_sites) && is_array($multi_sites)){
                foreach($multi_sites as &$oneSite){
                    // 判断当前站点存在加密token
                    if( !empty($oneSite['github_sync']['token']) ){
                        $oldEncToken = $oneSite['github_sync']['token'];
                        $plainToken  = Crypto::decrypt($oldEncToken, $snap_user_pass_hash);
                        if(!empty($plainToken)){
                            // 使用新密钥重新加密覆盖
                            $oneSite['github_sync']['token'] = Crypto::encrypt($plainToken, $newPassHash);
                        }
                    }
                }
                unset($oneSite); // 解除引用
                // 回写多站点配置文件
                $multiCode = "<?php\n// Multi site config (独立多站点配置文件)\n\$multi_sites = ".var_export($multi_sites,true).";\n?>";
                file_put_contents($multiSiteFile, $multiCode);
            }
        }

        // 更新管理员密码哈希
        $sp_config['user_pass'] = $newPassHash;
    }

    // 更新用户名
    if ($username !== '') {
        $sp_config['user_name'] = $username;
    }
    
    // 如果前台又填写了全新的github token，优先覆盖
    if (!empty($github_token = $getPost('github_token'))) {
        $qs_config['github_token'] = Crypto::encrypt($github_token, $sp_config['user_pass']);
    }

$is_change_github_token = (!empty($github_token) && $qs_config['github_token'] !== $snap_github_token_enc);

$is_change_username = ($username !== '' && $sp_config['user_name'] !== $snap_user_name);
$is_change_pass = ($new_user_pass !== '' && $sp_config['user_pass'] !== $snap_user_pass_hash);

$sp_config['admin_displayname'] = $getPost('admin_displayname');
    // --- C. 更新 qs_config (基础设置) ---
    $qs_config['site_directory'] = $getPost('site_directory');
    $qs_config['imagehost'] = $getPost('imagehost');
    $qs_config['site_name'] = $getPost('site_name');
    $qs_config['site_desc'] = $getPost('site_desc');
    $qs_config['site_link'] = $getPost('site_link');
    $qs_config['per_page'] = $getInt('per_page', 20);
    $qs_config['sitemode'] = $getPost('sitemode');
    $qs_config['url_ends'] = $getPost('url_ends');
    $qs_config['user_manage'] = $getPost('user_manage');
    $qs_config['role'] = $getPost('role');
    $qs_config['site_tags'] = $getPost('site_tags');
    $qs_config['qs_post_dir'] = $getPost('qs_post_dir');
    $qs_config['qs_page_dir'] = $getPost('qs_page_dir');

    // 处理 CSS 和语言的选择
    if (isset($_POST["css_two_values"])) {
        $vals = explode(",", $_POST["css_two_values"]);
        $qs_config['css_name'] = htmlspecialchars($vals[0]);
        $qs_config['css_theme'] = htmlspecialchars($vals[1]);
    }
    if (isset($_POST["LanguageGlobal"])) {
        $vals = explode(",", $_POST["LanguageGlobal"]);
        $qs_config['language_name'] = htmlspecialchars($vals[0]);
        $qs_config['language_file'] = htmlspecialchars($vals[1]);
    }

    // 处理留言文件名变更
    $newLiuyanName = preg_replace('/[^a-zA-Z0-9_\.]/', '', $getPost('liuyan_data_name'));
    if (!empty($newLiuyanName)) {
        if (!file_exists($datapaths . $newLiuyanName)) {
            @fopen($datapaths . $newLiuyanName, 'w');
        }
        if ($newLiuyanName != $qs_config['liuyan_data_name']) {
            @rename($datapaths . $qs_config['liuyan_data_name'], $datapaths . $newLiuyanName);
            $qs_config['liuyan_data_name'] = $newLiuyanName;
        }
    }

    // 批量处理普通文本字段
if (!isset($static_config)) $static_config = [];

// 普通配置（保留在 qs_config）
$textFields = [
    'site_close','site_close_desc','site_close_redirect','articlehead', 'site_about','qs_post_dir','qs_post_dir', 'head_code', 'tags_list', 'footer_code', 
    'sitelogoname','site_theme', 'sccpost_page', 'sccpage_page', 'edittools', 'openspeak', 'tagsonarticle', 
    'speakArticle', 'verify_email', 'bookgenreslist', 'other_code', 'carousel_config',
    // tar_backup 参数
    'remote_bak01', 'tar_exclude_directory', 'tar_name', 'tar_directory', 
    'des_directory', 'tar_email', 'tar_other',
    // 备份计划任务（定时）
    'backup_cron_on', 'backup_cron_freq', 'backup_cron_time', 'backup_cron_week', 'backup_cron_day', 'backup_keep',
    // 备份 FTP 自动上传
    'backup_ftp_on', 'backup_ftp_host', 'backup_ftp_port', 'backup_ftp_user', 'backup_ftp_dir',
    'backup_ftp_ssl', 'backup_ftp_pasv',
    // github 参数
    'github_owner','github_repo','github_branch','github_sync_dir','github_commit_msg',
    // PostAPI 参数
    'post_api','remote_api_url','remote_api_key',
    // AI 发文接口开关
    'ai_publish',
    'google_ads','IndexNow',
    // 页脚与隐私设置
    'footer_info_on','footer_copyright','footer_links','icp_no','icp_url',
    'privacy_title','privacy_slug','privacy_body','privacy_version','privacy_updated',
    // 收款打赏设置
    'payment_on','payment_slug','payment_page_title','payment_intro',
    'payment_alipay_url','payment_wechat_url','payment_paypal_url','payment_thanks','payment_return_url',
    'payment_donors_on','payment_donors_slug',
    'payment_api_on','payment_api_url','payment_api_pid','payment_api_key',
    'payment_paypal_on','payment_paypal_client','payment_paypal_mode',
    'payment_card_on',
    'payment_manage_on',
    'payment_alipay_slug','payment_wechat_slug','payment_paypal_slug',
];

// static_ 开头配置（存入第三个文件）
$staticFields = [
    'static_root_url', 'local_upload_dir', 'static_output', 'static_data_posts', 'static_data_pages',
    'static_publish_posts', 'static_publish_pages', 'static_theme_name', 'static_per_page','static_box_post_number','static_url_ends', 
    'static_upload_dir',  'static_sitelogo', 'static_ico', 'static_theme_dir',
    'static_footer_code','static_other_code', 'static_head_code','google_ads',
    'static_site_tags', 'static_language_name', 'static_language_file', 'static_robots','IndexNow',
];

// ========== PostAPI 本地密钥 ==========
// PostAPI - 本地站点密钥哈希
$raw_api_secret = $getPost('postapi_site_secret');
if (!empty($raw_api_secret) && $raw_api_secret !== 'API已生成') {
    $qs_config['postapi_site_secret'] = hash('sha256', $raw_api_secret);
}else{
    $qs_config['postapi_site_secret'] = $qs_config['postapi_site_secret'] ?? '';
}

// ========== AI 发文接口密钥 ==========
// AI - 外部 Agent 写入文章用的密钥（同样只存 sha256 哈希）
$raw_ai_secret = $getPost('ai_publish_key');
if (!empty($raw_ai_secret) && $raw_ai_secret !== L('AI_Key_Generated')) {
    $qs_config['ai_publish_key'] = hash('sha256', $raw_ai_secret);
}else{
    $qs_config['ai_publish_key'] = $qs_config['ai_publish_key'] ?? '';
}

// 原来逻辑不变
foreach ($textFields as $field) {
    $qs_config[$field] = $getPost($field);
}

// 收款二维码删除勾选
if (!empty($_POST['payment_alipay_qr_del'])) {
    $qs_config['payment_alipay_qr'] = '';
}
if (!empty($_POST['payment_wechat_qr_del'])) {
    $qs_config['payment_wechat_qr'] = '';
}

//======== AI‑API多组配置处理（存入 qs_config）========
//AI总开关
$qs_config['ai_api'] = $_POST['ai_api'] ?? 'close';

$aiSiteArr = $_POST['ai_site_name'] ?? [];
$aiModelArr = $_POST['ai_model'] ?? [];
$aiKeyArr = $_POST['ai_apikey'] ?? [];

$saveAiList = [];
if(is_array($aiSiteArr)){
    foreach ($aiSiteArr as $idx => $siteVal){
        $s = trim($siteVal ?? '');
        $m = trim($aiModelArr[$idx] ?? '');
        $k = trim($aiKeyArr[$idx] ?? '');
        //过滤三列全部为空的无效行，不保存
        if(!($s === '' && $m === '' && $k === '')){
            $saveAiList[] = [
                'site'  => $s,
                'model' => $m,
                'apikey'=> $k
            ];
        }
    }
}
$qs_config['ai_api_list'] = $saveAiList;
//======== end AI‑API配置 ========

//======== TTS 语音朗读配置处理（存入 qs_config['speak_tts_list']）========
$ttsKeyArr        = $_POST['tts_key'] ?? [];
$ttsNameArr       = $_POST['tts_name'] ?? [];
$ttsHandlerArr    = $_POST['tts_handler'] ?? [];
$ttsEnabledArr    = $_POST['tts_enabled'] ?? [];
$ttsVoiceArr      = $_POST['tts_voice'] ?? [];
$ttsLangArr       = $_POST['tts_lang'] ?? [];
$ttsRateArr       = $_POST['tts_rate'] ?? [];
$ttsMethodArr     = $_POST['tts_method'] ?? [];
$ttsApiUrlArr     = $_POST['tts_api_url'] ?? [];
$ttsApiKeyArr     = $_POST['tts_api_key'] ?? [];
$ttsApiSecretArr  = $_POST['tts_api_secret'] ?? [];
$ttsExtraArr      = $_POST['tts_extra'] ?? [];
$ttsCmdArr        = $_POST['tts_cmd'] ?? [];
$ttsCmdWorkdirArr = $_POST['tts_cmd_workdir'] ?? [];

$saveTtsList = [];
if (is_array($ttsKeyArr)) {
    foreach ($ttsKeyArr as $idx => $k) {
        $k = trim($k);
        if ($k === '') {
            continue; // 跳过没有 Key 的空行
        }
        $saveTtsList[] = [
            'key'          => $k,
            'name'         => trim($ttsNameArr[$idx] ?? ''),
            'handler'      => trim($ttsHandlerArr[$idx] ?? 'generic_http'),
            'enabled'      => (($ttsEnabledArr[$idx] ?? '1') === '1') ? '1' : '0',
            'voice'        => trim($ttsVoiceArr[$idx] ?? ''),
            'lang'         => trim($ttsLangArr[$idx] ?? ''),
            'rate'         => trim($ttsRateArr[$idx] ?? '1'),
            'method'       => strtoupper(trim($ttsMethodArr[$idx] ?? 'POST')),
            'api_url'      => trim($ttsApiUrlArr[$idx] ?? ''),
            'api_key'      => trim($ttsApiKeyArr[$idx] ?? ''),
            'api_secret'   => trim($ttsApiSecretArr[$idx] ?? ''),
            'extra'        => trim($ttsExtraArr[$idx] ?? ''),
            'cmd'          => trim($ttsCmdArr[$idx] ?? ''),
            'cmd_workdir'  => trim($ttsCmdWorkdirArr[$idx] ?? ''),
        ];
    }
}
// 至少保留一个浏览器原生默认项，保证朗读开关有意义
if (empty($saveTtsList)) {
    $saveTtsList[] = [
        'key' => 'web', 'name' => L('Browser_Native_Speech'), 'handler' => 'web', 'enabled' => '1',
        'voice' => '', 'lang' => 'zh-CN', 'rate' => '1', 'method' => 'POST',
        'api_url' => '', 'api_key' => '', 'api_secret' => '', 'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
    ];
}
$qs_config['speak_tts_list'] = $saveTtsList;

// 若当前选中的 speakArticle 不在启用列表中，自动退回第一个启用项
$speakSel = trim($qs_config['speakArticle'] ?? '');
$foundSel = false;
foreach ($saveTtsList as $p) {
    if (($p['key'] ?? '') === $speakSel && ($p['enabled'] ?? '1') !== '0') {
        $foundSel = true;
        break;
    }
}
if (!$foundSel) {
    $qs_config['speakArticle'] = $saveTtsList[0]['key'] ?? 'web';
}
//======== end TTS 配置 ========

$switchKeys = ['robots','sitemap','rss','search_index','page_404','urls_txt','cname'];
$qs_config['switch'] = [];
// 遍历所有开关项，勾选则赋值1，未勾选为空
foreach ($switchKeys as $key) {
    $qs_config['switch'][$key] = isset($_POST['switch'][$key]) ? 1 : '';
}

// 新增：把 static_ 字段存入 $static_config
foreach ($staticFields as $field) {
    $static_config[$field] = $getPost($field);
}

// ==========================================
// 👇 保存自定义输入框（带 custom_ 前缀 安全版）
// ==========================================
$custom_names = $getPost('custom_name', []);
$custom_values = $getPost('custom_value', []);
foreach ($custom_names as $i => $name) {
    $name = trim($name);
    if ($name !== '') {
        $key = 'custom_' . $name; // 👈 自动加前缀
        $qs_config[$key] = trim($custom_values[$i] ?? '');
    }
}

    // 处理备份密码逻辑
    if ($getPost('tar_passwd') != "") {
        $qs_config['tar_passwd'] = $getPost('tar_passwd');
    }

    // 处理 FTP 密码逻辑：留空表示沿用已有密码
    $ftpPassPost = $getPost('backup_ftp_pass');
    if (is_string($ftpPassPost) && $ftpPassPost !== '') {
        $qs_config['backup_ftp_pass'] = $ftpPassPost;
    }



    // --- D. 【核心升级】通用函数：处理带层级的列表数据 (菜单、链接、侧边栏) ---
    // 定义只需要一次！接收 5 个参数
    $processListWithHierarchy = function($nameKey, $linkKey, $targetKey, $parentKey, $selfKey) {
        $list = [];
        $names = isset($_POST[$nameKey]) ? $_POST[$nameKey] : [];
        $links = isset($_POST[$linkKey]) ? $_POST[$linkKey] : [];
        $targets = isset($_POST[$targetKey]) ? $_POST[$targetKey] : [];
        $parents = isset($_POST[$parentKey]) ? $_POST[$parentKey] : [];
        $selfIds = isset($_POST[$selfKey]) ? $_POST[$selfKey] : []; // 获取自身ID数组

        // 第一步：建立映射表 [SelfID => Index]
        $selfIdMap = [];
        foreach ($names as $idx => $name) {
            $sid = $selfIds[$idx] ?? '';
            if (!empty($sid)) {
                $selfIdMap[$sid] = $idx;
            }
        }

        foreach ($names as $index => $name) {
            // 跳过全空行
            
            $rawParentId = $parents[$index] ?? '0';
            $finalParentId = '0';
            $mySelfId = $selfIds[$index] ?? '';

            // 逻辑判断：解析层级关系
            if (!empty($rawParentId) && $rawParentId !== '0') {
                // 如果父ID存在于映射表中，说明父子关系有效
                if (isset($selfIdMap[$rawParentId])) {
                    $finalParentId = $rawParentId;
                } else {
                    // 父级不存在 (可能已被删除)，自动提升为顶级
                    $finalParentId = '0';
                }
            }

            $list[] = [
                "name" => htmlspecialchars($name),
                "link" => htmlspecialchars($links[$index] ?? ''),
                "tar" => $targets[$index] ?? '_self',
                "parent_id" => $finalParentId,
                "self_id" => $mySelfId // 保存自身ID
            ];
        }
        return $list;
    };

    // --- 调用处理函数 (已补全第 5 个参数) ---
    
    // 1. 处理主菜单 (对应 set_menu_manager.php 中的 menuseSelfid[])
    $qs_menu = $processListWithHierarchy(
        'menuname', 
        'menuurl', 
        'menutar', 
        'menuparentid', 
        'menuseSelfid'  
    );

    // 2. 处理侧边栏菜单 (对应 set_menu_manager.php 中的 side_menuseSelfid[])
    $qs_side_menu = $processListWithHierarchy(
        'side_menuname', 
        'side_menuurl', 
        'side_menutar', 
        'side_menuparentid', 
        'side_menuseSelfid' 
    );

    // 2b. 处理自定义菜单（每个菜单对应 cm_<标识>_ 前缀的字段）
    $customMenuKeys = isset($_POST['custom_menu_keys']) ? (array) $_POST['custom_menu_keys'] : [];
    $customMenuDel  = isset($_POST['del_custom_menu']) ? (array) $_POST['del_custom_menu'] : [];
    foreach ($customMenuDel as $delKey) {
        if (isset($qs_custom_menus[$delKey])) {
            unset($qs_custom_menus[$delKey]);
        }
    }

    // 新建自定义菜单（标识只能用小写字母/数字/下划线）
    $newKey  = strtolower(trim(isset($_POST['new_custom_menu_key']) ? (string) $_POST['new_custom_menu_key'] : ''));
    $newName = trim(isset($_POST['new_custom_menu_name']) ? (string) $_POST['new_custom_menu_name'] : '');
    if ($newKey !== '') {
        if (preg_match('/^[a-z][a-z0-9_]{0,30}$/', $newKey)
            && !isset($qs_custom_menus[$newKey])
            && !in_array($newKey, ['menu', 'sidemenu', 'custom_menu'], true)) {
            $qs_custom_menus[$newKey] = [
                'name'  => $newName !== '' ? htmlspecialchars($newName) : $newKey,
                'items' => [],
            ];
            $customMenuKeys[] = $newKey;
            $message = ($message !== '' ? $message . ' | ' : '') . sprintf('Menu created, call function: %s', 'qs_menu_' . $newKey . '()');
            $messageType = 'success';
        } else {
            $message = ($message !== '' ? $message . ' | ' : '') . 'Invalid or duplicate menu key, not created';
            $messageType = 'error';
        }
    }

    // 保存各自定义菜单的条目
    foreach (array_unique($customMenuKeys) as $cmKey) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,30}$/', (string) $cmKey)) {
            continue;
        }
        $cmItems = $processListWithHierarchy(
            'cm_' . $cmKey . '_name',
            'cm_' . $cmKey . '_url',
            'cm_' . $cmKey . '_tar',
            'cm_' . $cmKey . '_parent',
            'cm_' . $cmKey . '_self'
        );
        if (!isset($qs_custom_menus[$cmKey])) {
            $qs_custom_menus[$cmKey] = ['name' => $cmKey, 'items' => []];
        }
        $qs_custom_menus[$cmKey]['items'] = $cmItems;
    }

    // 3. 处理友情链接 (对应 set_link.php 中的 linkseSelfid[])
    $qs_links = $processListWithHierarchy(
        'link_name', 
        'link_url', 
        'link_menutar', 
        'linkparentid', 
        'linkseSelfid' 
    );

    // --- E. 写入配置文件 ---
    $code = "<?php\n";
    $code .= "// Generated by set.php on " . date('Y-m-d H:i:s') . "\n";
    $code .= "\$qs_config = " . var_export($qs_config, true) . ";\n";
    $code .= "\$qs_links = " . var_export($qs_links, true) . ";\n";
    $code .= "\$sp_config = " . var_export($sp_config, true) . ";\n";    
    $code .= "\$sp_config = " . var_export($sp_config, true) . ";\n";
    $code .= "?>";


$staticCode = "<?php\n";
$staticCode .= "// Static site config (独立ssg存储 static_ 配置)\n";
$staticCode .= "\$static_config = " . var_export($static_config, true) . ";\n";
$staticCode .= "?>";

// 菜单配置单独存 menu_conf.php（conf.php 不再保存菜单）
$menuConfFile = $datapaths . 'menu_conf.php';
$menuConfCode = qs_menu_conf_build_dynamic($qs_menu, $qs_side_menu, $qs_custom_menus);

// 统一判断保存结果
if (file_put_contents($configFile, $code) && file_put_contents($staticFile, $staticCode) && qs_menu_conf_write($menuConfFile, $menuConfCode) ) {
    if ($message !== '') {
    $message = L('settings_saved_successfully') . " | " . $message;
} else {
    $message = L('settings_saved_successfully');
}
$messageType = 'success';
    write_operate_log(qs_get_current_admin_name(), 'save_config', L('Log_Target_Basic_Config'), L('Log_Msg_Modify_Basic_Params'));

    // 图床配置单独存 control/data/imagebed.php（该目录不入库，密钥不会进版本库）
    if (isset($_POST['ib']) && is_array($_POST['ib'])) {
        require_once __DIR__ . '/../control/imagebed_lib.php';
        qs_imagebed_save($_POST['ib']);
    }

    // Cookie 同意配置单独存 control/data/cookie_consent.php（不入库）
    if (isset($_POST['cc']) && is_array($_POST['cc'])) {
        require_once __DIR__ . '/../control/consent_lib.php';
        qs_consent_save($_POST['cc']);
    }
    // 按保留期限清理旧的同意记录
    if (isset($_POST['cc_action']) && $_POST['cc_action'] === 'cleanup') {
        require_once __DIR__ . '/../control/consent_lib.php';
        $ccRemoved = qs_consent_cleanup();
        $message .= ' | ' . sprintf(L('CookieConsent_Cleanup_Done'), (int)$ccRemoved);
    }

    if(function_exists('write_operate_log') && isset($qs_adv_config['enable_system_logs']) && $qs_adv_config['enable_system_logs'] === 'open'){
        if($is_change_username){
            write_operate_log(qs_get_current_admin_name(), 'admin_change_username', L('Log_Target_Account_Settings'), sprintf(L('Log_Msg_Username_Changed'), $old_user_name, $username));
        }
        if($is_change_pass){
            write_operate_log(qs_get_current_admin_name(), 'admin_change_password', L('Log_Target_Account_Settings'), L('Log_Msg_Password_Updated_No_Plaintext'));
        }
        if($is_change_github_token){
            write_operate_log(qs_get_current_admin_name(), 'admin_change_github_token', L('Log_Target_GitHub_Sync_Cn'), L('Log_Msg_GitHub_Token_Updated_No_Secret'));
        }
    }
}
}

// ========== IndexNow推送独立action，不碰save保存逻辑 ==========
if(isset($_POST['action']) && $_POST['action'] === 'run_indexnow_push' && $is_admin)
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

    $key = trim($static_config['static_IndexNow'] ?? '');
    $host = rtrim($static_config['site_host'] ?? '', '/');
    $staticRoot = rtrim($static_config['static_output'] ?? '', '/');

    if(empty($key)){
        $log(L('IndexNow_Key_Empty_Fill_In_SSG'),false);
        exit(1);
    }
    if(!is_dir($staticRoot)){
        $log(sprintf(L('Static_Output_Dir_Not_Exist'), $staticRoot),false);
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
        $urlList[] = $host.$rel;
    }
    $urlList = array_values(array_unique($urlList));

    if(count($urlList)===0){
        $log(L('No_Html_Pages_Scanned_No_Push'),true);
        exit(0);
    }
    $log(sprintf(L('Scanned_Pages_Prepare_Full_Push'), count($urlList)),true);

    $apiUrl = 'https://api.indexnow.org/indexnow';
    $hostPure = parse_url($host,PHP_URL_HOST);
    $batchSize = 900;
    $batches = array_chunk($urlList,$batchSize);
    $allOk = true;

    foreach ($batches as $idx=>$batchUrls){
        $payload = json_encode([
            "host"=>$hostPure,
            "key"=>$key,
            "urlList"=>$batchUrls
        ],JSON_UNESCAPED_SLASHES);

        $ctx = stream_context_create([
            'http'=>[
                'method'=>'POST',
                'header'=>"Content-Type: application/json; charset=utf-8\r\n",
                'content'=>$payload,
                'timeout'=>15
            ]
        ]);
        $log(sprintf(L('Batch_Submit_Urls'), $idx+1, count($batches), count($batchUrls)),true);
        $resp = @file_get_contents($apiUrl,false,$ctx);
        if($resp === false){
            $log(sprintf(L('Batch_Request_Failed_Network_Timeout'), $idx+1),false);
            $allOk = false;
        }else{
            $log(sprintf(L('Batch_Return_Response'), $idx+1, $resp),true);
        }
        sleep(1);
    }

    if($allOk){
        $log(L('All_Batches_Submitted_Not_Guaranteed'),true);
    }else{
        $log(L('Some_Batches_Abnormal_Check_Network'),false);
        exit(1);
    }
    exit;
}
// ========== end IndexNow action ==========


// ==========================================
// 3. 准备视图数据
// ==========================================
if (!isset($static_config)) $static_config = [];

$defaults = [
    'edittools' => 'xheditor', 'openspeak' => 'open', 'speakArticle' => 'web',
    'speak_tts_list' => [[
        'key' => 'web', 'name' => L('Browser_Native_Speech'), 'handler' => 'web', 'enabled' => '1',
        'voice' => '', 'lang' => 'zh-CN', 'rate' => '1', 'method' => 'POST',
        'api_url' => '', 'api_key' => '', 'api_secret' => '', 'extra' => '', 'cmd' => '', 'cmd_workdir' => '',
    ]],
    'per_page' => 20, 'sccpost_page' => 10, 'sccpage_page' => 10,
    'sitelogoname' => 'all', 'sitemode' => 'blog'
];
foreach ($defaults as $key => $val) {
    if (!isset($qs_config[$key])) $qs_config[$key] = $val;
}

$staticDefaults = [
    // 如果你需要给 static 项设置默认值，在这里加
    // 例如：'static_per_page' => 20,
];
foreach ($staticDefaults as $key => $val) {
    if (!isset($static_config[$key])) $static_config[$key] = $val;
}


if (!empty($qs_config['tar_passwd'])) {
    $tar_passwd01 = L('password_has_been_set');
} else {
    $tar_passwd01 = L('password_no_set');
}

$defaults = array_merge($defaults, [
    'tar_name' => '', 'tar_directory' => '', 'des_directory' => '', 
    'tar_exclude_directory' => '', 'tar_email' => '', 'tar_other' => '',
    'backup_cron_on' => 'off', 'backup_cron_freq' => 'daily', 'backup_cron_time' => '03:00',
    'backup_cron_week' => '1', 'backup_cron_day' => '1', 'backup_keep' => '7',
    'backup_ftp_on' => 'off', 'backup_ftp_host' => '', 'backup_ftp_port' => '21',
    'backup_ftp_user' => '', 'backup_ftp_pass' => '', 'backup_ftp_dir' => '',
    'backup_ftp_ssl' => 'none', 'backup_ftp_pasv' => 'on'
]);


extract($qs_config);
extract($sp_config);
extract($static_config);

$site_name = $qs_config['site_name'] ?? '';
$user_name = $sp_config['user_name'] ?? '';
$admin_displayname = $sp_config['admin_displayname'] ?? '';
?>

<!-- ==========================================
     4. HTML 视图部分
     ========================================== -->
<div class="admin_page_name">
   <a href="set.php"><?= L('system_settings'); ?></a>
    <?php if (qs_can('user_manage')): ?>
    <a href="user/user_manage.php"><?= L('manage_user'); ?></a>
    <?php endif; ?>
  <?php if (qs_can('mail_setting')): ?>
  <a href="mail/mail_setting.php"><?= L('mail_setting'); ?></a>
  <?php endif; ?>
  <?php if (qs_can('picmanage')): ?>
  <a href="picmanage.php"><?= L('picmanage'); ?></a>
  <?php endif; ?>
  <?php if (qs_can('set_markdown')): ?>
  <a href="set_markdown.php"><?= L('Markdown'); ?></a>
  <?php endif; ?>
     <?php if (qs_can('multi_ssg') && ($qs_adv_config['post_api'] ?? 'close') == "open"): ?>
  <a href="set_multi_ssg.php"><?= L('Multi_Static_Site_Config'); ?></a>
    <?php endif; ?>
<?php if (qs_can('keyword_filter') && ($qs_adv_config['Keywords'] ?? 'close') == "open"): ?>
<a href="keywords/keywords_manage.php"><?= L('Keywords_Management');?></a>
<?php endif; ?>
  <?php if (qs_can('lang_editor') && ($qs_adv_config['langfileedit'] ?? 'close') == "open"): ?>
  <a href="lang_editor.php"><?= L('langfileedit'); ?></a>
    <?php endif; ?>
  <?php if (qs_can('theme_editor') && ($qs_adv_config['theme_editor'] ?? 'close') == "open"): ?>
  <a href="theme_code_editor.php"><?= L('theme_code_editor'); ?></a>
    <?php endif; ?>
    
</div>

<?php if ($message): ?>
    <div class="<?= $messageType === 'success' ? 'updated' : 'error'; ?>" style="padding:10px; margin:10px 0; border-radius:4px;">
        <?= $message; ?>
    </div>
<?php endif; ?>

<form action="" method="post" id="Big" enctype="multipart/form-data">
    <div id="fenye">
        <?php if (qs_can('set_basic')): ?><h3 class="active"><?= L('basic_parameters'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_basic')): ?><h3><?= L('Imagebed_Heading'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_articles')): ?><h3><?= L('Messages_Posts'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_account')): ?><h3><?= L('account_related'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><h3><?= L('extension_codes'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><h3><?= L('Footer_Privacy_Heading'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><h3><?= L('Payment_Heading'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><h3><?= L('CookieConsent_Heading'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_menu')): ?><h3><?= L('menu_control'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_link')): ?><h3><?= L('Friendship_Link_Settings'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_backend')): ?><h3><?= L('backend_control'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_backup')): ?><h3><?= L('backup_system'); ?></h3><?php endif; ?>
        <?php if (($qs_config['sitemode'] ?? '') == "novel" && qs_can('set_bookgenres')): ?>
        <h3><?= L('Book_genres'); ?></h3>
        <?php endif; ?>
        <?php if (qs_can('set_ssg')): ?><h3><?= L('Static_Site'); ?> (SSG)</h3><?php endif; ?>
        <?php if (qs_can('set_github')): ?><h3><?= L('GitHub_Pages_Sync'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_remote')): ?><h3><?= L('Remote_Article_Source_Sync'); ?></h3><?php endif; ?>
        <?php if (qs_can('set_api')): ?><h3><?= L('API_Interface_Config'); ?></h3><?php endif; ?>
        
        <?php if (qs_can('set_basic')): ?><ta style="display:block"><?php include("set_basicparameters.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_basic')): ?><ta table15><?php include("set_imagebed.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_articles')): ?><ta table2><?php include("set_articles.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_account')): ?><ta table3><?php include("set_account.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><ta table4><?php include("set_extensions.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><ta table14><?php include("set_footer_privacy.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><ta table17><?php include("set_payment.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_extensions')): ?><ta table16><?php include("set_cookie_consent.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_menu')): ?><ta table5><?php include("set_menu_manager.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_link')): ?><ta table6><?php include("set_link.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_backend')): ?><ta table7><?php include("set_backend.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_backup')): ?><ta table8><?php include("set_backup.php"); ?></ta><?php endif; ?>
        <?php if (($qs_config['sitemode'] ?? '') == "novel" && qs_can('set_bookgenres')): ?>
        <ta table9><?php include("set_bookgenres.php"); ?></ta>
        <?php endif; ?>
        <?php if (qs_can('set_ssg')): ?><ta table10><?php include("set_ssg.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_github')): ?><ta table11><?php include("set_github.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_remote')): ?><ta table13><?php include("set_remote_fetch.php"); ?></ta><?php endif; ?>
        <?php if (qs_can('set_api')): ?><ta table13><?php include("set_api.php"); ?></ta><?php endif; ?>

        <div id="fenye">
            <div class="small_form small_form2">
                <div class="field">
                    <input class="button" type="submit" name="save" onclick="submitAll()" value="<?= L('Save_Settings'); ?>" style="padding:10px 20px; font-size:16px; cursor:pointer;" />
                </div>
            </div>
        </div>
    </div>
</form>


<?php require 'foot.php'; ?>