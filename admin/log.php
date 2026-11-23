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
 * @File         log.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Operation / login log viewer: type-aware search filters and a clear-log action.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
if(!is_developer()){
    qs_404();
}
$act = $_GET['act'] ?? 'login';
$logFile = '';
$logTitle = '';
$list = [];

//操作类型中文映射，修复原有重复键、文案错误
$type_map = [
    'save_config'=>L('Save_Basic_Config'),
    'save_dev_config'=>L('Save_Dev_Config'),
    'post_save'=>L('Save_Article'),
    'post_draft'=>L('Article_Moved_To_Draft'),
    'post_delete'=>L('Article_Permanently_Deleted'),
    'post_trash'=>L('Article_Moved_To_Recycle_Bin'),
    'post_revert'=>L('Restore_Article_From_Recycle_Bin'),
    'page_trash'=>L('Page_Moved_To_Recycle_Bin'),
    'page_delete'=>L('Page_Permanently_Deleted'),
    'page_revert'=>L('Restore_Page_From_Recycle_Bin'),
    'page_to_draft'=>L('Page_To_Draft'),
    'page_draft_publish'=>L('Page_Draft_To_Publish'),
    'post_open_edit'=>L('Open_Article_Edit_Page'),
    'edit_article'=>L('Edit_Article'),
    'del_article'=>L('Delete_Article'),
    'status_article'=>L('Toggle_Article_Status'),
    'add_page'=>L('Add_Static_Page'),
    'edit_page'=>L('Edit_Static_Page'),
    'del_page'=>L('Delete_Static_Page'),
    'post_base64_decode'=>L('Perform_Base64_Decode'),
    'post_base64_encode'=>L('Perform_Base64_Encode'),
    'admin_change_username'=>L('Change_Admin_Username'),
    'admin_change_password'=>L('Change_Admin_Password'),
    'admin_change_github_token'=>L('Change_GitHub_Token'),
];

if($act === 'login'){
    $logFile = SITE_ROOT.'/control/data/logs/login_log.php';
    $logTitle = L('Login_Log');
    if(file_exists($logFile)){
        require $logFile;
        $list = $login_log ?? [];
    }
}elseif($act === 'operate'){
    $logFile = SITE_ROOT.'/control/data/logs/operate_log.php';
    $logTitle = L('Log_operate');
    if(file_exists($logFile)){
        require $logFile;
        $list = $operate_log ?? [];
    }
}else{
    qs_404();
}

//清空日志提交
if(isset($_POST['clear_log']) && $_POST['clear_log'] == 1 && !empty($logFile)){
    if($act === 'login'){
        $init_code = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$login_log = [];\n";
    }else{
        $init_code = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$operate_log = [];\n";
    }
    file_put_contents($logFile,$init_code);
    header('Location:log.php?act='.$act);
    exit;
}

// ==========搜索过滤（按日志类型区分检索字段，彻底消除登录日志缺字段报错）==========
$keyword = trim($_GET['keyword'] ?? '');
$filteredList = [];
if($keyword === ''){
    $filteredList = $list;
}else{
    foreach($list as $row){
        if($act === 'login'){
            //登录日志专属检索字段：时间、账号、登录状态、IP、备注
            $searchStr = implode(' ', [
                date('Y-m-d H:i:s',$row['time']),
                $row['username'] ?? '',
                $row['status']==1 ? L('Success') : L('Failed'),
                $row['ip'] ?? '',
                $row['msg'] ?? ''
            ]);
        }else{
            //操作日志专属检索字段：时间、操作人、类型、对象、IP、备注
            $opTypeVal = $type_map[$row['type'] ?? ''] ?? ($row['type'] ?? L('Unknown_Operation'));
            $searchStr = implode(' ', [
                date('Y-m-d H:i:s',$row['time']),
                $row['username'] ?? '',
                $opTypeVal,
                $row['target'] ?? '',
                $row['ip'] ?? '',
                $row['msg'] ?? ''
            ]);
        }
        if(stripos($searchStr, $keyword) !== false){
            $filteredList[] = $row;
        }
    }
}

// ==========分页逻辑==========
$pageSize = 20;
$totalCount = count($filteredList);
$totalPage = $totalCount > 0 ? ceil($totalCount / $pageSize) : 1;
$page = max(1, min(intval($_GET['page'] ?? 1), $totalPage));
$offset = ($page - 1) * $pageSize;
$pageData = array_slice($filteredList, $offset, $pageSize);

$pageQuery = http_build_query([
    'act' => $act,
    'keyword' => $keyword
]);
?>
<div class="main">
    <div class="box">
        <div class="title"><?=htmlspecialchars($logTitle)?></div>
        <div style="margin-bottom:12px;">
            <a href="log.php?act=login"><?= L('Login_Log'); ?></a> |
            <a href="log.php?act=operate"><?= L('Log_operate'); ?></a>
            <form method="get" style="display:inline;margin-left:20px;">
                <input type="hidden" name="act" value="<?=htmlspecialchars($act)?>">
                <input type="text" name="keyword" placeholder="<?= L('Keyword_Search_Placeholder'); ?>" value="<?=htmlspecialchars($keyword)?>" style="padding:3px 6px;width:200px;">
                <button type="submit"><?= L('Search'); ?></button>
                <?php if($keyword):?>
                    <a href="log.php?act=<?=htmlspecialchars($act)?>" style="margin-left:8px;"><?= L('Reset_Button'); ?></a>
                <?php endif;?>
            </form>
            <form method="post" style="display:inline;margin-left:12px;" onsubmit="return confirm('<?= L('Confirm_Clear_All_Logs'); ?>')">
                <input type="hidden" name="clear_log" value="1">
                <button type="submit"><?= L('Clear_Logs_Button'); ?></button>
            </form>
        </div>
        <div style="margin:8px 0;color:#666;">
            <?= sprintf(L('Pagination_Record_Info'), $totalCount, $page, $totalPage); ?>
        </div>
        <table class="table" width="100%" cellpadding="6" cellspacing="0">
            <tr>
                <th><?= L('Date_Time'); ?></th>
                <?php if($act === 'login'){ ?>
                <th><?= L('Account'); ?></th>
                <th><?= L('Login_Status'); ?></th>
                <?php }else{ ?>
                <th><?= L('Operator_Name'); ?></th>
                <th><?= L('Operation_Type'); ?></th>
                <th><?= L('Operation_Target'); ?></th>
                <?php } ?>
                <th>IP</th>
                <th><?= L('Remark_Info'); ?></th>
            </tr>
            <?php if(empty($pageData)){ ?>
            <tr>
                <td colspan="6" style="text-align:center;color:#888;"><?= L('No_Log_Records'); ?></td>
            </tr>
            <?php } ?>
            <?php foreach ($pageData as $row): ?>
            <tr>
                <td><?=htmlspecialchars(date('Y-m-d H:i:s',$row['time']))?></td>
                <?php if($act === 'login'){ ?>
                <td><?=htmlspecialchars($row['username'] ?? '')?></td>
                <td>
                    <?php if(($row['status'] ?? 0) == 1):?>
                    <span style="color:#090;"><?= L('Success'); ?></span>
                    <?php else:?>
                    <span style="color:#c00;"><?= L('Failed'); ?></span>
                    <?php endif;?>
                </td>
                <?php }else{ ?>
                <td><?=htmlspecialchars($row['username'] ?? '')?></td>
                <td><?=htmlspecialchars($type_map[$row['type'] ?? ''] ?? ($row['type'] ?? L('Unknown_Operation')))?></td>
                <td><?=htmlspecialchars($row['target'] ?? '')?></td>
                <?php } ?>
                <td><?=htmlspecialchars($row['ip'] ?? '')?></td>
                <td><?=htmlspecialchars($row['msg'] ?? '')?></td>
            </tr>
            <?php endforeach;?>
        </table>
        <div style="margin-top:10px;display:flex;gap:8px;align-items:center;">
            <?php if($page > 1):?>
                <a href="?<?=$pageQuery?>&page=<?=$page-1?>"><?= L('Previous_Page'); ?></a>
            <?php endif;?>
            <?php for($i=1;$i<=$totalPage;$i++):?>
                <?php if($i==$page):?>
                    <strong><?=$i?></strong>
                <?php else:?>
                    <a href="?<?=$pageQuery?>&page=<?=$i?>"><?=$i?></a>
                <?php endif;?>
            <?php endfor;?>
            <?php if($page < $totalPage):?>
                <a href="?<?=$pageQuery?>&page=<?=$page+1?>"><?= L('Next_Page'); ?></a>
            <?php endif;?>
        </div>
    </div>
</div>
<?php require 'foot.php'; ?>
