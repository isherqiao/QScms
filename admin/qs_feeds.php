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
 * @File         qs_feeds.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  RSS 2.0 feed generator: collects the published posts and pages and renders the feed document.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * QsCms 动态站点 RSS / Sitemap 生成
 *
 * 生成站点根目录 rss.xml 与 sitemap.xml。
 * 随文章、页面发布（保存成功）自动调用 qs_feeds_regenerate() 更新。
 * 仅收集 state=publish 的文章与页面；rss.xml 取最近 30 篇已发布文章。
 */
if (!defined('SITE_ROOT')) exit;

/**
 * XML 转义
 */
function qs_feeds_xml_esc($s) {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * 重新生成根目录 rss.xml 与 sitemap.xml
 */
function qs_feeds_regenerate() {
    global $qs_config;

    $root     = rtrim(SITE_ROOT, '/\\') . '/';
    $siteLink = rtrim(isset($qs_config['site_link']) ? (string)$qs_config['site_link'] : '', '/') . '/';
    $siteName = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : '';
    $siteDesc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
    $sitemode = isset($qs_config['sitemode']) ? $qs_config['sitemode'] : 'article';
    $urlEnds  = isset($qs_config['url_ends'])  ? $qs_config['url_ends']  : '.qshtml';
    $postDir  = rtrim(isset($qs_config['qs_post_dir']) ? (string)$qs_config['qs_post_dir'] : 'data/posts/', '/') . '/';

    // ---- 读取已发布文章 ----
    $posts = [];
    $postIndex = $root . $postDir . 'index/publish.php';
    if (is_file($postIndex)) {
        require $postIndex; // 定义 $qs_posts
        if (is_array($qs_posts)) {
            if (function_exists('post_sort')) {
                uasort($qs_posts, 'post_sort');
            }
            foreach ($qs_posts as $id => $p) {
                if (is_array($p) && isset($p['state']) && $p['state'] === 'publish') {
                    $p['id'] = $id;
                    $posts[] = $p;
                }
            }
        }
        unset($qs_posts);
    }

    // ---- 读取已发布页面 ----
    $pages = [];
    $pageIndex = $root . 'data/pages/index/publish.php';
    if (is_file($pageIndex)) {
        require $pageIndex; // 定义 $qs_pages
        if (is_array($qs_pages)) {
            foreach ($qs_pages as $name => $p) {
                if (is_array($p) && isset($p['state']) && $p['state'] === 'publish') {
                    $pages[] = $p;
                }
            }
        }
        unset($qs_pages);
    }

    // ---- RSS 2.0 ----
    $rss  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $rss .= "<rss version=\"2.0\">\n<channel>\n";
    $rss .= "  <title>" . qs_feeds_xml_esc($siteName) . "</title>\n";
    $rss .= "  <link>" . qs_feeds_xml_esc($siteLink) . "</link>\n";
    $rss .= "  <description>" . qs_feeds_xml_esc($siteDesc) . "</description>\n";
    $rss .= "  <language>zh-cn</language>\n";
    $recent = array_slice($posts, 0, 30);
    foreach ($recent as $p) {
        $id     = isset($p['id']) ? $p['id'] : '';
        $url    = $siteLink . '?' . $sitemode . '/' . $id . $urlEnds;
        $title  = isset($p['title']) ? $p['title'] : '';
        $author = isset($p['user'])  ? $p['user']  : '';
        $ts     = strtotime((isset($p['date']) ? $p['date'] : '') . ' ' . (isset($p['time']) ? $p['time'] : ''));
        $pubDate = $ts ? date('r', $ts) : '';

        // 摘要：读内容文件取 summary / content 截断
        $desc = '';
        $cf = $root . $postDir . 'data/' . $id . '.php';
        if (is_file($cf)) {
            $fc = file_get_contents($cf);
            $fc = preg_replace('/^<\?php\s*\/\/\s*/', '', $fc);
            $fc = preg_replace('/\?>$/', '', $fc);
            $cd = @unserialize($fc);
            if (is_array($cd)) {
                $raw = !empty($cd['summary']) ? $cd['summary'] : (isset($cd['content']) ? $cd['content'] : '');
                $raw = trim(strip_tags($raw));
                $raw = function_exists('mb_substr') ? mb_substr($raw, 0, 200, 'UTF-8') : substr($raw, 0, 200);
                $desc = $raw;
            }
        }
        if ($desc === '') { $desc = $title; }

        $rss .= "  <item>\n";
        $rss .= "    <title>" . qs_feeds_xml_esc($title) . "</title>\n";
        $rss .= "    <link>" . qs_feeds_xml_esc($url) . "</link>\n";
        $rss .= "    <guid isPermaLink=\"true\">" . qs_feeds_xml_esc($url) . "</guid>\n";
        if ($pubDate) { $rss .= "    <pubDate>" . $pubDate . "</pubDate>\n"; }
        if ($author !== '') { $rss .= "    <author>" . qs_feeds_xml_esc($author) . "</author>\n"; }
        $rss .= "    <description>" . qs_feeds_xml_esc($desc) . "</description>\n";
        $rss .= "  </item>\n";
    }
    $rss .= "</channel>\n</rss>\n";
    file_put_contents($root . 'rss.xml', $rss);

    // ---- Sitemap XML ----
    $sm  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $sm .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    $sm .= "  <url>\n    <loc>" . qs_feeds_xml_esc($siteLink) . "</loc>\n  </url>\n";
    foreach ($posts as $p) {
        $url = $siteLink . '?' . $sitemode . '/' . (isset($p['id']) ? $p['id'] : '') . $urlEnds;
        $lm  = date('Y-m-d', strtotime((isset($p['date']) ? $p['date'] : '') . ' ' . (isset($p['time']) ? $p['time'] : '')));
        $sm .= "  <url>\n    <loc>" . qs_feeds_xml_esc($url) . "</loc>\n    <lastmod>" . $lm . "</lastmod>\n  </url>\n";
    }
    foreach ($pages as $p) {
        if (empty($p['path'])) { continue; }
        $url = $siteLink . '?' . $p['path'] . '/';
        $lm  = date('Y-m-d', strtotime((isset($p['date']) ? $p['date'] : '') . ' ' . (isset($p['time']) ? $p['time'] : '')));
        $sm .= "  <url>\n    <loc>" . qs_feeds_xml_esc($url) . "</loc>\n    <lastmod>" . $lm . "</lastmod>\n  </url>\n";
    }
    $sm .= "</urlset>\n";
    file_put_contents($root . 'sitemap.xml', $sm);
}
