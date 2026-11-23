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
 * @File         config.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  IPv4/IPv6 redirect handler: persists the switch and both site addresses.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (isset($_POST['save'])) {

$v4v6auto = $_POST['v4v6auto'];
$site_link_ipv4 = $_POST['site_link_ipv4'];
$site_link_ipv6 = $_POST['site_link_ipv6'];
$plugin_Content .= "\$v4v6auto = " . ($v4v6auto) . ";\n";
$plugin_Content .= "\$site_link_ipv4 = '". $site_link_ipv4 . "';\n";
$plugin_Content .= "\$site_link_ipv6 = '". $site_link_ipv6 . "';\n";
}
?>
