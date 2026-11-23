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
 * @File         vote_edit.php
 * @Description  投票新建 / 编辑：标题、说明、单选多选、选项增删、启停、起止时间、结果展示。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = [
    'id'          => 0,
    'title'       => '',
    'description' => '',
    'type'        => 'single',
    'options'     => [],
    'enable'      => true,
    'sort'        => 0,
    'start_time'  => '',
    'end_time'    => '',
    'show_result' => true,
];
if ($id > 0 && function_exists('qs_db_vote_get')) {
    $exist = qs_db_vote_get($id);
    if ($exist) {
        $data = array_merge($data, $exist);
    }
}

$error = '';

// datetime 字符串 -> datetime-local 输入框值
$toLocal = function (?string $dt): string {
    if (!$dt) return '';
    $ts = strtotime($dt);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
};

// POST 保存
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $desc  = trim((string)($_POST['description'] ?? ''));
    $type  = ($_POST['type'] ?? 'single') === 'multiple' ? 'multiple' : 'single';
    $enable = !empty($_POST['enable']);
    $showResult = !empty($_POST['show_result']);
    $sort  = (int)($_POST['sort'] ?? 0);

    $start = trim((string)($_POST['start_time'] ?? ''));
    $end   = trim((string)($_POST['end_time'] ?? ''));
    $start = $start !== '' ? str_replace('T', ' ', $start) : '';
    $end   = $end   !== '' ? str_replace('T', ' ', $end)   : '';

    // 组装选项（平行数组 oid / text）；兼容单值误传为字符串
    $oids  = $_POST['option_oid']  ?? [];
    $texts = $_POST['option_text']  ?? [];
    if (!is_array($oids))  $oids  = [$oids];
    if (!is_array($texts)) $texts = [$texts];
    $options = [];
    foreach ($texts as $i => $text) {
        $text = trim((string)$text);
        if ($text === '') continue;
        $oid = trim((string)($oids[$i] ?? ''));
        if ($oid === '') $oid = 'opt' . substr(md5(uniqid('', true)), 0, 8);
        $options[] = ['oid' => $oid, 'text' => mb_substr($text, 0, 200)];
    }

    if ($title === '') {
        $error = L('vote_err_title');
    } elseif (count($options) < 2) {
        $error = L('vote_err_options');
    } else {
        $save = [
            'id'          => $data['id'],
            'title'       => $title,
            'description' => $desc,
            'type'        => $type,
            'options'     => $options,
            'enable'      => $enable,
            'sort'        => $sort,
            'start_time'  => $start,
            'end_time'    => $end,
            'show_result' => $showResult,
        ];
        qs_db_vote_save($save);
        header('Location: vote.php');
        exit;
    }

    // 校验失败：回填表单
    $data['title']       = $title;
    $data['description'] = $desc;
    $data['type']        = $type;
    $data['options']     = $options;
    $data['enable']      = $enable;
    $data['sort']        = $sort;
    $data['start_time']  = $start;
    $data['end_time']    = $end;
    $data['show_result'] = $showResult;
}

// 编辑页选项行：现有选项；新建时给 3 个空行
$rows = $data['options'];
if (!$rows) {
    $rows = [['oid' => '', 'text' => ''], ['oid' => '', 'text' => ''], ['oid' => '', 'text' => '']];
}
?>
    <div class="admin_page_name"><?php echo $data['id'] > 0 ? L('vote_edit_title') : L('vote_new'); ?></div>

    <?php if ($error !== '') { ?>
    <div style="margin:10px 0;color:#c0392b;"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div id="fenye">
        <div class="container">
            <form method="post">
                <table>
                    <?php if ($data['id'] > 0) { ?>
                    <tr>
                        <td style="width:140px"><?php echo L('vote_f_id'); ?></td>
                        <td><input type="text" value="<?php echo (int)$data['id']; ?>" readonly style="width:200px;"> <span><?php echo L('vote_f_id_lock'); ?></span></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo L('vote_th_title'); ?></td>
                        <td><input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" required style="width:380px;"></td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_desc'); ?></td>
                        <td><textarea name="description" rows="3" style="width:98%;"><?php echo htmlspecialchars($data['description']); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_type'); ?></td>
                        <td>
                            <label><input type="radio" name="type" value="single" <?php echo $data['type'] !== 'multiple' ? 'checked' : ''; ?>> <?php echo L('vote_type_single'); ?></label>
                            &nbsp;&nbsp;
                            <label><input type="radio" name="type" value="multiple" <?php echo $data['type'] === 'multiple' ? 'checked' : ''; ?>> <?php echo L('vote_type_multiple'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_options'); ?></td>
                        <td>
                            <div id="vote-options">
                                <?php foreach ($rows as $rowOpt) { ?>
                                <div class="vote-opt-row" style="margin:6px 0;">
                                    <input type="hidden" name="option_oid[]" value="<?php echo htmlspecialchars((string)($rowOpt['oid'] ?? '')); ?>">
                                    <input type="text" name="option_text[]" value="<?php echo htmlspecialchars((string)($rowOpt['text'] ?? '')); ?>" placeholder="<?php echo htmlspecialchars(L('vote_f_option_ph')); ?>" style="width:320px;">
                                    <button type="button" class="btn-del-opt"><?php echo L('Delete'); ?></button>
                                </div>
                                <?php } ?>
                            </div>
                            <button type="button" id="btn-add-opt"><?php echo L('vote_f_add_opt'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_start'); ?></td>
                        <td><input type="datetime-local" name="start_time" value="<?php echo $toLocal($data['start_time']); ?>"> <span><?php echo L('vote_f_start_hint'); ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_end'); ?></td>
                        <td><input type="datetime-local" name="end_time" value="<?php echo $toLocal($data['end_time']); ?>"> <span><?php echo L('vote_f_end_hint'); ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo L('Sort_Order'); ?></td>
                        <td><input type="number" name="sort" value="<?php echo (int)$data['sort']; ?>" style="width:120px;"> <span><?php echo L('vote_f_sort_hint'); ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_enable'); ?></td>
                        <td><input type="checkbox" name="enable" <?php echo $data['enable'] ? 'checked' : ''; ?>> <?php echo L('vote_f_enable_hint'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo L('vote_f_show_result'); ?></td>
                        <td><input type="checkbox" name="show_result" <?php echo $data['show_result'] ? 'checked' : ''; ?>> <?php echo L('vote_f_show_result_hint'); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" value="<?php echo htmlspecialchars(L('Save')); ?>">
                            <a href="vote.php" style="margin-left:15px;"><?php echo L('Back_To_List'); ?></a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div class="clear"></div>

<script>
(function () {
    var box = document.getElementById('vote-options');
    var addBtn = document.getElementById('btn-add-opt');
    var optPh = <?php echo json_encode(L('vote_f_option_ph'), JSON_UNESCAPED_UNICODE); ?>;
    var delTxt = <?php echo json_encode(L('Delete'), JSON_UNESCAPED_UNICODE); ?>;
    var minTxt = <?php echo json_encode(L('vote_err_options_js'), JSON_UNESCAPED_UNICODE); ?>;

    function bindDel(btn) {
        btn.addEventListener('click', function () {
            var rows = box.querySelectorAll('.vote-opt-row');
            if (rows.length <= 2) { alert(minTxt); return; }
            btn.closest('.vote-opt-row').remove();
        });
    }

    addBtn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'vote-opt-row';
        row.style.margin = '6px 0';
        row.innerHTML =
            '<input type="hidden" name="option_oid[]" value="">' +
            '<input type="text" name="option_text[]" placeholder="' + optPh.replace(/"/g, '&quot;') + '" style="width:320px;"> ' +
            '<button type="button" class="btn-del-opt">' + delTxt + '</button>';
        box.appendChild(row);
        bindDel(row.querySelector('.btn-del-opt'));
    });

    box.querySelectorAll('.btn-del-opt').forEach(bindDel);
})();
</script>

<?php require 'foot.php'; ?>
