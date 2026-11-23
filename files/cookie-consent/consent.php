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
 * @File         consent.php
 * @CreatedTime  15/11/26 03:35:00
 * @LastEditTime 15/11/26 03:35:00
 * @Description  Cookie consent front-end component renderer, independent of any theme.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// Cookie 同意组件：对外渲染入口
// 与主题无关。动态站由 index.php 注入，静态站由 SSG 调用同一函数。

require_once __DIR__ . '/../../control/consent_lib.php';

/**
 * 组件静态资源根地址
 */
function qs_consent_asset_base()
{
    global $qs_config;

    // 只用站点内相对路径：任何域名/IP 访问都由浏览器按当前页面地址解析，
    // 避免把配置里的 site_link（如 127.0.0.1）发给其它设备导致 404
    $dir = isset($qs_config['site_directory']) ? (string)$qs_config['site_directory'] : '/';
    $dir = '/' . trim($dir, '/');
    $dir = $dir === '/' ? '/' : $dir . '/';

    return $dir . 'files/cookie-consent/assets/';
}

/**
 * 组件数据接口地址（记录端点）
 */
function qs_consent_record_url()
{
    global $qs_config;

    // 后台配置了外部记录端点就优先用它（纯静态站要指向有 PHP 的站点）
    $cfg = qs_consent_config();
    $custom = trim((string)($cfg['record_endpoint'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }

    // 静态站：没配外部端点就没有可用的记录地址，不要指向站内并不存在的 record.php
    if (defined('QS_CONSENT_STATIC') && QS_CONSENT_STATIC) {
        return '';
    }

    // 同 asset_base：用相对路径，换域名/IP 访问也不会指到 127.0.0.1
    $dir = isset($qs_config['site_directory']) ? (string)$qs_config['site_directory'] : '/';
    $dir = '/' . trim($dir, '/');
    $dir = $dir === '/' ? '/' : $dir . '/';

    return $dir . 'files/cookie-consent/record.php';
}

/**
 * 渲染组件 HTML（不含 html/head/body 标签）
 *
 * @return string
 */
function qs_consent_render()
{
    if (!qs_consent_enabled()) {
        return '';
    }

    $payload = qs_consent_payload();
    if (empty($payload['languages'])) {
        return '';
    }

    $e   = static function ($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    };
    $cfg = qs_consent_config();
    $assetBase = qs_consent_asset_base();

    $payload['recordUrl'] = qs_consent_record_url();
    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // 资源版本：按文件修改时间带上查询串，改样式后浏览器不会继续用旧缓存
    $cssVer = @filemtime(__DIR__ . '/assets/consent.css') ?: 0;
    $jsVer  = @filemtime(__DIR__ . '/assets/consent.js') ?: 0;

    ob_start();
    ?>
<link rel="stylesheet" href="<?= $e($assetBase) ?>consent.css?v=<?= (int)$cssVer ?>">
<div class="qs-consent" id="qsConsentRoot" hidden>
  <div class="qs-consent-banner" id="qsConsentBanner" role="dialog" aria-live="polite">
    <div class="qs-consent-banner-langs" id="qsConsentBannerLangs"></div>
    <div class="qs-consent-banner-body">
      <h3 class="qs-consent-banner-title" data-qsc="banner_title"></h3>
      <p class="qs-consent-banner-text" data-qsc="banner_text"></p>
    </div>
    <div class="qs-consent-banner-actions">
      <button type="button" class="qs-consent-btn ghost" data-qsc-act="manage" data-qsc="link_manage"></button>
      <button type="button" class="qs-consent-btn ghost" data-qsc-act="rejectAll" data-qsc="btn_reject"></button>
      <button type="button" class="qs-consent-btn primary" data-qsc-act="acceptAll" data-qsc="btn_accept"></button>
    </div>
  </div>

  <div class="qs-consent-mask" id="qsConsentMask" hidden>
    <div class="qs-consent-modal" role="dialog" aria-modal="true" aria-labelledby="qsConsentModalTitle">
      <div class="qs-consent-modal-head">
        <h3 class="qs-consent-modal-title" id="qsConsentModalTitle" data-qsc="modal_title"></h3>
        <button type="button" class="qs-consent-close" data-qsc-act="close" aria-label="Close">&times;</button>
      </div>
      <div class="qs-consent-langs" id="qsConsentLangs"></div>
      <div class="qs-consent-modal-body">
        <p class="qs-consent-intro" data-qsc="modal_intro"></p>
        <div class="qs-consent-cats" id="qsConsentCats"></div>
      <div class="qs-consent-meta">
        <span data-qsc="version_label"></span>
        <span class="qs-consent-ver"><?= $e($cfg['policy_version']) ?></span>
      </div>
      </div>
      <div class="qs-consent-modal-foot">
        <button type="button" class="qs-consent-btn link" data-qsc-act="ignore" data-qsc="btn_ignore"></button>
        <span class="qs-consent-spacer"></span>
        <button type="button" class="qs-consent-btn ghost" data-qsc-act="rejectAll" data-qsc="btn_reject"></button>
        <button type="button" class="qs-consent-btn primary" data-qsc-act="confirm" data-qsc="btn_confirm"></button>
      </div>
    </div>
  </div>
</div>
<script>
window.QSConsentConfig = <?= $payloadJson ?>;
</script>
<script src="<?= $e($assetBase) ?>consent.js?v=<?= (int)$jsVer ?>" defer></script>
    <?php
    return ob_get_clean();
}

/**
 * 把组件注入到已渲染好的页面：优先插在 </body> 之前
 *
 * @param string $html
 * @return string
 */
function qs_consent_inject($html)
{
    $fragment = qs_consent_render();
    if ($fragment === '') {
        return $html;
    }
    $pos = stripos($html, '</body>');
    if ($pos === false) {
        return $html . $fragment;
    }
    return substr($html, 0, $pos) . $fragment . substr($html, $pos);
}
