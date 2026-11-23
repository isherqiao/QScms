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
 * @Description  Night-mode output: inverts the page when the current hour falls inside the configured range and offers a manual toggle.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if ($darkmode){
//判断白天或者夜间，以保护视力
$h=date('H');
if($h<=$beforetime || $h>=$aftertime) 
echo '
<div class="site" style="position: absolute; z-index: 999;">
<button id="styleToggleButton">关闭夜间模式</button></div>
<script language="javascript">
const styleToggleButton = document.getElementById("styleToggleButton");
const content = document.querySelector("html");
let isStyleInverted = false;
styleToggleButton.addEventListener("click", () => {
    // 切换样式状态
    isStyleInverted = !isStyleInverted;
    if (isStyleInverted) {
        content.classList.add("inverted");
    } else {
        content.classList.remove("inverted");
    }
});
</script>
<style>
@media (max-width:600px) {
/* 排除所有图片不受灰度滤镜影响 */
img,video {
  filter: invert(100%);
}
.regular, .logo-wrapper a {
  filter: invert(100%);
}
html {
  filter: invert(100%);
}
html.inverted{
    filter:none !important;
}
}
@media (min-width: 600px) {
    #styleToggleButton {
        display: none;
    }
}
</style>

';
//判断白天或者夜间，以保护视力截止
} 
?>
