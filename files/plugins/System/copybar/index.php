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
 * @CreatedTime  06/01/20 23:02:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Copy helper output: appends the configured signature text when a visitor copies page content.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if ($copybar){
?>

<script language="javascript">
var addCopyStr = function(str) {
	if(window.clipboardData) {
		var text = window.clipboardData.getData("Text");
		window.clipboardData.setData('Text',text + str);
	}else if(window.getSelection) {
      	console.log("b");
		event.preventDefault();
		var selection = window.getSelection();
		event.clipboardData.setData('text/plain', selection + str);
	}else{
		var selection = window.getSelection();
		var body = document.getElementsByTagName('body')[0];
		var range = selection.getRangeAt(0);
		extContent.style.position = 'absolute';
		extContent.style.left = '-99999px';
		extContent.innerHTML = selection + str;
		body.appendChild(extContent);
		selection.selectAllChildren(extContent);
		window.setTimeout(function() {
				body.removeChild(extContent);
				selection.removeAllRanges();
				selection.addRange(range);
				body = selection = null;
		},0);
	}
};

  $("body").on("copy",function(){
    console.log("copy");
    addCopyStr("\r\n本篇文章来源于 ："+location.href+"转载请注明出处");
  });
  </script>
  
  <?php
} 
?>