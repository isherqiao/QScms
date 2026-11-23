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
 * @File         ads.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 02/09/26 17:39:56
 * @Description  Advertisement list page: enable, disable, edit and delete the ad entries stored in control/data.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
$adsFile = $datapaths . $adconfig;
if (!file_exists($adsFile)) {
    file_put_contents($adsFile, '<?php return [];');
}
$ads = require $adsFile;
uasort($ads, function ($a, $b) {
    return $a['sort'] - $b['sort'];
});
// 删除广告
if(isset($_GET['act']) && $_GET['act']==='del' && !empty($_GET['id'])){
    $id = trim($_GET['id']);
    if(isset($ads[$id])){
        unset($ads[$id]);
        file_put_contents($adsFile, '<?php return '.var_export($ads,true).';');
        writeAdJsFile($id, null);
    }
    header('Location: ads.php');
    exit;
}

// 切换启用禁用
if(isset($_GET['act']) && $_GET['act']==='toggle' && !empty($_GET['id'])){
    $id = trim($_GET['id']);
    if(isset($ads[$id])){
        $ads[$id]['enable'] = !$ads[$id]['enable'];
        file_put_contents($adsFile, '<?php return '.var_export($ads,true).';');
        writeAdJsFile($id, $ads[$id]);
    }
    header('Location: ads.php');
    exit;
}

?>
    <div class="admin_page_name">
    		<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    	<a href="ads.php"  <?php echo $page_file == 'ads.php' ? 'class="current"' : ''; ?>><?= L('Ads_Manage_List'); ?></a>
    	  <a href="set_ads.php"  <?php echo $page_file == 'set_ads.php' ? 'class="current"' : ''; ?>><?= L('Ads_Function_Settings'); ?></a>
    	</div>

    <div id="fenye">
    	    	<ta style="display:block">
        <div class="container">
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table>
                <tr>
                    <th><?= L('Ad_ID'); ?></th>
                    <th><?= L('Ad_Name'); ?></th>
                    <th><?= L('Ad_Effective_Scope'); ?></th>
                    <th><?= L('Status'); ?></th>
                    <th><?= L('Sort_Order'); ?></th>
                    <th><?= L('Update_Time'); ?></th>
                    <th><?= L('Operations'); ?></th>
                </tr>
                <?php if(empty($ads)){ ?>
                <tr>
                    <td colspan="7"><?= L("No_Ad_Data"); ?></td>
                </tr>
                <?php }else foreach ($ads as $item){ ?>
                <?php
                    $scope = $item['ad_scope'] ?? 'both';
                    $scopeTextMap = [
                        'theme' => L('Scope_Template_Ad'),
                        'post'  => L('Scope_In_Article_Ad'),
                        'both'  => L('Scope_Both')
                    ];
                    $scopeText = $scopeTextMap[$scope] ?? L('Scope_Both');
                ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($item['id']); ?></code></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $scopeText; ?></td>
                    <td>
                        <?php if($item['enable']){ ?>
                            <a href="?act=toggle&id=<?php echo $item['id']; ?>"><?= L('Enable_Status'); ?></a>
                        <?php }else{ ?>
                            <a href="?act=toggle&id=<?php echo $item['id']; ?>"><?= L('Disable_Status'); ?></a>
                        <?php } ?>
                    </td>
                    <td><?php echo intval($item['sort']); ?></td>
                    <td><?php echo htmlspecialchars($item['mtime']); ?></td>
                    <td>
                        <a href="ads_edit.php?id=<?php echo $item['id']; ?>"><?= L('Edit'); ?></a>
                        |
                        <a href="?act=del&id=<?php echo $item['id']; ?>" onclick="return confirm('<?= L('Confirm_Delete_Ad_Tag_Render_Empty'); ?>')"><?= L('Delete'); ?></a>
                    </td>
                </tr>
                <?php } ?>
            </table>
            </div>
            <div class="pagination"></div>
            <a href="ads_edit.php"><?= L("New_Ad"); ?></a>
        </div>
    </div>
    <div class="clear"></div>
    <div style="margin:10px 0;font-size:13px;color:#666;">
        <?= L("Ad_Tag_Usage_Label"); ?><code>{{ad:广告ID}}</code>
    </div>

<?php require 'foot.php'; ?>
