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
 * @Description  前台投票：投票列表、投票详情、提交投票、结果进度条展示。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

global $qs_config, $qs_get_name;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 提交令牌：防止跨站批量伪造提交
if (empty($_SESSION['qs_vote_token'])) {
    $_SESSION['qs_vote_token'] = bin2hex(random_bytes(16));
}
$voteToken = function_exists('qs_vote_token') ? qs_vote_token() : (string)$_SESSION['qs_vote_token'];

// ===== 访客标识：浏览器 cookie 为主，IP 兜底，登录名补充 =====
$voterKey = trim((string)($_COOKIE['qs_voter'] ?? ''));
if ($voterKey === '') {
    $voterKey = bin2hex(random_bytes(16));
    setcookie('qs_voter', $voterKey, time() + 3600 * 12, '/');
}
$voterIp      = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$voterName    = trim((string)($_SESSION['Qsis_user_name'] ?? ''));

/** 有效期判定：返回 ['open'=>bool, 'text'=>提示] */
function vote_period_check(array $row): array
{
    $now   = time();
    $start = $row['start_time'] ? strtotime((string)$row['start_time']) : 0;
    $end   = $row['end_time']   ? strtotime((string)$row['end_time'])   : 0;
    if ($start && $start > $now) return ['open' => false, 'text' => '投票尚未开始'];
    if ($end && $end < $now)     return ['open' => false, 'text' => '投票已结束'];
    return ['open' => true, 'text' => ''];
}

$e = static function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

// ===== POST 提交投票 =====
$postError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_id'])) {
    $voteId = (int)$_POST['vote_id'];
    $row = function_exists('qs_db_vote_get') ? qs_db_vote_get($voteId) : null;

    if (!hash_equals($voteToken, (string)($_POST['vote_token'] ?? ''))) {
        // 令牌不符：跨站提交或会话过期，直接拒绝
        $postError = '提交校验失败，请刷新页面后重新投票。';
    } elseif (!$row || !$row['enable']) {
        $postError = '该投票不存在或已停用。';
    } else {
        $period = vote_period_check($row);
        $validOids = array_column($row['options'], 'oid');

        if (!$period['open']) {
            $postError = $period['text'] . '，无法投票。';
        } elseif (qs_db_vote_has_voted($voteId, $voterKey, $voterIp, $voterName)) {
            $postError = '您已参与过本次投票，不能重复投票。';
        } elseif (function_exists('qs_db_vote_rate_ok') && !qs_db_vote_rate_ok($voterIp, 600, 30)) {
            // 同一 IP 十分钟内提交次数上限，防止脚本刷票
            $postError = '提交过于频繁，请稍后再试。';
        } else {
            // 收集所选选项
            if ($row['type'] === 'multiple') {
                $picked = $_POST['options'] ?? [];
                $picked = is_array($picked) ? $picked : [];
            } else {
                $picked = [$_POST['option'] ?? ''];
            }
            $picked = array_values(array_intersect(array_map('strval', $picked), $validOids));

            if (empty($picked)) {
                $postError = '请先选择选项再提交。';
            } else {
                qs_db_vote_add_record($voteId, $picked, $voterIp, $voterKey, $voterName);
                // “已参与”标记只保留 12 小时：同一浏览器 12 小时内不能重复投，
                // 过期后视为新的访客（登录用户仍按账号长期判重）
                setcookie('qs_voted_' . $voteId, '1', time() + 3600 * 12, '/');
                // 选中的选项通过本次跳转带回页面，只用于“您投的是…”这一行提示，
                // 不写 cookie、不参与表单预选，下一个人用同一台电脑不会看到
                $vMine = rawurlencode(implode('|', $picked));
                // 从文章内嵌卡片提交时，跳回原文章；单独访问投票页则留在投票页
                $vReturn = trim((string)($_POST['vote_return'] ?? ''));
                if ($vReturn !== '' && $vReturn[0] === '/' && strpos($vReturn, '//') === false) {
                    $sep = (strpos($vReturn, '?') === false) ? '?' : '&';
                    header('Location: ' . $vReturn . $sep . 'qs_mine=' . $voteId . ':' . $vMine);
                } else {
                    header('Location: ?vote/' . $voteId . '/&done=1&mine=' . $vMine);
                }
                exit;
            }
        }
    }
    // 校验失败：落到下方详情页展示错误
    $qs_get_name = (string)$voteId;
}

// ===== 页面输出辅助 =====
$site = trim((string)($qs_config['site_name'] ?? ''));
$lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
$lang = preg_replace('/^lang_|\.php$/i', '', $lang);
$lang = str_replace('_', '-', $lang);

$voteId = (int)($qs_get_name ?? 0);

// 列表页
if ($voteId <= 0) {
    $votes = function_exists('qs_db_votes_list') ? qs_db_votes_list(true) : [];
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>投票<?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
:root { --ink:#22252a; --sub:#6b7280; --line:#e5e7eb; --paper:#fff; --bg:#f6f7f8; --accent:#1f5f8b; }
* { box-sizing: border-box; }
body { margin:0; padding:32px 16px 48px; background:var(--bg); color:var(--ink);
    font:400 16px/1.85 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; }
.wrap { max-width:820px; margin:0 auto; }
.card { background:var(--paper); border:1px solid var(--line); border-radius:12px; padding:28px 30px; margin-bottom:20px; }
h1 { font-size:24px; margin:0 0 18px; font-weight:600; }
.vote-title { font-size:18px; font-weight:600; margin:0 0 8px; }
.vote-title a { color:var(--ink); text-decoration:none; }
.vote-title a:hover { color:var(--accent); }
.vote-desc { color:var(--sub); font-size:14px; margin:0 0 12px; }
.vote-meta { color:var(--sub); font-size:13px; display:flex; flex-wrap:wrap; gap:6px 16px; }
.tag { display:inline-block; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-size:12px; color:var(--sub); }
.empty { color:var(--sub); font-size:15px; padding:30px 0; text-align:center; }
.foot { margin-top:26px; padding-top:18px; border-top:1px solid var(--line); color:var(--sub); font-size:13px; }
.foot a { color:var(--sub); text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
    <h1>投票</h1>
    <?php if (empty($votes)) { ?>
        <div class="card"><div class="empty">暂未发布投票。</div></div>
    <?php } foreach ($votes as $v) {
        $period = vote_period_check($v);
        $voters = function_exists('qs_db_vote_voter_count') ? qs_db_vote_voter_count($v['id']) : 0;
        $typeText = $v['type'] === 'multiple' ? '多选' : '单选';
    ?>
    <div class="card">
        <div class="vote-title"><a href="?vote/<?= (int)$v['id']; ?>/"><?= $e($v['title']); ?></a></div>
        <?php if (trim($v['description']) !== '') { ?>
        <div class="vote-desc"><?= nl2br($e($v['description'])); ?></div>
        <?php } ?>
        <div class="vote-meta">
            <span class="tag"><?= $e($typeText); ?></span>
            <span>参与人数：<?= (int)$voters; ?></span>
            <span>状态：<?= $e($period['text'] !== '' ? $period['text'] : '进行中'); ?></span>
        </div>
    </div>
    <?php } ?>
    <div class="foot"><a href="./">&larr; 返回首页</a></div>
</div>
</body>
</html>
    <?php
    return;
}

// 详情页
$row = function_exists('qs_db_vote_get') ? qs_db_vote_get($voteId) : null;
if (!$row) {
    http_response_code(404);
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>投票不存在</title></head>
<body style="font-family:system-ui,'Microsoft YaHei',sans-serif;padding:40px;text-align:center;">
<p>该投票不存在或已停用。</p><p><a href="?vote/">返回投票列表</a></p>
</body></html>
    <?php
    return;
}

$period   = vote_period_check($row);
$hasVoted = qs_db_vote_has_voted($voteId, $voterKey, $voterIp, $voterName);
$result   = qs_db_vote_result($voteId);
$voters   = qs_db_vote_voter_count($voteId);
$totalVotes = array_sum($result);
$done = isset($_GET['done']) && $_GET['done'] == '1';
$typeText = $row['type'] === 'multiple' ? '多选' : '单选';
$showResult = !empty($row['show_result']) || (function_exists('qs_Pass') && qs_Pass());
$canVote = $period['open'] && !$hasVoted && $row['enable'];
// 本次提交带回来的选项（只用于展示“您投的是”，与后台数据交叉校验后才显示）
$myOids = [];
if (isset($_GET['mine'])) {
    $myOids = array_values(array_filter(array_map('trim', explode('|', (string)$_GET['mine'])), 'strlen'));
    $myOids = array_values(array_intersect($myOids, array_column($row['options'], 'oid')));
}
$myLabels = [];
if (!empty($myOids)) {
    foreach ($row['options'] as $idx => $o) {
        if (in_array((string)$o['oid'], $myOids, true)) {
            $myLabels[] = '第' . ($idx + 1) . '项「' . $o['text'] . '」';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($row['title']) ?><?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
:root { --ink:#22252a; --sub:#6b7280; --line:#e5e7eb; --paper:#fff; --bg:#f6f7f8; --accent:#1f5f8b; --bar:#1f5f8b; }
* { box-sizing: border-box; }
body { margin:0; padding:32px 16px 48px; background:var(--bg); color:var(--ink);
    font:400 16px/1.85 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; }
.wrap { max-width:720px; margin:0 auto; }
.card { background:var(--paper); border:1px solid var(--line); border-radius:12px; padding:32px 34px; }
h1 { font-size:22px; line-height:1.4; margin:0 0 10px; font-weight:600; }
.meta { color:var(--sub); font-size:13px; margin:0 0 22px; padding-bottom:16px; border-bottom:1px solid var(--line); display:flex; flex-wrap:wrap; gap:6px 16px; }
.desc { font-size:15px; margin:0 0 22px; white-space:pre-line; }
.alert { border-radius:8px; padding:10px 14px; margin:0 0 20px; font-size:14px; }
.alert-ok { background:#eef6f0; color:#217a3f; border:1px solid #cde8d5; }
.alert-err { background:#fbeeee; color:#c0392b; border:1px solid #f2cfcf; }
.alert-warn { background:#f6f3ea; color:#8a6d1d; border:1px solid #e8ddb8; }
.opt { display:block; padding:12px 0; border-bottom:1px solid var(--line); }
.opt:last-of-type { border-bottom:0; }
.opt label { display:flex; align-items:flex-start; gap:10px; cursor:pointer; }
.opt input { margin-top:7px; }
.opt-text { flex:1; }
.bar-row { margin-top:8px; }
.bar { height:8px; border-radius:6px; background:#eef0f2; overflow:hidden; }
.bar > i { display:block; height:100%; background:var(--bar); border-radius:6px; }
.bar-num { color:var(--sub); font-size:13px; margin-top:4px; }
.btn { display:inline-block; margin-top:22px; background:var(--accent); color:#fff; border:0; border-radius:8px; padding:11px 26px; font-size:15px; cursor:pointer; }
.btn:hover { opacity:.92; }
.foot { margin-top:24px; padding-top:16px; border-top:1px solid var(--line); color:var(--sub); font-size:13px; display:flex; gap:16px; }
.foot a { color:var(--sub); text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
    <article class="card">
        <h1><?= $e($row['title']); ?></h1>
        <div class="meta">
            <span class="tag"><?= $e($typeText); ?></span>
            <span>参与人数：<?= (int)$voters; ?></span>
            <?php if ($row['end_time']) { ?><span>截止：<?= $e($row['end_time']); ?></span><?php } ?>
        </div>

        <?php if (trim($row['description']) !== '') { ?>
        <div class="desc"><?= $e($row['description']); ?></div>
        <?php } ?>

        <?php if ($done) { ?>
        <div class="alert alert-ok">投票成功，感谢您的参与！</div>
        <?php } ?>
        <?php if (!empty($myLabels)) { ?>
        <div class="alert alert-ok">您投的是：<?= $e(implode('、', $myLabels)); ?></div>
        <?php } ?>
        <?php if ($postError !== '') { ?>
        <div class="alert alert-err"><?= $e($postError); ?></div>
        <?php } ?>
        <?php if (!$period['open']) { ?>
        <div class="alert alert-warn"><?= $e($period['text']); ?>，当前不可投票。</div>
        <?php } elseif ($hasVoted) { ?>
        <div class="alert alert-warn">您已参与过本次投票<?php echo $showResult ? '，以下为当前结果。' : '。'; ?></div>
        <?php } elseif (!$showResult) { ?>
        <div class="alert alert-warn">本投票不公开实时结果，感谢您的参与！</div>
        <?php } ?>

        <?php if ($canVote) { ?>
        <form method="post" action="?vote/<?= (int)$voteId; ?>/">
            <input type="hidden" name="vote_id" value="<?= (int)$voteId; ?>">
            <input type="hidden" name="vote_token" value="<?= $e($voteToken); ?>">
            <?php foreach ($row['options'] as $opt) {
                $oid = $e($opt['oid']);
                $otext = $e($opt['text']);
            ?>
            <div class="opt">
                <label>
                    <?php if ($row['type'] === 'multiple') { ?>
                    <input type="checkbox" name="options[]" value="<?= $oid; ?>">
                    <?php } else { ?>
                    <input type="radio" name="option" value="<?= $oid; ?>" required>
                    <?php } ?>
                    <span class="opt-text"><?= $otext; ?></span>
                </label>
            </div>
            <?php } ?>
            <button type="submit" class="btn">提交投票</button>
        </form>
        <?php } ?>

        <?php if ($showResult) {
            foreach ($row['options'] as $opt) {
                $cnt = (int)($result[$opt['oid']] ?? 0);
                $pct = $totalVotes > 0 ? round($cnt / $totalVotes * 100, 1) : 0;
            ?>
            <div class="opt">
                <div class="opt-text"><?= $e($opt['text']); ?></div>
                <div class="bar-row">
                    <div class="bar"><i style="width:<?= $e($pct); ?>%"></i></div>
                    <div class="bar-num"><?= (int)$cnt; ?> 票 · <?= $e($pct); ?>%</div>
                </div>
            </div>
            <?php }
        } ?>

        <div class="foot">
            <a href="?vote/">&larr; 投票列表</a>
            <a href="./">返回首页</a>
        </div>
    </article>
</div>
</body>
</html>
