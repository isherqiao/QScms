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
 * @File         set_imagebed.php
 * @CreatedTime  15/11/26 10:30:00
 * @LastEditTime 15/11/26 10:30:00
 * @Description  后台「图床」页签：选择驱动、填各驱动参数、测试上传。配置写入 control/data/imagebed.php。
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require_once __DIR__ . '/../control/imagebed_lib.php';

$ibCfg     = qs_imagebed_config();
$ibDrivers = qs_imagebed_drivers();

$ibVal = function ($group, $key, $default = '') use ($ibCfg) {
    if ($group === '') {
        return isset($ibCfg[$key]) ? (string)$ibCfg[$key] : (string)$default;
    }
    return isset($ibCfg[$group][$key]) ? (string)$ibCfg[$group][$key] : (string)$default;
};
?>
<div class="small_form small_form2">
    <b><?= L('Imagebed_Heading'); ?></b>
    <span class="info" style="margin-left:8px;"><?= L('Imagebed_Heading_Tip'); ?></span>
</div>

<div class="field">
    <div class="label"><?= L('Imagebed_Driver'); ?></div>
    <div class="price">
        <select class="input" name="ib[driver]" id="ib_driver">
            <?php foreach ($ibDrivers as $dk => $dv): ?>
                <option value="<?= htmlspecialchars($dk, ENT_QUOTES, 'UTF-8'); ?>"<?= $ibVal('', 'driver', 'local') === $dk ? ' selected' : ''; ?>>
                    <?= htmlspecialchars($dv['title'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="info"><?= L('Imagebed_Driver_Tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Imagebed_On_Fail'); ?></div>
    <div class="price">
        <select class="input" name="ib[on_fail]">
            <option value="local"<?= $ibVal('', 'on_fail', 'local') === 'local' ? ' selected' : ''; ?>><?= L('Imagebed_On_Fail_Local'); ?></option>
            <option value="error"<?= $ibVal('', 'on_fail', 'local') === 'error' ? ' selected' : ''; ?>><?= L('Imagebed_On_Fail_Error'); ?></option>
        </select>
    </div>
    <div class="info"><?= L('Imagebed_On_Fail_Tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Imagebed_Extra'); ?></div>
    <div class="price">
        <label style="display:block;margin-bottom:6px;">
            <input type="checkbox" name="ib[keep_local]" value="1"<?= $ibVal('', 'keep_local') === '1' ? ' checked' : ''; ?>>
            <?= L('Imagebed_Keep_Local'); ?>
        </label>
        <input class="input" type="text" name="ib[url_prefix]" placeholder="https://cdn.example.com"
               value="<?= htmlspecialchars($ibVal('', 'url_prefix'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="info"><?= L('Imagebed_Url_Prefix_Tip'); ?></div>
</div>

<div class="field">
    <div class="label"><?= L('Imagebed_Path'); ?></div>
    <div class="price">
        <select class="input" name="ib[naming]" style="width:32%;margin-right:6px;">
            <option value="time"<?= $ibVal('', 'naming', 'time') === 'time' ? ' selected' : ''; ?>><?= L('Imagebed_Naming_Time'); ?></option>
            <option value="md5"<?= $ibVal('', 'naming', 'time') === 'md5' ? ' selected' : ''; ?>><?= L('Imagebed_Naming_Md5'); ?></option>
            <option value="orig"<?= $ibVal('', 'naming', 'time') === 'orig' ? ' selected' : ''; ?>><?= L('Imagebed_Naming_Orig'); ?></option>
        </select>
        <input class="input" type="text" name="ib[path]" style="width:62%;"
               value="<?= htmlspecialchars($ibVal('', 'path', 'images/{Y}/{m}/'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="info"><?= L('Imagebed_Path_Tip'); ?></div>
</div>

<div class="clear"></div>

<?php foreach ($ibDrivers as $dk => $dv): ?>
    <?php if ($dk === 'local' || empty($dv['fields'])) { continue; } ?>
    <div class="ib-group" data-driver="<?= htmlspecialchars($dk, ENT_QUOTES, 'UTF-8'); ?>" style="display:none;">
        <div class="small_form small_form2">
            <b><?= htmlspecialchars($dv['title'], ENT_QUOTES, 'UTF-8'); ?></b>
        </div>
        <?php foreach ($dv['fields'] as $fk => $fv):
            $label  = (string)($fv[0] ?? $fk);
            $tip    = (string)($fv[1] ?? '');
            $kind   = (string)($fv[2] ?? 'text');
            $name   = 'ib[' . $dk . '][' . $fk . ']';
            $val    = $ibVal($dk, $fk);
        ?>
        <div class="field">
            <div class="label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="price">
                <?php if ($kind === 'textarea'): ?>
                    <textarea class="input" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" rows="4" autocomplete="off"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php elseif ($kind === 'select' && $fk === 'path_style'): ?>
                    <select class="input" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="1"<?= $val === '1' ? ' selected' : ''; ?>>path style（/bucket/key）</option>
                        <option value="0"<?= $val === '0' ? ' selected' : ''; ?>>virtual host（bucket.host/key）</option>
                    </select>
                <?php elseif ($kind === 'select' && $fk === 'method'): ?>
                    <select class="input" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="POST"<?= $val === 'POST' ? ' selected' : ''; ?>>POST</option>
                        <option value="PUT"<?= $val === 'PUT' ? ' selected' : ''; ?>>PUT</option>
                    </select>
                <?php else: ?>
                    <input class="input" type="text" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                           value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                <?php endif; ?>
            </div>
            <?php if ($tip !== ''): ?>
                <div class="info"><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div class="clear"></div>
    </div>
<?php endforeach; ?>

<div class="field">
    <div class="label"><?= L('Imagebed_Test'); ?></div>
    <div class="price">
        <button type="button" class="button" id="ib_test_btn"><?= L('Imagebed_Test_Btn'); ?></button>
        <span id="ib_test_result" style="margin-left:10px;"></span>
    </div>
    <div class="info"><?= L('Imagebed_Test_Tip'); ?></div>
</div>

<script>
(function () {
    var sel = document.getElementById('ib_driver');
    function syncGroups() {
        var cur = sel.value;
        var groups = document.querySelectorAll('.ib-group');
        for (var i = 0; i < groups.length; i++) {
            groups[i].style.display = (groups[i].getAttribute('data-driver') === cur) ? '' : 'none';
        }
    }
    if (sel) { sel.addEventListener('change', syncGroups); syncGroups(); }

    var btn = document.getElementById('ib_test_btn');
    var out = document.getElementById('ib_test_result');
    if (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            out.style.color = '#666';
            out.textContent = '...';
            var fd = new FormData();
            var fields = document.querySelectorAll('[name^="ib"]');
            for (var i = 0; i < fields.length; i++) {
                var el = fields[i];
                if (el.type === 'checkbox' && !el.checked) { continue; }
                if (el.type === 'button' || el.type === 'submit') { continue; }
                fd.append(el.name, el.value);
            }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'imagebed_test.php', true);
            xhr.onload = function () {
                btn.disabled = false;
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { res = null; }
                if (!res) {
                    out.style.color = '#c00';
                    out.textContent = '返回异常：' + xhr.responseText.slice(0, 120);
                    return;
                }
                if (res.ok) {
                    out.style.color = '#188038';
                    out.textContent = '✅ ' + res.driver + ' → ' + res.url;
                } else {
                    out.style.color = '#c00';
                    out.textContent = '❌ ' + (res.err || '未知错误');
                }
            };
            xhr.onerror = function () {
                btn.disabled = false;
                out.style.color = '#c00';
                out.textContent = '❌ 请求失败';
            };
            xhr.send(fd);
        });
    }
})();
</script>
