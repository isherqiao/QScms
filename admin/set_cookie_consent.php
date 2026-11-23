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
 * @File         set_cookie_consent.php
 * @CreatedTime  15/11/26 03:55:00
 * @LastEditTime 15/11/26 03:55:00
 * @Description  Cookie consent settings panel: switch, languages, policy version and audit records.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_cookie_consent.php —— 后台「Cookie 同意」页签内容

require_once __DIR__ . '/../control/consent_lib.php';

$cc       = qs_consent_config();
$ccLangs  = qs_consent_lang_list();
$ccOn     = (array)$cc['languages'];
$ccStats  = qs_consent_stats();
$ccRows   = qs_consent_read(30);
$ccKeys   = qs_consent_text_keys();

// 文案分组，便于分块展示
$ccGroups = array(
    'Banner' => array('banner_title', 'banner_text'),
    'Buttons' => array('btn_accept', 'btn_reject', 'btn_ignore', 'btn_confirm',
                       'link_manage', 'link_manage_cookie', 'link_privacy'),
    'Modal' => array('modal_title', 'modal_intro'),
    'Categories' => array('cat_storage_name', 'cat_storage_desc',
                          'cat_ads_name', 'cat_ads_desc',
                          'cat_measure_name', 'cat_measure_desc',
                          'cat_geo_name', 'cat_geo_desc',
                          'cat_analytics_name', 'cat_analytics_desc',
                          'cat_social_name', 'cat_social_desc'),
    'Misc' => array('always_on', 'required_hint', 'saved', 'save_failed',
                    'reopen', 'version_label', 'updated_label'),
);

$ccLongFields = array('banner_text', 'modal_intro', 'cat_storage_desc', 'cat_ads_desc',
                      'cat_measure_desc', 'cat_geo_desc', 'cat_analytics_desc', 'cat_social_desc');

$ccActionMap = array(
    'accept_all' => L('CookieConsent_Act_Accept'),
    'reject_all' => L('CookieConsent_Act_Reject'),
    'custom'     => L('CookieConsent_Act_Custom'),
    'withdraw'   => L('CookieConsent_Act_Withdraw'),
);
?>
<b><?= L('CookieConsent_Heading'); ?></b>
<div class="small_form small_form2">
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_Switch'); ?></div>
        <select name="cc[enabled]" class="textbox" style="width:140px;">
            <option value="1" <?= $cc['enabled'] === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
            <option value="" <?= $cc['enabled'] !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
        </select>
        <div class="info"><p><?= L('CookieConsent_Switch_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_Languages'); ?></div>
        <div style="margin-bottom:6px; color:#666;"><?= L('CookieConsent_Languages_Tip'); ?></div>
        <?php foreach ($ccLangs as $code => $label): ?>
            <label style="display:inline-block; margin:0 14px 6px 0;">
                <input type="checkbox" name="cc[languages][]" value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                    <?= in_array($code, $ccOn, true) ? 'checked' : ''; ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                <span style="color:#999;">(<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>)</span>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_DefaultLang'); ?></div>
        <select name="cc[default_lang]" class="textbox" style="width:200px;">
            <?php foreach ($ccOn as $code): ?>
                <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                    <?= $cc['default_lang'] === $code ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($ccLangs[$code] ?? $code, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="info"><p><?= L('CookieConsent_DefaultLang_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_PolicyVersion'); ?></div>
        <input type="text" class="textbox" name="cc[policy_version]" style="width:160px;"
               value="<?= htmlspecialchars($cc['policy_version'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="v1.0">
        <input type="text" class="textbox" name="cc[privacy_url]" style="width:360px;"
               value="<?= htmlspecialchars($cc['privacy_url'], ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="<?= htmlspecialchars(qs_consent_privacy_url(), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="info"><p><?= L('CookieConsent_PolicyVersion_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_Record'); ?></div>
        <select name="cc[record]" class="textbox" style="width:140px;">
            <option value="1" <?= $cc['record'] === '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_On'); ?></option>
            <option value="" <?= $cc['record'] !== '1' ? 'selected' : ''; ?>><?= L('Footer_Privacy_Off'); ?></option>
        </select>
        <input type="text" class="textbox" name="cc[retention_months]" style="width:80px;"
               value="<?= (int)$cc['retention_months']; ?>"> <?= L('CookieConsent_Retention_Unit'); ?>
        <input type="text" class="textbox" name="cc[cookie_days]" style="width:80px;"
               value="<?= (int)$cc['cookie_days']; ?>"> <?= L('CookieConsent_Days_Unit'); ?>
        <div class="info"><p><?= L('CookieConsent_Record_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_Texts'); ?></div>
        <div style="margin-bottom:8px; color:#666;"><?= L('CookieConsent_Texts_Tip'); ?></div>
        <?php foreach ($ccOn as $code): ?>
            <?php $tpl = qs_consent_texts($code); ?>
            <details style="border:1px solid #e3e6ea; border-radius:8px; padding:8px 12px; margin-bottom:10px;">
                <summary style="cursor:pointer; font-weight:600; padding:4px 0;">
                    <?= htmlspecialchars($ccLangs[$code] ?? $code, ENT_QUOTES, 'UTF-8'); ?>
                    <span style="color:#999; font-weight:400;">(<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>)</span>
                </summary>
                <div style="padding-top:8px;">
                    <?php foreach ($ccGroups as $groupName => $groupKeys): ?>
                        <div style="margin:6px 0 12px; color:#888; font-size:12px;">— <?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8'); ?> —</div>
                        <?php foreach ($groupKeys as $key): ?>
                            <?php if (!in_array($key, $ccKeys, true)) { continue; } ?>
                            <div style="margin-bottom:8px;">
                                <div style="color:#999; font-size:12px; margin-bottom:3px;"><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php if (in_array($key, $ccLongFields, true)): ?>
                                    <textarea rows="3" class="textbox" style="width:100%;"
                                        name="cc[texts][<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>][<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"><?= htmlspecialchars((string)($tpl[$key] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <?php else: ?>
                                    <input type="text" class="textbox" style="width:100%;"
                                        name="cc[texts][<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>][<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]"
                                        value="<?= htmlspecialchars((string)($tpl[$key] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
    <div class="clear"></div>
</div>

<b><?= L('CookieConsent_Records'); ?></b>
<div class="small_form small_form2">
    <div class="field">
        <div class="label"><?= L('CookieConsent_Endpoint'); ?></div>
        <input type="text" class="textbox" style="width:100%;"
               name="cc[record_endpoint]"
               value="<?= htmlspecialchars((string)($cc['record_endpoint'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="https://your-site/files/cookie-consent/record.php">
        <div class="info"><p><?= L('CookieConsent_Endpoint_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>
    <div class="field">
        <div class="label"><?= L('CookieConsent_Origins'); ?></div>
        <textarea rows="3" class="textbox" style="width:100%;"
                  name="cc[allowed_origins]"><?= htmlspecialchars((string)($cc['allowed_origins'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
        <div class="info"><p><?= L('CookieConsent_Origins_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>
    <div class="field">
        <div class="label"><?= L('CookieConsent_Stats'); ?></div>
        <div style="line-height:1.9;">
            <?= L('CookieConsent_Total'); ?>: <strong><?= (int)$ccStats['total']; ?></strong>
            <?php foreach ($ccActionMap as $act => $actLabel): ?>
                <?php if (isset($ccStats['actions'][$act])): ?>
                    &nbsp;|&nbsp; <?= htmlspecialchars($actLabel, ENT_QUOTES, 'UTF-8'); ?>: <?= (int)$ccStats['actions'][$act]; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!empty($ccStats['langs'])): ?>
                <br><?= L('CookieConsent_ByLang'); ?>:
                <?php foreach ($ccStats['langs'] as $l => $n): ?>
                    <span style="margin-right:10px;"><?= htmlspecialchars((string)$l, ENT_QUOTES, 'UTF-8'); ?>: <?= (int)$n; ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="info"><p><?= L('CookieConsent_Records_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>

    <div class="field">
        <table class="table" style="width:100%; border-collapse:collapse; font-size:12.5px;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Time'); ?></th>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Action'); ?></th>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Switches'); ?></th>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Lang'); ?></th>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Version'); ?></th>
                    <th style="text-align:left; padding:6px 8px; border-bottom:1px solid #e3e6ea;"><?= L('CookieConsent_Col_Page'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ccRows)): ?>
                <tr><td colspan="6" style="padding:12px 8px; color:#999;"><?= L('CookieConsent_NoRecords'); ?></td></tr>
            <?php else: ?>
                <?php foreach ($ccRows as $row): ?>
                    <tr>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;"><?= htmlspecialchars((string)($row['t'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;"><?= htmlspecialchars($ccActionMap[$row['action'] ?? ''] ?? (string)($row['action'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;">
                            <?php
                            $sw = isset($row['switches']) && is_array($row['switches']) ? $row['switches'] : array();
                            $bits = array();
                            foreach ($sw as $k => $v) {
                                $bits[] = $k . ':' . ($v ? 'on' : 'off');
                            }
                            echo htmlspecialchars(implode(' ', $bits), ENT_QUOTES, 'UTF-8');
                            ?>
                        </td>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;"><?= htmlspecialchars((string)($row['lang'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;"><?= htmlspecialchars((string)($row['version'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;"><?= htmlspecialchars((string)($row['page'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="clear"></div>

    <div class="field">
        <div class="label"><?= L('CookieConsent_Cleanup'); ?></div>
        <button type="submit" name="cc_action" value="cleanup" class="button"
                onclick="return confirm('<?= L('CookieConsent_Cleanup_Confirm'); ?>');">
            <?= L('CookieConsent_Cleanup_Btn'); ?>
        </button>
        <div class="info"><p><?= L('CookieConsent_Cleanup_Tip'); ?></p></div>
    </div>
    <div class="clear"></div>
</div>
