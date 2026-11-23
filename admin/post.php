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
 * @File         post.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Post renderer: resolves the post by id and outputs it through the active theme.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require 'head.php' ?>
<?php

function safe_html($value) {
  $safe_value = $value ?? '';
  if (!is_string($safe_value)) {
    $safe_value = '';
  }
  return htmlspecialchars($safe_value, ENT_QUOTES, 'UTF-8');
}

// 基于SITE_ROOT合成真实绝对路径，不再依赖当前脚本所在目录
$realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
if (!is_dir($realPostRoot . 'data/')) {
    mkdir($realPostRoot . 'data/', 0755, true);
}

function get_index_file($state) {
    global $qs_config;
    $realPostRoot = rtrim(SITE_ROOT . '/' . $qs_config['qs_post_dir'], '/') . '/';
    if ($state == 'draft') {
        return $realPostRoot . 'index/draft.php';
    } else if ($state == 'delete') {
        return $realPostRoot . 'index/delete.php';
    } else {
        return $realPostRoot . 'index/publish.php';
    }
}

function load_posts($state) {
  global $qs_posts,$qs_config;
  
  $index_file = get_index_file($state);

  if (file_exists($index_file)) {
      if (function_exists('opcache_invalidate')) {
          opcache_invalidate($index_file, true);
      }
      
      unset($GLOBALS['qs_posts']);
      unset($qs_posts);
      
      require $index_file;
      $posts = isset($qs_posts) ? $qs_posts : array();
      
      foreach ($posts as &$post) {
          // 如果 bookname 不存在，设为空字符串
          if (!isset($post['bookname'])) {
              $post['bookname'] = '';
          }
          if (!isset($post['date']) || empty($post['date'])) {
              $post['date'] = date('Y-m-d H:i:s');
          }
          // 如果 tags 不存在（防止其他报错），也初始化一下
          if (!isset($post['tags']) || !is_array($post['tags'])) {
              $post['tags'] = array();
          }
          if (!isset($post['title'])) {
              $post['title'] = L('Untitled_Article');
          }
      }
      unset($post); // 解除引用
       return $posts;
  }
  
  return array();
}
// 置顶排序：置顶文章排最前，多个置顶按置顶时间倒序，其余保持原有顺序
function qs_sort_pinned_posts($posts) {
  $pinned = array();
  $normal = array();
  foreach ($posts as $id => $post) {
    if (!empty($post['pinned'])) {
      $pinned[$id] = $post;
    } else {
      $normal[$id] = $post;
    }
  }
  if (empty($pinned)) {
    return $posts;
  }
  uasort($pinned, function($a, $b) {
    $ta = isset($a['pinned_at']) ? (int)$a['pinned_at'] : 0;
    $tb = isset($b['pinned_at']) ? (int)$b['pinned_at'] : 0;
    if ($ta === $tb) return 0;
    return ($ta > $tb) ? -1 : 1;
  });
  return $pinned + $normal;
}

// 操作函数：都基于最新数据执行
function delete_post($post_id, $state) {
global $qs_config,$qs_dev_config;
  $posts = load_posts($state); // 读最新数据
  if (!isset($posts[$post_id])) return false;
  
  $post = $posts[$post_id];
  $post['prev_state'] = $state;
  unset($posts[$post_id]);
  
  // 获取对应状态索引文件
  $index_file = get_index_file($state);
  file_put_contents($index_file, "<?php\n\$qs_posts=".var_export($posts, true)."\n?>");
  
  // 写入删除文件（如果不是删除状态）
  if ($state != 'delete') {
    $delete_posts = load_posts('delete');
    $delete_posts[$post_id] = $post;
    $delete_file = get_index_file('delete');
    file_put_contents($delete_file, "<?php\n\$qs_posts=".var_export($delete_posts, true)."\n?>");
    if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
        write_operate_log(qs_get_current_admin_name(), 'post_trash', L('Log_Article_Manage'), sprintf(L('Log_ID'), $post_id));
    }
  } else {
    // 彻底删除数据文件
    $data_file = $qs_config['qs_post_dir'].'data/'.$post_id.'.php';
    if (file_exists($data_file)) unlink($data_file);
    if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
        write_operate_log(qs_get_current_admin_name(), 'post_delete', L('Log_Article_Manage'), sprintf(L('Log_ID'), $post_id));
    }
  }
  return true;
}

function revert_post($post_id) {
  global $qs_config,$qs_dev_config;
  $delete_posts = load_posts('delete'); // 读最新删除数据
  if (!isset($delete_posts[$post_id])) return false;
  
  $post = $delete_posts[$post_id];
  $prev_state = $post['prev_state'] ?? 'publish';
  unset($delete_posts[$post_id]);
  
  // 更新删除文件
  $delete_file = get_index_file('delete');
  file_put_contents($delete_file, "<?php\n\$qs_posts=".var_export($delete_posts, true)."\n?>");
  
  // 更新目标状态文件
  $target_posts = load_posts($prev_state);
  $target_posts[$post_id] = $post;
  $target_file = get_index_file($prev_state);
  file_put_contents($target_file, "<?php\n\$qs_posts=".var_export($target_posts, true)."\n?>");
  if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'post_revert', L('Log_Article_Manage'), sprintf(L('Log_ID'), $post_id));
  }
  return true;
}

function convert_to_draft($post_id, $state) {
  global $qs_config,$qs_dev_config;
  $posts = load_posts($state); // 读最新数据
  if (!isset($posts[$post_id])) return false;
  
  $post = $posts[$post_id];
  unset($posts[$post_id]);
  
  // 更新当前状态文件
  $index_file = get_index_file($state);
  file_put_contents($index_file, "<?php\n\$qs_posts=".var_export($posts, true)."\n?>");
  
  // 更新草稿文件
  $draft_posts = load_posts('draft');
  $draft_posts[$post_id] = $post;
  $draft_file = get_index_file('draft');
  file_put_contents($draft_file, "<?php\n\$qs_posts=".var_export($draft_posts, true)."\n?>");
  if(function_exists('write_operate_log') && isset($qs_dev_config['enable_system_logs']) && $qs_dev_config['enable_system_logs'] === 'open'){
      write_operate_log(qs_get_current_admin_name(), 'post_draft', L('Log_Article_Manage'), sprintf(L('Log_ID'), $post_id));
  }
  return true;
}

// 构建跳转URL
function build_redirect_url($state) {
  $params = [
    'state' => $state,
    'done' => '1'
  ];
  // 保留筛选参数
  if (isset($_GET['qs_search']) && !empty($_GET['qs_search'])) $params['qs_search'] = $_GET['qs_search'];
  if (isset($_GET['tag']) && !empty($_GET['tag'])) $params['tag'] = $_GET['tag'];
  if (isset($_GET['date']) && !empty($_GET['date'])) $params['date'] = $_GET['date'];
  if (isset($_GET['bookname']) && !empty($_GET['bookname'])) $params['bookname'] = $_GET['bookname'];
  if (isset($_GET['page']) && !empty($_GET['page'])) $params['page'] = $_GET['page'];
  
  return 'post.php?' . http_build_query($params);
}

$current_state = isset($_GET['state']) ? $_GET['state'] : 'publish';
$is_aggregate = !empty($qs_config['is_aggregate_site']);

// =================聚合站拦截：禁止扫描孤儿文件操作================
if (isset($_GET['action']) && $_GET['action'] == 'scan_orphans') {
    if ($is_aggregate) {
        header('Location: post.php?state='.$current_state);
        exit;
    }
    global $qs_config;
    // 1. 增加执行时间，防止文件多时超时
    set_time_limit(180);
    // 2. 临时关闭错误显示，防止部分警告中断流程 (日志仍会记录)
    $old_error_level = error_reporting();
    error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);

    $data_dir = $qs_config['qs_post_dir'].'data/';
    $index_dir = $qs_config['qs_post_dir'].'index/';
    $index_files = [
        get_index_file('publish'),
        get_index_file('draft'),
        get_index_file('delete')
    ];
    
    $existing_ids = [];
    
    // --- 第一步：收集现有索引中的 ID ---
    foreach ($index_files as $idx_path) {
        if (file_exists($idx_path)) {
            @include $idx_path; // 抑制包含错误
            if (isset($qs_posts) && is_array($qs_posts)) {
                foreach ($qs_posts as $id => $post) {
                    $existing_ids[(string)$id] = true;
                }
            }
            unset($qs_posts); // 清理变量
        }
    }
    
    $recovered_count = 0;
    
    if (is_dir($data_dir)) {
        $files = scandir($data_dir);
        // 加载当前的回收站数据
        $delete_posts = load_posts('delete'); 
        if (!is_array($delete_posts)) $delete_posts = [];
        
        foreach ($files as $file) {
            // 只处理 .php 文件，排除 index.php
            if (substr($file, -4) === '.php' && $file !== 'index.php') {
                $file_id = substr($file, 0, -4);
                
                // 核心判断：文件名ID不在现有索引中，且不在待恢复列表中
                if (!isset($existing_ids[$file_id]) && !isset($delete_posts[$file_id])) {
                    $file_path = $data_dir . $file;
                    $orphan_data = null;
                    
                    // --- 第二步：读取并解析文件内容 ---
                    $content = file_get_contents($file_path);
                    
                    if ($content !== false && strlen($content) > 0) {
                        $serialized_string = '';
                        
                        // 寻找 "//" 的位置
                        $pos_comment = strpos($content, '//');
                        
                        if ($pos_comment !== false) {
                            // 截取 "//" 之后的所有内容
                            // 这会自动去掉 "<?php //" 前缀，只留下序列化字符串
                            $serialized_string = trim(substr($content, $pos_comment + 2));
                        }
                        
                        // 尝试反序列化
                        if (!empty($serialized_string)) {
                            // 再次临时屏蔽 unserialize 可能产生的 Notice
                            $data = @unserialize($serialized_string);
                            if (is_array($data)) {
                                $orphan_data = $data;
                            }
                        }
                    }
                    
                    // --- 第三步：数据清洗与补全 (关键：消除 Warning) ---
                    if ($orphan_data && is_array($orphan_data)) {
                        // 确定最终 ID (优先取内容里的，其次取文件名)
                        $final_id = isset($orphan_data['id']) ? (string)$orphan_data['id'] : $file_id;
                        
                        // 二次检查 ID 冲突
                        if (!isset($existing_ids[$final_id]) && !isset($delete_posts[$final_id])) {
                            
                            // 【核心修复】使用 ?? 运算符提供默认值，彻底解决 "Undefined array key" 和 "null to htmlspecialchars"
                            $clean_data = [
                                'id'             => $final_id,
                                'title'          => $orphan_data['title'] ?? sprintf(L('Recovered_File_Title'), $file),
                                'bookname'       => $orphan_data['bookname'] ?? '',       // 旧文件无此字段时设为空
                                'bookgenres'     => $orphan_data['bookgenres'] ?? '',     // 旧文件无此字段时设为空
                                'tags'           => (isset($orphan_data['tags']) && is_array($orphan_data['tags'])) ? $orphan_data['tags'] : [],
                                'date'           => $orphan_data['date'] ?? date('Y-m-d H:i:s'),
                                'state'          => $orphan_data['state'] ?? 'publish',
                                'content'        => $orphan_data['content'] ?? '',
                                'summary'        => $orphan_data['summary'] ?? '',
                                // 元数据标记
                                'prev_state'     => 'orphan_recovery',
                                'recovered_time' => date('Y-m-d H:i:s'),
                                'recovered_from' => $file
                            ];
                            
                            $delete_posts[$final_id] = $clean_data;
                            $recovered_count++;
                        }
                    } else {
                        // --- 兜底方案：文件彻底损坏或格式不对 ---
                        $clean_data = [
                            'id'             => $file_id,
                            'title'          => sprintf(L('Corrupted_File_Recovery_Title'), $file),
                            'bookname'       => '',
                            'bookgenres'     => '',
                            'tags'           => [],
                            'date'           => date('Y-m-d H:i:s'),
                            'state'          => 'publish',
                            'content'        => '',
                            'summary'        => '',
                            'prev_state'     => 'corrupted_file',
                            'recovered_time' => date('Y-m-d H:i:s')
                        ];
                        $delete_posts[$file_id] = $clean_data;
                        $recovered_count++;
                    }
                }
            }
        }
        
        // --- 第四步：写入回收站索引 ---
        if ($recovered_count > 0) {
            $delete_file = get_index_file('delete');
            if (!is_dir($index_dir)) {
                mkdir($index_dir, 0755, true);
            }
            
            $export_content = "<?php\n\$qs_posts=" . var_export($delete_posts, true) . "\n?>";
            
            if (file_put_contents($delete_file, $export_content)) {
                // 清除 OPcache
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($delete_file, true);
                }
                // 恢复错误报告级别
                error_reporting($old_error_level);
                header('Location: post.php?state=delete&done=1&msg=recovered_' . $recovered_count);
                exit();
            } else {
                error_reporting($old_error_level);
                die(L('Error_Cannot_Write_Recycle_File'));
            }
        } else {
            error_reporting($old_error_level);
            header('Location: post.php?state=delete&done=1&msg=no_orphans');
            exit();
        }
    }
    
    // 恢复错误报告级别
    error_reporting($old_error_level);
    header('Location: post.php?state='.$current_state);
    exit();
}

// 1. 处理批量/单个操作，聚合模式直接拦截所有写操作
if (!$is_aggregate && (isset($_GET['apply']) || isset($_GET['delete']) || isset($_GET['revert']) || isset($_GET['draft']))) {
  $success = false;
  
  // 批量操作
  if (isset($_GET['apply'])) {
    $action = $_GET['apply'];
    $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : array();
    $ids = array_filter($ids, function($id) { return trim($id) != ''; });
    
    foreach ($ids as $id) {
      switch($action) {
        case 'delete':
          $success = delete_post($id, $current_state);
          break;
        case 'revert':
          $success = revert_post($id);
          break;
        case 'draft':
          $success = convert_to_draft($id, $current_state);
          break;
      }
    }
  }
  // 单个操作
  else {
    if (isset($_GET['delete'])) {
      $success = delete_post($_GET['delete'], $current_state);
    } elseif (isset($_GET['revert'])) {
      $success = revert_post($_GET['revert']);
    } elseif (isset($_GET['draft'])) {
      $success = convert_to_draft($_GET['draft'], $current_state);
    }
  }
  
  // 操作完成，强制重定向（关键：结束当前请求，避免旧数据渲染）
  header('Location: ' . build_redirect_url($current_state));
  exit();
}

// 1.5 置顶 / 取消置顶（聚合只读模式不执行写入）
if (!$is_aggregate && (isset($_GET['pin']) || isset($_GET['unpin']))) {
  $pin_posts = load_posts($current_state);
  $pin_changed = false;

  if (isset($_GET['pin'])) {
    $pin_id = $_GET['pin'];
    if (isset($pin_posts[$pin_id]) && empty($pin_posts[$pin_id]['pinned'])) {
      $pin_posts[$pin_id]['pinned'] = 1;
      $pin_posts[$pin_id]['pinned_at'] = time();
      $pin_changed = true;
    }
  } else {
    $pin_id = $_GET['unpin'];
    if (isset($pin_posts[$pin_id]) && !empty($pin_posts[$pin_id]['pinned'])) {
      $pin_posts[$pin_id]['pinned'] = 0;
      unset($pin_posts[$pin_id]['pinned_at']);
      $pin_changed = true;
    }
  }

  if ($pin_changed) {
    $pin_index_file = get_index_file($current_state);
    file_put_contents($pin_index_file, "<?php\n\$qs_posts=".var_export($pin_posts, true)."\n?>");
    if (function_exists('opcache_invalidate')) {
      opcache_invalidate($pin_index_file, true);
    }
  }

  header('Location: ' . build_redirect_url($current_state));
  exit();
}

// 2. 处理bookname更新（POST）聚合模式拦截
if (!$is_aggregate && isset($_POST['update']) && !empty($_POST['newBookname'])) {
  global $qs_config;
  $selected = $_POST['selectedBookname'];
  $new = $_POST['newBookname'];
  
  $posts = load_posts('publish');
  $newBookpic = '';
  foreach ($posts as &$post) {
    if ($post['bookname'] == $new && !empty($post['bookpic'])) $newBookpic = $post['bookpic'];
    if ($post['bookname'] == $selected || $post['bookname'] == $new) {
      $post['bookname'] = $new;
      if (!empty($newBookpic)) $post['bookpic'] = $newBookpic;
    }
  }
  
  file_put_contents(get_index_file('publish'), "<?php\n\$qs_posts=".var_export($posts, true)."\n?>");
  header('Location: ' . build_redirect_url($current_state));
  exit();
}

$qs_posts = load_posts($current_state);

// 筛选逻辑（基于最新数据）
// 1. 用户过滤
if (isset($_SESSION['Qsis_user_name'])) {
  $current_user = $_SESSION['Qsis_user_name'] ?? '';
  $qs_posts = array_filter($qs_posts, function($post) use ($current_user) {
    return isset($post['user']) && $post['user'] === $current_user;
  });
}

// 2. 搜索过滤
$qs_get_name = isset($_GET['qs_search']) ? trim($_GET['qs_search']) : '';
if (!empty($qs_get_name)) {
  $search_results = array();
  foreach ($qs_posts as $k => $v) {
    $content_file = $qs_config['qs_post_dir'] . 'data/' . $k . '.php';
    $content = file_exists($content_file) ? file_get_contents($content_file) : '';
    if (
      preg_match('/' . preg_quote($qs_get_name, '/') . '/i', $v['title']) ||
      preg_match('/' . preg_quote($qs_get_name, '/') . '/i', implode(', ', $v['tags'])) ||
      preg_match('/' . preg_quote($qs_get_name, '/') . '/i', $content)
    ) {
      $search_results[$k] = $v;
    }
  }
  $qs_posts = $search_results;
}

// 3. 标签/日期/书籍名过滤
$filter_tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_bookname = isset($_GET['bookname']) ? explode(',', $_GET['bookname']) : array();

$filtered_posts = array();
foreach ($qs_posts as $id => $post) {
  if (!empty($filter_tag) && !in_array($filter_tag, $post['tags'])) continue;
  if (!empty($filter_date) && substr($post['date'], 0, 7) != $filter_date) continue;
  if (!empty($filter_bookname) && !in_array($post['bookname'], $filter_bookname)) continue;
  $filtered_posts[$id] = $post;
}
$qs_posts = $filtered_posts;

// 置顶文章排到最前（在分页前排序，保证置顶始终落在第一页）
$qs_posts = qs_sort_pinned_posts($qs_posts);

// 分页处理
$post_count = count($qs_posts);
$sccpost_page = isset($qs_config['sccpost_page']) ? $qs_config['sccpost_page'] : 10;
$page_num = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$last_page = max(1, ceil($post_count / $sccpost_page));
$page_num = min($page_num, $last_page);
$prev_page = max(1, $page_num - 1);
$next_page = min($last_page, $page_num + 1);

// 构建筛选器数据
$date_array = array();
$tags_array = array();
$bookname_array = array();
foreach ($qs_posts as $post) {
  $date_array[] = substr($post['date'], 0, 7);
  $tags_array = array_merge($tags_array, $post['tags']);
  $bookname_array[] = $post['bookname'] ?? '';
}
$date_array = array_unique($date_array);
$tags_array = array_unique($tags_array);
$bookname_array = array_unique($bookname_array);
sort($date_array);
sort($tags_array);
sort($bookname_array);

// 操作成功提示
$message = '';
if (isset($_GET['done'])) {
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] == 'no_orphans') {
            $message = L('Orphan_Scan_No_Missing');
        } elseif (strpos($_GET['msg'], 'recovered_') === 0) {
            $count = str_replace('recovered_', '', $_GET['msg']);
            $message = sprintf(L('Recovered_Orphan_Files'), $count);
        } else {
            $message = L('Operation_successful');
        }
    } else {
        $message = L('Operation_successful');
    }
}
?>

<script type="text/javascript">
function check_all(name) {
  var els = document.getElementsByName(name);
  for (var i = 0; i < els.length; i++) els[i].checked = true;
}
function clear_all(name) {
  var els = document.getElementsByName(name);
  for (var i = 0; i < els.length; i++) els[i].checked = false;
}

function apply_all(opid, name) {
  var op = document.getElementById(opid);
  var action = op.value;
  if (!action) {
    alert("<?= L('Please_Select_Operation_Type'); ?>");
    return;
  }
  
  var ids = [];
  var els = document.getElementsByName(name);
  for (var i = 0; i < els.length; i++) {
    if (els[i].checked) ids.push(els[i].value);
  }
  
  if (ids.length == 0) {
    alert("<?= L('Please_Select_Articles'); ?>");
    return;
  }
  
  var params = new URLSearchParams(window.location.search);
  params.set('apply', action);
  params.set('ids', ids.join(','));
  window.location.href = 'post.php?' + params.toString();
}

function do_filter() {
  var state = "<?= $current_state; ?>";
  var date = document.getElementById('date').value;
  var tag = document.getElementById('tag').value || '';
  var bookname = document.getElementById('bookname') ? document.getElementById('bookname').value : '';
  var qs_search = "<?= $qs_get_name; ?>";
  
  var params = new URLSearchParams();
  params.set('state', state);
  if (date) params.set('date', date);
  if (tag) params.set('tag', tag);
  if (bookname) params.set('bookname', bookname);
  if (qs_search) params.set('qs_search', qs_search);
  
  window.location.href = 'post.php?' + params.toString();
}

function goto_page(e) {
  if (e.keyCode == 13) {
    var params = new URLSearchParams(window.location.search);
    params.set('page', e.target.value);
    window.location.href = 'post.php?' + params.toString();
  }
}

function do_search() {
  var searchValue = document.getElementById('qs_search_input').value.trim();
  var params = new URLSearchParams(window.location.search);
  if (searchValue) params.set('qs_search', searchValue);
  else params.delete('qs_search');
  params.delete('page');
  window.location.href = 'post.php?' + params.toString();
}
</script>

<?php if ($message) { ?>
<div class="updated"><?= $message; ?></div>
<?php } ?>

<div class="admin_page_name">
  <?= L('Manage_post');?>
  <?php if(!$is_aggregate) { ?>
  <a class="link_button" href="post-edit.php"><?= L('Write_Article');?></a>
  <?php } ?>
  <div style="float:right">
    <?= L('System_Mode'); ?>:
    <?php 
    $mode = ($qs_config['sitemode'] == "game") ? L('Game_System') : 
            (($qs_config['sitemode'] == "business") ? L('Business_System') : 
            (($qs_config['sitemode'] == "post") ? L('Blog_System') : 
            (($qs_config['sitemode'] == "video") ? L('Video_System') : 
            (($qs_config['sitemode'] == "novel") ? L('Novel_System') : 
            (($qs_config['sitemode'] == "article") ? L('Literature_System') : 
            (($qs_config['sitemode'] == "download")? L('Download_System') : ""))))));
    echo $mode;
    ?>
    <?php if ($is_aggregate): ?><?= L('Aggregate_Readonly_Mode'); ?><?php endif; ?>
  </div>
</div>

<div class="post_mode_link">
  <a href="?state=publish" class="link_button <?php if ($current_state == 'publish') echo 'current'; ?>"><?= L('Published');?></a>
  <a href="?state=draft" class="link_button <?php if ($current_state == 'draft') echo 'current'; ?>"><?= L('Drafts');?></a>
  <a href="?state=delete" class="link_button <?php if ($current_state == 'delete') echo 'current'; ?>"><?= L('Recycle');?></a>
  <?php if(!$is_aggregate) { ?>
  <a href="#" class="link_button" onclick="localStorage.clear();"><?= L('Empty_All_Autosaved_Data');?></a>
  <?php } ?>
</div>
<?php if ($current_state == 'delete' && !$is_aggregate) { ?>
<?php if (!empty($_SESSION['Qsis_adminname'])) : ?>
<div style="margin: 15px 0; padding: 12px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404; display: flex; align-items: center; justify-content: space-between;">
    <div>
        <strong>🛡️ <?= L('Data_Recovery_Tool'); ?></strong><br>
        <small><?= L('Data_Recovery_Tool_Desc'); ?></small>
    </div>
    <a href="?action=scan_orphans" class="link_button" style="background-color: #856404; color: white; text-decoration: none;" onclick="return confirm(<?= htmlspecialchars(json_encode(L('Confirm_Scan_Orphans')), ENT_QUOTES, 'UTF-8'); ?>);">
        🔍 <?= L('Scan_And_Recover_Orphans'); ?>
    </a>
</div>
<?php endif; ?>
<?php } ?>
<div class="search-container">
  <input type="text" id="qs_search_input" placeholder="<?= L('Search_Articles_Placeholder'); ?>" value="<?= safe_html($qs_get_name); ?>" />
  <input type="button" value="<?= L('Search'); ?>" onclick="do_search();" />
</div>

<?php if ($qs_config['sitemode'] == "novel" && !$is_aggregate) { ?>
<div>
  <div class="label"><?= L('UC_Change_bookname'); ?></div>
  <div class="label">
    <form id="updateForm" method="post">
      <select name="selectedBookname" id="booknameSelect">
        <?php foreach ($bookname_array as $bookname): ?>
          <option value="<?= safe_html($bookname); ?>"><?= safe_html($bookname); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="newBookname" id="newBookname" placeholder="<?= L('Newbookname'); ?>">
      <button type="submit" name="update"><?= L('Update'); ?></button>
    </form>
  </div>
</div>
<?php } ?>

<div class="table_list_tool">
  <span>
    <?php if(!$is_aggregate) { ?>
    <select id="op1">
      <option value=""><?= L('Bulk_Operations');?></option>
      <?php if ($current_state == 'delete') { ?>
      <option value="revert"><?= L('Restore');?></option>
      <option value="delete"><?= L('Delete');?></option>
      <?php } else { ?>
      <option value="delete"><?= L('Recycle');?></option>
      <option value="draft"><?= L('Convert_to_draft');?></option>
      <?php } ?>
    </select>
    <input type="button" value="<?= L('Apply');?>" onclick="apply_all('op1','ids');"/>
    <?php } ?>
  </span>
  <span>
    <select id="date">
      <option value=""><?= L('Show_All_Dates');?></option>
      <?php foreach ($date_array as $date_name) { ?>
      <option value="<?= $date_name; ?>" <?php if ($filter_date == $date_name) echo ' selected="selected"'; ?>><?= $date_name; ?></option>
      <?php } ?>
    </select>
    <?php if ($qs_config['sitemode'] != "novel") { ?>
    <select id="tag">
      <option value=""><?= L('Show_All_Tags');?></option>
      <?php foreach ($tags_array as $tag_name) { ?>
      <option value="<?= $tag_name; ?>" <?php if ($filter_tag == $tag_name) echo ' selected="selected"'; ?>><?= $tag_name; ?></option>
      <?php } ?>
    </select>
    <?php } ?>
    <?php if ($qs_config['sitemode'] == "novel") { ?>
    <select id="bookname">
      <option value=""><?= L('Show_bookname');?></option>
      <?php foreach ($bookname_array as $bookname_name) { ?>
      <option value="<?= $bookname_name; ?>" <?php if (isset($_GET['bookname']) && in_array($bookname_name, explode(',', $_GET['bookname']))) echo 'selected="selected"'; ?>><?= $bookname_name; ?></option>
      <?php } ?>
    </select>    
    <?php } ?>
    <input type="button" value="<?= L('Filter');?>" onclick="do_filter();"/>
  </span>
  <span class="pager">
    <?= L('Total');?> <?= $post_count; ?> &nbsp;&nbsp;
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>">&laquo;</a>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $prev_page; ?>">&lsaquo;</a>
    <?= L('The');?> <input type="text" value="<?= $page_num; ?>" id="page_input_1" onkeydown="goto_page(event);"/> / <?= $last_page; ?> <?= L('page');?>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $next_page; ?>">&rsaquo;</a>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $last_page; ?>">&raquo;</a>
  </span>
</div>

<?php
// 列表列数：勾选 + 标题 + 第三列（仅小说/文学/视频/游戏有）+ 作者(可选) + 日期
// 企业系统等模式没有第三列数据，表头也不能留空格子，否则整张表会错位
$qs_col_count = in_array($qs_config['sitemode'] ?? '', ['novel', 'article', 'blog', 'video', 'game'], true) ? 4 : 3;
if (($qs_config['user_manage'] ?? '') == '1') $qs_col_count++;
?>
<div class="table_list post_list">
  <table cellpadding="0" cellspacing="0" id="list">
    <thead>
      <tr>
        <td style="width:20px">
            <?php if(!$is_aggregate) { ?><input type="checkbox" onclick="if(this.checked) check_all('ids'); else clear_all('ids');"/><?php } ?>
        </td>
        <td><?= L('Title');?></td>
        <?php if ($qs_config['sitemode'] == "novel") { ?>
        <td style="width:25%"><?= L('Book_genres');?> ： <?= L('Novel_Name');?></td>
        <?php } elseif (($qs_config['sitemode'] == "article") || ($qs_config['sitemode'] == "blog")) { ?>
        <td style="width:25%"><?= L('Tags');?></td>
        <?php } elseif (($qs_config['sitemode'] == "video") || ($qs_config['sitemode'] == "game")) { ?>
        <td style="width:25%"><?= L('video');?></td>
        <?php } ?>
        <?php if($qs_config['user_manage'] == "1") { ?>
        <td style="width:10%"><?= L('Author');?></td>
        <?php } ?>
        <td style="width:10%"><?= L('Date');?></td>
      </tr>
    </thead>
    <tbody>
      <?php if ($post_count == 0) { ?>
      <tr>
        <td colspan="<?= $qs_col_count; ?>" style="text-align:center; padding:20px;"><?= L('No posts found');?></td>
      </tr>
      <?php } else { ?>
      <?php 
      $start = ($page_num - 1) * $sccpost_page;
      $displayed_posts = array_slice($qs_posts, $start, $sccpost_page, true);
      $i = 0;
      foreach ($displayed_posts as $post_id => $post) { 
      $pin_mark = !empty($post['pinned']) ? '📌 ' : '';
      ?>
      <tr<?php if ($i % 2 == 0) echo ' class="alt"'; ?>>
        <td class="select">
            <?php if(!$is_aggregate) { ?><input type="checkbox" name="ids" value="<?= $post_id; ?>"/><?php } ?>
        </td>
        <td>
          <?php if ($qs_config['sitemode'] == "novel") {
            $bookpic = isset($post['bookpic']) ? $post['bookpic'] : '';
            $bookname = isset($post['bookname']) ? $post['bookname'] : '';
            $bookgenres = isset($post['bookgenres']) ? $post['bookgenres'] : '';
          ?>
          <?php if(!$is_aggregate) { ?>
          <a class="row_name" href="post-edit.php?id=<?= $post_id; ?>&bookpic=<?= urlencode($bookpic); ?>&bookname=<?= urlencode($bookname); ?>&bookgenres=<?= urlencode($bookgenres); ?>"><?= $pin_mark; ?><?= safe_html($post['title']);?></a>
          <div class="row_tool">
            <a class="link_button" href="post-edit.php?id=<?= $post_id; ?>&bookpic=<?= urlencode($bookpic); ?>&bookname=<?= urlencode($bookname); ?>&bookgenres=<?= urlencode($bookgenres); ?>"><?= L('Edit');?></a>
          <?php } else { ?>
          <span class="row_name"><?= $pin_mark; ?><?= safe_html($post['title']);?></span>
          <div class="row_tool">
          <?php } ?>
          <?php } else { ?>
              <?php if(!$is_aggregate) { ?>
          <a class="row_name" href="post-edit.php?id=<?= $post_id; ?>"><?= $pin_mark; ?><?= safe_html($post['title']);?></a>
          <div class="row_tool">
            <a class="link_button" href="post-edit.php?id=<?= $post_id; ?>"><?= L('Edit');?></a>
              <?php } else { ?>
          <span class="row_name"><?= $pin_mark; ?><?= safe_html($post['title']);?></span>
          <div class="row_tool">
              <?php } ?>
          <?php } ?>

          <?php if (!$is_aggregate && $current_state != 'delete') { ?>
          <a class="link_button" href="?state=<?= $current_state; ?>&<?= !empty($post['pinned']) ? 'unpin' : 'pin'; ?>=<?= $post_id; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&bookname=<?= implode(',', $filter_bookname); ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $page_num; ?>"><?= !empty($post['pinned']) ? '取消置顶' : '📌 置顶'; ?></a>
          <?php } ?>

          <?php if (!$is_aggregate && $current_state != 'delete' && $current_state != 'draft') { ?>
          <a class="link_button" href="?state=<?= $current_state; ?>&draft=<?= $post_id; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&bookname=<?= implode(',', $filter_bookname); ?>&qs_search=<?= urlencode($qs_get_name); ?>"><?= L('Convert_to_draft');?></a>
          <?php } ?>

          <?php if (!$is_aggregate && $current_state == 'delete') { ?>
          <a class="link_button" href="?state=delete&revert=<?= $post_id; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&bookname=<?= implode(',', $filter_bookname); ?>&qs_search=<?= urlencode($qs_get_name); ?>"><?= L('Restore');?></a>
          <a class="link_button" href="?state=delete&delete=<?= $post_id; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&bookname=<?= implode(',', $filter_bookname); ?>&qs_search=<?= urlencode($qs_get_name); ?>" onclick="return confirm('<?= L('Are you sure to delete permanently?');?>');"><?= L('Delete');?></a>
          <?php } elseif (!$is_aggregate && $current_state != 'draft') { ?>
          <a class="link_button" href="?state=<?= $current_state; ?>&delete=<?= $post_id; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&bookname=<?= implode(',', $filter_bookname); ?>&qs_search=<?= urlencode($qs_get_name); ?>" onclick="return confirm('<?= L('Are you sure to move to recycle?');?>');"><?= L('Recycle');?></a>
          <?php } ?>

          <a class="link_button" href="../?<?= $qs_config['sitemode'];?>/<?= $post_id.$qs_config['url_ends']; ?>" target="_blank"><?= L('Browse');?></a>
        </div>
        </td>

        <?php if ($qs_config['sitemode'] == "novel" && isset($post['bookname'])) { ?>
        <td><?= htmlspecialchars($post['bookgenres'] ?? '');?> ： <a href="?state=<?= $current_state; ?>&bookname=<?= htmlspecialchars($post['bookname']); ?>&qs_search=<?= urlencode($qs_get_name); ?>"><?= htmlspecialchars($post['bookname']);?></a></td>
        <?php } elseif (($qs_config['sitemode'] == "video") || ($qs_config['sitemode'] == "game")) { ?>
        <td>
          <?php if (isset($post['video']) && !empty($post['video'])) { ?>
          <video controls="" autoplay="no" muted="" type="video/mp4" style="max-width:200px;" src="<?= $post['video']; ?>"></video>
          <?php } else { ?>
          <?= L('no_video');?>
          <?php } ?>
        </td>
        <?php } elseif (($qs_config['sitemode'] == "article") || ($qs_config['sitemode'] == "blog")) { ?>
        <td>
          <?php foreach ($post['tags'] as $tag) { ?>
          <a href="?state=<?= $current_state; ?>&tag=<?= htmlspecialchars($tag); ?>&qs_search=<?= urlencode($qs_get_name); ?>"><?= htmlspecialchars($tag);?></a><?php if ($tag != end($post['tags'])) echo ', '; ?>
          <?php } ?>
        </td>
        <?php } ?>

        <?php if($qs_config['user_manage'] == "1") { ?>
        <td><?php
          $_post_user = isset($post['user']) ? $post['user'] : '';
          $_post_display = (isset($sp_config_users[$_post_user]['displayname']) && $sp_config_users[$_post_user]['displayname'] !== '') ? $sp_config_users[$_post_user]['displayname'] : $_post_user;
          echo htmlspecialchars($_post_display);
        ?></td>
        <?php } ?>

        <td><?= safe_html($post['date']);?></td>
      </tr>
      <?php $i++; } ?>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <td>
            <?php if(!$is_aggregate) { ?><input type="checkbox" onclick="if(this.checked) check_all('ids'); else clear_all('ids');"/><?php } ?>
        </td>
        <td><?= L('Title');?></td>
        <?php if ($qs_config['sitemode'] == "novel") { ?>
        <td><?= L('Book_genres');?> ： <?= L('Novel_Name');?></td>
        <?php } elseif (($qs_config['sitemode'] == "article") || ($qs_config['sitemode'] == "blog")) { ?>
        <td><?= L('Tags');?></td>
        <?php } elseif (($qs_config['sitemode'] == "video") || ($qs_config['sitemode'] == "game")) { ?>
        <td><?= L('video');?></td>
        <?php } ?>
        <?php if($qs_config['user_manage'] == "1") { ?>
        <td><?= L('Author');?></td>
        <?php } ?>
        <td><?= L('Date');?></td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="table_list_tool">
  <span>
    <?php if(!$is_aggregate) { ?>
    <select id="op1_2">
      <option value=""><?= L('Bulk_Operations');?></option>
      <?php if ($current_state == 'delete') { ?>
      <option value="revert"><?= L('Restore');?></option>
      <option value="delete"><?= L('Delete');?></option>
      <?php } else { ?>
      <option value="delete"><?= L('Recycle');?></option>
      <option value="draft"><?= L('Convert_to_draft');?></option>
      <?php } ?>
    </select>
    <input type="button" value="<?= L('Apply');?>" onclick="apply_all('op1_2','ids');"/>
    <?php } ?>
  </span>
  <span class="pager">
    <?= L('Total');?> <?= $post_count; ?> &nbsp;&nbsp;
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>">&laquo;</a>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $prev_page; ?>">&lsaquo;</a>
    <?= L('The');?> <input type="text" value="<?= $page_num; ?>" id="page_input_2" onkeydown="goto_page(event);"/> / <?= $last_page; ?> <?= L('page');?>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $next_page; ?>">&rsaquo;</a>
    <a class="link_button" href="?state=<?= $current_state; ?>&date=<?= $filter_date;?>&tag=<?= $filter_tag; ?>&qs_search=<?= urlencode($qs_get_name); ?>&page=<?= $last_page; ?>">&raquo;</a>
  </span>
</div>

<?php require 'foot.php' ?> 