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
 * @File         set_articles.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Article display settings: items per page, editor toolbar and the text-to-speech switch.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_articles.php
// 变量已从主文件 extract，可直接使用 $per_page, $edittools, $openspeak 等
?>
<b><?= L('Article_Settings'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>
    
    <!-- 每页显示数量 -->
    <div class="field">
        <div class="label"><?= L('BT1'); ?></div>
        <div class="price"><input class="input" type="number" name="per_page" value="<?= htmlspecialchars($per_page ?? 20); ?>" /></div>
        <div class="info"><?= L('B1'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- 每条显示字数 -->
    <div class="field">
        <div class="label"><?= L('BT2'); ?></div>
        <div class="price"><input class="input" type="text" name="articlehead" value="<?= htmlspecialchars($articlehead ?? ''); ?>" /></div>
        <div class="info"><?= L('B2'); ?></div>
    </div>
    <div class="clear"></div>
    
        <!-- tag文章内连 -->
    <div class="field">
        <div class="label"><?= L('Tag_Link_In_Article'); ?> </div>
        <div class="price"><select class="select-1" name="tagsonarticle" id="tags_link">
    <option value="1" <?php if($qs_config['tagsonarticle'] == '1') echo 'selected'; ?>><?= L('open'); ?></option>
    <option value="0" <?php if($qs_config['tagsonarticle'] == '0') echo 'selected'; ?>><?= L('close'); ?></option>
</select></div>
        <div class="info"><?= L('B2'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- 默认文章图片 -->
    <div class="field">
        <div class="label"><?= L('Article_Image_Default'); ?></div>
<div class="price" >
<!-- 1.本地上传 -->
<input class="input" type="file" name="image" id="image"><br>
<!-- 2.选择服务器已有图片 -->
<select name="sel_logo" style="margin:4px 0; min-width:260px;">
    <?= $imageOptions ?>
</select>
<br>
<!--3.外部URL输入框 -->
<input class="input" type="text" name="url_logo" placeholder="<?= L('Image_URL_Placeholder'); ?>" value="<?= htmlspecialchars($qs_config['sitelogo'] ?? '') ?>" style="margin:4px 0;">
<br>
<img width="100px" height="auto" src="../<?php echo $sitelogo; ?>" />
</div>
        <div class="info"><?= L('B4'); ?>
        	<br><?= L('Upload_Priority_Hint'); ?>
        	</div>
    </div>
    <div class="clear"></div>

    <!-- 编辑器选择 -->
    <div class="field">
        <div class="label"><?= L('EditorTools'); ?></div>
        <div class="price">
            <select class="select-1" name="edittools" id="edittools">
                <option value="xheditor" <?= ($edittools ?? '') == 'xheditor' ? 'selected' : ''; ?>><?= L('xheditor'); ?></option>
                <option value="Quill" <?= ($edittools ?? '') == 'Quill' ? 'selected' : ''; ?>><?= L('Quill'); ?></option>
                <option value="qseditor" <?= ($edittools ?? '') == 'qseditor' ? 'selected' : ''; ?>>QS Editor</option>
            </select>
        </div>
        <div class="info"><?= L('B6'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- 语音朗读设置 -->
    <b><?= L('SpeakArticle_Settings'); ?></b>
    
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('SpeakArticle'); ?><?= L('Global_Switch_Marker'); ?></div>
        <div class="price">
            <select class="select-1" name="openspeak" id="openspeak">
                <option value="open" <?= ($openspeak ?? '') == 'open' ? 'selected' : ''; ?>><?= L('open'); ?></option>
                <option value="close" <?= ($openspeak ?? '') == 'close' ? 'selected' : ''; ?>><?= L('close'); ?></option>
            </select>
        </div>
        <div class="info"><?= L('B7'); ?>；<?= L('Speak_Disabled_Hide_Widget'); ?></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('SpeakArticle_Api'); ?><?= L('Current_Using_Marker'); ?></div>
        <div class="price">
            <select class="select-1" name="speakArticle" id="speakArticle">
                <?php
                $ttsList = $qs_config['speak_tts_list'] ?? [];
                if (!is_array($ttsList)) $ttsList = [];
                $sel = $speakArticle ?? '';
                if (empty($ttsList)) {
                    echo '<option value="web"' . (($sel === 'web') ? ' selected' : '') . '>' . L('Browser_Native_Speak_Default') . '</option>';
                }
                foreach ($ttsList as $p) {
                    if (!is_array($p)) continue;
                    $pkey = $p['key'] ?? '';
                    $pname = $p['name'] ?? $pkey;
                    $pen = ($p['enabled'] ?? '1') !== '0';
                    if (!$pen) continue;
                    echo '<option value="' . htmlspecialchars($pkey, ENT_QUOTES) . '"' . (($sel === $pkey) ? ' selected' : '') . '>' .
                        htmlspecialchars($pname . ($pen ? '' : L('Disabled_Suffix')), ENT_QUOTES) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="info"><?= L('Selected_TTS_Fallback_Hint'); ?></div>
    </div>
    <div class="clear"></div>

    <!-- ======== TTS 提供者列表 ======== -->
    <div class="field">
        <div class="label"><?= L('TTS_List'); ?></div>
        <div class="price" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <select id="ttsPresetSelect" class="input" style="min-width:180px;">
                <?php foreach (qs_tts_presets() as $pk => $pv): ?>
                    <option value="<?= htmlspecialchars($pk, ENT_QUOTES); ?>"><?= htmlspecialchars($pv['cfg']['name'] ?? $pk, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button" onclick="qsTtsAddPreset()"><?= L('Add_TTS_From_Preset'); ?></button>
            <span style="color:#999;"><?= L('API_Key_Blank_Hint'); ?></span>
        </div>
        <div class="info" ><?= L('TTS_Config_Intro'); ?></div>
        <div class="clear"></div>
    </div>

    <style>
    /* TTS 表格：桌面/平板（≥768px）预留左侧标签列，手机（<768px）用整宽不缩进 */
    #ttsRowsWrap { margin-left: 0; }
    @media (min-width: 768px) { #ttsRowsWrap { margin-left: 200px; } }
    .tts-row { box-sizing: border-box; width: 100%; }
    </style>
    <div id="ttsRowsWrap">
<?php
foreach ($ttsList as $idx => $p) {
    if (!is_array($p)) continue;
    echo qs_tts_row_html($p);
}
?>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"></div>
        <div class="price">
            <button type="button" class="button" onclick="qsTtsAddPreset()"><?= L('Add_TTS_Row'); ?></button>
        </div>
        <div class="info"></div>
        <div class="clear"></div>
    </div>

<script>
var QS_TTS_PROXY = <?= json_encode(WEB_BASE . 'control/tts_api.php'); ?>;
var QS_TTS_PRESETS = <?= json_encode(qs_tts_presets(), JSON_UNESCAPED_UNICODE); ?>;
var QS_TTS_HANDLERS = <?= json_encode(qs_tts_handlers(), JSON_UNESCAPED_UNICODE); ?>;
var QS_TTS_ROW_IDX = <?= count($ttsList); ?>;

function qsTtsRowHtml(cfg, key) {
    cfg = cfg || {};
    var h = '';
    h += '<div class="tts-row" style="border:1px solid #e5e5e5;border-radius:6px;padding:10px;margin:8px 0;background:#fcfcfc;">';
    h += '<div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">';
    h += '<input type="text" name="tts_key[]" value="' + qsTtsEsc(key || cfg.key || '') + '" placeholder="<?= L('TTS_Unique_Key_Placeholder'); ?>" style="flex:1;min-width:140px;">';
    h += '<input type="text" name="tts_name[]" value="' + qsTtsEsc(cfg.name || '') + '" placeholder="<?= L('Display_Name_Placeholder'); ?>" style="flex:1;min-width:120px;">';
    h += '<select name="tts_handler[]" class="input" onchange="qsTtsHandlerNote(this)">' + qsTtsHandlerOptions(cfg.handler) + '</select>';
    h += '<input type="hidden" name="tts_enabled[]" value="' + ((cfg.enabled === '0') ? '0' : '1') + '"><label style="white-space:nowrap;"><input type="checkbox" class="tts-enabled-box" ' + ((cfg.enabled === '0') ? '' : 'checked') + ' onclick="qsTtsEnabled(this)"> <?= L('Enable_Status'); ?></label>';
    h += '<button type="button" class="button" onclick="qsTtsTest(this)"><?= L('TTS_Test_Synthesis'); ?></button>';
    h += '<button type="button" class="button" onclick="this.closest(\'.tts-row\').remove()"><?= L('Delete'); ?></button>';
    h += '<span class="tts-test-result" style="color:#333;"></span>';
    h += '</div>';
    h += '<div class="tts-help" style="margin-bottom:6px;color:#888;font-size:12px;">' + qsTtsHandlerNoteText(cfg.handler) + '</div>';
    h += '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">';
    h += '<label><?= L('TTS_Voice'); ?> <input type="text" name="tts_voice[]" value="' + qsTtsEsc(cfg.voice || '') + '" style="width:120px;"></label>';
    h += '<label><?= L('TTS_Language'); ?> <input type="text" name="tts_lang[]" value="' + qsTtsEsc(cfg.lang || '') + '" style="width:90px;"></label>';
    h += '<label><?= L('TTS_Default_Rate'); ?> <input type="text" name="tts_rate[]" value="' + qsTtsEsc(cfg.rate || '1') + '" style="width:60px;"></label>';
    h += '<label><?= L('TTS_Request_Method'); ?> <select name="tts_method[]" class="input" style="width:90px;">' +
        '<option value="POST"' + ((cfg.method||'POST')==='POST'?' selected':'') + '>POST</option>' +
        '<option value="GET"' + ((cfg.method||'')==='GET'?' selected':'') + '>GET</option></select></label>';
    h += '</div>';
    h += '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">';
    h += '<label style="flex:1;min-width:220px;"><?= L('TTS_API_Address'); ?> <input type="text" name="tts_api_url[]" value="' + qsTtsEsc(cfg.api_url || '') + '" style="width:100%;"></label>';
    h += '<label>API Key <input type="text" name="tts_api_key[]" value="' + qsTtsEsc(cfg.api_key || '') + '" style="width:140px;"></label>';
    h += '<label>Secret <input type="text" name="tts_api_secret[]" value="' + qsTtsEsc(cfg.api_secret || '') + '" style="width:140px;"></label>';
    h += '</div>';
    h += '<div style="margin-bottom:6px;"><?= L('TTS_Extra_Params'); ?> <textarea name="tts_extra[]" rows="2" style="width:100%;">' + qsTtsEsc(cfg.extra || '') + '</textarea></div>';
    h += '<div style="margin-bottom:6px;"><?= L('TTS_Local_Cmd_Template'); ?> <input type="text" name="tts_cmd[]" value="' + qsTtsEsc(cfg.cmd || '') + '" style="width:100%;" placeholder="<?= L('TTS_Local_Cmd_Placeholder'); ?>"></div>';
    h += '<div><?= L('TTS_Working_Directory'); ?> <input type="text" name="tts_cmd_workdir[]" value="' + qsTtsEsc(cfg.cmd_workdir || '') + '" style="width:100%;"></div>';
    h += '</div>';
    return h;
}

function qsTtsHandlerOptions(sel) {
    var s = '';
    for (var k in QS_TTS_HANDLERS) {
        s += '<option value="' + k + '"' + ((k === sel) ? ' selected' : '') + '>' + QS_TTS_HANDLERS[k].name + '</option>';
    }
    return s;
}
function qsTtsHandlerNoteText(h) {
    return QS_TTS_HANDLERS[h] ? QS_TTS_HANDLERS[h].note : '';
}
function qsTtsHandlerNote(selEl) {
    var note = qsTtsHandlerNoteText(selEl.value);
    var help = selEl.closest('.tts-row').querySelector('.tts-help');
    if (help) help.textContent = note;
}
function qsTtsEsc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function qsTtsEnabled(cb) {
    var h = cb.closest('.tts-row').querySelector('[name="tts_enabled[]"]');
    if (h) h.value = cb.checked ? '1' : '0';
}
function qsTtsGenKey() {
    return 'tts_' + Date.now().toString(36) + Math.floor(Math.random() * 1000).toString(36);
}
function qsTtsAddPreset() {
    var sel = document.getElementById('ttsPresetSelect');
    var key = (sel ? sel.value : '') || 'custom_http';
    var cfg = QS_TTS_PRESETS[key] ? JSON.parse(JSON.stringify(QS_TTS_PRESETS[key].cfg)) : {};
    // 用预设建议 key，若已存在则追加序号避免重复
    var baseKey = cfg.key || qsTtsGenKey();
    var keyInputs = document.querySelectorAll('.tts-row [name="tts_key[]"]');
    var existing = [];
    for (var i = 0; i < keyInputs.length; i++) existing.push(keyInputs[i].value);
    var finalKey = baseKey, n = 2;
    while (existing.indexOf(finalKey) !== -1) { finalKey = baseKey + '_' + n; n++; }
    var wrap = document.getElementById('ttsRowsWrap');
    var div = document.createElement('div');
    div.innerHTML = qsTtsRowHtml(cfg, finalKey);
    wrap.appendChild(div.firstChild);
    var row = wrap.lastElementChild;
    var help = row.querySelector('.tts-help');
    if (help) {
        var hk = (row.querySelector('[name="tts_handler[]"]') || {}).value;
        var note = qsTtsHandlerNoteText(hk);
        var presetHelp = QS_TTS_PRESETS[key] ? QS_TTS_PRESETS[key].help : '';
        help.textContent = (note ? note + '。' : '') + presetHelp;
    }
}
function qsTtsTest(btn) {
    var row = btn.closest('.tts-row');
    var res = row.querySelector('.tts-test-result');
    res.textContent = '<?= L('TTS_Testing'); ?>';
    res.style.color = '#888';
    var fd = new FormData();
    fd.append('action', 'test');
    fd.append('inline', '1');
    fd.append('text', '<?= L('TTS_Test_Sentence'); ?>');
    fd.append('handler', row.querySelector('[name="tts_handler[]"]').value);
    fd.append('voice', row.querySelector('[name="tts_voice[]"]').value);
    fd.append('lang', row.querySelector('[name="tts_lang[]"]').value);
    fd.append('rate', row.querySelector('[name="tts_rate[]"]').value);
    fd.append('method', row.querySelector('[name="tts_method[]"]').value);
    fd.append('api_url', row.querySelector('[name="tts_api_url[]"]').value);
    fd.append('api_key', row.querySelector('[name="tts_api_key[]"]').value);
    fd.append('api_secret', row.querySelector('[name="tts_api_secret[]"]').value);
    fd.append('extra', row.querySelector('[name="tts_extra[]"]').value);
    fd.append('cmd', row.querySelector('[name="tts_cmd[]"]').value);
    fd.append('cmd_workdir', row.querySelector('[name="tts_cmd_workdir[]"]').value);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', QS_TTS_PROXY, true);
    xhr.onload = function () {
        try {
            var j = JSON.parse(xhr.responseText);
            if (j.ok) { res.textContent = '✅ ' + '<?= L('TTS_Test_Success_Bytes'); ?>'.replace('%s', j.size); res.style.color = 'green'; }
            else { res.textContent = '❌ ' + (j.error || '<?= L('TTS_Default_Fail_Msg'); ?>'); res.style.color = 'red'; }
        } catch (e) { res.textContent = '❌ <?= L('TTS_Response_Error'); ?>'; res.style.color = 'red'; }
    };
    xhr.onerror = function () { res.textContent = '❌ <?= L('TTS_Request_Failed'); ?>'; res.style.color = 'red'; };
    xhr.send(fd);
}
</script>
    <div class="clear"></div>

    <!-- 留言设置 -->
    <b><?= L('Message_Settings'); ?></b>
    <div class="clear"></div>
    <div class="field">
        <div class="label"><?= L('message_database_filename'); ?></div>
        <div class="price"><input class="input" type="text" name="liuyan_data_name" value="<?= htmlspecialchars($liuyan_data_name ?? 'messages.dat'); ?>" /></div>
        <div class="info"><?= L('B5'); ?></div>
    </div>
    <div class="info"></div>
</div>