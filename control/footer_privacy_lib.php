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
 * @File         footer_privacy_lib.php
 * @CreatedTime  15/11/26 23:10:00
 * @LastEditTime 15/11/26 23:10:00
 * @Description  Shared footer/privacy renderer used by the dynamic site (functions.php) and the static generator (ssg_work.php).
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// 页脚与隐私：共用渲染库
// 只依赖全局 $qs_config 与 L()，动态站和 SSG 生成器各自引入即可。

/**
 * 输出结构化页脚信息：版权行 + 自定义链接 + 备案号
 * 数据来自后台「设置 → 页脚与隐私」。
 *
 * @param bool $print 是否直接输出
 * @return string
 */
function qs_footer_info($print = true)
{
    global $qs_config;
    if (!is_array($qs_config)) {
        return '';
    }
    // 开关：明确关闭时不输出
    if (isset($qs_config['footer_info_on']) && $qs_config['footer_info_on'] === '') {
        return '';
    }
    // 没填任何内容时不输出空壳，老站保持原样
    $hasData = trim((string)($qs_config['footer_copyright'] ?? '')) !== ''
            || trim((string)($qs_config['footer_links'] ?? '')) !== ''
            || trim((string)($qs_config['icp_no'] ?? '')) !== '';
    if (!$hasData) {
        return '';
    }

    $out = '';

    $copy = trim((string)($qs_config['footer_copyright'] ?? ''));
    if ($copy !== '') {
        $out .= '<div class="qs-footer-copyright">' . htmlspecialchars($copy, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    $links = trim((string)($qs_config['footer_links'] ?? ''));
    if ($links !== '') {
        $items = [];
        foreach (preg_split('/\r\n|\r|\n/', $links) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = explode('|', $line, 2);
            $name  = trim($parts[0]);
            $url   = isset($parts[1]) ? trim($parts[1]) : '';
            if ($name === '') {
                continue;
            }
            if ($url === '') {
                $items[] = '<span>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>';
            } else {
                $items[] = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" rel="nofollow">'
                         . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</a>';
            }
        }
        if (!empty($items)) {
            $out .= '<div class="qs-footer-links">' . implode(' | ', $items) . '</div>';
        }
    }

    $icp = trim((string)($qs_config['icp_no'] ?? ''));
    if ($icp !== '') {
        $icpUrl = trim((string)($qs_config['icp_url'] ?? ''));
        if ($icpUrl === '') {
            $icpUrl = 'https://beian.miit.gov.cn/';
        }
        $out .= '<div class="qs-footer-icp"><a href="' . htmlspecialchars($icpUrl, ENT_QUOTES, 'UTF-8')
              . '" target="_blank" rel="nofollow noopener">' . htmlspecialchars($icp, ENT_QUOTES, 'UTF-8') . '</a></div>';
    }

    if ($print) {
        echo $out;
        return $out;
    }
    return $out;
}

/**
 * 生成隐私政策页完整 HTML（不依赖任何主题模板）
 *
 * @return string
 */
function qs_privacy_html()
{
    global $qs_config;

    $title = trim((string)($qs_config['privacy_title'] ?? ''));
    if ($title === '') {
        $title = function_exists('L') ? L('Privacy_Title') : 'Privacy Policy';
    }
    $body = (string)($qs_config['privacy_body'] ?? '');
    $ver  = trim((string)($qs_config['privacy_version'] ?? ''));
    $upd  = trim((string)($qs_config['privacy_updated'] ?? ''));
    $site = trim((string)($qs_config['site_name'] ?? ''));
    $copy = trim((string)($qs_config['footer_copyright'] ?? ''));
    $icp  = trim((string)($qs_config['icp_no'] ?? ''));

    $home = trim((string)($qs_config['site_directory'] ?? '/'));
    if ($home === '') {
        $home = '/';
    }

    $lang = (string)($qs_config['language_file'] ?? 'lang_zh-CN.php');
    $lang = preg_replace('/^lang_|\.php$/i', '', $lang);
    $lang = str_replace('_', '-', $lang);

    $e = static function ($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    };

    $meta = '';
    if ($ver !== '') {
        $meta .= '<span>' . $e($ver) . '</span>';
    }
    if ($upd !== '') {
        $meta .= '<span>' . $e($upd) . '</span>';
    }

    $backHome = function_exists('L') ? L('Privacy_Back_Home') : 'Back to home';
    $emptyTip = function_exists('L') ? L('Privacy_Not_Configured') : 'Privacy policy is not written yet.';

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="<?= $e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index,follow">
<title><?= $e($title) ?><?= $site !== '' ? ' - ' . $e($site) : '' ?></title>
<style>
    :root { --ink:#22252a; --sub:#6b7280; --line:#e5e7eb; --paper:#fff; --bg:#f6f7f8; }
    * { box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; }
    body { margin:0; padding:32px 16px 48px; background:var(--bg); color:var(--ink);
        font:400 16px/1.85 -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; }
    .wrap { max-width:820px; margin:0 auto; }
    .card { background:var(--paper); border:1px solid var(--line); border-radius:12px; padding:40px 44px 36px; }
    h1 { font-size:26px; line-height:1.4; margin:0 0 10px; font-weight:600; letter-spacing:.5px; }
    .meta { color:var(--sub); font-size:13px; margin:0 0 26px; padding-bottom:18px; border-bottom:1px solid var(--line); }
    .meta span + span::before { content:"·"; margin:0 8px; color:#c9ccd1; }
    .body { font-size:15.5px; }
    .body h2 { font-size:18px; margin:30px 0 10px; font-weight:600; }
    .body h3 { font-size:16px; margin:24px 0 8px; font-weight:600; }
    .body p { margin:0 0 14px; }
    .body ul, .body ol { margin:0 0 16px; padding-left:22px; }
    .body li { margin-bottom:6px; }
    .body a { color:#1f5f8b; text-decoration:none; border-bottom:1px solid #cfe0ec; }
    .body a:hover { border-bottom-color:#1f5f8b; }
    .body img { max-width:100%; height:auto; }
    .body table { width:100%; border-collapse:collapse; margin:0 0 16px; font-size:14px; }
    .body table th, .body table td { border:1px solid var(--line); padding:8px 10px; text-align:left; }
    .empty { color:var(--sub); font-size:14px; padding:18px 0; }
    .foot { margin-top:26px; padding-top:18px; border-top:1px solid var(--line); color:var(--sub); font-size:13px; display:flex; flex-wrap:wrap; gap:6px 16px; align-items:center; }
    .foot a { color:var(--sub); text-decoration:none; }
    .foot a:hover { color:var(--ink); }
    @media (max-width:640px) {
        body { padding:16px 10px 32px; font-size:15px; }
        .card { padding:26px 20px 24px; border-radius:10px; }
        h1 { font-size:21px; }
        .body { font-size:15px; }
    }
    @media print { body { background:#fff; padding:0; } .card { border:0; padding:0; } .foot { display:none; } }
</style>
</head>
<body>
<div class="wrap">
    <article class="card">
        <h1><?= $e($title) ?></h1>
        <div class="meta"><?= $meta ?></div>
        <div class="body">
            <?php if (trim($body) === ''): ?>
                <div class="empty"><?= $e($emptyTip) ?></div>
            <?php else: ?>
                <?= $body ?>
            <?php endif; ?>
        </div>
        <div class="foot">
            <a href="<?= $e($home) ?>">&larr; <?= $e($backHome) ?></a>
            <?php if ($copy !== ''): ?><span><?= $e($copy) ?></span><?php endif; ?>
            <?php if ($icp !== ''): ?><span><?= $e($icp) ?></span><?php endif; ?>
        </div>
    </article>
</div>
</body>
</html>
    <?php
    return ob_get_clean();
}
