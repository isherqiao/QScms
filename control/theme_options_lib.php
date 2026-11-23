<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         theme_options_lib.php
 * @Description  文件版主题设置引擎：schema 驱动读取 / 保存 / 渲染表单。
 *               配置值统一落在 control/data/<主题目录名>/theme_options.php，
 *               上传的主题图片也落在同一目录，与文件版“数据存文件”的存储方式保持一致。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

/**
 * 主题配置的存储目录：control/data/<主题目录名>
 *
 * @param string $themeDir 主题目录绝对路径
 * @return string
 */
function qs_theme_opts_store_dir($themeDir)
{
    $themeName = basename(str_replace('\\', '/', rtrim((string)$themeDir, '/\\')));
    return rtrim(SITE_ROOT, '/\\') . '/control/data/' . $themeName;
}

/**
 * 主题配置文件的绝对路径：control/data/<主题目录名>/theme_options.php
 *
 * @param string $themeDir 主题目录绝对路径
 * @return string
 */
function qs_theme_opts_store_file($themeDir)
{
    return qs_theme_opts_store_dir($themeDir) . '/theme_options.php';
}

/**
 * 确保存储目录存在
 *
 * @param string $dir
 * @return bool
 */
function qs_theme_opts_ensure_dir($dir)
{
    if (is_dir($dir)) {
        return true;
    }
    return @mkdir($dir, 0755, true);
}

/**
 * 读取主题配置：schema 默认值 + 已保存值
 *
 * 优先读 control/data/<主题>/theme_options.php；读不到时回退到主题目录内
 * 的 theme_options.php（兼容从 MySQL 版搬来的旧配置）。
 *
 * @param string $themeDir 主题目录绝对路径
 * @param array  $schema   字段定义
 * @return array
 */
function qs_theme_opts_load($themeDir, $schema)
{
    $out = array();
    if (!is_array($schema)) {
        return $out;
    }
    foreach ($schema as $key => $def) {
        $out[$key] = isset($def['default']) ? (string)$def['default'] : '';
    }

    $saved = array();
    $file = qs_theme_opts_store_file($themeDir);
    if (is_file($file)) {
        $theme_options_saved = array();
        include $file;
        if (is_array($theme_options_saved)) {
            $saved = $theme_options_saved;
        }
    } else {
        $legacy = rtrim($themeDir, '/\\') . '/theme_options.php';
        if (is_file($legacy)) {
            $theme_options_saved = array();
            include $legacy;
            if (is_array($theme_options_saved)) {
                $saved = $theme_options_saved;
            }
        }
    }

    foreach ($schema as $key => $def) {
        if (isset($saved[$key])) {
            $out[$key] = (string)$saved[$key];
        }
    }
    return $out;
}

/**
 * 处理主题配置提交，写入 control/data/<主题>/theme_options.php
 *
 * @param string $themeDir 主题目录绝对路径
 * @param array  $schema   字段定义
 * @return string 空字符串表示没有提交，否则为结果提示
 */
function qs_theme_opts_save($themeDir, $schema)
{
    if (empty($_POST['qs_theme_opts_save']) || !is_array($schema)) {
        return '';
    }

    $dir = qs_theme_opts_store_dir($themeDir);
    if (!qs_theme_opts_ensure_dir($dir)) {
        return '保存失败，无法创建配置目录 control/data/' . basename($dir);
    }

    $vals = array();
    $errs = array();
    foreach ($schema as $key => $def) {
        $type = isset($def['type']) ? $def['type'] : 'text';

        if ($type === 'image') {
            $vals[$key] = qs_theme_opts_handle_image($themeDir, $key, $errs);
            continue;
        }

        $raw = isset($_POST['opt'][$key]) ? $_POST['opt'][$key] : '';
        if (!is_string($raw)) {
            $raw = '';
        }

        switch ($type) {
            case 'switch':
                $vals[$key] = !empty($raw) ? '1' : '0';
                break;
            case 'number':
                $vals[$key] = (string)intval($raw);
                break;
            case 'color':
                $vals[$key] = preg_match('/^#[0-9a-fA-F]{3,8}$/', $raw)
                    ? $raw
                    : (isset($def['default']) ? (string)$def['default'] : '#000000');
                break;
            case 'textarea':
                $vals[$key] = str_replace(array("\r\n", "\r"), "\n", trim($raw));
                break;
            default:
                $vals[$key] = trim($raw);
        }
    }

    $code = "<?php\n"
        . "if (!defined('SITE_ROOT')) exit;\n"
        . "/** 本文件由后台「主题设置」自动生成，请勿手工编辑。 */\n"
        . "\$theme_options_saved = " . var_export($vals, true) . ";\n";

    $file = $dir . '/theme_options.php';
    $ok = @file_put_contents($file, $code);
    if ($ok === false) {
        return '保存失败，请检查 control/data/' . basename($dir) . ' 的写入权限';
    }
    @chmod($file, 0644);

    if ($errs) {
        return implode('；', $errs);
    }
    return '保存成功';
}

/**
 * 处理单个图片字段的「删除 / 上传 / 保留」三选一，返回应写入配置的文件名
 *
 * 图片落在 control/data/<主题>/ 下，便于站点直接访问。
 *
 * @param string $themeDir 主题目录绝对路径
 * @param string $key      字段名
 * @param array  $errs     错误收集（引用）
 * @return string          文件名（空字符串表示无图）
 */
function qs_theme_opts_handle_image($themeDir, $key, &$errs)
{
    $dir = qs_theme_opts_store_dir($themeDir);
    $old = isset($_POST['opt'][$key]) ? (string)$_POST['opt'][$key] : '';
    $old = basename(str_replace('\\', '/', $old));

    $del = !empty($_POST['qs_img_del'][$key]);
    if ($del) {
        if ($old !== '') {
            $p = $dir . '/' . $old;
            if (is_file($p)) {
                @unlink($p);
            }
        }
        return '';
    }

    $name = isset($_FILES['qs_img']['name'][$key]) ? (string)$_FILES['qs_img']['name'][$key] : '';
    $tmp  = isset($_FILES['qs_img']['tmp_name'][$key]) ? (string)$_FILES['qs_img']['tmp_name'][$key] : '';
    $size = isset($_FILES['qs_img']['size'][$key]) ? (int)$_FILES['qs_img']['size'][$key] : 0;
    $code = isset($_FILES['qs_img']['error'][$key]) ? (int)$_FILES['qs_img']['error'][$key] : UPLOAD_ERR_NO_FILE;

    if ($name === '' || $code === UPLOAD_ERR_NO_FILE) {
        return $old;
    }
    if ($code !== UPLOAD_ERR_OK) {
        $errs[] = '「' . $key . '」上传失败，错误码 ' . $code;
        return $old;
    }
    if ($size <= 0 || $size > 6 * 1024 * 1024) {
        $errs[] = '「' . $key . '」图片超过 6MB 限制';
        return $old;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif'), true)) {
        $errs[] = '「' . $key . '」只支持 jpg / png / webp / gif';
        return $old;
    }
    if (!is_file($tmp) || !@getimagesize($tmp)) {
        $errs[] = '「' . $key . '」不是有效图片文件';
        return $old;
    }
    if (!qs_theme_opts_ensure_dir($dir)) {
        $errs[] = '「' . $key . '」无法创建存储目录';
        return $old;
    }

    $safeKey = preg_replace('/[^a-z0-9_]/i', '', $key);
    if ($safeKey === '') {
        $safeKey = 'img';
    }
    $newFile = 'opt-' . $safeKey . '-' . date('YmdHis') . '.' . $ext;
    $target  = $dir . '/' . $newFile;

    $moved = @move_uploaded_file($tmp, $target);
    if (!$moved && is_file($tmp)) {
        $moved = @copy($tmp, $target);
    }
    if (!$moved) {
        $errs[] = '「' . $key . '」写入失败，请检查 control/data 目录权限';
        return $old;
    }
    @chmod($target, 0644);

    if ($old !== '' && $old !== $newFile) {
        $p = $dir . '/' . $old;
        if (is_file($p)) {
            @unlink($p);
        }
    }

    return $newFile;
}

/**
 * 生成主题配置图片的访问地址（control/data/<主题>/<文件名>）
 *
 * @param string $themeDir 主题目录绝对路径
 * @param string $file     文件名
 * @return string
 */
function qs_theme_opts_img_url($themeDir, $file)
{
    $file = trim((string)$file);
    if ($file === '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $file)) {
        return $file;
    }
    $themeName = basename(str_replace('\\', '/', rtrim($themeDir, '/\\')));
    $base = function_exists('qs_site_link') ? rtrim((string)qs_site_link(false), '/') : '';
    return $base . '/control/data/' . $themeName . '/' . basename($file);
}

/**
 * 把 textarea 里「每行一项、竖线分隔字段」的内容拆成二维数组
 *
 * @param string $text
 * @param int    $cols 期望列数
 * @return array
 */
function qs_theme_opts_rows($text, $cols = 2)
{
    $rows = array();
    if (!is_string($text) || $text === '') {
        return $rows;
    }
    $lines = preg_split('/\n/', $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $cells = array_map('trim', explode('|', $line));
        $row = array();
        for ($i = 0; $i < $cols; $i++) {
            $row[] = isset($cells[$i]) ? $cells[$i] : '';
        }
        $rows[] = $row;
    }
    return $rows;
}

/**
 * 输出 :root 层面的 CSS 变量覆盖，用于后台可调颜色
 *
 * @param array $pairs 变量名 => 值（变量名不带 -- 前缀）
 * @param bool  $scrollbar 是否输出自定义滚动条样式
 * @param string $tb 滚动条轨道色
 * @param string $th 滚动条滑块色
 * @return string
 */
function qs_theme_opts_css($pairs, $scrollbar = false, $tb = '#eef1f5', $th = '#b8c2cc')
{
    $rules = array();
    foreach ($pairs as $name => $value) {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            continue;
        }
        $safe = str_replace(array(';', '{', '}', '<', '>'), '', (string)$value);
        if ($safe === '') {
            continue;
        }
        $rules[] = '--' . $name . ':' . $safe;
    }

    $css = $rules ? ':root{' . implode(';', $rules) . '}' : '';

    if ($scrollbar) {
        $css .= ' ::-webkit-scrollbar{width:10px;height:10px}'
            . ' ::-webkit-scrollbar-track{background:' . $tb . '}'
            . ' ::-webkit-scrollbar-thumb{background:' . $th . ';border-radius:6px}'
            . ' ::-webkit-scrollbar-thumb:hover{filter:brightness(0.9)}'
            . ' html{scrollbar-color:' . $th . ' ' . $tb . ';scrollbar-width:thin}';
    }

    return $css;
}

/**
 * 渲染主题配置表单
 *
 * @param array  $schema  字段定义
 * @param array  $values  当前值
 * @param string $title   面板标题
 * @param string $message 保存结果提示
 * @param string $backUrl 返回系统设置的链接
 * @param string $themeDir 主题目录绝对路径（有图片字段时需要，用于生成预览地址）
 * @return string
 */
function qs_theme_opts_form($schema, $values, $title, $message = '', $backUrl = '', $themeDir = '')
{
    $h = function ($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    };

    $storeName = $themeDir !== '' ? basename(str_replace('\\', '/', rtrim($themeDir, '/\\'))) : '';

    $out = array();
    $out[] = '<style>';
    $out[] = 'body{margin:0;padding:24px;background:#f4f6f9;color:#243040;'
        . 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;font-size:14px;line-height:1.7}';
    $out[] = '.to-box{max-width:860px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(20,40,70,.08);padding:26px 28px 30px}';
    $out[] = '.to-title{margin:0 0 4px;font-size:19px;font-weight:700}';
    $out[] = '.to-note{margin:0 0 20px;color:#7a8794;font-size:13px}';
    $out[] = '.to-msg{padding:10px 14px;border-radius:6px;margin:0 0 18px;font-size:13px}';
    $out[] = '.to-ok{background:#e7f6ec;color:#1a6b38;border:1px solid #bfe4cd}';
    $out[] = '.to-err{background:#fdecec;color:#8c2525;border:1px solid #f3c6c6}';
    $out[] = '.to-field{padding:14px 0;border-bottom:1px dashed #e3e8ef}';
    $out[] = '.to-field:last-of-type{border-bottom:0}';
    $out[] = '.to-label{display:block;font-weight:600;margin-bottom:6px}';
    $out[] = '.to-help{display:block;color:#8b97a4;font-size:12px;font-weight:400;margin-top:2px}';
    $out[] = '.to-input,.to-textarea{width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #d3dbe4;'
        . 'border-radius:5px;font-size:14px;font-family:inherit;background:#fcfdfe}';
    $out[] = '.to-input:focus,.to-textarea:focus{outline:0;border-color:#4a7db8;background:#fff}';
    $out[] = '.to-textarea{min-height:110px;resize:vertical;line-height:1.8;font-family:ui-monospace,Consolas,monospace}';
    $out[] = '.to-color{display:flex;align-items:center;gap:12px}';
    $out[] = '.to-color input[type=color]{width:52px;height:34px;padding:0;border:1px solid #d3dbe4;border-radius:5px;background:#fff}';
    $out[] = '.to-color .to-input{max-width:180px}';
    $out[] = '.to-switch{display:flex;align-items:center;gap:10px}';
    $out[] = '.to-actions{display:flex;gap:12px;align-items:center;margin-top:22px;flex-wrap:wrap}';
    $out[] = '.to-btn{padding:9px 22px;border:0;border-radius:5px;background:#2b5d9c;color:#fff;font-size:14px;cursor:pointer}';
    $out[] = '.to-btn:hover{background:#234d83}';
    $out[] = '.to-back{color:#5b6a7a;font-size:13px;text-decoration:none}';
    $out[] = '.to-back:hover{text-decoration:underline}';
    $out[] = '.to-image{display:flex;flex-direction:column;gap:10px}';
    $out[] = '.to-img-preview{width:100%;max-width:360px;border:1px solid #d3dbe4;border-radius:6px;overflow:hidden;background:#f6f8fa}';
    $out[] = '.to-img-preview img{display:block;width:100%;height:auto}';
    $out[] = '.to-img-empty{color:#98a4b1;font-size:13px;margin:0}';
    $out[] = '.to-img-del{display:inline-flex;align-items:center;gap:6px;color:#8c2525;font-size:13px;font-weight:400}';
    $out[] = '.to-file{font-size:13px}';
    $out[] = '</style><div class="to-box">';
    $out[] = '<h2 class="to-title">' . $h($title) . '</h2>';
    $out[] = '<p class="to-note">这里的改动只影响当前主题的显示效果，保存后立即生效；配置写入 '
        . $h($storeName !== '' ? 'control/data/' . $storeName . '/theme_options.php' : 'control/data 目录') . '。</p>';

    if ($message !== '') {
        $cls = (strpos($message, '成功') !== false) ? 'to-ok' : 'to-err';
        $out[] = '<div class="to-msg ' . $cls . '">' . $h($message) . '</div>';
    }

    $out[] = '<form method="post" action="" enctype="multipart/form-data">';
    foreach ($schema as $key => $def) {
        $label = isset($def['label']) ? $def['label'] : $key;
        $type  = isset($def['type']) ? $def['type'] : 'text';
        $help  = isset($def['help']) ? $def['help'] : '';
        $val   = isset($values[$key]) ? (string)$values[$key] : '';
        $name  = 'opt[' . $h($key) . ']';

        $out[] = '<div class="to-field">';
        $out[] = '<label class="to-label">' . $h($label);
        if ($help !== '') {
            $out[] = '<span class="to-help">' . $h($help) . '</span>';
        }
        $out[] = '</label>';

        switch ($type) {
            case 'switch':
                $checked = ($val === '1' || $val === 'on' || $val === 'true') ? ' checked' : '';
                $out[] = '<div class="to-switch"><input type="checkbox" id="' . $h($key) . '" name="' . $name
                    . '" value="1"' . $checked . '><label for="' . $h($key) . '" style="font-weight:400">启用</label></div>';
                break;

            case 'color':
                $out[] = '<div class="to-color"><input type="color" value="' . $h($val) . '" '
                    . 'oninput="this.nextElementSibling.value=this.value">'
                    . '<input class="to-input" type="text" name="' . $name . '" value="' . $h($val) . '" '
                    . 'oninput="this.previousElementSibling.value=this.value"></div>';
                break;

            case 'textarea':
                $out[] = '<textarea class="to-textarea" name="' . $name . '">' . $h($val) . '</textarea>';
                break;

            case 'number':
                $out[] = '<input class="to-input" type="number" min="0" name="' . $name . '" value="' . $h($val) . '">';
                break;

            case 'image':
                $imgUrl = ($themeDir !== '' && $val !== '') ? qs_theme_opts_img_url($themeDir, $val) : '';
                $out[] = '<div class="to-image">';
                if ($imgUrl !== '') {
                    $out[] = '<div class="to-img-preview"><img src="' . $h($imgUrl) . '" alt=""></div>';
                    $out[] = '<label class="to-img-del"><input type="checkbox" name="qs_img_del[' . $h($key)
                        . ']" value="1"> 移除这张图（保存后生效）</label>';
                } else {
                    $out[] = '<p class="to-img-empty">当前未设置图片</p>';
                }
                $out[] = '<input type="hidden" name="' . $name . '" value="' . $h($val) . '">';
                $out[] = '<input class="to-file" type="file" name="qs_img[' . $h($key) . ']" accept="image/*">';
                $out[] = '<span class="to-help">支持 jpg / png / webp / gif，单张不超过 6MB；上传后存入 control/data/'
                    . $h($storeName) . ' 目录。</span>';
                $out[] = '</div>';
                break;

            default:
                $out[] = '<input class="to-input" type="text" name="' . $name . '" value="' . $h($val) . '">';
        }
        $out[] = '</div>';
    }

    $out[] = '<div class="to-actions"><button class="to-btn" type="submit" name="qs_theme_opts_save" value="1">保存设置</button>';
    if ($backUrl !== '') {
        $out[] = '<a class="to-back" href="' . $h($backUrl) . '">← 返回系统设置</a>';
    }
    $out[] = '</div></form></div>';

    return implode("\n", $out);
}
