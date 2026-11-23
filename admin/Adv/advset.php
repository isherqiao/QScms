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
 * @File         advset.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Advanced settings shell: loads the advanced configuration panels such as IndexNow and the embedded file manager.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
<?php 

$display_info = false;

$configList = [
    'enable_backup_mail_log' => [
        'title' => L('Backup_Mail'),
        'label' => L('Backup_Mail_Log'),
        'desc'  => L('Adv_Desc_BackupMail'),
    ],
    'mail_smtp_debug_global' => [
        'title' => L('SMTP_Mail_Debug'),
        'label' => L('Global_SMTP_Debug_Log'),
        'desc'  => L('SMTP_Debug_Default_Open_Tip'),
    ],
    'ai_api' => [
        'title' => L('AI_Function'),
        'label' => L('AI_API_Integration'),
        'desc'  => L('Adv_Desc_AiApi'),
    ],
    'enable_post_page_storage' => [
        'title' => L('Posts_Page_Storage'),
        'label' => L('Show_Storage_Settings'),
        'desc'  => L('Adv_Desc_PostPageStorage'),
    ],
    'enable_system_logs' => [
        'title' => L('system_logs'),
        'label' => L('Enable_System_Logs'),
        'desc'  => L('Adv_Desc_SystemLogs'),
    ],
    'github' => [
        'title' => L('GitHub_Pages_Upload_Sync'),
        'label' => L('Whether_Enable'),
        'desc'  => L('Adv_Desc_GithubSync'),
    ],
    'post_api' => [
        'title' => L('Enable_Post_API'),
        'label' => L('Enable_Post_API'),
        'desc'  => L('Adv_Desc_PostApi'),
    ],
    'Keywords' => [
        'title' => L('Keywords_Management'),
        'label' => L('Keywords_Management'),
        'desc'  => L('Adv_Desc_Keywords'),
    ],
    
    'remote_fetch' => [
        'title' => L('Enable_Remote_Fetch_API'),
        'label' => L('Operations'),
        'desc'  => L('Adv_Desc_RemoteFetch'),
    ],
    'ads' => [
        'title' => L('Ads_Function_Enable'),
        'label' => L('Operations'),
        'desc'  => L('Adv_Desc_Ads'),
    ],
    'langfileedit' => [
        'title' => L('langfileedit'),
        'label' => L('Operations'),
        'desc'  => L('Adv_Desc_LangFileEdit'),
    ],
    'markdown' => [
        'title' => L('Markdown_Operations'),
        'label' => L('Operations'),
        'desc'  => L('Adv_Desc_Markdown'),
    ],
    'theme_editor' => [
        'title' => L('theme_editor'),
        'label' => L('Theme_Editor_Switch'),
        'desc'  => L('Adv_Desc_ThemeEditor'),
    ],
    'post_translate' => [
        'title' => L('Post_Translate_Switch'),
        'label' => L('Whether_Enable'),
        'desc'  => L('Post_Translate_Switch_Desc'),
    ],
];

if (isset($_POST['save'])) {
    $old_cfg = $qs_adv_config;

    foreach ($configList as $key => $item) {
        $rawVal = $_POST[$key] ?? 'close';
        $qs_adv_config[$key] = in_array($rawVal, ['open','close']) ? $rawVal : 'close';
    }

    $code = "<?php\n\$qs_adv_config = ".var_export($qs_adv_config, true).";\n";
    file_put_contents($datapaths . $advcconfig, $code);
    $display_info = true;

    $log_text = [];
    $map = [
    'enable_backup_mail_log' => L('Backup_Mail_Log'),
    'mail_smtp_debug_global' => L('Smtp_Debug_Global_Switch_Name'),
    'enable_post_page_storage' => L('Posts_Page_Storage_Name'),
    'enable_system_logs' => L('system_logs'),
    'github' => L('Github_Sync_Name'),
    'post_api' => 'PostAPI',
    'remote_fetch' => 'RemoteFetch',
    'post_translate' => L('Post_Translate_Switch')
];
    foreach($map as $k=>$name){
        if( ($old_cfg[$k] ?? 'close') !== $qs_adv_config[$k] ){
            $log_text[] = "{$name}：".($old_cfg[$k]??'close')." → {$qs_adv_config[$k]}";
        }
    }
    $desc = empty($log_text) ? L('Adv_Config_Nothing_Changed') : sprintf(L('Adv_Config_Changed_Diff'), implode(' | ',$log_text));
}
?>
<div class="admin_page_name">
  <a href="advset.php"><?= L('Advanced_Maintenance_Mode'); ?></a>
  <a href="indexnow.php"><?= L('IndexNow'); ?></a>
</div>

<?php if ($display_info): ?>
<div class="updated"><?= L('Saved_Already'); ?></div>
<?php 
write_operate_log(qs_get_current_admin_name(), 'save_adv_config', L('Adv_Settings_Saved'), $desc);
endif; ?>

<div id="mail">
 <div id="fenye">
  <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <div class="settings-grid">
        <?php foreach ($configList as $fieldKey => $cfgItem): ?>
        <?php $isOpen = (($qs_adv_config[$fieldKey] ?? 'close') === 'open'); ?>
        <div class="setting-card">
          <h4 class="card-title"><?= $cfgItem['title'] ?></h4>
          <div class="card-body">
            <div class="label"><?= $cfgItem['label'] ?></div>
            <div class="switch-row">
                <label class="switch">
                  <input type="checkbox" class="switch-checkbox" data-name="<?= $fieldKey ?>" <?= $isOpen ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </label>
                <input type="hidden" name="<?= $fieldKey ?>" value="<?= $isOpen ? 'open' : 'close' ?>" class="switch-input">
            </div>
            <div class="info"><?= $cfgItem['desc'] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
     <div id="fenye">
        <div class="field">
        <div class="small_form small_form2">
            <input class="button" type="submit" name="save" value="<?= L('Save_Settings'); ?>" />
        </div>
        </div>
    </div>
  </form>
 </div>
</div>

<script>
document.querySelectorAll('.switch-checkbox').forEach(function(ck){
    ck.addEventListener('change', function(){
        var name = this.getAttribute('data-name');
        // 修复选择器：属性选择器与class选择器之间不加任何字符，正确写法如下
        var hiddenInput = document.querySelector('input[name="' + name + '"].switch-input');
        if(hiddenInput){
            hiddenInput.value = this.checked ? 'open' : 'close';
        }
    });
});
</script>

<?php require_once __DIR__.'/../foot.php'; ?>
