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
 * @File         filter.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Blocked-keyword filter: loads the keyword library including its chunk files and matches post content against the words.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

/**
 * 关键词过滤（屏蔽词）公共函数库
 * 被 admin/post-edit.php、admin/page-edit.php、admin/keywords/keywords_manage.php 共用
 * 配置存于 control/data/blocked_keywords.php
 *
 * 数据结构（分类结构）：
 *   $qs_blocked_keywords['分类键'] = ['name'=>显示名, 'enabled'=>1/0, 'words'=>[词,...]]
 */

if (!function_exists('qs_kw_default_categories')) {
    /** 默认分类定义（含基础词条与衍生变异词库） */
    function qs_kw_default_categories(): array {
        return [
            'sexy'     => ['name' => '色情',       'enabled' => 1, 'words' => []],
            'gamble'   => ['name' => '赌博',       'enabled' => 1, 'words' => []],
            'violence' => ['name' => '暴恐',       'enabled' => 1, 'words' => []],
            'drug'     => ['name' => '违禁药品',   'enabled' => 1, 'words' => []],
            'traffic'  => ['name' => '违规引流',   'enabled' => 1, 'words' => []],
            'abuse'    => ['name' => '低俗辱骂',   'enabled' => 1, 'words' => []],
        ];
    }
}

if (!function_exists('qs_kw_config_file')) {
    function qs_kw_config_file(): string {
        return rtrim(SITE_ROOT, '/') . '/control/data/blocked_keywords.php';
    }
}

if (!function_exists('qs_kw_normalize')) {
    /** 统一为分类结构：补齐默认分类、迁移旧一维词格式（旧词并入"色情"） */
    function qs_kw_normalize($data): array {
        $defaults = qs_kw_default_categories();
        $out = $defaults;
        if (!is_array($data)) return $out;

        // 旧格式：元素为字符串（一维词数组）→ 整体并入第一个分类
        $legacy = [];
        foreach ($data as $v) {
            if (is_string($v)) $legacy[] = trim($v);
        }
        if ($legacy) {
            $out['sexy']['words'] = array_values(array_unique(array_filter($legacy)));
            return $out;
        }

        foreach ($data as $k => $cat) {
            if (!is_array($cat)) continue;
            $key = (string)$k;
            if (!isset($out[$key])) {
                $out[$key] = ['name' => $key, 'enabled' => 1, 'words' => []];
            }
            $out[$key]['name']    = (string)($cat['name'] ?? $key);
            $out[$key]['enabled'] = !empty($cat['enabled']) ? 1 : 0;
            $words = $cat['words'] ?? [];
            $out[$key]['words'] = is_array($words)
                ? array_values(array_unique(array_filter(array_map('trim', $words), fn($w) => $w !== '')))
                : [];
        }
        return $out;
    }
}

if (!defined('QS_KW_CHUNK_MAX')) {
    /** 词库单文件字节上限：超过则自动切割为多个分片文件（可在此调整，默认 512KB） */
    define('QS_KW_CHUNK_MAX', 512 * 1024);
}

if (!function_exists('qs_blocked_keywords')) {
    /** 读取完整分类词库（自动合并分片文件，已归一化） */
    function qs_blocked_keywords(): array {
        static $cache = null;
        if ($cache !== null) return $cache;
        $qs_blocked_keywords = [];
        $file = qs_kw_config_file();
        if (file_exists($file)) {
            require $file;
            // 合并分片文件：每个分片定义 $qs_kw_chunk['分类键']=[词,...]
            if (is_array($qs_blocked_keywords)) {
                $dir = dirname($file);
                foreach ($qs_blocked_keywords as $ck => &$cat) {
                    if (!is_array($cat) || empty($cat['chunks'])) continue;
                    $words = (isset($cat['words']) && is_array($cat['words'])) ? $cat['words'] : [];
                    foreach ((array)$cat['chunks'] as $seq) {
                        $chunkFile = $dir . '/blocked_keywords_' . (int)$seq . '.php';
                        if (!is_file($chunkFile)) continue;
                        $qs_kw_chunk = null;
                        require $chunkFile;
                        if (is_array($qs_kw_chunk) && isset($qs_kw_chunk[$ck]) && is_array($qs_kw_chunk[$ck])) {
                            foreach ($qs_kw_chunk[$ck] as $w) $words[] = $w;
                        }
                    }
                    $cat['words'] = $words;
                }
                unset($cat);
            }
        }
        $cache = qs_kw_normalize($qs_blocked_keywords);
        return $cache;
    }
}

if (!function_exists('qs_keyword_check')) {
    /**
     * 检测标题/正文是否命中"已启用分类"的屏蔽词。
     * 返回 [词 => 分类名]，仅含参与过滤的分类；未启用分类不参与。
     */
    function qs_keyword_check(string $title, string $content): array {
        $cats = qs_blocked_keywords();
        $text = $title . "\n" . $content;
        $hits = [];
        foreach ($cats as $ck => $cat) {
            if (empty($cat['enabled'])) continue; // 未启用分类跳过
            foreach (($cat['words'] ?? []) as $w) {
                if ($w !== '' && mb_strpos($text, $w) !== false) {
                    $hits[$w] = $cat['name'] ?? $ck;
                }
            }
        }
        return $hits;
    }
}

if (!function_exists('qs_keyword_highlight_script')) {
    /**
     * 生成 Quill 编辑器关键词高亮 JS（纯 JS 片段，不带 <script> 标签）。
     * 调用处位于现有 <script> 块的 DOMContentLoaded 回调内、quill 创建之后，直接作为普通 JS 执行。
     */
    function qs_keyword_highlight_script(array $hits, string $quillVar = 'quill'): string {
        $words = array_keys($hits);
        if (!$words) return '';
        $json = json_encode($words, JSON_UNESCAPED_UNICODE);
        $q = $quillVar === '' ? 'quill' : $quillVar;
        return "\n    (function(){\n"
            . "        var words = {$json};\n"
            . "        var quill = window.{$q} || {$q};\n"
            . "        if (!quill) return;\n"
            . "        var text = quill.getText();\n"
            . "        words.forEach(function(w){\n"
            . "            if (!w) return;\n"
            . "            var from = 0, idx;\n"
            . "            while ((idx = text.indexOf(w, from)) > -1) {\n"
            . "                quill.formatText(idx, w.length, { background: '#ffe58f', color: '#ad6800' });\n"
            . "                from = idx + w.length;\n"
            . "            }\n"
            . "        });\n"
            . "    })();\n";
    }
}

if (!function_exists('qs_kw_write_main')) {
    /** 写主文件：分类结构 + 分片引用（chunks） */
    function qs_kw_write_main(array $cats): void {
        file_put_contents(qs_kw_config_file(),
            "<?php\n// 屏蔽关键词配置（分类结构，由 admin/keywords/keywords_manage.php 维护；enabled 1=参与过滤 0=不参与）\n"
            . "// 词库较大时自动切割为 blocked_keywords_N.php 分片，主文件通过 chunks 引用\n"
            . "\$qs_blocked_keywords = " . var_export($cats, true) . ";\n?>\n");
    }
}

if (!function_exists('qs_kw_write_chunk')) {
    /** 写分片文件：$qs_kw_chunk['分类键'] = [词,...] */
    function qs_kw_write_chunk(string $dir, int $seq, array $data): void {
        file_put_contents(rtrim($dir, '/\\') . '/blocked_keywords_' . $seq . '.php',
            "<?php\n// 屏蔽关键词分片（编号 {$seq}），由主文件引用\n\$qs_kw_chunk = " . var_export($data, true) . ";\n?>\n");
    }
}

if (!function_exists('qs_kw_delete_chunks')) {
    /** 删除目录下所有分片文件（blocked_keywords_N.php） */
    function qs_kw_delete_chunks(string $dir): void {
        foreach (glob(rtrim($dir, '/\\') . '/blocked_keywords_*.php') as $f) {
            @unlink($f);
        }
    }
}

if (!function_exists('qs_kw_save')) {
    /**
     * 写回分类词库配置。
     * 单文件序列化字节不超过 QS_KW_CHUNK_MAX 时整体内联保存；
     * 超过阈值时自动切割为多个分片文件（每片不超阈值），主文件仅存分类元数据 + chunks 引用。
     */
    function qs_kw_save(array $cats): void {
        $cats = qs_kw_normalize($cats);
        $dir = dirname(qs_kw_config_file());
        $budget = QS_KW_CHUNK_MAX;

        // 全量序列化估算：未超阈值 → 单文件内联保存，并清理旧分片
        if (strlen(var_export($cats, true)) <= $budget) {
            qs_kw_delete_chunks($dir);
            qs_kw_write_main($cats);
            return;
        }

        // 超过阈值 → 切分：主文件只存元数据 + 分片引用，词切分到分片文件
        $chunks = [];
        $catChunks = [];
        $seq = 0;
        foreach ($cats as $ck => &$cat) {
            $words = $cat['words'] ?? [];
            $cat['words'] = [];
            $cat['chunks'] = [];
            if (!$words) continue;
            $cur = [];
            $curSize = 0;
            foreach ($words as $w) {
                $itemSize = strlen($w) + 40; // 每词在 var_export 中的近似占用（含缩进/标点）
                if ($cur && ($curSize + $itemSize) > $budget) {
                    $seq++;
                    $chunks[$seq] = [$ck => $cur];
                    $catChunks[$ck][] = $seq;
                    $cur = [];
                    $curSize = 0;
                }
                $cur[] = $w;
                $curSize += $itemSize;
            }
            if ($cur) {
                $seq++;
                $chunks[$seq] = [$ck => $cur];
                $catChunks[$ck][] = $seq;
            }
            $cat['chunks'] = $catChunks[$ck];
        }
        unset($cat);

        qs_kw_delete_chunks($dir); // 清空旧分片，避免残留
        qs_kw_write_main($cats);
        foreach ($chunks as $n => $data) {
            qs_kw_write_chunk($dir, $n, $data);
        }
    }
}

/* ==================== 导入解析 ==================== */

if (!function_exists('qs_kw_split_words')) {
    /** 按全/半角逗号、顿号、分号、竖线、换行切分为词数组 */
    function qs_kw_split_words(string $raw): array {
        $parts = preg_split('/[,，、;；\x{ff0c}\x{3001}\x{ff1b}\x{007c}\r\n]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $words = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') $words[] = $p;
        }
        return array_values(array_unique($words));
    }
}

if (!function_exists('qs_kw_parse_txt')) {
    function qs_kw_parse_txt(string $raw): array {
        return qs_kw_split_words($raw);
    }
}

if (!function_exists('qs_kw_parse_csv')) {
    /** 解析 CSV：取每行第一列非空文本 */
    function qs_kw_parse_csv(string $file): array {
        $words = [];
        if (($h = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($h)) !== false) {
                $first = trim((string)($row[0] ?? ''));
                if ($first !== '') $words[] = $first;
            }
            fclose($h);
        }
        return array_values(array_unique($words));
    }
}

if (!function_exists('qs_kw_xlsx_words')) {
    /** 从 xlsx 的 sheet1.xml + sharedStrings.xml 提取第一列(A列)非空文本 */
    function qs_kw_xlsx_words(string $sheetXml, string $sharedXml): array {
        $shared = [];
        if ($sharedXml !== '') {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false) {
                foreach ($sx->xpath('//*[local-name()="si"]') as $si) {
                    $txt = '';
                    foreach ($si->xpath('.//*[local-name()="t"]') as $t) {
                        $txt .= (string)$t;
                    }
                    $shared[] = $txt;
                }
            }
        }
        $x = @simplexml_load_string($sheetXml);
        if ($x === false) return [];
        $words = [];
        foreach ($x->xpath('//*[local-name()="c"]') as $c) {
            $ref = (string)$c['r'];
            if ($ref === '' || $ref[0] !== 'A') continue; // 只取 A 列
            $t = (string)$c['t'];
            $v = trim((string)$c->v);
            $val = ($t === 's') ? (string)($shared[(int)$v] ?? '') : $v;
            $val = trim($val);
            if ($val !== '') $words[] = $val;
        }
        // 跳过可能的表头行
        if ($words && in_array(mb_strtolower($words[0]), ['关键词', '词', 'keyword', 'keywords', 'word', 'words'], true)) {
            array_shift($words);
        }
        return array_values(array_unique($words));
    }
}

if (!function_exists('qs_kw_rmdir')) {
    function qs_kw_rmdir(string $dir): void {
        if (!is_dir($dir)) return;
        foreach (scandir($dir) as $e) {
            if ($e === '.' || $e === '..') continue;
            $p = $dir . '/' . $e;
            is_dir($p) ? qs_kw_rmdir($p) : @unlink($p);
        }
        @rmdir($dir);
    }
}

if (!function_exists('qs_kw_parse_excel')) {
    /**
     * 解析 Excel(.xlsx) 第一列关键词。
     * 优先使用 PHP ZipArchive（宝塔等主流环境支持）；否则回退系统 tar/unzip 解压后解析。
     * 失败返回空数组。
     */
    function qs_kw_parse_excel(string $file): array {
        $tmp = rtrim(sys_get_temp_dir(), '/\\') . '/qskw_' . bin2hex(random_bytes(6));
        @mkdir($tmp, 0777, true);
        $words = [];

        // 1) ZipArchive（覆盖最广）
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($file) === true) {
                $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
                $shared = $zip->getFromName('xl/sharedStrings.xml');
                $zip->close();
                if ($sheet !== false) {
                    $words = qs_kw_xlsx_words($sheet, $shared !== false ? $shared : '');
                }
            }
        }

        // 2) 系统 tar/unzip 回退
        if (!$words) {
            $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
            $shell_ok = function_exists('shell_exec') && !in_array('shell_exec', $disabled, true);
            if ($shell_ok) {
                if (stripos(PHP_OS, 'WIN') === 0) {
                    @shell_exec('tar -xf ' . escapeshellarg($file) . ' -C ' . escapeshellarg($tmp) . ' 2>&1');
                } else {
                    @shell_exec('unzip -o ' . escapeshellarg($file) . ' -d ' . escapeshellarg($tmp) . ' 2>&1');
                }
                $sheetFile = $tmp . '/xl/worksheets/sheet1.xml';
                if (is_file($sheetFile)) {
                    $words = qs_kw_xlsx_words(
                        (string)file_get_contents($sheetFile),
                        is_file($tmp . '/xl/sharedStrings.xml') ? (string)file_get_contents($tmp . '/xl/sharedStrings.xml') : ''
                    );
                }
            }
        }

        qs_kw_rmdir($tmp);
        return $words;
    }
}
