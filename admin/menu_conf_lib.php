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
 * @File         menu_conf_lib.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Menu configuration helper: load and save menu trees and validate the parent-child relations.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!function_exists('qs_menu_hierarchy_from_post')) {
    /**
     * 从 POST 读取带层级的菜单列表（与 set.php 内既有逻辑一致）
     * @return array 每项含 name/link/tar/parent_id/self_id
     */
    function qs_menu_hierarchy_from_post($nameKey, $urlKey, $tarKey, $parentKey, $selfKey)
    {
        $names   = isset($_POST[$nameKey])   ? (array) $_POST[$nameKey]   : array();
        $links   = isset($_POST[$urlKey])    ? (array) $_POST[$urlKey]    : array();
        $targets = isset($_POST[$tarKey])    ? (array) $_POST[$tarKey]    : array();
        $parents = isset($_POST[$parentKey]) ? (array) $_POST[$parentKey] : array();
        $selfIds = isset($_POST[$selfKey])   ? (array) $_POST[$selfKey]   : array();

        // 建立映射表 [SelfID => Index]，用于校验父子关系
        $selfIdMap = array();
        foreach ($names as $idx => $name) {
            $sid = isset($selfIds[$idx]) ? $selfIds[$idx] : '';
            if (!empty($sid)) {
                $selfIdMap[$sid] = $idx;
            }
        }

        $list = array();
        foreach ($names as $index => $name) {
            $rawParentId  = isset($parents[$index]) ? $parents[$index] : '0';
            $finalParentId = '0';
            $mySelfId      = isset($selfIds[$index]) ? $selfIds[$index] : '';

            if (!empty($rawParentId) && $rawParentId !== '0') {
                if (isset($selfIdMap[$rawParentId])) {
                    $finalParentId = $rawParentId;
                }
            }

            $list[] = array(
                'name'      => htmlspecialchars((string) $name),
                'link'      => htmlspecialchars(isset($links[$index]) ? (string) $links[$index] : ''),
                'tar'       => isset($targets[$index]) ? (string) $targets[$index] : '_self',
                'parent_id' => $finalParentId,
                'self_id'   => $mySelfId,
            );
        }
        return $list;
    }
}

if (!function_exists('qs_menu_key_valid')) {
    /**
     * 自定义菜单标识校验：小写字母开头，只允许小写字母/数字/下划线，长度 1-30
     * 同时避开内置函数名与 qs_menu / qs_sidemenu
     */
    function qs_menu_key_valid($key)
    {
        if (!is_string($key) || $key === '') {
            return false;
        }
        if (!preg_match('/^[a-z][a-z0-9_]{0,30}$/', $key)) {
            return false;
        }
        $reserved = array('menu', 'sidemenu', 'custom_menu');
        if (in_array($key, $reserved, true)) {
            return false;
        }
        return !function_exists('qs_menu_' . $key);
    }
}

if (!function_exists('qs_menu_conf_renderer_code')) {
    /**
     * 自定义菜单的通用渲染函数代码（与 functions.php 中 qs_menu 的标签结构保持一致）
     */
    function qs_menu_conf_renderer_code()
    {
        return <<<'PHPCODE'
if (!function_exists('qs_custom_menu')) {
function qs_custom_menu($key, $cssClass = '') {
    global $qs_custom_menus;
    if (!isset($qs_custom_menus[$key]['items']) || !is_array($qs_custom_menus[$key]['items'])) {
        return;
    }
    echo '<ul>';
    foreach ($qs_custom_menus[$key]['items'] as $url) {
        $urlParts = parse_url($url['link']);
        $path  = isset($urlParts['path']) ? $urlParts['path'] : '';
        $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $now = $path . $query;
        $full_url = urldecode($_SERVER['REQUEST_URI']);
        echo '<li ' . ($full_url == $now ? 'class="current"' : '') . '><a href="' . $url['link'] . '" target="' . $url['tar'] . '" class="' . $cssClass . '">' . $url['name'] . '</a></li>';
    }
    echo '</ul>';
}
}

PHPCODE;
    }
}

if (!function_exists('qs_menu_conf_build_dynamic')) {
    /**
     * 生成动态站菜单配置文件内容（control/data/menu_conf.php）
     */
    function qs_menu_conf_build_dynamic($qs_menu, $qs_side_menu, $qs_custom_menus)
    {
        $qs_menu        = is_array($qs_menu) ? $qs_menu : array();
        $qs_side_menu   = is_array($qs_side_menu) ? $qs_side_menu : array();
        $qs_custom_menus = is_array($qs_custom_menus) ? $qs_custom_menus : array();

        $code  = "<?php\n";
        $code .= "// menu_conf.php - 动态站菜单配置（系统自带两个菜单 + 自定义菜单）\n";
        $code .= "// Generated by set.php on " . date('Y-m-d H:i:s') . "\n";
        $code .= "// 注意：本文件由后台菜单管理自动生成，请勿手工修改。\n\n";
        $code .= qs_menu_conf_renderer_code();
        $code .= "\$qs_menu = " . var_export($qs_menu, true) . ";\n";
        $code .= "\$qs_side_menu = " . var_export($qs_side_menu, true) . ";\n";
        $code .= "\$qs_custom_menus = " . var_export($qs_custom_menus, true) . ";\n";

        // 为每个自定义菜单生成调用函数：qs_menu_<标识>()
        foreach ($qs_custom_menus as $key => $item) {
            if (!preg_match('/^[a-z][a-z0-9_]{0,30}$/', (string) $key)) {
                continue;
            }
            $code .= "\n// 自定义菜单「" . (isset($item['name']) ? $item['name'] : $key) . "」调用函数\n";
            $code .= "function qs_menu_" . $key . "(\$cssClass = '') { qs_custom_menu('" . $key . "', \$cssClass); }\n";
        }

        $code .= "?>";
        return $code;
    }
}

if (!function_exists('qs_menu_conf_build_ssg')) {
    /**
     * 生成静态站菜单配置文件内容（control/data/ssg_menu_conf.php）
     * 只定义 $qs_menu / $qs_side_menu，渲染仍由 ssg_work.php 里原有函数负责
     */
    function qs_menu_conf_build_ssg($qs_menu, $qs_side_menu)
    {
        $qs_menu      = is_array($qs_menu) ? $qs_menu : array();
        $qs_side_menu = is_array($qs_side_menu) ? $qs_side_menu : array();

        $code  = "<?php\n";
        $code .= "// ssg_menu_conf.php - 静态站菜单配置（仅 SSG 生成使用，与动态站菜单互不影响）\n";
        $code .= "// Generated by set_ssg_menu.php on " . date('Y-m-d H:i:s') . "\n";
        $code .= "// 注意：本文件由后台静态站菜单设置自动生成，请勿手工修改。\n\n";
        $code .= "\$qs_menu = " . var_export($qs_menu, true) . ";\n";
        $code .= "\$qs_side_menu = " . var_export($qs_side_menu, true) . ";\n";
        $code .= "?>";
        return $code;
    }
}

if (!function_exists('qs_menu_conf_write')) {
    /**
     * 写入菜单配置文件
     */
    function qs_menu_conf_write($path, $content)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            return false;
        }
        return (bool) file_put_contents($path, $content);
    }
}
