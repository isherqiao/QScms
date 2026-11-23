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
 * @File         404.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Not-found handler for the front end: renders the 404 template with the proper HTTP status.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
<title></title> 

<style>
	body {
    margin: 0px;
    padding: 0px;
    font-family: "宋体";
    font-size: 12px;
    background: url(files/images/bg.jpg);
}
.errpic {
    width: 667px;
    height: 333px;
    border-top-width: 0px;
    border-right-width: 0px;
    border-bottom-width: 0px;
    border-left-width: 0px;
    -webkit-border-horizontal-spacing: 0px;
    -webkit-border-vertical-spacing: 0px;
    background-image: url(files/images/bg2.jpg);
}

#nav {
    margin-top: 240px;
    text-align: center;
    width: 560px;
    height: 30px;
    padding-left: 107px;
}

#nav span {
    width: 108px;
    height: 27px;
    background: url(files/images/btn.jpg) no-repeat;
    margin-right: 20px;
    display: block;
    cursor: pointer;
    float: left;
}

#nav span a {
	text-decoration: none;
    color: #FFFFFF;
    font-size: 12px;
    line-height: 27px;
    width: 108px;
    height: 27px;
    display: block;
    cursor: pointer;
    float: left;
}
#nav span a:hover {
    color: #FFFFFF;
    font-size: 12px;
    line-height: 27px;
    width: 108px;
    height: 27px;
    display: block;
    cursor: pointer;
    float: left;
    text-decoration: none;
}
</style>

</head>

<body> 

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">

<tr> 

<td align="center"><table class="errpic"> 

<tr>

<td align="center"><div id="nav" class="404000"><span><a href="<?php qs_site_link(); ?>">返回首页</a></span><span><a href="javascript:window.history.back();">返回上页</a></span><span><a href="javascript:location.href=location.href;">刷新本页</a></span></div></td> 

</tr> 

</table></td> 

</tr> 

</table>

</body> 

</html> 

