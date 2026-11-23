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
 * @File         set_bookgenres.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Book genre settings: defines the genre options used by the book module.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><b><?= L('Book_genres'); ?></b>
<div class="small_form small_form2">
<font color=#ff0000></font>
<div class="field">
<div class="label">
<?= L('Bookgenreslist'); ?><br>
</div>
<div class="price">
<input class="input" type="text" name="bookgenreslist" value="<?= htmlspecialchars($bookgenreslist);?>" />
</div>
<div class="info">
	<?= L('H1'); ?>
</div>
</div>

     <script>
        function addOption() {
            var input = document.getElementById('newOption').value;
            if (input) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "bookgenres_process.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        document.getElementById('dynamicSelect').innerHTML = xhr.responseText;
                        document.getElementById('newOption').value = '';
                    }
                };
                xhr.send("action=add&option=" + encodeURIComponent(input));
            }
        }

        function deleteOption() {
            var select = document.getElementById('dynamicSelect');
            var selectedOption = select.options[select.selectedIndex].value;
            if (selectedOption) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "bookgenres_process.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        document.getElementById('dynamicSelect').innerHTML = xhr.responseText;
                    }
                };
                xhr.send("action=delete&option=" + encodeURIComponent(selectedOption));
            }
        }
    </script>
    
	   <div class="clear"></div>
<div class="field">
<div class="label">
<label for="newOption"><?= L('Book_genres'); ?></label>
</div>
<div class="price">

    <select id="dynamicSelect" name="dynamicSelect" >
        <?php
        // Initial loading of options from a file or database
if (file_exists($datapaths.$qs_config['bookgenreslist'])) {
    $options = file($datapaths.$qs_config['bookgenreslist'], FILE_IGNORE_NEW_LINES);
    foreach ($options as $option) {
        echo "<option value=\"$option\">$option</option>";
    }
}
        ?>
    </select>
    <input type="button" value="<?= L('Delete'); ?>" onclick="deleteOption()">
</div>
<div class="info">

</div>
</div>

<div class="clear"></div>
<div class="field">
<div class="label">

<label for="newOption"><?= L('Add_Bookgenres'); ?> </label>
</div>
<div class="price">
<input type="text" id="newOption" name="newOption">
<input type="button" value="<?= L('Add'); ?>" onclick="addOption()">
</div>
<div class="info">

</div>

</div>
 <div class="clear"></div>
</div>
