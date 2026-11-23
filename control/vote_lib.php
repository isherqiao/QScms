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
 * @File         vote_lib.php
 * @Description  文件版投票数据层：以 PHP 文件存储投票定义与投票记录，
 *               对外提供与 MySQL 版同名同签名的 qs_db_vote_* 系列函数，
 *               使 files/vote.php、control/vote_embed_lib.php、admin/vote*.php 无需按存储方式分叉。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', dirname(__DIR__));
}

// =====================================================================
// 存储层：control/data/votes.php（投票定义）、control/data/vote_records.php（投票记录）
// 记录与配置一律 PHP 文件，不使用 JSON。
// =====================================================================

/** 数据文件绝对路径 */
function qs_vote_store_path(string $name): string
{
    return SITE_ROOT . '/control/data/' . $name . '.php';
}

/** 读取数据文件；不存在或格式异常时返回空数组 */
function qs_vote_store_read(string $name): array
{
    $path = qs_vote_store_path($name);
    if (!is_file($path)) {
        return [];
    }
    $data = include $path;
    return is_array($data) ? $data : [];
}

/** 写入数据文件（PHP 文件，临时文件 + rename 原子替换） */
function qs_vote_store_write(string $name, array $data): bool
{
    $path = qs_vote_store_path($name);
    $dir  = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $php = "<?php\n"
        . "if (!defined('SITE_ROOT')) { exit; }\n\n"
        . "// 投票数据文件，由后台与前台自动维护\n"
        . "return " . var_export($data, true) . ";\n";

    $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $php, LOCK_EX) === false) {
        return false;
    }
    // Windows 下 rename 可能因目标被占用而失败，重试数次后再退化为直接覆盖写
    for ($i = 0; $i < 5; $i++) {
        if (@rename($tmp, $path)) {
            return true;
        }
        usleep(20000);
    }
    if (@file_put_contents($path, $php, LOCK_EX) !== false) {
        @unlink($tmp);
        return true;
    }
    @unlink($tmp);
    return false;
}

/**
 * 在独占锁内做一次 "读 - 改 - 写"
 *
 * @param string   $name    数据文件名（不含 .php）
 * @param callable $mutator 签名 function (array &$data): array{0:bool,1:mixed}；
 *                          返回 [是否写回, 需要向上返回的值]
 * @return mixed mutator 返回的第二个元素
 */
function qs_vote_store_tx(string $name, callable $mutator)
{
    $lockPath = qs_vote_store_path($name) . '.lock';
    $fh = @fopen($lockPath, 'c');
    if ($fh) {
        @flock($fh, LOCK_EX);
    }
    try {
        $data = qs_vote_store_read($name);
        $ret  = $mutator($data);
        if (is_array($ret) && !empty($ret[0])) {
            if (!qs_vote_store_write($name, $data)) {
                // 写入失败不能静默：记一笔日志，便于排查目录权限或文件占用
                error_log('QsCms vote: 写入 ' . $name . '.php 失败');
            }
        }
        return $ret[1] ?? null;
    } finally {
        if ($fh) {
            @flock($fh, LOCK_UN);
            @fclose($fh);
        }
    }
}

// =====================================================================
// 业务层：与 MySQL 版 control/db.php 中的 qs_db_vote_* 保持同名同签名
// =====================================================================

/** 归一化投票类型：只允许 single / multiple */
function qs_db_vote_type_norm(string $type): string
{
    return $type === 'multiple' ? 'multiple' : 'single';
}

/** 行归一化（内部辅助） */
function qs_db_vote_row(array $r): array
{
    return [
        'id'          => (int)$r['id'],
        'title'       => (string)($r['title'] ?? ''),
        'description' => (string)($r['description'] ?? ''),
        'type'        => qs_db_vote_type_norm((string)($r['type'] ?? 'single')),
        'options'     => is_array($r['options'] ?? null) ? array_values($r['options']) : [],
        'enable'      => !empty($r['enable']),
        'sort'        => (int)($r['sort'] ?? 0),
        'start_time'  => $r['start_time'] ?? null,
        'end_time'    => $r['end_time'] ?? null,
        'show_result' => !empty($r['show_result']),
        'ctime'       => $r['ctime'] ?? null,
        'mtime'       => $r['mtime'] ?? null,
    ];
}

/** 读取投票列表；$enabledOnly=true 只取启用项。返回 id => 投票数组 */
function qs_db_votes_list(bool $enabledOnly = false): array
{
    $raw = qs_vote_store_read('votes');
    $out = [];
    foreach ($raw as $id => $r) {
        if (!is_array($r)) {
            continue;
        }
        if (!isset($r['id']) || (int)$r['id'] <= 0) {
            $r['id'] = (int)$id;
        }
        $row = qs_db_vote_row($r);
        if ($enabledOnly && !$row['enable']) {
            continue;
        }
        $out[$row['id']] = $row;
    }
    // 与 MySQL 版一致：sort 升序，sort 相同按 id 降序
    uasort($out, static function (array $a, array $b): int {
        if ($a['sort'] !== $b['sort']) {
            return $a['sort'] <=> $b['sort'];
        }
        return $b['id'] <=> $a['id'];
    });
    return $out;
}

/** 单条读取，不存在返回 null */
function qs_db_vote_get(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $raw = qs_vote_store_read('votes');
    $key = null;
    if (isset($raw[$id]) && is_array($raw[$id])) {
        $key = $id;
    } else {
        foreach ($raw as $k => $r) {
            if (is_array($r) && (int)($r['id'] ?? 0) === $id) {
                $key = $k;
                break;
            }
        }
    }
    if ($key === null) {
        return null;
    }
    $r = $raw[$key];
    $r['id'] = $id;
    return qs_db_vote_row($r);
}

/** 保存投票（新增或更新），返回投票 id */
function qs_db_vote_save(array $v): int
{
    // 清洗选项：去空文本，补齐 oid
    $options = [];
    foreach (($v['options'] ?? []) as $opt) {
        if (!is_array($opt)) {
            continue;
        }
        $oid  = trim((string)($opt['oid'] ?? ''));
        $text = trim((string)($opt['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        if ($oid === '') {
            $oid = 'opt' . substr(md5(uniqid('', true)), 0, 8);
        }
        $options[] = ['oid' => $oid, 'text' => mb_substr($text, 0, 200)];
    }

    $start = trim((string)($v['start_time'] ?? ''));
    $end   = trim((string)($v['end_time'] ?? ''));
    $id    = (int)($v['id'] ?? 0);

    $fields = [
        'title'       => mb_substr(trim((string)($v['title'] ?? '')), 0, 255),
        'description' => trim((string)($v['description'] ?? '')),
        'type'        => qs_db_vote_type_norm((string)($v['type'] ?? 'single')),
        'options'     => $options,
        'enable'      => !empty($v['enable']),
        'sort'        => (int)($v['sort'] ?? 0),
        'start_time'  => $start !== '' ? $start : null,
        'end_time'    => $end !== '' ? $end : null,
        'show_result' => !empty($v['show_result']),
    ];

    return (int)qs_vote_store_tx('votes', static function (array &$data) use ($id, $fields): array {
        $now = date('Y-m-d H:i:s');

        if ($id > 0 && isset($data[$id]) && is_array($data[$id])) {
            $row = array_merge($data[$id], $fields);
            $row['id']    = $id;
            $row['mtime'] = $now;
            $data[$id] = $row;
            return [true, $id];
        }

        // 新建：编号取现有最大 id + 1；投票全删空后自然从 1 开始
        $maxId = 0;
        foreach ($data as $k => $r) {
            $cand = is_array($r) ? (int)($r['id'] ?? 0) : (int)$k;
            if ($cand > $maxId) {
                $maxId = $cand;
            }
        }
        $newId = $maxId + 1;

        $data[$newId] = array_merge($fields, [
            'id'    => $newId,
            'ctime' => $now,
            'mtime' => $now,
        ]);
        return [true, $newId];
    });
}

/** 删除投票及其全部记录 */
function qs_db_vote_delete(int $id): void
{
    if ($id <= 0) {
        return;
    }

    qs_vote_store_tx('votes', static function (array &$data) use ($id): array {
        if (isset($data[$id])) {
            unset($data[$id]);
        }
        foreach ($data as $k => $r) {
            if (is_array($r) && (int)($r['id'] ?? 0) === $id) {
                unset($data[$k]);
            }
        }
        return [true, null];
    });

    qs_vote_store_tx('vote_records', static function (array &$data) use ($id): array {
        $kept = [];
        foreach ($data as $rec) {
            if (!is_array($rec)) {
                continue;
            }
            if ((int)($rec['vote_id'] ?? 0) === $id) {
                continue;
            }
            $kept[] = $rec;
        }
        $data = $kept;
        return [true, null];
    });
}

/**
 * 是否已投过：登录用户按 username 判定，未登录按浏览器 cookie 判定。
 * IP 仅作留存记录，不参与重复拦截，避免同一公网 NAT 下多人被误伤。
 */
function qs_db_vote_has_voted(int $voteId, string $cookieKey, string $ip, string $username): bool
{
    if ($voteId <= 0) {
        return false;
    }
    if ($username === '' && $cookieKey === '') {
        return false;
    }
    $records = qs_vote_store_read('vote_records');
    foreach ($records as $rec) {
        if (!is_array($rec) || (int)($rec['vote_id'] ?? 0) !== $voteId) {
            continue;
        }
        if ($username !== '') {
            if ((string)($rec['username'] ?? '') === $username) {
                return true;
            }
        } else {
            if ((string)($rec['cookie_key'] ?? '') === $cookieKey) {
                return true;
            }
        }
    }
    return false;
}

/** 写入投票记录（多选时写多条） */
function qs_db_vote_add_record(int $voteId, array $optionIds, string $ip, string $cookieKey, string $username): void
{
    if ($voteId <= 0 || empty($optionIds)) {
        return;
    }
    $now = time();

    qs_vote_store_tx('vote_records', static function (array &$data) use ($voteId, $optionIds, $ip, $cookieKey, $username, $now): array {
        $next = 1;
        foreach ($data as $rec) {
            if (is_array($rec)) {
                $cand = (int)($rec['id'] ?? 0);
                if ($cand >= $next) {
                    $next = $cand + 1;
                }
            }
        }
        foreach ($optionIds as $oid) {
            $data[] = [
                'id'         => $next,
                'vote_id'    => $voteId,
                'option_id'  => (string)$oid,
                'ip'         => $ip,
                'cookie_key' => $cookieKey,
                'username'   => $username,
                'addtime'    => $now,
            ];
            $next++;
        }
        return [true, null];
    });
}

/** 计票：返回 option_id => 票数 */
function qs_db_vote_result(int $voteId): array
{
    $out = [];
    if ($voteId <= 0) {
        return $out;
    }
    foreach (qs_vote_store_read('vote_records') as $rec) {
        if (!is_array($rec) || (int)($rec['vote_id'] ?? 0) !== $voteId) {
            continue;
        }
        $oid = (string)($rec['option_id'] ?? '');
        if ($oid === '') {
            continue;
        }
        $out[$oid] = (int)($out[$oid] ?? 0) + 1;
    }
    return $out;
}

/** 提交频率限制：同一 IP 在 $window 秒内最多 $max 次 */
function qs_db_vote_rate_ok(string $ip, int $window = 600, int $max = 30): bool
{
    if ($ip === '') {
        return true;
    }
    $since = time() - $window;
    $count = 0;
    foreach (qs_vote_store_read('vote_records') as $rec) {
        if (!is_array($rec)) {
            continue;
        }
        if ((string)($rec['ip'] ?? '') === $ip && (int)($rec['addtime'] ?? 0) > $since) {
            $count++;
            if ($count >= $max) {
                return false;
            }
        }
    }
    return true;
}

/** 参与人数（按投票者去重：登录名 > cookie > IP） */
function qs_db_vote_voter_count(int $voteId): int
{
    if ($voteId <= 0) {
        return 0;
    }
    $seen = [];
    foreach (qs_vote_store_read('vote_records') as $rec) {
        if (!is_array($rec) || (int)($rec['vote_id'] ?? 0) !== $voteId) {
            continue;
        }
        $username  = (string)($rec['username'] ?? '');
        $cookieKey = (string)($rec['cookie_key'] ?? '');
        if ($username !== '') {
            $key = 'u:' . $username;
        } elseif ($cookieKey !== '') {
            $key = 'c:' . $cookieKey;
        } else {
            $key = 'i:' . (string)($rec['ip'] ?? '');
        }
        $seen[$key] = 1;
    }
    return count($seen);
}
