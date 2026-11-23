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
 * @File         page.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Static page renderer: resolves the page by id and outputs it through the active theme.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>
<?php

$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
if (!is_dir($realPostRoot . 'data/')) {
    mkdir($realPostRoot . 'data/', 0755, true);
}
// 根据state拼接索引文件完整路径
function get_index_file($state) {
    global $qs_config;
    $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_page_dir'], '/') . '/';
    if ($state == 'draft') {
        return $realPostRoot . 'index/draft.php';
    } else if ($state == 'delete') {
        return $realPostRoot . 'index/delete.php';
    } else {
        return $realPostRoot . 'index/publish.php';
    }
}

function load_pages($state) {
    global $qs_pages,$qs_config;
    $index_file = get_index_file($state);
    if (file_exists($index_file)) {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($index_file, true);
        }
        unset($GLOBALS['qs_pages']);
        unset($qs_pages);
        require $index_file;
        $pages = isset($qs_pages) ? $qs_pages : array();

        foreach ($pages as &$page) {
            if (!isset($page['bookname'])) {
                $page['bookname'] = '';
            }
            if (!isset($page['date']) || empty($page['date'])) {
                $page['date'] = date('Y-m-d H:i:s');
            }
            if (!isset($page['tags']) || !is_array($page['tags'])) {
                $page['tags'] = array();
            }
            if (!isset($page['title'])) {
                $page['title'] = L('No_Title_Page');
            }
        }
        unset($page);
        return $pages;
    }
    return array();
}

function delete_page($page_id, $state) {
  global $qs_config,$qs_dev_config;
    $pages = load_pages($state);
    if (!isset($pages[$page_id])) return false;

    $page = $pages[$page_id];
    $page['prev_state'] = $state;
    unset($pages[$page_id]);

    $index_file = get_index_file($state);
    file_put_contents($index_file, "<?php\n\$qs_pages=".var_export($pages, true)."\n?>\n");

    if ($state != 'delete') {
        $delete_pages = load_pages('delete');
        $delete_pages[$page_id] = $page;
        $delete_file = get_index_file('delete');
        file_put_contents($delete_file, "<?php\n\$qs_pages=".var_export($delete_pages, true)."\n?>\n");
      if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'page_trash', L('Page_Management'), 'Page：'.$page_id);
      }  
    } else {
        $data_file = $qs_config['qs_page_data_dir'].$page['file'].'.dat';
        if (file_exists($data_file)) {
            unlink($data_file);
       if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'page_delete', L('Page_Management'), 'Page：'.$page_id);
      }  
        }
    }
    return true;
}

function revert_page($page_id) {
  global $qs_config,$qs_dev_config;
    $delete_pages = load_pages('delete');
    if (!isset($delete_pages[$page_id])) return false;

    $page = $delete_pages[$page_id];
    $prev_state = $page['prev_state'] ?? 'publish';
    unset($delete_pages[$page_id]);

    $delete_file = get_index_file('delete');
    file_put_contents($delete_file, "<?php\n\$qs_pages=".var_export($delete_pages, true)."\n?>\n");

    $target_pages = load_pages($prev_state);
    $target_pages[$page_id] = $page;
    $target_file = get_index_file($prev_state);
    file_put_contents($target_file, "<?php\n\$qs_pages=".var_export($target_pages, true)."\n?>\n");
       if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'page_revert', L('Page_Management'), 'Page：'.$page_id);
      }  
    return true;
}

function convert_page_to_draft($page_id, $state) {
  global $qs_config,$qs_dev_config;
    $pages = load_pages($state);
    if (!isset($pages[$page_id])) return false;

    $page = $pages[$page_id];
    unset($pages[$page_id]);

    $index_file = get_index_file($state);
    file_put_contents($index_file, "<?php\n\$qs_pages=".var_export($pages, true)."\n?>\n");

    $draft_pages = load_pages('draft');
    $draft_pages[$page_id] = $page;
    $draft_file = get_index_file('draft');
    file_put_contents($draft_file, "<?php\n\$qs_pages=".var_export($draft_pages, true)."\n?>\n");
       if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'page_to_draft', L('Page_Management'), 'Page：'.$page_id, '');
      }  
    return true;
}

//草稿 → 发布
function publish_page($page_id) {
  global $qs_config,$qs_dev_config;
    $draft_pages = load_pages('draft');
    if (!isset($draft_pages[$page_id])) return false;

    $page = $draft_pages[$page_id];
    unset($draft_pages[$page_id]);
    $draft_file = get_index_file('draft');
    file_put_contents($draft_file, "<?php\n\$qs_pages=".var_export($draft_pages, true)."\n?>\n");

    $pub_pages = load_pages('publish');
    $pub_pages[$page_id] = $page;
    $pub_file = get_index_file('publish');
    file_put_contents($pub_file, "<?php\n\$qs_pages=".var_export($pub_pages, true)."\n?>\n");
       if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'page_draft_publish', L('Page_Management'), 'Page：'.$page_id, '');
      }  
    return true;
}

//state白名单校验
$allow_state = ['publish','draft','delete'];
$state = isset($_GET['state']) && in_array($_GET['state'],$allow_state) ? $_GET['state'] : 'publish';

$qs_pages = load_pages($state);

//单点删除回收
if(isset($_GET['delete'])){
    delete_page($_GET['delete'], $state);
    Header('Location:page.php?done=true&state='.urlencode($state));
    exit;
}

//发布草稿
if(isset($_GET['publish'])){
    publish_page($_GET['publish']);
    Header('Location:page.php?done=true&state='.urlencode($state));
    exit;
}

//单点还原（回收站页面）
if(isset($_GET['revert'])){
    revert_page($_GET['revert']);
    Header('Location:page.php?done=true&state='.urlencode($state));
    exit;
}
//转为草稿（仅publish状态可用）
if(isset($_GET['todraft'])){
    convert_page_to_draft($_GET['todraft'], $state);
    Header('Location:page.php?done=true&state='.urlencode($state));
    exit;
}

//批量操作
if (isset($_GET['apply'])) {
    $ids = explode(',', $_GET['ids']);
    foreach ($ids as $id) {
        $id = trim($id);
        if ($id === '') continue;
        switch ($_GET['apply']) {
            case 'delete':
                delete_page($id, $state);
                break;
            case 'revert':
                revert_page($id);
                break;
            case 'todraft':
                convert_page_to_draft($id, $state);
                break;
            case 'publish':
                publish_page($id);
                break;
        }
    }
    Header('Location:page.php?done=true&state='.urlencode($state));
    exit;
}

if (isset($_GET['done'])) {
    $message = L('Operation_successful');
}

$page_ids = array_keys($qs_pages);
$page_count = count($page_ids);

$date_array = array();
for ($i = $page_count - 1; $i >= 0; $i --) {
    $page_id = $page_ids[$i];
    $page = $qs_pages[$page_id];
    $date_array[] = substr($page['date'], 0, 7);
}
$date_array = array_unique($date_array);

$filter_date = isset($_GET['date']) ? trim($_GET['date']) : '';

$qs_pages2 = array();
for ($i = 0; $i < $page_count; $i ++) {
    $page_id = $page_ids[$i];
    $page = $qs_pages[$page_id];
    if ($filter_date != '' && strpos($page['date'], $filter_date) !== 0)
        continue;
    $qs_pages2[$page_id] = $page;
}
$qs_pages = $qs_pages2;
$page_ids = array_keys($qs_pages);
$page_count = count($page_ids);

$last_page = ceil($page_count / $qs_config['sccpage_page']);
$page_num = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($page_num < 1) $page_num = 1;
if ($last_page > 0 && $page_num > $last_page) $page_num = $last_page;

$prev_page = $page_num > 1 ? $page_num - 1 : 1;
$next_page = $page_num < $last_page ? $page_num + 1 : $last_page;

$esc_state = htmlspecialchars($state);
$esc_filter_date = htmlspecialchars($filter_date);
?>

<script type="text/javascript">
function check_all(name)
{
  var el = document.getElementsByTagName('input');
  var len = el.length;
  for(var i=0; i<len; i++) {
    if((el[i].type=="checkbox") && (el[i].name==name)) {
      el[i].checked = true;
    }
  }
}
function clear_all(name)
{
  var el = document.getElementsByTagName('input');
  var len = el.length;
  for(var i=0; i<len; i++) {
    if((el[i].type=="checkbox") && (el[i].name==name)) {
      el[i].checked = false;
    }
  }
}
function apply_all(opid, name)
{
  var el = document.getElementsByTagName('input');
  var len = el.length;
  var ids = '';
  for(var i=0; i<len; i++) {
    if((el[i].type=="checkbox") &&
       (el[i].name==name) &&
       el[i].checked == true &&
       el[i].value != '') {
      ids += el[i].value + ',';
    }
  }
  var op = document.getElementById(opid);
  if (ids != '')
    location.href = '?state=<?php echo $esc_state; ?>&apply=' + op.value + '&ids=' + ids;
}
function do_filter()
{
  var date = document.getElementById('date');
  location.href = '?state=<?php echo $esc_state; ?>&date=' + encodeURIComponent(date.value);
}
function goto_page(e)
{
  var evt = e || window.event;
  var eventSrc = evt.target||evt.srcElement;
  if ((e.keyCode || e.which) == 13) {
      evt.preventDefault();
      location.href = '?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=' + eventSrc.value;
  }
}
</script>
<?php if (isset($message)) { ?>
<div class="updated"><?php echo $message; ?></div>
<?php } ?>
<div class="admin_page_name"><?= L('Manage_post');?><a class="link_button" href="page-edit.php"><?= L('Write_Page');?></a></div>
<div class="post_mode_link">
<a href="?state=publish" class="link_button <?php if ($state == 'publish') echo 'current'; ?>"><?= L('Published');?></a>
<a href="?state=draft" class="link_button <?php if ($state == 'draft') echo 'current'; ?>"><?= L('Drafts');?></a>
<a href="?state=delete" class="link_button <?php if ($state == 'delete') echo 'current'; ?>"><?= L('Recycle');?></a>
</div>
<div class="table_list_tool">
  <span>
    <select id="op1">
      <option value=""><?= L('Bulk_Operations');?></option>
      <?php if ($state == 'delete') { ?>
      <option value="revert"><?= L('Restore');?></option>
      <option value="delete"><?= L('Delete');?></option>
      <?php } elseif ($state == 'draft') { ?>
      <option value="publish"><?= L('Publish');?></option>
      <option value="delete"><?= L('Recycle');?></option>
      <?php } else { ?>
      <option value="delete"><?= L('Recycle');?></option>
      <option value="todraft"><?= L('Convert_to_draft');?></option>
      <?php } ?>
    </select>
    <input type="button" value="<?= L('Apply');?>" onclick="apply_all('op1','ids');"/>
  </span>
  <span>
    <select id="date">
      <option value=""><?= L('Show_All_Dates');?></option>
      <?php foreach ($date_array as $date_name) { ?>
      <option value="<?php echo htmlspecialchars($date_name); ?>" <?php if ($filter_date == $date_name) echo ' selected="selected"'; ?>><?php echo htmlspecialchars($date_name); ?></option>
      <?php } ?>
    </select>
    <input type="submit" value="<?= L('Filter');?>" onclick="do_filter();"/>
  </span>
  <span class="pager">
    <?= L('Total');?> <?php echo $page_count; ?> &nbsp;&nbsp;
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=1">&laquo;</a>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $prev_page; ?>">&lsaquo;</a>
    <?= L('The');?> <input type="text" value="<?php echo $page_num; ?>" id="page_input_1"/> / <?php echo $last_page; ?> <?= L('page');?>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $next_page; ?>">&rsaquo;</a>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $last_page; ?>">&raquo;</a>
  </span>
  <script type="text/javascript">
  document.getElementById('page_input_1').onkeydown = goto_page;
  </script>
</div>
<div class="table_list post_list">
<table cellpadding="0" cellspacing="0" id="list">
  <thead>
    <tr>
    <td style="width:20px"><input type="checkbox" name="ids" onclick="if(this.checked==true) { check_all('ids'); } else { clear_all('ids'); }" value=""/></td>
    <td><?= L('Title');?></td><td style="width:25%"><?= L('Path');?></td><td style="width:15%"><?= L('Date');?></td>
    </tr>
  </thead>
  <tbody>
  <?php for ($i = 0; $i < $page_count; $i ++) {
      if ($i < ($page_num - 1) * $qs_config['sccpage_page'] || $i >= ($page_num * $qs_config['sccpage_page'])) continue;
      $page_id = $page_ids[$i];
      $page = $qs_pages[$page_id];
  ?>
    <tr<?php if ($i % 2 == 0) echo ' class="alt"'; ?>>
      <td><input type="checkbox" name="ids" value="<?php echo htmlspecialchars($page_id); ?>"/></td>
      <td>
        <a class="row_name" href="page-edit.php?file=<?php echo htmlspecialchars($page['file']); ?>"><?php echo htmlspecialchars($page['title']);?></a>
        <div class="row_tool">
          <a class="link_button" href="page-edit.php?file=<?php echo htmlspecialchars($page['file']); ?>"><?= L('Edit');?></a>
          <?php if ($state == 'delete') { ?>
          <a class="link_button" href="?revert=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Restore');?></a>
          <a class="link_button" href="?delete=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Delete');?></a>
          <?php } elseif ($state == 'draft') { ?>
          <a class="link_button" href="?publish=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Publish');?></a>
          <a class="link_button" href="?delete=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Recycle');?></a>
          <?php } else { ?>
          <a class="link_button" href="?todraft=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Convert_to_draft');?></a>
          <a class="link_button" href="?delete=<?php echo htmlspecialchars($page_id); ?>&state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>"><?= L('Recycle');?></a>
          <?php } ?>
          <a class="link_button" href="../?<?php echo urlencode($page_id); ?>/" target="_blank"><?= L('Browse');?></a>
        </div>
      </td>
      <td><?php
        $paths = explode('/', $page_id);
        $paths_count = count($paths);
        for ($j = 0; $j < $paths_count - 1; $j ++) {
          echo '－';
        }
        echo htmlspecialchars($paths[$paths_count - 1]); ?></td>
      <td><?php echo htmlspecialchars($page['date']);?></td>
    </tr>
  <?php } ?>
  </tbody>
  <tfoot>
    <tr>
    <td><input type="checkbox" name="ids" onclick="if(this.checked==true) { check_all('ids'); } else { clear_all('ids'); }" value=""/></td>
    <td><?= L('Title');?></td><td><?= L('Path');?></td><td><?= L('Date');?></td>
    </tr>
  </tfoot>
</table>
</div>
<div class="table_list_tool">
  <span>
    <select id="op2">
      <option value=""><?= L('Bulk_Operations');?></option>
      <?php if ($state == 'delete') { ?>
      <option value="revert"><?= L('Restore');?></option>
      <option value="delete"><?= L('Delete');?></option>
      <?php } elseif ($state == 'draft') { ?>
      <option value="publish"><?= L('Publish');?></option>
      <option value="delete"><?= L('Recycle');?></option>
      <?php } else { ?>
      <option value="delete"><?= L('Recycle');?></option>
      <option value="todraft"><?= L('Convert_to_draft');?></option>
      <?php } ?>
    </select>
    <input type="button" value="<?= L('Apply');?>" onclick="apply_all('op2','ids');"/>
  </span>
  <span class="pager">
    <?= L('Total');?> <?php echo $page_count; ?> &nbsp;&nbsp;
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=1">&laquo;</a>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $prev_page; ?>">&lsaquo;</a>
    <?= L('The');?> <input type="text" value="<?php echo $page_num; ?>" id="page_input_2"/> / <?php echo $last_page; ?> <?= L('page');?>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $next_page; ?>">&rsaquo;</a>
    <a class="link_button" href="?state=<?php echo $esc_state; ?>&date=<?php echo $esc_filter_date;?>&page=<?php echo $last_page; ?>">&raquo;</a>
  </span>
  <script type="text/javascript">
  document.getElementById('page_input_2').onkeydown = goto_page;
  </script>
</div>
<?php require 'foot.php' ?>