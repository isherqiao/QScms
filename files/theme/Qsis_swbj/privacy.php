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
 * @File         privacy.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the static-site theme "Qsis_swbj": privacy policy static page template.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?><style>
/* ========== 底部横幅：严格按截图还原 ========== */
#cookieConsentWrap {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background: #ffffff;
    border-top: 1px solid #e7e7e7;
    box-shadow: 0 -2px 14px rgba(0, 0, 0, 0.12);
    padding: 14px 18px;
    transform: translateY(100%);
    transition: transform 0.4s ease-out;
}
#cookieConsentWrap.show {
    transform: translateY(0);
}
.cookie-inner {
    max-width: 1080px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 22px;
    align-items: center;
}
/* 左侧文字区 */
.cookie-text-block {
    font-size: 13px;
    color: #444;
    line-height: 1.6;
    text-align: left;
}
.cookie-title {
    font-size: 15px;
    font-weight: 600;
    color: #111;
    margin: 0 0 6px 0;
}
.cookie-text-block p {
    margin: 0;
}
.cookie-text-block a {
    color: #0052cc;
    text-decoration: underline;
}
/* 多语言Tab */
.cookie-lang-tabs {
    display:flex;
    gap:6px;
    margin:8px 0;
}
.cookie-lang-tab {
    font-size:12px;
    padding:3px 8px;
    border:1px solid #ccc;
    background:#fff;
    cursor:pointer;
    border-radius:2px;
}
.cookie-lang-tab.active {
    background:#0078d4;
    color:#fff;
    border-color:#0078d4;
}
/* 右侧三个蓝色按钮，垂直排列 */
.cookie-btn-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 130px;
}
.cookie-btn {
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    padding: 9px 14px;
    background: #0078d4;
    color: #ffffff;
    border-radius: 2px;
    text-align: center;
    white-space: nowrap;
}
.cookie-btn:hover {
    background: #005a9e;
}
.cookie-btn.secondary {
    background:#6c757d;
}
.cookie-btn.secondary:hover {
    background:#5a6268;
}
/* 横幅内部重置链接 */
#resetCookieConsent {
    display: inline-block;
    margin-top: 8px;
    color: #0052cc;
    text-decoration: underline;
    cursor: pointer;
}
/* 移动端适配 */
@media (max-width: 720px) {
    .cookie-inner {
        grid-template-columns: 1fr;
    }
    .cookie-btn-group {
        flex-direction: row;
        flex-wrap: wrap;
    }
}
/* ========== 独立 Cookie 设置模态框 ========== */
#cookieSettingModal {
    position: fixed;
    inset: 0;
    z-index: 100000;
    background: rgba(0, 0, 0, 0.45);
    display: none;
    align-items: center;
    justify-content: center;
}
#cookieSettingModal.open {
    display: flex;
}
.modal-container {
    width: 640px;
    max-width: 92vw;
    max-height: 86vh;
    background: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-head {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-title {
    font-size: 17px;
    font-weight: 600;
    margin: 0;
    color: #111;
}
.modal-close {
    background: transparent;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 28px;
    height: 28px;
}
.modal-body {
    padding: 18px 24px;
    overflow-y: auto;
    flex: 1;
    font-size: 13px;
    color: #444;
    line-height: 1.6;
}
.modal-body p {
    margin: 0 0 14px 0;
}
.modal-lang-tabs {
    display:flex;
    gap:6px;
    margin-bottom:16px;
}
.cookie-category-item {
    border-bottom: 1px solid #eeeeee;
}
.cat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 0;
    cursor: pointer;
}
.cat-name {
    font-size: 14px;
    color: #222;
}
.cat-toggle-switch {
    position: relative;
    width: 40px;
    height: 22px;
}
.cat-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    inset: 0;
    background: #cccccc;
    border-radius: 22px;
    cursor: pointer;
    transition: 0.2s;
}
.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background: #ffffff;
    border-radius: 50%;
    transition: 0.2s;
}
input:checked + .slider {
    background: #0078d4;
}
input:checked + .slider:before {
    transform: translateX(18px);
}
.cat-desc {
    font-size: 12px;
    color: #666;
    line-height: 1.55;
    padding: 0 0 14px 0;
    display: none;
}
.cat-desc.show {
    display: block;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #eeeeee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.modal-footer .cookie-btn {
    min-width: 120px;
}
</style>
<!-- 底部主横幅 -->
<div id="cookieConsentWrap">
    <div class="cookie-inner">
        <div class="cookie-text-block">
            <div class="cookie-title" data-i18n="bannerTitle"><?= $qs_config['site_name']; ?>非常重视重视您的隐私</div>
            <p data-i18n="bannerText">
                本站和第三方供应商使用 Cookie 存储访问唯一 ID、个人信息，用于提供、维护、改进服务与广告。
                如果您同意，本站将个性化展示内容与广告。
                您可以选择“我接受”同意全部用途，或单击
                <a href="#" onclick="openCookieModal();return false;" data-i18n="linkManage">管理首选项</a>
                查看选项。
                您可随时在管理首选项中撤回同意。
                <a href="/privacy-cn.html" data-i18n-href="privacyUrl">隐私政策</a>
            </p>
            <div class="cookie-lang-tabs">
                <button class="cookie-lang-tab active" data-lang="zh">中文</button>
                <button class="cookie-lang-tab" data-lang="en">English</button>
                <button class="cookie-lang-tab" data-lang="ja">日本語</button>
            </div>
            <a href="#" id="resetCookieConsent" onclick="resetCookieSetting();return false;" data-i18n="linkManageCookie">
                管理 Cookie 设置
            </a>
        </div>
        <div class="cookie-btn-group">
            <button class="cookie-btn" id="acceptAllBtn" data-i18n="btnAccept">我接受</button>
            <button class="cookie-btn" id="rejectAllBtn" data-i18n="btnReject">全部拒绝</button>
            <button class="cookie-btn secondary" id="ignoreBannerBtn" data-i18n="btnIgnore">忽略</button>
        </div>
    </div>
</div>
<!-- 独立 Cookie 设置模态框 -->
<div id="cookieSettingModal">
    <div class="modal-container">
        <div class="modal-head">
            <h3 class="modal-title" data-i18n="modalTitle">关于你的隐私</h3>
            <button class="modal-close" onclick="closeCookieModal();">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-lang-tabs">
                <button class="cookie-lang-tab active" data-lang="zh">中文</button>
                <button class="cookie-lang-tab" data-lang="en">English</button>
                <button class="cookie-lang-tab" data-lang="ja">日本語</button>
            </div>
            <p data-i18n="modalIntro">
                我们处理你的数据以提供内容和广告，衡量投放效果，并基于合法利益与合作伙伴共享信息。
            </p>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc1">
                    <span class="cat-name" data-i18n="cat1Name">在设备上存储或访问信息</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_storage" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc1" data-i18n="cat1Desc">
                    存储或访问设备上的信息，例如唯一标识符、Cookie 等。
                </div>
            </div>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc2">
                    <span class="cat-name" data-i18n="cat2Name">个性化广告和内容</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_ad">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc2" data-i18n="cat2Desc">
                    根据您的兴趣、访问记录和设备信息展示个性化广告与内容。
                </div>
            </div>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc3">
                    <span class="cat-name" data-i18n="cat3Name">个性化内容和广告效果衡量</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_measure">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc3" data-i18n="cat3Desc">
                    收集关于内容和广告的访问数据，用于衡量效果并改进服务。
                </div>
            </div>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc4">
                    <span class="cat-name" data-i18n="cat4Name">使用精确的地理位置数据</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_geo">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc4" data-i18n="cat4Desc">
                    经您同意后，使用精确地理位置数据提供相关服务。
                </div>
            </div>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc5">
                    <span class="cat-name" data-i18n="cat5Name">第三方分析</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_analytic">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc5" data-i18n="cat5Desc">
                    使用第三方分析服务了解网站访问情况和用户行为。
                </div>
            </div>
            <div class="cookie-category-item">
                <div class="cat-header" data-target="desc6">
                    <span class="cat-name" data-i18n="cat6Name">社交媒体 Cookie</span>
                    <label class="cat-toggle-switch">
                        <input type="checkbox" id="cat_social">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="cat-desc" id="desc6" data-i18n="cat6Desc">
                    用于社交媒体功能、分享组件和相关登录服务。
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="cookie-btn secondary" id="modalIgnoreBtn" data-i18n="btnIgnore">忽略</button>
            <button class="cookie-btn" id="modalRejectAll" data-i18n="btnReject">全部拒绝</button>
            <button class="cookie-btn" id="modalConfirm" data-i18n="btnConfirm">确认我的选择</button>
        </div>
    </div>
</div>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
const consentKey = "site_cookie_consent";
const ignoreKey = "site_cookie_ignore";
const IGNORE_TTL = 180 * 86400 * 1000; // 忽略有效期：6个月

// =====多语言文本库=====
const i18nTexts = {
    zh:{
        bannerTitle:"<?= $qs_config['site_name']; ?>重视您的隐私",
        bannerText:`本站和第三方供应商使用 Cookie 存储访问唯一 ID、个人信息，用于提供、维护、改进服务与广告。
如果您同意，本站将个性化展示内容与广告。
您可以选择“我接受”同意全部用途，或单击<a href="#" onclick="openCookieModal();return false;">管理首选项</a>查看选项。
您可随时在管理首选项中撤回同意。<a href="/privacy-cn.html">隐私政策</a>`,
        linkManage:"管理首选项",
        privacyUrl:"/privacy-cn.html",
        linkManageCookie:"管理 Cookie 设置",
        btnAccept:"我接受",
        btnReject:"全部拒绝",
        btnIgnore:"忽略",
        modalTitle:"关于你的隐私",
        modalIntro:"我们处理你的数据以提供内容和广告，衡量投放效果，并基于合法利益与合作伙伴共享信息。",
        btnConfirm:"确认我的选择",
        cat1Name:"在设备上存储或访问信息",
        cat1Desc:"存储或访问设备上的信息，例如唯一标识符、Cookie 等。",
        cat2Name:"个性化广告和内容",
        cat2Desc:"根据您的兴趣、访问记录和设备信息展示个性化广告与内容。",
        cat3Name:"个性化内容和广告效果衡量",
        cat3Desc:"收集关于内容和广告的访问数据，用于衡量效果并改进服务。",
        cat4Name:"使用精确的地理位置数据",
        cat4Desc:"经您同意后，使用精确地理位置数据提供相关服务。",
        cat5Name:"第三方分析",
        cat5Desc:"使用第三方分析服务了解网站访问情况和用户行为。",
        cat6Name:"社交媒体 Cookie",
        cat6Desc:"用于社交媒体功能、分享组件和相关登录服务。"
    },
    en:{
        bannerTitle:"<?= $qs_config['site_name']; ?> values your privacy",
        bannerText:`This site and third-party providers use cookies to store unique IDs and personal information to provide, maintain and improve services and advertising.
If you agree, personalized content and ads may be displayed.
You may select "Accept all" to consent to all purposes, or click <a href="#" onclick="openCookieModal();return false;">Manage preferences</a> to review options.
You may withdraw your consent at any time in preferences. <a href="/privacy-en.html">Privacy Policy</a>`,
        linkManage:"Manage preferences",
        privacyUrl:"/privacy-en.html",
        linkManageCookie:"Manage Cookie settings",
        btnAccept:"Accept all",
        btnReject:"Reject all",
        btnIgnore:"Dismiss",
        modalTitle:"About your privacy",
        modalIntro:"We process your data to deliver content and ads, measure campaign performance and share information with partners based on legitimate interests.",
        btnConfirm:"Confirm my choices",
        cat1Name:"Store or access information on your device",
        cat1Desc:"Store or access information on your device such as unique identifiers and cookies.",
        cat2Name:"Personalised ads and content",
        cat2Desc:"Show personalised ads and content based on your interests, visits and device information.",
        cat3Name:"Measurement of personalised content and ad performance",
        cat3Desc:"Collect visit statistics for content and ads to measure performance and improve services.",
        cat4Name:"Use precise geolocation data",
        cat4Desc:"Use precise geolocation data to deliver relevant services upon your consent.",
        cat5Name:"Third-party analytics",
        cat5Desc:"Use third-party analytics to understand website traffic and user behaviour.",
        cat6Name:"Social media cookies",
        cat6Desc:"Used for social-media features, share components and related login services."
    },
    ja:{
        bannerTitle:"<?= $qs_config['site_name']; ?> はプライバシーを重視しています",
        bannerText:`本サイトおよび第三者プロバイダーはCookieを使用し、一意のIDや個人情報を保存し、サービス・広告の提供、維持、改善に利用します。
同意すると、カスタマイズされたコンテンツや広告が表示されます。
「すべて承諾」で全ての目的に同意するか、<a href="#" onclick="openCookieModal();return false;">設定を管理</a>からオプションを確認できます。
設定からいつでも同意を取り消せます。<a href="/privacy-ja.html">プライバシーポリシー</a>`,
        linkManage:"設定を管理",
        privacyUrl:"/privacy-ja.html",
        linkManageCookie:"Cookie設定を管理",
        btnAccept:"すべて承諾",
        btnReject:"すべて拒否",
        btnIgnore:"閉じる",
        modalTitle:"プライバシーについて",
        modalIntro:"コンテンツ・広告配信、効果測定、正当な利益に基づくパートナーとの情報共有のためデータを処理します。",
        btnConfirm:"選択を確認",
        cat1Name:"デバイス上の情報の保存・参照",
        cat1Desc:"一意の識別子、Cookieなどの情報をデバイスに保存または参照します。",
        cat2Name:"カスタマイズ広告とコンテンツ",
        cat2Desc:"興味、閲覧履歴、端末情報に基づきカスタマイズされた広告・コンテンツを表示します。",
        cat3Name:"カスタマイズコンテンツ・広告効果測定",
        cat3Desc:"コンテンツ・広告のアクセス情報を収集し効果測定とサービス改善に活用します。",
        cat4Name:"高精度位置情報の使用",
        cat4Desc:"同意を得た上で高精度位置情報をサービス提供に利用します。",
        cat5Name:"第三者分析",
        cat5Desc:"第三者分析ツールによりサイトアクセスとユーザー行動を把握します。",
        cat6Name:"ソーシャルメディアCookie",
        cat6Desc:"SNS機能、共有ボタン、ログイン関連機能に使用されます。"
    }
};
let currentLang = "zh";
// 切换语言函数
function setLanguage(lang){
    currentLang = lang;
    const texts = i18nTexts[lang];
    document.querySelectorAll('[data-i18n]').forEach(el=>{
        const key = el.getAttribute('data-i18n');
        el.innerHTML = texts[key];
    });
    document.querySelectorAll('[data-i18n-href]').forEach(el=>{
        const key = el.getAttribute('data-i18n-href');
        el.href = texts[key];
    });
    // 更新tab激活状态
    document.querySelectorAll('.cookie-lang-tab').forEach(tab=>{
        tab.classList.toggle('active', tab.dataset.lang === lang);
    });
}

window.applyConsentToUI = function(saved){
    if(!saved) return;
    const catStorage = document.getElementById('cat_storage');
    const catAd = document.getElementById('cat_ad');
    const catMeasure = document.getElementById('cat_measure');
    const catGeo = document.getElementById('cat_geo');
    const catAnalytic = document.getElementById('cat_analytic');
    const catSocial = document.getElementById('cat_social');
    if(!catStorage) return;
    catStorage.checked = true;
    if(saved.adStorage === 'granted'){
        catAd.checked = true;
        catMeasure.checked = true;
        catGeo.checked = true;
        catSocial.checked = true;
    }else{
        catAd.checked = false;
        catMeasure.checked = false;
        catGeo.checked = false;
        catSocial.checked = false;
    }
    catAnalytic.checked = saved.analyticsStorage === 'granted';
}
window.openCookieModal = function(){
    const modal = document.getElementById('cookieSettingModal');
    if(!modal){
        console.error("找不到弹窗DOM #cookieSettingModal");
        return;
    }
    const savedRaw = localStorage.getItem(consentKey);
    if(savedRaw){
        window.applyConsentToUI(JSON.parse(savedRaw));
    }
    modal.classList.add('open');
}
window.closeCookieModal = function(){
    const modal = document.getElementById('cookieSettingModal');
    if(modal){
        modal.classList.remove('open');
    }
}
window.resetCookieSetting = function(){
    window.openCookieModal();
}
window.loadAdScripts = function(adStorageGranted){
    if(!adStorageGranted) return;
    const adScript = document.createElement('script');
    adScript.src = "https://swbj.qsis.cn/ads/ad_1.js";
    document.body.appendChild(adScript);
}
window.setConsent = function(adStorage, analyticsStorage){
    localStorage.removeItem(ignoreKey);
    gtag('consent', 'update', {
        ad_storage: adStorage,
        analytics_storage: analyticsStorage
    });
    localStorage.setItem(consentKey, JSON.stringify({
        adStorage: adStorage,
        analyticsStorage: analyticsStorage,
        expire: Date.now() + 180 * 86400 * 1000
    }));
    const banner = document.getElementById('cookieConsentWrap');
    if(banner) banner.classList.remove('show');
    window.closeCookieModal();
    window.loadAdScripts(adStorage === 'granted');
}
// 忽略逻辑，写入带时间戳JSON
window.doCookieIgnore = function(){
    localStorage.setItem(ignoreKey, JSON.stringify({
        ignored:true,
        timestamp: Date.now(),
        expire: Date.now() + IGNORE_TTL
    }));
    const banner = document.getElementById('cookieConsentWrap');
    if(banner) banner.classList.remove('show');
    window.closeCookieModal();
}
// 校验忽略标记是否过期
function isIgnoreValid(){
    const raw = localStorage.getItem(ignoreKey);
    if(!raw) return false;
    try{
        const obj = JSON.parse(raw);
        if(obj.expire && Date.now() > obj.expire){
            localStorage.removeItem(ignoreKey);
            return false;
        }
        return true;
    }catch(e){
        localStorage.removeItem(ignoreKey);
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function(){
    const banner = document.getElementById('cookieConsentWrap');
    const modal = document.getElementById('cookieSettingModal');
    const acceptAllBtn = document.getElementById('acceptAllBtn');
    const rejectAllBtn = document.getElementById('rejectAllBtn');
    const ignoreBannerBtn = document.getElementById('ignoreBannerBtn');
    const modalRejectAll = document.getElementById('modalRejectAll');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalIgnoreBtn = document.getElementById('modalIgnoreBtn');

    // 多语言tab事件
    document.querySelectorAll('.cookie-lang-tab').forEach(tab=>{
        tab.addEventListener('click',()=>{
            setLanguage(tab.dataset.lang);
        });
    });

    if(modal){
        modal.addEventListener('click', function(e){
            if(e.target === modal){
                window.closeCookieModal();
            }
        });
    }
    document.querySelectorAll('.cat-header').forEach(function(item){
        item.addEventListener('click', function(ev){
            const targetId = ev.currentTarget.dataset.target;
            const descDom = document.getElementById(targetId);
            if(descDom){
                descDom.classList.toggle('show');
            }
        });
    });

    // 按钮事件绑定
    if(ignoreBannerBtn){
        ignoreBannerBtn.addEventListener('click', function(){
            window.doCookieIgnore();
        });
    }
    if(modalIgnoreBtn){
        modalIgnoreBtn.addEventListener('click', function(){
            window.doCookieIgnore();
        });
    }
    if(acceptAllBtn){
        acceptAllBtn.addEventListener('click', function(){
            const catStorage = document.getElementById('cat_storage');
            const catAd = document.getElementById('cat_ad');
            const catMeasure = document.getElementById('cat_measure');
            const catGeo = document.getElementById('cat_geo');
            const catAnalytic = document.getElementById('cat_analytic');
            const catSocial = document.getElementById('cat_social');
            catStorage.checked = true;
            catAd.checked = true;
            catMeasure.checked = true;
            catGeo.checked = true;
            catAnalytic.checked = true;
            catSocial.checked = true;
            window.setConsent('granted', 'granted');
        });
    }
    if(rejectAllBtn){
        rejectAllBtn.addEventListener('click', function(){
            const catStorage = document.getElementById('cat_storage');
            const catAd = document.getElementById('cat_ad');
            const catMeasure = document.getElementById('cat_measure');
            const catGeo = document.getElementById('cat_geo');
            const catAnalytic = document.getElementById('cat_analytic');
            const catSocial = document.getElementById('cat_social');
            catStorage.checked = true;
            catAd.checked = false;
            catMeasure.checked = false;
            catGeo.checked = false;
            catAnalytic.checked = false;
            catSocial.checked = false;
            window.setConsent('denied', 'denied');
        });
    }
    if(modalRejectAll){
        modalRejectAll.addEventListener('click', function(){
            const catStorage = document.getElementById('cat_storage');
            const catAd = document.getElementById('cat_ad');
            const catMeasure = document.getElementById('cat_measure');
            const catGeo = document.getElementById('cat_geo');
            const catAnalytic = document.getElementById('cat_analytic');
            const catSocial = document.getElementById('cat_social');
            catStorage.checked = true;
            catAd.checked = false;
            catMeasure.checked = false;
            catGeo.checked = false;
            catAnalytic.checked = false;
            catSocial.checked = false;
            window.setConsent('denied', 'denied');
        });
    }
    if(modalConfirm){
        modalConfirm.addEventListener('click', function(){
            const catAd = document.getElementById('cat_ad');
            const catMeasure = document.getElementById('cat_measure');
            const catAnalytic = document.getElementById('cat_analytic');
            const adStorage = (catAd.checked || catMeasure.checked) ? 'granted' : 'denied';
            const analyticsStorage = catAnalytic.checked ? 'granted' : 'denied';
            window.setConsent(adStorage, analyticsStorage);
        });
    }

    // 初始化逻辑
    const savedConsent = localStorage.getItem(consentKey);
    const hasValidIgnore = isIgnoreValid();

    if(!savedConsent && !hasValidIgnore){
        setTimeout(function(){
            if(banner) banner.classList.add('show');
        }, 600);
        gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            wait_for_update: 500
        });
    }else{
        if(savedConsent){
            let obj = JSON.parse(savedConsent);
            gtag('consent', 'default', {
                ad_storage: obj.adStorage,
                analytics_storage: obj.analyticsStorage,
                wait_for_update: 500
            });
            window.applyConsentToUI(obj);
            window.loadAdScripts(obj.adStorage === 'granted');
        }else{
            gtag('consent', 'default', {
                ad_storage: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500
            });
        }
    }
});
</script>
