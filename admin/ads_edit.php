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
 * @File         ads_edit.php
 * @CreatedTime  30/08/26 10:21:15
 * @LastEditTime 30/08/26 10:21:15
 * @Description  Advertisement editor: creates or updates a single advertisement record.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
$adsFile = $datapaths . $adconfig;
if (!file_exists($adsFile)) {
    file_put_contents($adsFile, '<?php return [];');
}
$ads = require $adsFile;
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$data = [
    'id' => '',
    'name' => '',
    'ad_code' => '',
    'enable' => true,
    'sort' => 1,
    'ctime' => date('Y-m-d H:i:s'),
    'mtime' => date('Y-m-d H:i:s'),
    'ad_scope' => 'both'
];
if ($id !== '' && isset($ads[$id])) {
    $data = $ads[$id];
    //旧广告没有该字段，补默认值
    if(!isset($data['ad_scope'])){
        $data['ad_scope'] = 'both';
    }
}
// POST保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $adcode = $_POST['ad_code'];
    $enable = !empty($_POST['enable']);
    $sort   = intval($_POST['sort']);
    //读取生效范围，并校验合法值
    $ad_scope = $_POST['ad_scope'] ?? 'both';
    if(!in_array($ad_scope,['theme','post','both'])){
        $ad_scope = 'both';
    }
    //编辑模式：沿用原有id，不生成
    if ($id !== '') {
        $newId = $id;
    } else {
        //=====新建：自动生成 ad_N 自增ID =====
        $maxNum = 0;
        foreach(array_keys($ads) as $k){
            if(str_starts_with($k,"ad_")){
                $num = intval(substr($k,3));
                if($num>$maxNum) $maxNum = $num;
            }
        }
        $maxNum++;
        $newId = "ad_".$maxNum;
    }
    $now = date('Y-m-d H:i:s');
    $save = [
        'id'=>$newId,
        'name'=>$name,
        'ad_code'=>$adcode,
        'enable'=>$enable,
        'sort'=>$sort,
        'ctime'=>$id==='' ? $now : $data['ctime'],
        'mtime'=>$now,
        'ad_scope' => $ad_scope
    ];
$ads[$newId] = $save;
file_put_contents($adsFile, '<?php return '.var_export($ads,true).';');
writeAdJsFile($newId, $save);
header('Location: ads.php');
exit;

}
?>
    <div class="admin_page_name"><?php echo $id=='' ? L('New_Ad') : L('Edit_Ad'); ?></div>
    <div id="fenye">
        <div class="container">
            <form method="post">
                <table>
                    <?php if($id!==''){ ?>
                    <tr>
                        <td style="width:140px"><?= L("Ad_ID"); ?></td>
                        <td>
                            <input type="text" value="<?php echo htmlspecialchars($data['id']); ?>" readonly style="width:350px;">
                            <span><?= L("Not_Editable_Bracket"); ?></span>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?= L("Ad_Name"); ?></td>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($data['name']); ?>" required style="width:350px;"></td>
                    </tr>
                    <tr>
                        <td><?= L("Ad_HTML_Code"); ?></td>
                        <td><textarea name="ad_code" rows="14" style="width:98%;"><?php echo htmlspecialchars($data['ad_code']); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><?= L("Ad_Effective_Scope"); ?></td>
                        <td>
                            <label><input type="radio" name="ad_scope" value="theme" <?php if($data['ad_scope']==='theme') echo 'checked'; ?>><?= L("Ad_Scope_Template_Detail"); ?></label>
                            &nbsp;&nbsp;
                            <label><input type="radio" name="ad_scope" value="post" <?php if($data['ad_scope']==='post') echo 'checked'; ?>><?= L("Ad_Scope_Post_Detail"); ?></label>
                            &nbsp;&nbsp;
                            <label><input type="radio" name="ad_scope" value="both" <?php if($data['ad_scope']==='both') echo 'checked'; ?>><?= L("Scope_Both"); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td><?= L("Enable_Ad"); ?></td>
                        <td><input type="checkbox" name="enable" <?php echo $data['enable']?'checked':''; ?>><?= L("Check_To_Enable"); ?></td>
                    </tr>
                    <tr>
                        <td><?= L("Sort_Number"); ?></td>
                        <td><input type="number" name="sort" value="<?php echo intval($data['sort']); ?>" style="width:120px;"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" value="<?= L("Save"); ?>">
                            <a href="ads.php" style="margin-left:15px;"><?= L("Back_To_List"); ?></a>
                        </td>
                    </tr>
                </table>
                <?php if($id!==''){ ?>
                <div style="margin-top:12px;font-size:13px;color:#666;">
                    <?= L("Placeholder_Tag_Example"); ?><code>{{ad:<?php echo $data['id'] ?>}}</code>
                </div>
                <?php } ?>
            </form>
            <?php if($id===''){ ?>
            <div style="margin-top:12px;font-size:13px;color:#666;">
                <?= L("New_Ad_Auto_ID_Tip"); ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    
<?php require 'foot.php'; ?>
