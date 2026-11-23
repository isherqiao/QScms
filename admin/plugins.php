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
 * @File         plugins.php
 * @CreatedTime  15/09/19 04:02:00
 * @LastEditTime 16/09/26 12:00:39
 * @Description  Plugin manager: lists the installed plugins, toggles them on or off and maintains the blacklist metadata file.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
// ========== 黑名单元数据文件 control/data/plugin_status.php ==========
$statusFile = $datapaths . 'plugin_status.php';
//读取黑名单
$pluginStatus = ['fault' => []];
if (file_exists($statusFile)) {
    $pluginStatus = require $statusFile;
}
$faultList = $pluginStatus['fault'];
//保存黑名单辅助函数
function save_plugin_status(array $pluginStatus, string $statusFile): void
{
    $out = "<?php\nreturn " . var_export($pluginStatus, true) . ";\n";
    $tmp = $statusFile . '.tmp';
    file_put_contents($tmp, $out);
    rename($tmp, $statusFile);
}
// ========== 黑名单清除操作 ==========
if (isset($_POST['clear_fault_key']) && !empty($_POST['clear_fault_key'])) {
    $k = $_POST['clear_fault_key'];
    if (isset($pluginStatus['fault'][$k])) {
        unset($pluginStatus['fault'][$k]);
        save_plugin_status($pluginStatus, $statusFile);
        write_operate_log(qs_get_current_admin_name(), sprintf(L('Log_Clear_Plugin_Fault_Mark'), $k), L('Log_Target_Plugin_Settings'), 'plugins.php');
    }
}
if (isset($_POST['clear_all_fault'])) {
    $pluginStatus['fault'] = [];
    save_plugin_status($pluginStatus, $statusFile);
    write_operate_log(qs_get_current_admin_name(), L('Log_Clear_All_Plugin_Fault_Marks'), L('Log_Target_Plugin_Settings'), 'plugins.php');
}
// ========== PHP8 捕获运行时致命错误，记录黑名单 ==========
$currentProcessingPluginKey = null;
register_shutdown_function(function () use (&$currentProcessingPluginKey, &$pluginStatus, $statusFile) {
    $err = error_get_last();
    if ($err !== null && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if ($currentProcessingPluginKey !== null) {
            $pluginStatus['fault'][$currentProcessingPluginKey] = sprintf(L('Runtime_Fatal_Error_Message'), $err['message']);
            save_plugin_status($pluginStatus, $statusFile);
            write_operate_log(qs_get_current_admin_name(), sprintf(L('Log_Plugin_Fatal_Error_Blacklisted'), $currentProcessingPluginKey, $err['message']), L('Log_Target_Plugin_Settings'), 'plugins.php');
        }
    }
});
// ========== 扫描插件目录 + 前置校验：必须同时存在 admin.php config.php ==========
$basePluginPath = SITE_ROOT . '/files/plugins/';
$systemDirs = glob($basePluginPath . 'System/*/', GLOB_ONLYDIR);
$userDirs = glob($basePluginPath . 'User/*/', GLOB_ONLYDIR);
$allPluginDirs = array_merge((array)$systemDirs, (array)$userDirs);
$validPlugins = [];
foreach ($allPluginDirs as $dir) {
    $pluginKey = rtrim(str_replace($basePluginPath, '', $dir), '/');
    if (isset($faultList[$pluginKey])) {
        continue;
    }
    $adminF = $dir . 'admin.php';
    $configF = $dir . 'config.php';
    if (!is_file($adminF) || !is_file($configF)) {
        $pluginStatus['fault'][$pluginKey] = L('Plugin_Broken_Missing_Admin_Config');
        write_operate_log(qs_get_current_admin_name(), sprintf(L('Log_Broken_Plugin_Missing_Necessary_Files'), $pluginKey), L('Log_Target_Plugin_Settings'), 'plugins.php');
        save_plugin_status($pluginStatus, $statusFile);
        continue;
    }
    $validPlugins[] = [
        'key'    => $pluginKey,
        'dir'    => $dir,
        'admin'  => $adminF,
        'config' => $configF
    ];
}
// ========== POST保存配置，原版行为：执行config.php，既拼装文本写盘，也更新当前内存变量；不跳转，本页直接渲染 ==========
$display_info = false;
if (isset($_POST['save'])) {
    // 还原原版 checkbox 提交形态：关闭的开关不提交
    foreach ($_POST as $k => $v) {
        if ($k === 'save') continue;
        if ($v === 'close') unset($_POST[$k]);
    }
    $plugin_Content = "<?php\n";
    foreach ($validPlugins as $p) {
        $currentProcessingPluginKey = $p['key'];
        require $p['config'];
    }
    $plugin_Content .= "?>";
    //原子写入临时文件再rename，防止配置半截损坏
    $tmpConf = $datapaths . $plugin_conf . '.tmp';
    file_put_contents($tmpConf, $plugin_Content);
    rename($tmpConf, $datapaths . $plugin_conf);
    write_operate_log(qs_get_current_admin_name(), L('Log_Plugin_Save_Config'), L('Log_Target_Plugin_Settings'), 'plugins.php');
    $display_info = true;
}
?>
<div class="admin_page_name">
<?= L('Plugins_Panel');?>
</div>
<?php if ($display_info) { ?>
    <div class="updated">
<?= L('Operation_successful');?>
    </div>
<?php } ?>
<?php
// ========== 黑名单管理面板 ==========
if (!empty($pluginStatus['fault'])) {
?>
<div class="small_form small_form2" style="border:1px solid #f8baba;background:#fff6f6;padding:10px;margin-bottom:15px;">
    <h4 style="margin-top:0;"><?= L('Fault_Blacklist_Plugins_Heading'); ?></h4>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']; ?>">
    <?php foreach ($pluginStatus['fault'] as $faultKey => $faultMsg): ?>
        <div style="padding:4px 0;border-bottom:#ddd 1px dotted;">
            <strong><?= htmlspecialchars($faultKey) ?></strong>
            <div style="font-size:12px;color:#666;"><?= htmlspecialchars($faultMsg) ?></div>
            <button type="submit" name="clear_fault_key" value="<?= htmlspecialchars($faultKey) ?>" style="margin-top:4px;"><?= L('Clear_This_Fault_Mark'); ?></button>
        </div>
    <?php endforeach; ?>
    <div style="margin-top:8px;">
        <button type="submit" name="clear_all_fault" onclick="return confirm('<?= L('Confirm_Clear_All_Fault_Marks'); ?>')"><?= L('Clear_All_Fault_Marks_Button'); ?></button>
    </div>
    </form>
</div>
<?php } ?>
<script language="javascript" src="set.js"></script>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <div id="fenye">
        <h3 class="active"><?= L('0E1'); ?></h3>
        <h3><?= L('0E2'); ?></h3>
        <ta style="display:block">
            <div class="small_form small_form2">
                <b><?= L('0E1'); ?></b>
                <div class="clear"></div>
                <div class="field">
                    <div class="label"><?= L('Name'); ?></div>
                    <div class="label"><?= L('Switch_Label'); ?></div>
                    <div class="info"><?= L('Description_Label'); ?></div>
                </div>
                <div class="clear"></div>
<?php
//渲染System有效插件
foreach ($validPlugins as $p) {
    if (str_starts_with($p['key'], 'System/')) {
        ob_start();
        include $p['admin'];
        $pluginHtml = ob_get_clean();
        //把旧的 checkbox 替换成 advset 的switch html，读取原checked状态
        $pluginHtml = preg_replace_callback(
            '/<input[^>]*type=["\']checkbox["\'][^>]*name=["\']([^"\']+)["\']([^>]*?)>/is',
            function($m){
                $name = $m[1];
                $attr = $m[2];
                $isChecked = stripos($attr, 'checked') !== false;
                $checkedAttr = $isChecked ? ' checked' : '';
                $hiddenVal  = $isChecked ? 'on' : 'close';
                return '<label class="switch"><input type="checkbox" class="switch-checkbox" data-name="'.$name.'"'.$checkedAttr.'><span class="slider"></span></label><input type="hidden" name="'.$name.'" class="switch-input" value="'.$hiddenVal.'">';
            },
            $pluginHtml
        );
        echo $pluginHtml;
    }
}
?>
            </div>
        </ta>
        <ta table2>
            <div class="small_form small_form2">
                <b><?= L('0E2'); ?></b>
                <div class="clear"></div>
                <div class="field">
                    <div class="label"><?= L('Name'); ?></div>
                    <div class="label"><?= L('Switch_Label'); ?></div>
                    <div class="info"><?= L('Description_Label'); ?></div>
                </div>
<?php
//渲染User有效插件
foreach ($validPlugins as $p) {
    if (str_starts_with($p['key'], 'User/')) {
        ob_start();
        include $p['admin'];
        $pluginHtml = ob_get_clean();
        //把旧的 checkbox 替换成 advset 的switch html，读取原checked状态
        $pluginHtml = preg_replace_callback(
            '/<input[^>]*type=["\']checkbox["\'][^>]*name=["\']([^"\']+)["\']([^>]*?)>/is',
            function($m){
                $name = $m[1];
                $attr = $m[2];
                $isChecked = stripos($attr, 'checked') !== false;
                $checkedAttr = $isChecked ? ' checked' : '';
                $hiddenVal  = $isChecked ? 'on' : 'close';
                return '<label class="switch"><input type="checkbox" class="switch-checkbox" data-name="'.$name.'"'.$checkedAttr.'><span class="slider"></span></label><input type="hidden" name="'.$name.'" class="switch-input" value="'.$hiddenVal.'">';
            },
            $pluginHtml
        );
        echo $pluginHtml;
    }
}
?>
            </div>
        </ta>
        <div class="clear"></div>
        <div id="fenye">
            <div  class="small_form small_form2">
                <div  class="field" >
                    <input class="button" type="submit" name="save" value="<?= L('Save_Settings'); ?>" />
                </div>
            </div>
        </div>
    </div>
</form>
<!-- 直接复制advset.php的开关联动JS，其余全部不动 -->
<script>
document.querySelectorAll('.switch-checkbox').forEach(function(ck){
    ck.addEventListener('change', function(){
        // 替换dataset，兼容IE11
        var name = this.getAttribute('data-name');
        // 不用ES6模板字符串，改用字符串拼接
        var hiddenInput = document.querySelector('input[name="' + name + '"].switch-input');
        if(hiddenInput){
            hiddenInput.value = this.checked ? 'open' : 'close';
        }
    });
});
</script>
<?php require 'foot.php'; ?>
</div>
