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
 * @File         admin.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Theme-colour panel: enable checkbox and the colour picker for the browser theme colour.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>                <div class="clear"></div>
                <div class="field">
                    <div class="label">
<?= L('0EAT7'); ?>
                        
                    </div>

<div class="label">
<label><input type="checkbox" name="themecoloroc" <?= $themecoloroc ? 'checked' : '' ?>></label>
                   </div>     
                   <div class="info"><?= L('0EA7'); ?></div>
                   </div>

                 <div class="clear"></div>
                <div class="field">
                    <div class="label">
                        <?= L('0EA7C'); ?>
                    </div>   
                    <div class="label">
<input type="color" name="colorcode" id="colorcode" onchange="changeColor()" value="<?php echo htmlspecialchars($colorcode); ?>">    <!--span id="colorInfo"></span-->
</body>
<script type="text/javascript">
var colorcode = document.getElementById("colorcode"); 　　      　//通过使用 getElementById() 来访问 <color> 元素
colorcode.style.color = colorcode.value; 　　　　　　　 　　　　　　　　 //给<span>加内容(<color>的值)
function changeColor(){ 　　　　　　　　　　　　　　　　　　　　 //改变颜色的事件
colorcode.style.color = colorcode.value;
}
</script>
</div>
</div>
<!--
//将下面内容添加到tags.php文件中
//2020-7-4 by isher
//function qs_themecoloroc($print = true) {
  global $qs_config;
  $themecoloroc = htmlspecialchars($qs_config['themecoloroc']);
  return $themecoloroc;
}

//function qs_colorcode($print = true) {
  global $qs_config;
  $colorcode = htmlspecialchars($qs_config['colorcode']);
  if ($print) {
    echo $colorcode;
    return;
  }
  return $colorcode;
}
-->