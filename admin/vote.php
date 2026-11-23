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
 * @File         vote.php
 * @Description  投票管理列表：新建 / 编辑 / 启停 / 删除投票，查看参与人数与有效期状态。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

$votes = function_exists('qs_db_votes_list') ? qs_db_votes_list() : [];

// 删除投票（连带删除记录）
if (isset($_GET['act']) && $_GET['act'] === 'del' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (function_exists('qs_db_vote_delete')) {
        qs_db_vote_delete($id);
    }
    header('Location: vote.php');
    exit;
}

// 切换启用 / 停用
if (isset($_GET['act']) && $_GET['act'] === 'toggle' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $row = function_exists('qs_db_vote_get') ? qs_db_vote_get($id) : null;
    if ($row) {
        $row['enable'] = !$row['enable'];
        qs_db_vote_save($row);
    }
    header('Location: vote.php');
    exit;
}

/** 有效期状态文案 */
function vote_status_text(array $row): string
{
    $now = time();
    $start = $row['start_time'] ? strtotime($row['start_time']) : 0;
    $end   = $row['end_time']   ? strtotime($row['end_time'])   : 0;
    if ($start && $start > $now) return L('vote_period_not_started');
    if ($end && $end < $now)     return L('vote_period_ended');
    return L('vote_period_ongoing');
}

/** 给 JS 用的安全字符串（JSON + HTML 属性转义） */
function vote_js_text(string $key): string
{
    return htmlspecialchars(json_encode(L($key), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
}
?>
    <div class="admin_page_name">
        <a href="set.php"><?php echo L('system_settings'); ?></a>
        &gt; <?php echo L('vote_menu_title'); ?>
    </div>

    <div id="fenye">
        <ta style="display:block">
        <div class="container">
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table>
                <tr>
                    <th>ID</th>
                    <th><?php echo L('vote_th_title'); ?></th>
                    <th><?php echo L('vote_th_type'); ?></th>
                    <th><?php echo L('vote_th_options'); ?></th>
                    <th><?php echo L('vote_th_voters'); ?></th>
                    <th><?php echo L('vote_th_period'); ?></th>
                    <th><?php echo L('Status'); ?></th>
                    <th><?php echo L('Sort_Order'); ?></th>
                    <th><?php echo L('vote_th_mtime'); ?></th>
                    <th><?php echo L('vote_th_share'); ?></th>
                    <th><?php echo L('Operations'); ?></th>
                </tr>
                <?php if (empty($votes)) { ?>
                <tr>
                    <td colspan="11"><?php echo L('vote_empty'); ?></td>
                </tr>
                <?php } else foreach ($votes as $item) {
                    $optCount = is_array($item['options']) ? count($item['options']) : 0;
                    $voters   = function_exists('qs_db_vote_voter_count') ? qs_db_vote_voter_count($item['id']) : 0;
                    $typeText = $item['type'] === 'multiple' ? L('vote_type_multiple') : L('vote_type_single');
                    $voteUrl  = rtrim((string)qs_site_link(false), '/') . '/?vote/' . (int)$item['id'] . '/';
                ?>
                <tr>
                    <td><code><?php echo (int)$item['id']; ?></code></td>
                    <td>
                        <a href="<?php echo htmlspecialchars($voteUrl); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($item['title']); ?></a>
                        <div style="font-size:12px;opacity:.65;margin-top:2px;"><?php echo L('vote_open_result_hint'); ?></div>
                    </td>
                    <td><?php echo $typeText; ?></td>
                    <td><?php echo $optCount; ?></td>
                    <td><?php echo $voters; ?></td>
                    <td><?php echo htmlspecialchars(vote_status_text($item)); ?></td>
                    <td>
                        <?php if ($item['enable']) { ?>
                            <a href="?act=toggle&id=<?php echo (int)$item['id']; ?>"><?php echo L('vote_state_on'); ?></a>
                        <?php } else { ?>
                            <a href="?act=toggle&id=<?php echo (int)$item['id']; ?>"><?php echo L('vote_state_off'); ?></a>
                        <?php } ?>
                    </td>
                    <td><?php echo (int)$item['sort']; ?></td>
                    <td><?php echo htmlspecialchars((string)$item['mtime']); ?></td>
                    <td style="min-width:260px;">
                        <?php
                        $voteTag  = '[vote id="' . (int)$item['id'] . '"]';
                        ?>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="text" readonly value="<?php echo htmlspecialchars($voteUrl); ?>" onclick="this.select()" style="width:210px;font-size:12px;">
                            <button type="button" onclick="qsVoteCopy(this, '<?php echo htmlspecialchars($voteUrl); ?>')"><?php echo L('vote_copy'); ?></button>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;margin-top:4px;">
                            <code style="font-size:12px;"><?php echo htmlspecialchars($voteTag); ?></code>
                            <button type="button" onclick="qsVoteCopy(this, '<?php echo htmlspecialchars($voteTag); ?>')"><?php echo L('vote_copy_tag'); ?></button>
                        </div>
                        <div style="margin-top:4px;font-size:12px;opacity:.7;"><?php echo L('vote_tag_hint'); ?></div>
                    </td>
                    <td>
                        <a href="vote_edit.php?id=<?php echo (int)$item['id']; ?>"><?php echo L('Edit'); ?></a>
                        |
                        <a href="?act=del&id=<?php echo (int)$item['id']; ?>" onclick="return confirm(<?php echo vote_js_text('vote_del_confirm'); ?>)"><?php echo L('Delete'); ?></a>
                    </td>
                </tr>
                <?php } ?>
            </table>
            </div>
            <div class="pagination"></div>
            <a href="vote_edit.php"><?php echo L('vote_new'); ?></a>
        </div>
    </div>
    <div class="clear"></div>

<script>
var qsVoteCopiedText = <?php echo json_encode(L('vote_copied'), JSON_UNESCAPED_UNICODE); ?>;
var qsVoteCopyManual = <?php echo json_encode(L('vote_copy_manual'), JSON_UNESCAPED_UNICODE); ?>;
function qsVoteCopy(btn, text) {
    var done = function () {
        var old = btn.textContent;
        btn.textContent = qsVoteCopiedText;
        setTimeout(function () { btn.textContent = old; }, 1200);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done, function () { prompt(qsVoteCopyManual, text); });
    } else {
        prompt(qsVoteCopyManual, text);
    }
}
</script>

<?php require 'foot.php'; ?>
