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
 * @File         link.php
 * @CreatedTime  15/09/19 04:02:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Friend-link management page: saves, orders and deletes the link entries kept in the link data file.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>

<?php

$display_info = false;

if (isset($_POST['save'])) {
	
if (isset($_POST["link_name"])){
$linkItems = $_POST["link_name"];
$FLinks = $_POST["link_url"];
$linkTargets = $_POST["link_menutar"];

$links = array();

foreach (array_map(null, $linkItems, $FLinks, $linkTargets) as list($item, $link, $target)) {

    $links[] = array(
        "name" => $item,
        "link" => $link,
        "tar" => $target
    );
}

    $code = "<?php\n\$links = " . var_export($links, true) . ";\n?>";
    file_put_contents('../control/data/link.php', $code);
    
}

    $display_info = true;
}

?>

<div class="admin_page_name">

<?= L('Friendship_Link_Settings'); ?>
</div>
<?php if ($display_info) {
    ?>
    <div class="updated">
<?= L('settings_saved_successfully'); ?>
    </div>
    <?php
}
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
<div id="fenye">

<ta style="display:block">
	<div class="small_form small_form2">
<div class="field" id="links_menus"><?= L('Dragto_move_up_down') ?>
  <?php
  for ($i = 0; $i < count($links); $i++) {
      $url = $links[$i];
echo '<div class="links_menu" style="line-height: 40px;" id="links_menu_' . $i . '" draggable="true" data-index="' . $i . '">' . L('Name') . '：<input class="" type="text" name="link_name[]" value="' . $url["name"] . '" />';
echo L('Link') . '：<input id="link_url_' . $i . '" type="text" name="link_url[]" value="' . $url["link"] . '" />';
echo '<select name="link_menutar[]">';
echo '<option value="_blank" ' . ($url['tar'] === '_blank' ? 'selected' : '') . '>_blank</option>';
echo '<option value="_self" ' . ($url['tar'] === '_self' ? 'selected' : '') . '>_self</option>';
echo '</select>';
echo '| <a href="javascript:void(0)" onclick="deleteItem(\'links_menu_' . $i . '\');">' . L('Delete') . '</a>';
echo '<font color=#ff0000>   ⇧⇩</font></div>';
}
  ?>
<a id='link' href='javascript:void(0)' onclick='addlinks();'><?= L('Add'); ?></a> 
<a href="javascript:void(0)" onclick="deletelinks('links_menu');"><?= L('Delete_all_newly_added'); ?></a>

</div>
</div>
 <div class="clear"></div>
<script type="text/javascript">
	
function deleteItem(id) {
    var element = document.getElementById(id);
    if (element && element.parentNode) {
        element.parentNode.removeChild(element);
    }
}

function deletelinks(className) {
        var elements = document.querySelectorAll('.' + className);
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }
    }

// 更新JavaScript代码以确保每次添加新元素时都使用唯一的id
function addlinks() {
    var linksMenus = document.getElementById("links_menus");
    var div = document.createElement("div");
    var uniqueId = Date.now(); // 生成唯一的ID
    div.style.width = "100%";
    div.style.float = "left";
    div.className = "links_menu";
    div.innerHTML = "<?= L('Name'); ?>：<input  type='text' name='link_name[]' value=''><?= L('Link'); ?>：<input class='' id='link_url_" + uniqueId + "' type='text' name='link_url[]' value=''><select class='select-2' name='link_menutar[]'><option value='_blank' >_blank</option><option value='_self' >_self</option></select>";
    linksMenus.appendChild(div);
}

 document.addEventListener('DOMContentLoaded', function () {
    var dragSrcEl = null;

    function handleDragStart(e) {
      dragSrcEl = this;
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
      if (e.preventDefault) {
        e.preventDefault();
      }
      e.dataTransfer.dropEffect = 'move';
      return false;
    }

    function handleDragEnter(e) {
      this.classList.add('over');
    }

    function handleDragLeave(e) {
      this.classList.remove('over');
    }

    function handleDrop(e) {
      if (e.stopPropagation) {
        e.stopPropagation(); // stops the browser from redirecting.
      }
      if (dragSrcEl !== this) {
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = e.dataTransfer.getData('text/html');
      }
      return false;
    }

    function handleDragEnd(e) {
      document.querySelectorAll('.links_menu').forEach(function (cell) {
        cell.classList.remove('over');
      });
    }

    var cols = document.querySelectorAll('.links_menu');
    [].forEach.call(cols, function (col) {
      col.addEventListener('dragstart', handleDragStart, false);
      col.addEventListener('dragenter', handleDragEnter, false);
      col.addEventListener('dragover', handleDragOver, false);
      col.addEventListener('dragleave', handleDragLeave, false);
      col.addEventListener('drop', handleDrop, false);
      col.addEventListener('dragend', handleDragEnd, false);
    });
  });
</script>

</ta>

        <div id="fenye">
        	<div class="small_form small_form2">
	 <div  class="field" >
      <input class="button" type="submit" name="save" value="<?= L('Save_Settings'); ?>" /></div>
    </div>
</div>
</form>
         
        <div class="info"></div>
    </div>

<?php require 'foot.php' ?>

