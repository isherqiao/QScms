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
 * @File         set_ads.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 02/09/26 17:39:56
 * @Description  Advertisement settings panel: global advertisement configuration values.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

$adsConfigFile = $datapaths . 'ads_config.php';
if (!file_exists($adsConfigFile)) {
    file_put_contents($adsConfigFile, '<?php return [];');
}
$adsConfig = require $adsConfigFile;

// 配置默认值
if (!isset($adsConfig['ad_all_enable']))     $adsConfig['ad_all_enable'] = true;
if (!isset($adsConfig['template_ad_enable']))$adsConfig['template_ad_enable'] = true;
if (!isset($adsConfig['article_ad_enable'])) $adsConfig['article_ad_enable'] = true;

$message = '';
$messageType = '';

//本页POST保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ad_setting'])) {
    $adsConfig['ad_all_enable']      = !empty($_POST['ad_all_enable']);
    $adsConfig['template_ad_enable'] = !empty($_POST['template_ad_enable']);
    $adsConfig['article_ad_enable']  = !empty($_POST['article_ad_enable']);

    $writeCode = "<?php\nreturn ".var_export($adsConfig,true).";\n";
if(file_put_contents($adsConfigFile, $writeCode)){
    $message = L('Ads_Settings_Saved_Rebuild_SSG');
    $messageType = 'success';
    if(function_exists('write_operate_log')){
        write_operate_log(qs_get_current_admin_name(),'save_ad_setting', L('Ads_Function_Settings'), L('Ad_Module_Switch_Config_Modified'));
    }

    global $datapaths, $adconfig;
    $adsFile = $datapaths . $adconfig;
    $ads = [];
    if(file_exists($adsFile)){
        $ads = require $adsFile;
    }
    if(is_array($ads)){
        foreach($ads as $aid=>$adItem){
            writeAdJsFile($aid,$adItem);
        }
    }

}else{
    $message = L('Save_Failed_Check_Dir_Permission');
    $messageType = 'error';
}

}
?>

    <div class="admin_page_name">
    		<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
    	<a href="ads.php"  <?php echo $page_file == 'ads.php' ? 'class="current"' : ''; ?>><?= L('Ads_Manage_List'); ?></a>
    	  <a href="set_ads.php"  <?php echo $page_file == 'set_ads.php' ? 'class="current"' : ''; ?>><?= L('Ads_Function_Settings'); ?></a>
    	</div>

<?php if ($message): ?>
    <div class="<?= $messageType === 'success' ? 'updated' : 'error'; ?>" style="padding:10px; margin:10px 0; border-radius:4px;">
        <?= $message; ?>
    </div>
<?php endif; ?>

    <div id="fenye">
        <ta style="display:block">
        <div class="container">
            <form method="post" action="">
                <table>
                    <tr>
                        <td style="width:240px;"><?= L('Ads_Master_Switch_Label'); ?></td>
                        <td>
                            <label>
                                <input type="checkbox" name="ad_all_enable" value="1"
                                <?php echo $adsConfig['ad_all_enable'] ? 'checked' : ''; ?>>
                                <?= L('Enable_All_Ads') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td><?= L('Template_Ad_Label'); ?></td>
                        <td>
                            <label>
                                <input type="checkbox" name="template_ad_enable" value="1"
                                <?php echo $adsConfig['template_ad_enable'] ? 'checked' : ''; ?>>
                                <?= L('Enable_Template_Ads') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td><?= L('In_Article_Ad_Label'); ?></td>
                        <td>
                            <label>
                                <input type="checkbox" name="article_ad_enable" value="1"
                                <?php echo $adsConfig['article_ad_enable'] ? 'checked' : ''; ?>>
                                <?= L('Enable_In_Article_Ads') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" name="save_ad_setting" value="<?= L('Save_Settings') ?>">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div class="clear"></div>
    <div style="margin:10px 0;font-size:13px;color:#666;">
        <?= L('Ads_Master_Switch_Desc') ?>
    </div>
<?php require 'foot.php'; ?>
