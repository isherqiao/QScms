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
 * @File         keywords_manage.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Blocked-keyword manager: maintains categories and words and splits a large library into chunk files.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 require_once __DIR__.'/../head.php'; ?>
<?php
require_once __DIR__.'/filter.php';

$cats = qs_blocked_keywords();
$notice = '';
$error  = '';

$act = $_POST['act'] ?? ($_GET['act'] ?? '');

/* ---------- 1. 分类启用/停用开关 ---------- */
if ($act === 'toggle' && isset($_GET['cat'])) {
    $ck = (string)$_GET['cat'];
    if (isset($cats[$ck])) {
        $cats[$ck]['enabled'] = $cats[$ck]['enabled'] ? 0 : 1;
        qs_kw_save($cats);
        $notice = ($cats[$ck]['enabled'] ? L('Enable') : L('Disable')) . '：' . $cats[$ck]['name'];
    }
    header('Location: keywords_manage.php?cat=' . urlencode($ck));
    exit;
}

/* ---------- 2. 批量添加关键词 ---------- */
if ($act === 'add' && isset($_POST['cat'], $_POST['new_keywords'])) {
    $ck = (string)$_POST['cat'];
    $newWords = qs_kw_split_words((string)$_POST['new_keywords']);
    if (isset($cats[$ck]) && $newWords) {
        $added = [];
        foreach ($newWords as $w) {
            if (!in_array($w, $cats[$ck]['words'], true) && !in_array($w, $added, true)) {
                $cats[$ck]['words'][] = $w;
                $added[] = $w;
            }
        }
        if ($added) {
            qs_kw_save($cats);
            $notice = sprintf(L('Keyword_Added'), count($added));
        } else {
            $error = L('Keyword_Already_Exists');
        }
    } else {
        $error = L('Keyword_Already_Exists');
    }
    header('Location: keywords_manage.php?cat=' . urlencode($ck));
    exit;
}

/* ---------- 3. 编辑关键词（改词 / 改分类） ---------- */
if ($act === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldCat  = (string)($_POST['old_cat'] ?? '');
    $oldWord = trim((string)($_POST['old_word'] ?? ''));
    $newCat  = (string)($_POST['new_cat'] ?? '');
    $newWord = trim((string)($_POST['new_word'] ?? ''));
    if (isset($cats[$oldCat], $cats[$newCat]) && $oldWord !== '' && $newWord !== '') {
        $cats[$oldCat]['words'] = array_values(array_filter($cats[$oldCat]['words'], fn($w) => $w !== $oldWord));
        if (!in_array($newWord, $cats[$newCat]['words'], true)) {
            $cats[$newCat]['words'][] = $newWord;
        }
        qs_kw_save($cats);
        $notice = L('Keyword_Updated');
    } else {
        $error = L('Keyword_Update_Failed');
    }
    header('Location: keywords_manage.php?cat=' . urlencode($newCat));
    exit;
}

/* ---------- 4. 删除单个关键词 ---------- */
if ($act === 'del' && isset($_GET['cat'], $_GET['word'])) {
    $ck = (string)$_GET['cat'];
    $w  = (string)$_GET['word'];
    if (isset($cats[$ck])) {
        $cats[$ck]['words'] = array_values(array_filter($cats[$ck]['words'], fn($x) => $x !== $w));
        qs_kw_save($cats);
        $notice = sprintf(L('Keyword_Deleted'), htmlspecialchars($w));
    }
    header('Location: keywords_manage.php?cat=' . urlencode($ck));
    exit;
}

/* ---------- 5. 导入 txt / csv / xlsx ---------- */
if ($act === 'import' && isset($_POST['cat']) && isset($_FILES['kwfile'])) {
    $ck = (string)$_POST['cat'];
    $f  = $_FILES['kwfile'];
    if ($f['error'] === UPLOAD_ERR_OK && is_uploaded_file($f['tmp_name'])) {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $tmpImport = rtrim(sys_get_temp_dir(), '/\\') . '/qskw_import_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (move_uploaded_file($f['tmp_name'], $tmpImport)) {
            $imported = [];
            if ($ext === 'txt')      { $imported = qs_kw_parse_txt((string)file_get_contents($tmpImport)); }
            elseif ($ext === 'csv')  { $imported = qs_kw_parse_csv($tmpImport); }
            elseif ($ext === 'xlsx') { $imported = qs_kw_parse_excel($tmpImport); }
            @unlink($tmpImport);
            if (isset($cats[$ck]) && $imported) {
                $added = [];
                foreach ($imported as $w) {
                    if (!in_array($w, $cats[$ck]['words'], true) && !in_array($w, $added, true)) {
                        $cats[$ck]['words'][] = $w;
                        $added[] = $w;
                    }
                }
                if ($added) {
                    qs_kw_save($cats);
                    $notice = sprintf(L('Import_Ok'), count($added));
                } else {
                    $error = L('Keyword_Already_Exists');
                }
            } else {
                $error = L('Import_Failed');
            }
        } else {
            $error = L('Import_Failed');
        }
    } else {
        $error = L('Import_Failed');
    }
    header('Location: keywords_manage.php?cat=' . urlencode($ck));
    exit;
}

/* ---------- 当前分类 / 分页（每 100 词一页） ---------- */
$curCat = (string)($_GET['cat'] ?? '');
if ($curCat === '' || !isset($cats[$curCat])) {
    $curCat = array_key_first($cats) ?: '';
}
$curWords = ($curCat !== '' && isset($cats[$curCat]['words'])) ? $cats[$curCat]['words'] : [];
$per = 100;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = count($curWords);
$pages = max(1, (int)ceil($total / $per));
if ($page > $pages) $page = $pages;
$start = ($page - 1) * $per;
$pageWords = array_slice($curWords, $start, $per);

/* 编辑预填 */
$editCat  = (string)($_GET['edit_cat'] ?? '');
$editWord = (string)($_GET['edit_word'] ?? '');
?>

<div class="admin_page_name">
  <a href="../set.php"><?= L('system_settings'); ?></a>  <a href="keywords_manage.php"><?= L('Keywords_Management'); ?></a>
</div>

<div class="setting_block">
  <h4><?= L('Blocked_Keywords'); ?></h4>
  <p class="menu-tip"><?= L('Category_Toggle_Notice'); ?></p>

  <?php if ($notice !== ''): ?><p style="color:#28a745;"><?= $notice ?></p><?php endif; ?>
  <?php if ($error  !== ''): ?><p style="color:#dc3545;"><?= $error ?></p><?php endif; ?>

  <!-- 分类启用开关 + 词数 -->
  <table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
    <tr>
      <th style="text-align:left;padding:6px;border-bottom:1px solid #ccc;"><?= L('Keywords_Category'); ?></th>
      <th style="text-align:center;padding:6px;border-bottom:1px solid #ccc;"><?= L('Enable'); ?>/<?= L('Disable'); ?></th>
      <th style="text-align:center;padding:6px;border-bottom:1px solid #ccc;"><?= L('Total'); ?></th>
    </tr>
    <?php foreach ($cats as $ck => $cat): ?>
    <tr>
      <td style="padding:6px;border-bottom:1px solid #eee;">
        <a href="keywords_manage.php?cat=<?= urlencode($ck) ?>" style="text-decoration:none;font-weight:<?= $ck === $curCat ? 'bold' : 'normal'; ?>;">
          <?= htmlspecialchars($cat['name']); ?>
        </a>
        <?php if ($ck === $curCat): ?> <span style="color:#999;">&#8592;</span><?php endif; ?>
      </td>
      <td style="text-align:center;padding:6px;border-bottom:1px solid #eee;">
        <a href="keywords_manage.php?act=toggle&cat=<?= urlencode($ck) ?>"
           onclick="return confirm('<?= L('Confirm_Toggle_Category'); ?>')"
           style="color:<?= $cat['enabled'] ? '#28a745' : '#dc3545'; ?>;text-decoration:none;">
          <?= $cat['enabled'] ? '&#10004; ' . L('Enable') : '&#10008; ' . L('Disable'); ?>
        </a>
      </td>
      <td style="text-align:center;padding:6px;border-bottom:1px solid #eee;"><?= count($cat['words']); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <!-- 当前分类词列表 + 分页 -->
  <?php if ($curCat !== ''): ?>
  <div style="margin:10px 0;">
    <strong><?= htmlspecialchars($cats[$curCat]['name']); ?>（<?= sprintf(L('Keywords_In_Category'), $total); ?>）</strong>
    <?php if (empty($cats[$curCat]['enabled'])): ?>
      <span style="color:#dc3545;margin-left:10px;"><?= L('Category_Disabled_Notice'); ?></span>
    <?php endif; ?>
  </div>

  <?php if (empty($pageWords)): ?>
    <p><?= L('No_Blocked_Keywords'); ?></p>
  <?php else: ?>
    <div class="small_form">
      <?php foreach ($pageWords as $w): ?>
      <div class="field">
        <span style="display:inline-block;min-width:220px;"><?= htmlspecialchars($w); ?></span>
        <a href="keywords_manage.php?edit_cat=<?= urlencode($curCat) ?>&edit_word=<?= urlencode($w) ?>&cat=<?= urlencode($curCat) ?>"
           style="margin-right:10px;"><?= L('Edit'); ?></a>
        <a href="keywords_manage.php?act=del&cat=<?= urlencode($curCat) ?>&word=<?= urlencode($w) ?>"
           onclick="return confirm('<?= L('Confirm_Delete'); ?>')" style="color:#dc3545;"><?= L('Delete'); ?></a>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:10px;">
      <?= L('Total'); ?> <?= $total; ?> &#183; <?= L('Page'); ?> <?= $page; ?>/<?= $pages; ?>
      <?php if ($page > 1): ?><a href="keywords_manage.php?cat=<?= urlencode($curCat) ?>&page=<?= $page - 1 ?>">&#171;</a><?php endif; ?>
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i === $page): ?><strong>[<?= $i ?>]</strong><?php else: ?><a href="keywords_manage.php?cat=<?= urlencode($curCat) ?>&page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="keywords_manage.php?cat=<?= urlencode($curCat) ?>&page=<?= $page + 1 ?>">&#187;</a><?php endif; ?>
    </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- 编辑单个关键词 -->
  <?php if ($editCat !== '' && $editWord !== '' && isset($cats[$editCat]) && in_array($editWord, $cats[$editCat]['words'], true)): ?>
  <div style="margin-top:18px;padding:12px;border:1px solid #ccc;border-radius:4px;">
    <h4 style="margin:0 0 8px;"><?= L('Edit_Keyword'); ?></h4>
    <form method="post" action="keywords_manage.php">
      <input type="hidden" name="act" value="edit">
      <input type="hidden" name="old_cat" value="<?= htmlspecialchars($editCat) ?>">
      <input type="hidden" name="old_word" value="<?= htmlspecialchars($editWord) ?>">
      <label><?= L('New_Keyword'); ?>:</label>
      <input type="text" name="new_word" value="<?= htmlspecialchars($editWord) ?>" style="padding:4px;min-width:200px;">
      <label style="margin-left:10px;"><?= L('Keywords_Category'); ?>:</label>
      <select name="new_cat" style="padding:4px;">
        <?php foreach ($cats as $ck => $cat): ?>
        <option value="<?= htmlspecialchars($ck) ?>" <?= $ck === $editCat ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>
    </form>
  </div>
  <?php endif; ?>

  <!-- 批量添加 -->
  <div style="margin-top:18px;padding:12px;border:1px solid #ccc;border-radius:4px;">
    <h4 style="margin:0 0 8px;"><?= L('Add_Blocked_Keywords'); ?></h4>
    <form method="post" action="keywords_manage.php">
      <input type="hidden" name="act" value="add">
      <label><?= L('Keywords_Category'); ?>:</label>
      <select name="cat" style="padding:4px;">
        <?php foreach ($cats as $ck => $cat): ?>
        <option value="<?= htmlspecialchars($ck) ?>" <?= $ck === $curCat ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <div style="margin:8px 0;">
        <textarea name="new_keywords" rows="3" style="width:100%;box-sizing:border-box;padding:6px;" placeholder="<?= L('Keyword_Batch_Helper'); ?>"></textarea>
      </div>
      <input type="submit" name="save" value="<?= L('Save'); ?>" style="padding:6px 20px;"/>
    </form>
  </div>

  <!-- 导入 txt / csv / xlsx -->
  <div style="margin-top:18px;padding:12px;border:1px solid #ccc;border-radius:4px;">
    <h4 style="margin:0 0 8px;"><?= L('Import_Keywords'); ?></h4>
    <form method="post" action="keywords_manage.php" enctype="multipart/form-data">
      <input type="hidden" name="act" value="import">
      <label><?= L('Keywords_Category'); ?>:</label>
      <select name="cat" style="padding:4px;">
        <?php foreach ($cats as $ck => $cat): ?>
        <option value="<?= htmlspecialchars($ck) ?>" <?= $ck === $curCat ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <span style="margin-left:10px;color:#888;"><?= L('Only_Import_Format'); ?></span>
      <div style="margin:8px 0;">
        <input type="file" name="kwfile" accept=".txt,.csv,.xlsx">
      </div>
      <input type="submit" name="save" value="<?= L('Upload'); ?>" style="padding:6px 20px;"/>
    </form>
  </div>

</div>
