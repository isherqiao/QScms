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
 * @File         git-cron-config.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Runtime configuration for the Git cron job: repository target, branch and sync parameters.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require '{addslashes(__DIR__ . "/head.php")}';

$token = Crypto::decrypt($qs_config['github_token'], $sp_config['user_pass']);

echo implode('|', [
    $qs_config['github_owner'],
    $qs_config['github_repo'],
    $token,
    $qs_config['github_sync_dir'],
    $qs_config['github_branch'] ?? 'main'
]);