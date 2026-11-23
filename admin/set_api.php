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
 * @File         set_api.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 12/11/26 09:15:00
 * @Description  Post API and AI API settings panel: manages several API endpoint configurations and shows the saved entries.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_postapi.php｜PostAPI配置
$post_api = $qs_adv_config['post_api'] ?? 'close';
$AI_api = $qs_adv_config['ai_api'] ?? 'close';
$postapi_site_secret  = $qs_config['postapi_site_secret'] ?? '';

// 多条AI API配置数组
$ai_api_list = $qs_config['ai_api_list'] ?? [];
?>
<div class="small_form small_form2">
    <b><?= L('PostAPI_Config') ?></b>
    
    <?php if ($post_api === 'open'): ?>
    <div class="field">
        <div class="label"><?= L('API_Work_Mode') ?></div>
        <div class="price">
            <select name="post_api" class="input">
                <option value="close" <?= $post_api==='close' ? 'selected' : '' ?>><?= L('close') ?></option>
                <option value="open" <?= $post_api==='open' ? 'selected' : '' ?>><?= L('Open_Local_Readonly_API') ?></option>
            </select>
        </div>
        <div class="info"><?= L('Open_Local_Readonly_API_Desc') ?></div>
        <div class="clear"></div>
    </div>
    <div class="field">
        <div class="label"><?= L('Local_Site_Key') ?></div>
        <div class="price" style="display:flex;gap:8px;align-items:center;">
            <input 
                type="text" 
                id="postapi_site_secret" 
                name="postapi_site_secret" 
                value="<?= !empty($postapi_site_secret) ? L('API_Key_Generated') : '' ?>" 
                class="input" 
                style="flex:1;"
            >
            <button type="button" class="button" onclick="genQsisApiKey()"><?= L('Generate_Random_Secret_Key'); ?></button>
        </div>
        <div class="info"><?= L('Remote_Fetch_Index_Desc') ?></div>
        <div class="clear"></div>
    </div>
<script>
function genQsisApiKey(){
    const prefix = "QsisCms-";
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let rand = "";
    for(let i=0;i<32;i++){
        rand += chars[Math.floor(Math.random()*chars.length)];
    }
    document.getElementById('postapi_site_secret').value = prefix + rand;
}
</script>
    <?php else: ?>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <span style="color:#999;"><?= L('PostAPI_Closed') ?></span>
        </div>
        <div class="clear"></div>
    </div>
    <?php endif; ?>
</div>

<div class="small_form small_form2">
    <b><?= L('AI_API_Config') ?><?= L('Execute_Top_To_Bottom') ?></b>
    
    <?php if ($AI_api === 'open'): ?>
    <div class="field">
        <div class="label"><?= L('API_Interface') ?></div>
        <div class="price"><?= L('Model_Version') ?></div>
        <div class="info">API Key</div>
        <div class="clear"></div>
    </div>

    <!-- 动态行容器 -->
    <div id="aiApiRowsWrap">
<?php
// 回显已经保存的多条配置
if(!empty($ai_api_list) && is_array($ai_api_list)){
    foreach($ai_api_list as $item){
        $site = htmlspecialchars($item['site']??'');
        $model = htmlspecialchars($item['model']??'');
        $key = htmlspecialchars($item['apikey']??''); $siteNamePh = L('API_Site_Name_Placeholder'); $modelIdPh = L('Model_ID_Placeholder'); $delPh = L('Delete');
        echo <<<HTML
    <div class="field ai-api-row">
        <div class="label">
            <input type="text" name="ai_site_name[]" value="{$site}" style="flex:1;" placeholder="{$siteNamePh}">
        </div>
        <div class="price" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="ai_model[]" value="{$model}" style="flex:1;" placeholder="{$modelIdPh}">
        </div>
        <div class="info">
            <div style="display:flex;gap:6px;align-items:center;">
                <input type="text" name="ai_apikey[]" value="{$key}" style="flex:1;" placeholder="sk-xxx API Key">
                <button type="button" class="button ai-row-del">{$delPh}</button>
            </div>
        </div>
        <div class="clear"></div>
    </div>
HTML;
    }
}
?>
    </div>

    <div class="field">
        <div class="label"></div>
        <div class="price">
            <button type="button" class="button" id="addAiApiRow"><?= L('Add_API_Row'); ?></button>
        </div>
        <div class="info"></div>
        <div class="clear"></div>
    </div>
<script>
function createAiApiRowHtml(){
    var div = document.createElement('div');
    div.className = "field ai-api-row";
    div.innerHTML = `
        <div class="label">
            <input type="text" name="ai_site_name[]" value="" style="flex:1;" placeholder="<?= L('API_Site_Name_Placeholder'); ?>">
        </div>
        <div class="price" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="ai_model[]" value="" style="flex:1;" placeholder="<?= L('Model_ID_Placeholder'); ?>">
        </div>
        <div class="info">
            <div style="display:flex;gap:6px;align-items:center;">
                <input type="text" name="ai_apikey[]" value="" style="flex:1;" placeholder="sk‑xxx API Key">
                <button type="button" class="button ai-row-del"><?= L('Delete'); ?></button>
            </div>
        </div>
        <div class="clear"></div>
    `;
    return div;
}

document.addEventListener('DOMContentLoaded', function(){
    var wrap = document.getElementById('aiApiRowsWrap');
    var addBtn = document.getElementById('addAiApiRow');

    //增加行
    if(addBtn && wrap){
        addBtn.addEventListener('click', function(e){
            e.preventDefault();
            var newRow = createAiApiRowHtml();
            wrap.appendChild(newRow);
        });
    }

    //删除行：事件委托
    document.body.addEventListener('click', function(e){
        if(e.target.classList.contains('ai-row-del')){
            e.preventDefault();
            var row = e.target.closest('.ai-api-row');
            if(row) row.remove();
        }
    });
});
</script>

    <?php else: ?>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <span style="color:#999;"><?= L('AI_API_Closed') ?></span>
        </div>
        <div class="clear"></div>
    </div>
    <?php endif; ?>
</div>

<?php
// AI 发文接口（外部 Agent 写入文章）
$ai_publish = $qs_config['ai_publish'] ?? 'close';
$ai_publish_key = $qs_config['ai_publish_key'] ?? '';
$ai_endpoint = rtrim((string) ($qs_config['site_link'] ?? ''), '/') . QS_ADMIN_URL . '/AI/publish.php';
?>
<div class="small_form small_form2">
    <b><?= L('AI_Publish_API') ?></b>

    <div class="field">
        <div class="label"><?= L('AI_Publish_Mode') ?></div>
        <div class="price">
            <select name="ai_publish" class="input">
                <option value="close" <?= $ai_publish === 'close' ? 'selected' : '' ?>><?= L('close') ?></option>
                <option value="open" <?= $ai_publish === 'open' ? 'selected' : '' ?>><?= L('open') ?></option>
            </select>
        </div>
        <div class="info"><?= L('AI_Publish_API_Desc') ?></div>
        <div class="clear"></div>
    </div>

    <?php if ($ai_publish === 'open'): ?>
    <div class="field">
        <div class="label"><?= L('AI_Publish_Key') ?></div>
        <div class="price" style="display:flex;gap:8px;align-items:center;">
            <input
                type="text"
                id="ai_publish_key"
                name="ai_publish_key"
                value="<?= !empty($ai_publish_key) ? L('AI_Key_Generated') : '' ?>"
                class="input"
                style="flex:1;"
            >
            <button type="button" class="button" onclick="genAiPublishKey()"><?= L('Generate_Random_Secret_Key') ?></button>
        </div>
        <div class="info"><?= L('AI_Publish_Key_Desc') ?></div>
        <div class="clear"></div>
    </div>
    <div class="field">
        <div class="label"><?= L('AI_Publish_Endpoint') ?></div>
        <div class="price">
            <input type="text" class="input" readonly value="<?= htmlspecialchars($ai_endpoint, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="info"><?= L('AI_Publish_Fields_Desc') ?></div>
        <div class="clear"></div>
    </div>
<script>
function genAiPublishKey(){
    var prefix = "QsisAI-";
    var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var rand = "";
    for(var i=0;i<32;i++){
        rand += chars[Math.floor(Math.random()*chars.length)];
    }
    document.getElementById('ai_publish_key').value = prefix + rand;
}
</script>
    <?php else: ?>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <span style="color:#999;"><?= L('AI_Publish_API_Closed') ?></span>
        </div>
        <div class="clear"></div>
    </div>
    <?php endif; ?>
</div>
