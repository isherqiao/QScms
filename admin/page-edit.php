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
 * @File         page-edit.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Static page editor: edits body content, publish state and metadata of a page stored under data/pages.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
require __DIR__.'/keywords/filter.php';
$blocked_hits = [];
$page_file        = '';
$page_path        = '';
$page_state       = '';
$page_title       = '';
$page_content     = '';
$page_date        = '';
$page_tags        = array();
$page_time        = '';
$page_can_comment = '';
$error_msg        = '';
$succeed          = false;
if (isset($_POST['_IS_POST_BACK_'])) {
  $page_file        = $_POST['file'];
  $page_path        = $_POST['path'];
  $page_state       = $_POST['state'];
  $page_title       = trim($_POST['title']);
  $page_content     = stripslashes(trim($_POST['content']));
  $page_date        = date("Y-m-d");
  $page_time        = date("H:i:s");
  $page_tags        = explode(',', trim($_POST['tags']));
  $page_can_comment = $_POST['can_comment'];
  if ($_POST['year'] != '')
    $page_date = substr_replace($page_date, $_POST['year'], 0, 4);
  if ($_POST['month'] != '')
    $page_date = substr_replace($page_date, $_POST['month'], 5, 2);
  if ($_POST['day'] != '')
    $page_date = substr_replace($page_date, $_POST['day'], 8, 2);
  if ($_POST['hourse'] != '')
    $page_time = substr_replace($page_time, $_POST['hourse'], 0, 2);
  if ($_POST['minute'] != '')
    $page_time = substr_replace($page_time, $_POST['minute'], 3, 2);
  if ($_POST['second'] != '')
    $page_time = substr_replace($page_time, $_POST['second'], 6, 2);
    
    $page_path_part  = explode('/', $page_path);
    $page_path_count = count($page_path_part);

    $page_tags_count = count($page_tags);
  for ($i = 0; $i < $page_tags_count; $i ++) {
    $trim = trim($page_tags[$i]);
    if ($trim == '') {
      unset($page_tags[$i]);
    } else {
      $page_tags[$i] = $trim;
    }
  }
  reset($page_tags);

  for ($i = 0; $i < $page_path_count; $i ++) {
    $trim = trim($page_path_part[$i]);
    if ($trim == '') {
      unset($page_path_part[$i]);
    } else {
      $page_path_part[$i] = $trim;
    }
  }
  reset($page_path_part);
  $page_path = implode('/', $page_path_part);
  if ($page_title == '') {
    $error_msg = L('Page_Title_Cannot_Empty');
  }
  else if ($page_path == '') {
    $error_msg = L('Page_Path_Cannot_Empty');
  }
  else if (!empty($blocked_hits = qs_keyword_check($page_title, $page_content))) {
    $error_msg = sprintf(L('Article_Has_Blocked_Keywords'), implode('、', array_map(fn($c, $w) => $w . '（' . $c . '）', $blocked_hits, array_keys($blocked_hits))));
  }
  else {
    if ($page_file == '') {
      $file_names = shorturl($page_title);
      foreach ($file_names as $file_name) {
        $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
        $file_path = $realPostRoot.'data/'.$file_name.'.php';
        if (!is_file($file_path)) {
          $page_file = $file_name;
          break;
        }
      }
    } else {
    $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
    $file_path = $realPostRoot.'data/'.$page_file.'.php';
    //$data = unserialize(file_get_contents($file_path));
    $file_content = file_get_contents($file_path);
    // 移除前后的 PHP 标签
    $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
    $file_content = preg_replace('/\?>$/', '', $file_content);
 	 $data = unserialize($file_content);
      $page_old_path  = $data['path'];
      $page_old_state = $data['state'];
      if ($page_old_state != $page_state || $page_old_path != $page_path) {
$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
    $index_file = $realPostRoot.'index/'.$post_state.'.php';
            require $index_file;
        unset($qs_pages[$page_old_path]);
        file_put_contents($index_file,
          "<?php\n\$qs_pages=".var_export($qs_pages, true)."\n?>"
        );
      }
    }
    $data = array(
      'file'        => $page_file,
      'path'        => $page_path,
      'state'       => $page_state,
      'title'       => $page_title,
      'tags'        => $page_tags,
      'date'        => $page_date,
      'time'        => $page_time,
      'can_comment' => $page_can_comment,
    );

    $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
    $index_file = $realPostRoot.'index/'.$page_state.'.php';
    require $index_file;
    $qs_pages[$page_path] = $data;
    ksort($qs_pages);
    file_put_contents($index_file,
      "<?php\n\$qs_pages=".var_export($qs_pages, true)."\n?>"
    );
    $data['content'] = $page_content;   
   $data = "<?php //" . serialize($data) . "?>";
		file_put_contents($file_path, $data);
    $succeed = true;
        if ($succeed && file_exists(__DIR__ . '/qs_feeds.php')) {
            require_once __DIR__ . '/qs_feeds.php';
            qs_feeds_regenerate();
        }
        if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
            write_operate_log(qs_get_current_admin_name(), 'page_edit', L('Page_Management'), L('Log_Edit_Modify_Page'));
        }
  }
} else if (isset($_GET['file'])) {
        $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
        $file_path = $realPostRoot.'data/'.$_GET['file'].'.php';

  if(file_exists($file_path))
{
  // $data = unserialize(file_get_contents($file_path));
    $file_content = file_get_contents($file_path);
    // 移除前后的 PHP 标签
    $file_content = preg_replace('/^<\?php\s*\/\/\s*/', '', $file_content);
    $file_content = preg_replace('/\?>$/', '', $file_content);
  $data = unserialize($file_content);
  $page_file        = $data['file'];
  $page_path        = $data['path'];
  $page_state       = $data['state'];
  $page_title       = $data['title'];
 @$page_tags    		= $data['tags'];
  $page_content     = $data['content'];
  $page_date        = $data['date'];
  $page_time        = $data['time'];
  $page_can_comment = isset($data['can_comment']) ? $data['can_comment'] : '1';
}
else
{
Header("Location:");
}
}

?>
<script type="text/javascript">
function empty_textbox_focus(target){
  if (target.temp_value != undefined && target.value != target.temp_value)
    return;
  target.temp_value = target.value;
  target.value='';
  target.style.color='#000';
}
function empty_textbox_blur(target) {
  if (target.value == '') {
    target.style.color='#888';
    target.value = target.temp_value;
  }
}
</script>

<?php 
$nick_code = $qs_config['nick_code'];
    if ($page_path  == '') {
        $tempid = 'newpage';
        $autsaveid = $nick_code.'-'.$tempid;
    } else {
      $tempid = $page_path ;
     $autsaveid = $nick_code.'-'.$tempid;
   }
?>

<form action="" method="post" id="<?php echo $autsaveid; ?>" name="<?php echo $tempid; ?>" >
  <script src="marked.min.js"></script>
  <script src="markdown-support.js"></script>
  <input type="hidden" name="_IS_POST_BACK_" value=""/>
  <?php if ($succeed) { ?>
  <?php if ($page_state == 'publish') { ?>
  <div class="updated"><?= L('Page_Published'); ?> <a href="../?<?php echo $page_path; ?>/" target="_blank"><?= L('View_Page'); ?></a></div>
  <?php } else { ?>
  <div class="updated"><?= L('Page_Saved_To_Drafts'); ?> <a href="page.php?state=draft"><?= L('Open_Drafts'); ?></a></div>
  <?php } ?>
  <?php } ?>
  <div class="admin_page_name">
  <?php if ($page_path == '') echo L('Write_Page'); else echo L('Edit_Page'); ?>    <input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>   <?php  if ($page_path != '') echo "<a href=/?".$page_path."/ target=_blank>".L('Check_Page')."</a>"; ?> <?php echo $page_path; ?>
  </div>
  <?php if (!empty($error_msg)): ?>
  <div style="margin:12px 0;padding:12px 14px;border:1px solid #dc3545;border-radius:4px;background:#fff5f5;color:#dc3545;font-weight:bold;">
    <?= htmlspecialchars($error_msg); ?>
  </div>
  <?php endif; ?>
  <div style="margin-bottom:20px;">
    <input name="title" type="text" class="edit_textbox" value="<?php
    if ($page_title == "") {
      echo L('Enter_Title_Here') . '" " style="color:#888;" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)';
    }
    else {
      echo htmlspecialchars($page_title);
    }
    ?>"/>
  </div>
  
  <div style="margin-bottom:20px;">
<?php
if (isset($qs_config['edittools'])){
$edittools = $qs_config['edittools'];
} else {
$edittools = "xheditor";
}
?>

<script language="javascript" src="xheditor/jquery/jquery-1.11.2.min.js"></script>
<script language="javascript" src="sisyphus.min.js"></script>

<?php
if ($edittools == "xheditor" ) {
?>

<script language="javascript" src="xheditor/xheditor.js"></script>
<script language="javascript" src="xheditor/xheditor_lang/zh-cn.js"></script>

	<script language="javascript">
		$(pageInit);
		function pageInit(){
		$('#contentbox').xheditor({tools:'full',internalScript:true,inlineScript:true,cleanPaste:2,onPaste:function(h){ return (window.qsMarkdown && qsMarkdown.processPastedHtml) ? qsMarkdown.processPastedHtml(h) : h; },urlType:'rel',html5Upload:false,upBtnText:"<?= L('Upload'); ?>",upMultiple:99,upLinkUrl:'supldaa.php',upLinkExt:'zip,rar,txt',upImgUrl:'supldaa.php',upImgExt:'jpg,jpeg,gif,png',upMediaUrl:'supldaa.php',upMediaExt:'wmv,avi,mp4,wma,mp3,mid,m4a,aac',upVideoUrl:'supldaa.php',upVideoExt:'wmv,avi,mp4,wma,mp3,mid,m4a,aac',emotPath:'xheditor/xheditor_emot/',onUpload:insertUpload});
				}
		function insertUpload(arrMsg){
		var i,msg;
		for(i=0;i<arrMsg.length;i++){
		msg=arrMsg[i];
		$("#uploadList").append('<option value="'+msg.id+'">'+msg.localname+'</option>');
				}
		}
	</script>
 <textarea id="contentbox" name="content" style="width: 100%; height: 450px; display: none; "><?php echo htmlspecialchars($page_content); ?></textarea>
<?php
} elseif ($edittools == "qseditor") {
?>
<link href="./qseditor/qseditor.css" rel="stylesheet">
<script src="qseditor/qseditor.js"></script>
<textarea id="contentbox" name="content" style="display: none;"><?php echo htmlspecialchars($page_content); ?></textarea>
<div style="display:flex; align-items:center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
    <?= L('Words_number'); ?>：
    <div id="word-count" style="margin-right: 10px;"></div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function () {
    var ed = QSEditor.init('contentbox', { placeholder: '请输入页面内容...' });
    window.qsEditorMain = ed;
    if (ed) {
        var origSync = ed.sync;
        ed.sync = function () {
            origSync();
            var wc = document.getElementById('word-count');
            if (wc) {
                var text = ed.body.innerText || '';
                wc.textContent = text.trim().length + ' ' + "<?= L('Word_Char'); ?>";
            }
        };
        ed.sync();
    }
});
</script>
<?php
} elseif ($edittools == "Quill" )  {
?>
<link href="./quill/quill.snow.css" rel="stylesheet">
 <script src="quill/quill.js"></script>
 <textarea id="contentbox" name="content" style="display: none;"><?php echo htmlspecialchars($page_content); ?></textarea>
<div id="editor" name="content"></div>
<div style="display:flex; align-items:center;"><?= L('Words_number'); ?>：
    <div id="word-count" style="margin-right: 10px;">0</div>
    <input type="file" id="file-Input" multiple style="width:80px;font-size:16px; word-wrap: break-word; overflow-wrap: break-word;">
</div>

<script>
    // 在 Quill 脚本加载完成后执行以下代码
    window.addEventListener('DOMContentLoaded', (event) => {
        // 创建Quill编辑器并填充内容
        var editorContent = document.getElementById('contentbox').value;
        var editor = document.getElementById('editor');
        var quill = new Quill(editor, {
            theme: 'snow', // 使用 "snow" 主题
            modules: {
                toolbar: [
                    ['undo', 'redo'], // 添加撤销和重做按钮
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    ['link', 'image', 'video'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean'] // 清除格式
                ]
            }
        });

        if (window.qsMarkdown) {
            qsMarkdown.enableQuill(quill);
        }
        if (window.qsMarkdown && qsMarkdown.isLikely(editorContent)) {
            editorContent = qsMarkdown.toHtml(editorContent);
        }
        
        // 填充内容到Quill编辑器
        quill.clipboard.dangerouslyPasteHTML(editorContent);

        <?php echo qs_keyword_highlight_script($blocked_hits, 'quill'); ?>

                $('#editor').sisyphus({
            excludeFields: ['#file-Input'], // 排除文件输入框以防止自动保存
            timeout: 2, // 设置自动保存间隔时间（秒）
            onSave: function() {
                var content = quill.root.innerHTML;
                $('#contentbox').val(content);
            }
        });

        // 在表单提交时，将Quill编辑器的内容放入隐藏的textarea中
        var form = document.getElementById('<?php echo $autsaveid; ?>');
        form.addEventListener('submit', function(event) {
            var content = quill.root.innerHTML;
            document.getElementById('contentbox').value = content;
        });

        // 为撤销按钮添加点击事件
        var undoButton = document.querySelector('.ql-undo');
        undoButton.addEventListener('click', function() {
            quill.history.undo();
        });

        // 为重做按钮添加点击事件
        var redoButton = document.querySelector('.ql-redo');
        redoButton.addEventListener('click', function() {
            quill.history.redo();
        });

  // 获取图片输入框
  var imageInput = document.getElementById('file-Input');

  // 当文件选择框中的文件被选中时
  imageInput.addEventListener('change', function(event) {
    var files = event.target.files;
    if (files) {
      for (var i = 0; i < files.length; i++) {
        // 读取文件
        var file = files[i];
        var reader = new FileReader();
        reader.onload = function(e) {
          // 插入图片
          var range = quill.getSelection();
          quill.insertEmbed(range.index, 'image', e.target.result);
        }
        reader.readAsDataURL(file); // 读取文件数据作为 URL
      }
    }
  });

// 监听编辑器内容变化事件
quill.on('text-change', function(delta, oldDelta, source) {
    // 延迟更新字数统计
    setTimeout(function() {
        // 获取编辑器的纯文本内容
        var text = quill.getText();
        // 获取编辑器纯文本内容长度，并减去一个字数
        var textLength = text.trim().length - 1;
        // 更新字数统计
        document.getElementById('word-count').textContent = textLength + ' ' + "<?= L('Word_Char'); ?>";
    }, 0);
});
  });
</script>

 <?php
} 
?>
</div>
 
  <div style="margin-bottom:20px;">
    <input placeholder="<?= L('Article_Tags_Keywords'); ?>" value="<?php echo htmlspecialchars(implode(',', $page_tags)); ?>" name="tags" type="text" class="edit_textbox"/></input>
  </div>
  <div style="margin-bottom:20px;">
    <input name="path" type="text" class="edit_textbox" value="<?php
    if ($page_path == '') {
      echo L('Enter_Page_Path_Here') . '" " style="color:#888;" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)';
    }
    else {
      echo htmlspecialchars($page_path);
    }
    ?>"/>
  </div>
  

 <div class="st-w">
    <div class="st-w-l">
    <?= L('Date_Time'); ?>：
    </div>
<div class="st-w-r">
    <select name="year">
      <option value=""></option>
<?php $year = substr($page_date, 0, 4); for ($i = 1990; $i <= 2030; $i ++) { ?>
      <option value="<?php echo $i; ?>" <?php if ($year == $i) echo 'selected="selected";' ?>><?php echo $i; ?></option>
<?php } ?>
    </select> -
    <select name="month">
      <option value=""></option>
<?php $month = substr($page_date, 5, 2); for ($i = 1; $i <= 12; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($month == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> -
    <select name="day">
      <option value=""></option>
<?php $day = substr($page_date, 8, 2); for ($i = 1; $i <= 31; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($day == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select>&nbsp;
    <select name="hourse">
      <option value=""></option>
<?php $hourse = substr($page_time, 0, 2); for ($i = 0; $i <= 23; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($hourse == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> :
    <select name="minute">
      <option value=""></option>
<?php $minute = substr($page_time, 3, 2); for ($i = 0; $i <= 59; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($minute == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> :
    <select name="second">
      <option value=""></option>
<?php $second = substr($page_time, 6, 2); for ($i = 0; $i <= 59; $i ++) { $m = sprintf("%02d", $i); ?>
      <option value="<?php echo $m; ?>" <?php if ($second == $m) echo 'selected="selected";' ?>><?php echo $m; ?></option>
<?php } ?>
    </select> <?= L('Leave_blank_current_time'); ?>
  </div>
    </div>

     <div class="st-w">
    <div class="st-w-l">
    <?= L('Comment'); ?>：
      </div>
     <div class="st-w-r">
    <select name="can_comment" style="margin-right:16px;">
      <option value="1" <?php if ($page_can_comment == '1') echo 'selected="selected";'; ?>><?= L('Allow'); ?></option>
      <option value="0" <?php if ($page_can_comment == '0') echo 'selected="selected";'; ?>><?= L('Disable_Status'); ?></option>
    </select>
   </div>
    </div>
     <div class="st-w">
    <div class="st-w-l">
    <?= L('Status'); ?>：
      </div>
     <div class="st-w-r">
    <select name="state" style="width:100px;">
      <option value="publish" <?php if ($page_state == 'publish') echo 'selected="selected"'; ?>><?= L('Publish'); ?></option>
      <option value="draft" <?php if ($page_state == 'draft') echo 'selected="selected"'; ?>><?= L('Drafts'); ?></option>
    </select>
  </div>
  <div style="text-align:right">
    <input type="hidden" name="file" value="<?php echo $page_file; ?>"/>
    <input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>
  </div>
</form>
<script>
$( function() {
    //$( "#elem1post" ).sisyphus();
	// or you can persist all formexcludeFields: s data at one command
	// $( "form" ).sisyphus();excludeFields: 
$( "#<?php echo $autsaveid; ?>" ).sisyphus( {
//excludeFields: $( "#elem1post" ),
//locationBased: true,基于url地址的存储
timeout: 3
} );
} );
</script>
<?php require 'foot.php' ?>
