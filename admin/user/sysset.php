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
 * @File         sysset.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Backend feature registry helper: scans the core definitions and the admin CSS files to build the backend navigation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__.'/../head.php';
$userName = $_SESSION['Qsis_user_name'] ?? '';
$tip = '';
$tipType = '';
$curr_user = $sp_config_users[$userName] ?? [];
$themeList = [];

// 正则匹配name标记
$namePattern = '/^\/\/name:(.+)$/m';

// 读取core定义后台CSS物理目录扫描文件
if (is_dir($controlcss)) {
    $dirRes = opendir($controlcss);
    while ($fileName = readdir($dirRes)) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'css') continue;
        $filePath = $controlcss . $fileName;
        $showName = $fileName;
        // 读取前三行提取自定义name
        $handle = fopen($filePath, 'r');
        if ($handle) {
            $headLines = [];
            for ($i = 0; $i < 3 && !feof($handle); $i++) {
                $headLines[] = trim(fgets($handle));
            }
            fclose($handle);
            $headContent = implode("\n", $headLines);
            if (preg_match($namePattern, $headContent, $match)) {
                $showName = trim($match[1]);
            }
        }
        $themeList[] = [
            'file' => $fileName,
            'name' => $showName
        ];
    }
    closedir($dirRes);
    // 按展示名排序
    usort($themeList, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// 提交保存主题
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_theme'])) {
    $selTheme = trim($_POST['user_theme'] ?? '');
    $validFiles = array_column($themeList, 'file');
    if (!isset($sp_config_users[$userName])) {
        $tip = L('User_Config_Missing_Save');
        $tipType = 'error';
    } elseif (empty($selTheme) || !in_array($selTheme, $validFiles)) {
        $tip = L('Invalid_Theme_File');
        $tipType = 'error';
    } else {
        $sp_config_users[$userName]['themes'] = $selTheme;
        $writeCode = "<?php\n";
        $writeCode .= "\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";
        // 使用core定义用户配置路径写入
        $saveRes = file_put_contents($datapaths . $config_user, $writeCode, LOCK_EX);
        if ($saveRes) {
            $curr_user['themes'] = $selTheme;
            $_SESSION['user_theme'] = $selTheme;
            $tip = L('Theme_Switched_Refresh_Tip');
            $tipType = 'success';
        } else {
            $tip = L('Config_Write_Failed_Perm');
        }
    }
}
?>
<div class="admin_page_name">
    <a href="userset.php"><?= L('My_Profile_Link'); ?></a>
    <a href="sysset.php"><?= L('System_Style_Settings_Link'); ?></a>
</div>
<div id="fenye">
    <div class="container">
        <?php if ($tip): ?>
            <div class="alert <?= $tipType ?>"><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-row" style="display:flex;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
                <label style="width:100px;flex-shrink:0;"><?= L('Backend_Style_Label'); ?></label>
                <select name="user_theme" style="width:100%;max-width:520px;box-sizing:border-box;padding:8px;">
                    <?php foreach ($themeList as $item): ?>
                        <option value="<?= htmlspecialchars($item['file'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= (!empty($curr_user['themes']) && $curr_user['themes'] === $item['file']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="padding-left:112px;gap:10px;display:flex;flex-wrap:wrap;">
                <button type="submit" name="save_theme"><?= L('Save_Style_Settings_Button'); ?></button>
            </div>
        </form>
    </div>
</div>
<div class="clear"></div>
<?php require_once __DIR__.'/../foot.php'; ?>
