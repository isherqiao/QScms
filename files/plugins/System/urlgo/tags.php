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
 * @File         tags.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Client-IP helper for the redirect plugin: resolves the visitor address from proxy headers or REMOTE_ADDR.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

function getip() /*获取客户端IP*/
{
if (@$_SERVER["HTTP_X_FORWARDED_FOR"]){ //跳过代理获取客户端IP
$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
}
else if (@$_SERVER["HTTP_CLIENT_IP"]){//直接获取获取客户端IP
$ip = $_SERVER["HTTP_CLIENT_IP"];
}
else if (@$_SERVER["REMOTE_ADDR"]){
$ip = $_SERVER["REMOTE_ADDR"];
}
else if (@getenv("HTTP_X_FORWARDED_FOR")){
$ip = getenv("HTTP_X_FORWARDED_FOR");
}
else if (@getenv("HTTP_CLIENT_IP")){
$ip = getenv("HTTP_CLIENT_IP");
}
else if (@getenv("REMOTE_ADDR")){
$ip = getenv("REMOTE_ADDR");
}
else {
$ip = "Unknown";
}
return $ip;
}

$ip=getIP(); //echo "var ip_address ='$ip'; ";
global $qs_config;
if ($qs_config['v4v6auto'] == "1"){

 if (filter_var(getIP(), \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV6)) {
function qs_site_link($print = true) {
  global $qs_config;
  $site_link = $qs_config['site_link_ipv6'];
  if ($print) {
    echo $site_link;
    return;
  }
  return $site_link;
}
} else if (filter_var(getIP(), \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV4)) {
function qs_site_link($print = true) {
  global $qs_config;
  $site_link = $qs_config['site_link_ipv4'];
  if ($print) {
    echo $site_link;
    return;
  }
  return $site_link;
}
}

} //判断是否开启auto46
else
{
function qs_site_link($print = true) {
  global $qs_config;
  $site_link = $qs_config['site_link'];
  if ($print) {
    echo $site_link;
    return;
  }
  return $site_link;
}
}

function echo_ipv6() {
	  global $qs_config;
  if (filter_var(getIP(), \FILTER_VALIDATE_IP,\FILTER_FLAG_IPV6)) {
  echo '<div>ipv6</div>';
  }
 
}

 function qs_site_link_ipv4($print = true) {
  global $qs_config;
  $site_link = $qs_config['site_link_ipv4'];
  if ($print) {
    echo $site_link;
    return;
  }
  return $site_link;
}
 
  function qs_site_link_ipv6($print = true) {
  global $qs_config;
  $site_link = $qs_config['site_link_ipv6'];
  if ($print) {
    echo $site_link;
    return;
  }
  return $site_link;
}

?>