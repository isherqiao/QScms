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
 * @File         github_cron.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Cron entry for the automatic Git sync: builds the shell script, writes the temporary config and triggers the push.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';

// 读取配置
$owner   = $qs_config['github_owner'];
$repo    = $qs_config['github_repo'];
$token   = Crypto::decrypt($qs_config['github_token'], $sp_config['user_pass']);
$dir     = $qs_config['github_sync_dir'];
$branch  = $qs_config['github_branch'] ?? 'main';

// ✅ 关键修复：把 ../../static_site 转换成服务器绝对路径！
$real_dir = realpath(rtrim($dir, '/'));

// 全部放 /tmp
$shFile        = '/tmp/git-auto-sync.sh';
$phpConfigFile = '/tmp/git-cron-config.php';

// 生成纯静态 config（绝对路径，无依赖）
$phpCode = "<?php
echo '$owner|$repo|$token|$real_dir|$branch';
";

file_put_contents($phpConfigFile, $phpCode);

// 生成同步脚本
$shCode = <<<'BASH'
#!/bin/bash
PHP_CONFIG="/tmp/git-cron-config.php"
RESULT=$(php "$PHP_CONFIG")
IFS='|' read -r OWNER REPO TOKEN DIR BRANCH <<< "$RESULT"

cd "$DIR" || exit 1
REPO_URL="https://$OWNER:$TOKEN@github.com/$OWNER/$REPO.git"

git fetch origin
git pull origin "$BRANCH" --ff-only
git add .
git commit -m "Auto sync $(date '+%Y-%m-%d %H:%M:%S')"
git push origin "$BRANCH"
BASH;

file_put_contents($shFile, $shCode);
chmod($shFile, 0755);

// 写入 cron
$cronLine = "*/10 * * * * $shFile";

$existing = shell_exec('crontab -l 2>/dev/null');
$existing = $existing === null ? '' : trim($existing);
$lines = $existing ? explode("\n", $existing) : [];

$newLines = [];
foreach ($lines as $line) {
    if (str_contains((string)$line, $shFile)) continue;
    $newLines[] = $line;
}
$newLines[] = $cronLine;

file_put_contents('/tmp/my.cron', implode("\n", $newLines));
shell_exec('crontab /tmp/my.cron');

echo "✅ " . L('Auto_Sync_Created_Tip');