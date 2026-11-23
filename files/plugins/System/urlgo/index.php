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
 * @File         index.php
 * @CreatedTime  08/03/21 23:02:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  IPv4/IPv6 redirect output: detects the visitor stack and forwards to the matching site address, keeping the port consistent.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$url = $_SERVER['HTTP_HOST'];
$httpurl = 'http://'.$url;
$httpsurl = 'https://'.$url;
$ipv4 = $site_link_ipv4;
//如果ipv4带有端口，那么在服务器端，解析域名的时候，要在ipv6的解析上添加与ipv4相同的端口号
//例如ipv4是88，则ipv6解析也加上88端口
//因为默认ipv6是优先的，所以当端口相同都是88的时候，先判断当前走的是不是ipv6
//如果是，那么让其走v6的默认端口80上，即不再加端口号
if ($v4v6auto == "1"){
if ($httpurl == $ipv4 || $httpsurl == $ipv4) {
   if (filter_var(getIP(), \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV6)) { 

?>
<script language=javascript>

var current_url = window.location.href;
target_url = current_url.replace("<?php echo qs_site_link_ipv4(); ?>","<?php echo qs_site_link_ipv6(); ?>");
window.location = target_url;
    </script>
<?php
}
}
}
?>

