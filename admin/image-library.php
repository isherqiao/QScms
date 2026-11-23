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
 * @File         image-library.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Image library browser: walks the upload tree recursively and returns image URLs for the editor picker.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$serverImages = [];
$current_dir = str_replace('\\', '/', realpath(__DIR__));
$parent_dir = dirname($current_dir);
$uploadDir = $parent_dir . '/upload/';
$root_path = realpath($_SERVER['DOCUMENT_ROOT']);
$parent_url = str_replace($root_path, '', $parent_dir);
$baseWebPath = $parent_url . '/upload/';

function scanImageDirectory($dir, $baseWebPath)
{
    $images = [];
    if (!is_dir($dir)) return $images;

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            // 递归子文件夹（202503 / 202504 这种）
            $subImages = scanImageDirectory($path, $baseWebPath);
            $images = array_merge($images, $subImages);
        } else {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'apng'])) {

                // 生成 绝对正确的网站路径 /upload/202503/a.png
                $realPath = realpath($path);
                $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);

                if ($docRoot && strpos($realPath, $docRoot) === 0) {
                    $webPath = substr($realPath, strlen($docRoot));
                } else {
                    $webPath = $baseWebPath . basename($dir) . '/' . $file;
                }

                $webPath = str_replace('\\', '/', $webPath);
                $webPath = preg_replace('#/+#', '/', $webPath);

                $images[] = [
                    'path' => $webPath,
                    'name' => $file
                ];
            }
        }
    }
    return $images;
}
$serverImages = scanImageDirectory($uploadDir, $baseWebPath);

usort($serverImages, function ($a, $b) {
    $aPath = $_SERVER['DOCUMENT_ROOT'] . ltrim($a['path'], '/');
    $bPath = $_SERVER['DOCUMENT_ROOT'] . ltrim($b['path'], '/');

    if (!file_exists($aPath)) return 1;
    if (!file_exists($bPath)) return -1;

    return filemtime($bPath) - filemtime($aPath);
});
?>

<style>
.lib-img-item{position:relative;cursor:pointer;border:2px solid transparent;border-radius:4px;overflow:hidden}
.lib-img-item img{width:100%;height:100px;object-fit:cover;display:block}
</style>

<div id="imageLibraryModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; overflow:auto;">
    <div style="background:#fff; margin:5% auto; padding:20px; width:80%; max-width:900px; border-radius:8px; position:relative;">
        <button onclick="closeImageLibrary()" style="position:absolute; top:10px; right:15px; font-size:24px; cursor:pointer; border:none; background:none;">&times;</button>
        <h3 style="margin-top:0;">📂 <?= L('Server_Image_Library'); ?></h3>
        
        <div style="padding:10px; background:#f7f7f7; border-radius:5px; margin-bottom:15px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <input type="file" id="uploadFile" accept="image/*">
                <button type="button" onclick="ajaxUploadImage(event)" style="padding:6px 12px; background:#0073aa; color:#fff; border:none; border-radius:3px; cursor:pointer;"><?= L('Upload_Image'); ?></button>
            </div>
            <div id="uploadResult" style="margin-top:8px; font-size:12px;"></div>
        </div>

        <div id="imageGrid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:15px; max-height:50vh; overflow-y:auto; padding:10px; border:1px solid #eee;">
            <?php if (empty($serverImages)): ?>
                <div style="grid-column:1/-1; text-align:center; padding:30px; color:#999;"><?= L('No_Images'); ?></div>
            <?php else: ?>
                <?php foreach ($serverImages as $img): ?>
                    <div class="lib-img-item">
                        <button type="button" onclick="deleteImage('<?php echo htmlspecialchars($img['path']); ?>')"
                        style="position:absolute; top:4px; right:4px; width:22px; height:22px; background:rgba(255,0,0,0.7); color:#fff; border:none; border-radius:50%; font-size:12px; cursor:pointer; z-index:10">×</button>
                        <img src="<?php echo $img['path']; ?>" 
                             onclick="selectImage(event, '<?php echo htmlspecialchars($img['path']); ?>')"
                             style="width:100%; height:100px; object-fit:cover;">
                        <div style="font-size:11px; padding:4px; text-align:center; background:#f9f9f9; white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo $img['name']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="margin-top:15px; text-align:right; padding-top:10px; border-top:1px solid #eee;">
            <button onclick="closeImageLibrary()" style="padding:8px 15px; margin-right:10px;"><?= L('Cancel'); ?></button>
            <button id="confirmImageBtn" onclick="confirmImageSelection()" style="padding:8px 20px; background:#0073aa; color:#fff; border:none; border-radius:3px; cursor:pointer;" disabled><?= L('Confirm_Use'); ?></button>
        </div>
    </div>
</div>

<script>
// 全局状态
let selectedImagePath = '';
let targetEditorId = null;

// 打开图片库（带event防闪退）
function openImageLibrary(e, targetId) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    targetEditorId = targetId;
    selectedImagePath = '';
    document.getElementById('confirmImageBtn').disabled = true;
    document.getElementById('imageLibraryModal').style.display = 'block';
}

// 关闭图片库
function closeImageLibrary() {
    document.getElementById('imageLibraryModal').style.display = 'none';
    selectedImagePath = '';
    targetEditorId = null;
    document.getElementById('confirmImageBtn').disabled = true;
    document.querySelectorAll('.lib-img-item').forEach(item => {
        item.style.borderColor = 'transparent';
    });
}

// 选择图片（防冒泡+高亮）
function selectImage(e, path) {
    e.stopPropagation();
    resetImageSelection();
    selectedImagePath = path;
    document.getElementById('confirmImageBtn').disabled = false;
    const item = e.target.closest('.lib-img-item');
    if (item) {
        item.style.borderColor = '#0073aa';
        item.style.boxShadow = '0 0 5px #0073aa';
    }
}

function resetImageSelection() {
    selectedImagePath = '';
    document.getElementById('confirmImageBtn').disabled = true;
    document.querySelectorAll('.lib-img-item').forEach(el => {
        el.style.borderColor = 'transparent';
        el.style.boxShadow = 'none';
    });
}

// 核心插入逻辑（通用版）
function confirmImageSelection() {
    if (!selectedImagePath || !targetEditorId) {
        closeImageLibrary();
        return;
    }

    const imgUrl = selectedImagePath.replace(/^\/[^/]*\/upload\//, '../upload/');
    const imgTag = `<img src="${imgUrl}" alt="QSIS.CMS.img">`;

    // 1. Quill 编辑器（按规则：window.quill_编辑器ID）
    if (window['quill_' + targetEditorId]) {
        const quill = window['quill_' + targetEditorId];
        const range = quill.getSelection();
        const index = range ? range.index : quill.getLength();
        quill.insertEmbed(index, 'image', imgUrl);
    }
    // 2. xheditor
    else if (window.jQuery && $('#' + targetEditorId).data('xheditor')) {
        $('#' + targetEditorId).xheditor('insert', imgTag);
    }
    // 3. 普通 textarea
    else {
        const el = document.getElementById(targetEditorId);
        if (el) el.value += imgTag;
    }

    closeImageLibrary();
}

// 上传图片
function ajaxUploadImage(e) {
    e.preventDefault();
    const fileInput = document.getElementById('uploadFile');
    const result = document.getElementById('uploadResult');
    const file = fileInput.files[0];
    
    if (!file) {
        result.innerText = '❌ ' + "<?= L('Please_Select_Image'); ?>";
        return;
    }

    const btn = e.target;
    btn.disabled = true;
    btn.innerText = "<?= L('Uploading'); ?>";
    result.innerText = '';

    const formData = new FormData();
    formData.append('filedata', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'supldaa.php', true);
    xhr.onload = function () {
        btn.disabled = false;
        btn.innerText = "<?= L('Upload_Image'); ?>";
        try {
            const res = JSON.parse(this.responseText);
            if (res.err) {
                result.innerText = '❌ ' + res.err;
                return;
            }
            const url = res.msg.url;
            const name = res.msg.localname;
            result.innerText = '✅ ' + "<?= L('Upload_Success'); ?>";

            const html = `
            <div class="lib-img-item" style="position:relative; cursor:pointer; border:2px solid transparent; border-radius:4px; overflow:hidden;">
                <button type="button" class="delete-img-btn" onclick="deleteImage('${url}')"
                        style="position:absolute; top:4px; right:4px; width:22px; height:22px; background:rgba(255,0,0,0.7); color:#fff; border:none; border-radius:50%; font-size:12px; cursor:pointer; z-index:10; line-height:20px; padding:0;">×</button>
                <img src="${url}" onclick="selectImage(event, '${url}')" style="width:100%; height:100px; object-fit:cover;">
                <div style="font-size:11px; padding:4px; text-align:center; background:#f9f9f9; white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${name}</div>
            </div>`;

            document.getElementById('imageGrid').insertAdjacentHTML('afterbegin', html);
            fileInput.value = '';
        } catch (e) {
            console.error(e);
            result.innerText = '❌ ' + "<?= L('Upload_Parse_Failed'); ?>";
        }
    };
    xhr.onerror = function () {
        btn.disabled = false;
        btn.innerText = "<?= L('Upload_Image'); ?>";
        result.innerText = '❌ ' + "<?= L('Network_Error_Check_Server'); ?>";
    };
    xhr.send(formData);
}

// 删除图片
function deleteImage(imgUrl) {
    if (!confirm("<?= L('Confirm_Delete_Image_Library'); ?>")) return;
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'deleteImage.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (this.responseText === 'ok') {
            document.querySelectorAll('.lib-img-item').forEach(item => {
                if (item.querySelector('img').src.includes(imgUrl)) item.remove();
            });
        } else {
            alert("<?= L('Delete_Failed_Response'); ?>" + this.responseText);
        }
    };
    xhr.send('path=' + encodeURIComponent(imgUrl));
}

// 点击空白关闭弹窗
document.getElementById('imageLibraryModal').addEventListener('click', function(e) {
    if (e.target === this) closeImageLibrary();
});
</script>