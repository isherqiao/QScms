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
 * @File         vote_embed_lib.php
 * @Description  Renders vote cards and expands the [vote id="N"] shortcode in post content,
 *               for both the dynamic site and the SSG static build.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!function_exists('qs_vote_period')) {
    /**
     * 投票有效期判定
     * @return array{open:bool,text:string}
     */
    function qs_vote_period(array $row): array
    {
        $now   = time();
        $start = !empty($row['start_time']) ? strtotime((string)$row['start_time']) : 0;
        $end   = !empty($row['end_time'])   ? strtotime((string)$row['end_time'])   : 0;
        if ($start && $start > $now) { return ['open' => false, 'text' => '投票尚未开始']; }
        if ($end && $end < $now)     { return ['open' => false, 'text' => '投票已结束']; }
        return ['open' => true, 'text' => ''];
    }
}

if (!function_exists('qs_vote_page_url')) {
    /**
     * 投票页绝对地址：动态站走 ?vote/<id>/，静态站指向动态站
     */
    function qs_vote_page_url(int $id, bool $static = false): string
    {
        global $qs_config, $static_config;
        if ($static) {
            $base = rtrim((string)($qs_config['site_link'] ?? ($static_config['static_root_url'] ?? '')), '/');
        } else {
            $base = rtrim(function_exists('qs_site_link') ? (string)qs_site_link(false) : '', '/');
            if ($base === '') {
                $base = rtrim((string)($qs_config['site_link'] ?? ''), '/');
            }
        }
        $path = '?vote/' . $id . '/';
        return $base === '' ? $path : $base . '/' . $path;
    }
}

if (!function_exists('qs_vote_token')) {
    /**
     * 投票提交令牌（与会话绑定，用于校验表单来源）
     */
    function qs_vote_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (empty($_SESSION['qs_vote_token'])) {
            $_SESSION['qs_vote_token'] = bin2hex(random_bytes(16));
        }
        return (string)$_SESSION['qs_vote_token'];
    }
}

if (!function_exists('qs_vote_voter_key')) {
    /** 访客标识：浏览器 cookie，缺失时生成并写回 */
    function qs_vote_voter_key(): string
    {
        $k = trim((string)($_COOKIE['qs_voter'] ?? ''));
        if ($k === '') {
            $k = bin2hex(random_bytes(16));
            if (!headers_sent()) {
                setcookie('qs_voter', $k, time() + 3600 * 12, '/');
            }
            $_COOKIE['qs_voter'] = $k;
        }
        return $k;
    }
}

if (!function_exists('qs_vote_voter_name')) {
    /** 已登录访客的用户名，未登录返回空串 */
    function qs_vote_voter_name(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        return trim((string)($_SESSION['Qsis_user_name'] ?? ''));
    }
}

if (!function_exists('qs_vote_my_choice')) {
    /**
     * 本次提交带回来的选择（来自跳转参数 qs_mine=<投票id>:<oid|oid>）
     * 只用于“您投的是…”提示，不写 cookie、不预选表单
     */
    function qs_vote_my_choice(int $id): array
    {
        $raw = (string)($_GET['qs_mine'] ?? '');
        if ($raw === '' || strpos($raw, ':') === false) {
            return [];
        }
        [$vid, $list] = explode(':', $raw, 2);
        if ((int)$vid !== $id) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode('|', $list)), 'strlen'));
    }
}

if (!function_exists('qs_vote_card_css')) {
    /** 卡片样式（每次请求只输出一次） */
    function qs_vote_card_css(): string
    {
        static $done = false;
        if ($done) { return ''; }
        $done = true;
        return '<style>'
            . '.qs-vote-card{border:1px solid rgba(127,127,127,.35);border-radius:12px;padding:16px 18px;margin:22px 0;line-height:1.7;}'
            . '.qs-vote-card .qs-vote-hd{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;}'
            . '.qs-vote-card .qs-vote-t{font-size:1.05em;font-weight:600;}'
            . '.qs-vote-card .qs-vote-kind{font-size:12px;padding:1px 8px;border:1px solid rgba(127,127,127,.4);border-radius:999px;opacity:.8;}'
            . '.qs-vote-card .qs-vote-desc{opacity:.85;margin:0 0 12px;font-size:.95em;}'
            . '.qs-vote-card form{margin:0;}'
            . '.qs-vote-card label.qs-vote-opt{display:flex;align-items:center;gap:8px;padding:7px 8px;border-radius:8px;cursor:pointer;}'
            . '.qs-vote-card label.qs-vote-opt:hover{background:rgba(127,127,127,.12);}'
            . '.qs-vote-card .qs-vote-btn{margin-top:12px;padding:8px 20px;border:1px solid rgba(127,127,127,.5);border-radius:8px;background:transparent;cursor:pointer;font-size:.95em;}'
            . '.qs-vote-card .qs-vote-bar{position:relative;height:28px;border-radius:6px;background:rgba(127,127,127,.14);margin:6px 0;overflow:hidden;}'
            . '.qs-vote-card .qs-vote-bar i{position:absolute;left:0;top:0;bottom:0;background:rgba(59,111,224,.32);}'
            . '.qs-vote-card .qs-vote-bar span{position:relative;z-index:1;padding:0 10px;line-height:28px;font-size:.9em;}'
            . '.qs-vote-card .qs-vote-ft{margin-top:12px;font-size:.85em;opacity:.75;}'
            . '.qs-vote-card .qs-vote-ft a{text-decoration:underline;}'
            . '</style>';
    }
}

if (!function_exists('qs_vote_card_html')) {
    /**
     * 渲染投票卡片
     *
     * @param array $row    qs_db_vote_get() 返回的投票行
     * @param array $opt    ['static'=>bool,'voted'=>bool,'show_form'=>bool]
     */
    function qs_vote_card_html(array $row, array $opt = []): string
    {
        $static = !empty($opt['static']);
        $id     = (int)($row['id'] ?? 0);
        if ($id <= 0 || empty($row['enable'])) { return ''; }

        $e       = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $opts    = is_array($row['options'] ?? null) ? $row['options'] : [];
        $multiple = (($row['type'] ?? 'single') === 'multiple');
        $period  = qs_vote_period($row);
        $voted   = !empty($opt['voted']);
        $result  = function_exists('qs_db_vote_result') ? (array)qs_db_vote_result($id) : [];
        $total   = array_sum($result);
        $voters  = 0;
        if (function_exists('qs_db_vote_voter_count')) {
            try { $voters = (int)qs_db_vote_voter_count($id); } catch (Throwable $e2) { $voters = 0; }
        }

        // 显示表单的条件：动态站、投票进行中、未投过
        $showForm = (!$static) && $period['open'] && !$voted && !empty($opts);
        // 显示结果的条件：后台勾选了“展示结果”，或当前访问者是管理员
        $showResult = $static ? false : (!empty($row['show_result']) || (function_exists('qs_Pass') && qs_Pass()));
        $myTexts = array_values(array_filter((array)($opt['my_texts'] ?? []), 'strlen'));

        $html  = qs_vote_card_css();
        $html .= '<div class="qs-vote-card" data-vote="' . $id . '">';
        $html .= '<div class="qs-vote-hd"><span class="qs-vote-t">' . $e($row['title'] ?? '') . '</span>'
               . '<span class="qs-vote-kind">' . ($multiple ? '多选' : '单选') . '</span></div>';
        if (!empty($row['description'])) {
            $html .= '<p class="qs-vote-desc">' . nl2br($e($row['description'])) . '</p>';
        }

        $html .= '<div class="qs-vote-body">';
        if ($showForm) {
            $html .= '<form method="post" action="' . $e(qs_vote_page_url($id, false)) . '">';
            $html .= '<input type="hidden" name="vote_id" value="' . $id . '">';
            $html .= '<input type="hidden" name="vote_token" value="' . $e(qs_vote_token()) . '">';
            $html .= '<input type="hidden" name="vote_return" value="' . $e((string)($_SERVER['REQUEST_URI'] ?? '')) . '">';
            foreach ($opts as $o) {
                $oid  = $e($o['oid'] ?? '');
                $text = $e($o['text'] ?? '');
                $html .= '<label class="qs-vote-opt"><input type="' . ($multiple ? 'checkbox' : 'radio') . '" name="' . ($multiple ? 'options[]' : 'option') . '" value="' . $oid . '"> ' . $text . '</label>';
            }
            $html .= '<button type="submit" class="qs-vote-btn">提交投票</button>';
            $html .= '</form>';
        }

        // 本次提交带回来的选择，只显示给自己看，不做任何预选
        if (!empty($myTexts)) {
            $html .= '<p class="qs-vote-mine">您投的是：' . $e(implode('、', $myTexts)) . '</p>';
        }

        if ($showResult) {
            foreach ($opts as $o) {
                $oid   = (string)($o['oid'] ?? '');
                $text  = $e($o['text'] ?? '');
                $cnt   = (int)($result[$oid] ?? 0);
                $pct   = $total > 0 ? round($cnt * 100 / $total, 1) : 0;
                $html .= '<div class="qs-vote-bar"><i style="width:' . $pct . '%"></i>'
                       . '<span>' . $text . ' · ' . $pct . '%（' . $cnt . '票）</span></div>';
            }
            if (empty($opts)) {
                $html .= '<p class="qs-vote-desc">该投票暂无选项。</p>';
            }
        }

        if (!$showForm && !$showResult) {
            $msg = $static ? '投票需到动态站点参与，点击下方链接进入。' : ($period['text'] !== '' ? $period['text'] : '本投票不公开实时结果。');
            $html .= '<p class="qs-vote-desc">' . $e($msg) . '</p>';
        }
        $html .= '</div>';

        $html .= '<div class="qs-vote-ft">共 ' . ($voters > 0 ? $voters : $total) . ' 人参与 · '
               . '<a href="' . $e(qs_vote_page_url($id, $static)) . '"' . ($static ? ' target="_blank" rel="noopener"' : '') . '>'
               . ($static ? '到站点投票' : '投票页') . '</a>';
        if (!$static && $period['open']) {
            $html .= ' · 截止：' . ($row['end_time'] ? $e($row['end_time']) : '不限');
        }
        $html .= '</div>';

        return $html . '</div>';
    }
}

if (!function_exists('qs_vote_shortcodes')) {
    /**
     * 展开正文里的投票短标签：支持 [vote id="3"]、[vote id=3]、[vote:3]
     */
    function qs_vote_shortcodes(string $html, bool $static = false): string
    {
        if ($html === '' || strpos($html, '[vote') === false) { return $html; }
        if (!function_exists('qs_db_vote_get')) { return $html; }

        return (string)preg_replace_callback(
            '~\[vote[^\]]{0,40}\]~i',
            static function (array $m) use ($static): string {
                if (!preg_match('~(\d+)~', $m[0], $idm)) { return $m[0]; }
                $id  = (int)$idm[1];
                $row = qs_db_vote_get($id);
                if (!is_array($row)) { return ''; }
                // 显示“您投的是…”：选项来自本次跳转参数，不写 cookie、不预选表单
                $voted = false;
                $mine  = [];
                if (!$static) {
                    $picked = qs_vote_my_choice($id);
                    if (!empty($picked)) {
                        $voted = true;
                        foreach ((array)($row['options'] ?? []) as $i => $o) {
                            if (in_array((string)($o['oid'] ?? ''), $picked, true)) {
                                $mine[] = '第' . ($i + 1) . '项「' . (string)($o['text'] ?? '') . '」';
                            }
                        }
                    } elseif (isset($_COOKIE['qs_voted_' . $id])) {
                        $voted = true;
                    }
                }
                return qs_vote_card_html($row, ['static' => $static, 'voted' => $voted, 'my_texts' => $mine]);
            },
            $html
        );
    }
}
