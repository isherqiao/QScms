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
 * @File         picmanage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Image manager: browses, uploads and deletes the images kept in the upload directory.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// --- [调试模式] 生产环境确认无误后可注释掉 ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ----------------------------------------

require_once __DIR__.'/head.php';

// --- 配置区域 ---
$baseDir = __DIR__ . '/upload/';
if (!is_dir($baseDir)) {
    $baseDir = __DIR__ . '/../upload/';
}

// Web 路径前缀 (确保以 / 开头)
$uploadWebBase = '/upload/'; 
$items_per_page = 20;

// --- 辅助函数：递归扫描图片 ---
function scanImages($dir, $webBase) {
    $images = [];
    if (!is_dir($dir) || !is_readable($dir)) return $images;
    
    $files = @scandir($dir);
    if ($files === false) return $images;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        $cleanWebBase = rtrim($webBase, '/');
        $webPath = $cleanWebBase . '/' . $file;
        
        if (is_dir($path)) {
            $subImages = scanImages($path, $webPath);
            $images = array_merge($images, $subImages);
        } else {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'apng'])) {
                $size = @filesize($path);
                $images[] = [
                    'name' => $file,
                    'path' => $path,
                    'webPath' => $webPath,
                    'size' => ($size === false ? 0 : $size),
                    'folder' => dirname($webPath)
                ];
            }
        }
    }
    return $images;
}

// --- 处理删除请求 ---
$deleteMsg = '';
$errorMsg = '';
$cleanedDirsMsg = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['file'])) {
    $fileToDelete = $_GET['file'];
    $realBase = realpath($baseDir);
    $realTarget = realpath($fileToDelete);
    
    if ($realBase && $realTarget && strpos($realTarget, $realBase) === 0) {
        $fileDir = dirname($realTarget); // 获取文件所在目录
        
        if (unlink($realTarget)) {
            $deleteMsg = sprintf(L('File_Deleted'), basename($fileToDelete));
            
            // [核心功能] 尝试清理空文件夹
            $cleanedCount = 0;
            $currentCheckDir = $fileDir;
            
            while (true) {
                $realCurrent = realpath($currentCheckDir);
                // 安全检查：不能删除 baseDir 本身
                if (!$realCurrent || strlen($realCurrent) <= strlen($realBase)) {
                    break;
                }
                
                $files = @scandir($currentCheckDir);
                $isEmpty = true;
                if ($files) {
                    foreach ($files as $f) {
                        if ($f !== '.' && $f !== '..') {
                            $isEmpty = false;
                            break;
                        }
                    }
                }
                
                if ($isEmpty) {
                    if (rmdir($currentCheckDir)) {
                        $cleanedCount++;
                        $currentCheckDir = dirname($currentCheckDir);
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            
            if ($cleanedCount > 0) {
                $cleanedDirsMsg = sprintf(L('Cleaned_Empty_Folders'), $cleanedCount);
            }
        } else {
            $errorMsg = L('Delete_Failed_Permission');
        }
    } else {
        $errorMsg = L('Security_Block_Illegal_Path');
    }
}

// --- 获取数据 ---
$allImages = scanImages($baseDir, $uploadWebBase);

usort($allImages, function($a, $b) {
    return strcmp($b['name'], $a['name']); 
});

$totalItems = count($allImages);
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// 【修复点】这里定义的是 $totalPages (大写 P)
$totalPages = max(1, ceil($totalItems / $items_per_page));
$start = ($page - 1) * $items_per_page;
$currentImages = array_slice($allImages, $start, $items_per_page);

function formatSize($bytes) {
    if ($bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

$totalSize = 0;
foreach ($allImages as $img) { $totalSize += $img['size']; }
?>

<div class="admin_page_name">
    <a href="set.php"><?= L('system_settings'); ?></a> 
    > <span style="font-weight:bold;">🖼️ <?= L('Image_Resource_Manage'); ?></span>
    <span style="float:right; color:#666; font-size:14px;">
        <?= sprintf(L('Total_Images_And_Size'), $totalItems, formatSize($totalSize)) ?>
    </span>
</div>

<?php if ($deleteMsg): ?>
    <div class="updated" style="margin:10px 0; padding:10px; background:#d4edda; border:1px solid #c3e6cb; color:#155724; border-radius:4px;">
        ✅ <?= $deleteMsg ?> 
        <?php if ($cleanedDirsMsg): ?>
            <br>🧹 <strong><?= $cleanedDirsMsg ?></strong>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <div class="error" style="margin:10px 0; padding:10px; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; border-radius:4px;"><?= $errorMsg; ?></div>
<?php endif; ?>

<!-- 路径诊断 -->
<div style="background:#eef; padding:10px; margin:10px 0; border:1px solid #ccd; font-size:13px; color:#335;">
    <strong>🔍 <?= L('Path_Diagnosis'); ?></strong><br>
    <?= L('Physical_Root_Dir'); ?><code><?= htmlspecialchars($baseDir) ?></code><br>
    <?= L('Web_Access_Prefix'); ?><code><?= htmlspecialchars($uploadWebBase) ?></code><br>
    <?php if (!empty($currentImages)): ?>
        <?= L('Test_Image_URL'); ?><code><?= htmlspecialchars($currentImages[0]['webPath']) ?></code>
    <?php else: ?>
        <em><?= L('No_Image_Or_Path_Error'); ?></em>
    <?php endif; ?>
</div>

<div class="container" style="margin-top:20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
        <?php if (empty($currentImages)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:60px; color:#999; background:#f9f9f9; border-radius:8px; border: 2px dashed #ddd;">
                📂 <?= L('No_Images_In_Dir'); ?><br>
                <span style="font-size:12px;"><?= L('Empty_Folders_Auto_Cleaned'); ?></span>
            </div>
        <?php else: ?>
            <?php foreach ($currentImages as $img): ?>
                <div style="border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                    <div style="height:160px; overflow:hidden; background:#f4f4f4; display:flex; align-items:center; justify-content:center; border-bottom:1px solid #eee;">
                        <?php if (file_exists($img['path'])): ?>
                            <img src="<?= htmlspecialchars($img['webPath']) ?>" 
                                 alt="<?= htmlspecialchars($img['name']) ?>" 
                                 style="max-width:100%; max-height:100%; object-fit:contain;">
                        <?php else: ?>
                            <div style="color:#999; font-size:12px; text-align:center;">⚠️ <?= L('File_Lost'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="padding:12px;">
                        <div title="<?= htmlspecialchars($img['name']) ?>" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:bold; margin-bottom:6px; color:#333; font-size:14px;">
                            <?= htmlspecialchars($img['name']) ?>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; color:#666; font-size:12px; margin-bottom:8px;">
                            <span>📏 <?= formatSize($img['size']) ?></span>
                            <span style="color:#0073aa; font-size:10px;" title="<?= htmlspecialchars($img['folder']) ?>">
                                📁 <?= str_replace($uploadWebBase, '', $img['folder']) ?>
                            </span>
                        </div>
                        
                        <div style="display:flex; gap:6px; margin-top:10px;">
                            <a href="<?= htmlspecialchars($img['webPath']) ?>" target="_blank" 
                               style="flex:1; text-align:center; padding:6px 0; background:#0073aa; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;">
                               👁️ <?= L('View_Image'); ?>
                            </a>
                            
                            <a href="?action=delete&file=<?= urlencode($img['path']) ?>&page=<?= $page ?>" 
                               onclick="return confirm('⚠️ ' + <?= htmlspecialchars(json_encode(sprintf(L('Confirm_Delete_Image'), addslashes($img['name']))), ENT_QUOTES, 'UTF-8'); ?>);"
                               style="flex:1; text-align:center; padding:6px 0; background:#dc3545; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;">
                               🗑️ <?= L('Delete'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 分页部分：已修复变量名 $totalPages -->
    <div class="pagination" style="margin-top:30px; text-align:center; padding:20px 0; border-top:1px solid #eee;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" style="padding:8px 16px; background:#f0f0f0; text-decoration:none; color:#333; border-radius:4px;">&laquo; <?= L('Previous_Page'); ?></a>
        <?php endif; ?>
        
        <span style="padding:8px 12px; color:#555; font-weight:bold; border:1px solid #ddd; border-radius:4px; margin:0 5px;">
            <?= $page ?> / <?= $totalPages ?>
        </span>

        <!-- 【修复点】这里之前写成了 $total_pages，现在改为 $totalPages -->
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" style="padding:8px 16px; background:#f0f0f0; text-decoration:none; color:#333; border-radius:4px;"><?= L('Next_Page'); ?> &raquo;</a>
        <?php endif; ?>
    </div>
    
    <div style="text-align:center; margin-top:20px; color:#28a745; font-size:13px; font-weight:bold;">
        💡 <?= L('Smart_Clean_Enabled_Desc'); ?>
    </div>
</div>

<div class="clear"></div>
<?php require_once __DIR__.'/foot.php'; ?>