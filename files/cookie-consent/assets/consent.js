/*!
 * QScms Cookie Consent - behavior
 * Independent of any theme. Config injected by files/cookie-consent/consent.php
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
(function () {
    'use strict';

    var CFG = window.QSConsentConfig || {};
    if (!CFG.languages || !Object.keys(CFG.languages).length) {
        return;
    }

    var LS_CHOICE = 'qs_consent_choice';
    var LS_LANG = 'qs_consent_lang';
    var LS_ID = 'qs_consent_id';
    var COOKIE_NAME = CFG.cookieName || 'qs_consent';
    var CATEGORIES = [
        { key: 'storage',   nameKey: 'cat_storage_name',   descKey: 'cat_storage_desc',   locked: true  },
        { key: 'ads',       nameKey: 'cat_ads_name',       descKey: 'cat_ads_desc',       locked: false },
        { key: 'measure',   nameKey: 'cat_measure_name',   descKey: 'cat_measure_desc',   locked: false },
        { key: 'geo',       nameKey: 'cat_geo_name',       descKey: 'cat_geo_desc',       locked: false },
        { key: 'analytics', nameKey: 'cat_analytics_name', descKey: 'cat_analytics_desc', locked: false },
        { key: 'social',    nameKey: 'cat_social_name',    descKey: 'cat_social_desc',    locked: false }
    ];

    var root = document.getElementById('qsConsentRoot');
    var banner = document.getElementById('qsConsentBanner');
    var mask = document.getElementById('qsConsentMask');
    var langsBox = document.getElementById('qsConsentLangs');
    var bannerLangsBox = document.getElementById('qsConsentBannerLangs');
    var catsBox = document.getElementById('qsConsentCats');
    if (!root || !banner || !mask || !langsBox || !catsBox) {
        return;
    }

    var currentLang = pickLang();
    var state = { storage: true, ads: false, measure: false, geo: false, analytics: false, social: false };

    /* ---------------- helpers ---------------- */

    function pickLang() {
        var saved = '';
        try { saved = localStorage.getItem(LS_LANG) || ''; } catch (e) {}
        if (saved && CFG.languages[saved]) { return saved; }

        // 站点后台设定的默认语言优先：中文站不该因为浏览器语言是英文就弹英文
        if (CFG.defaultLang && CFG.languages[CFG.defaultLang]) { return CFG.defaultLang; }

        var cands = [];
        if (navigator.languages && navigator.languages.length) {
            cands = cands.concat(navigator.languages);
        }
        if (navigator.language) { cands.push(navigator.language); }

        for (var i = 0; i < cands.length; i++) {
            var tag = String(cands[i] || '').toLowerCase();
            if (!tag) { continue; }
            var exact = matchLang(tag);
            if (exact) { return exact; }
            var short = tag.split('-')[0];
            var loose = matchLang(short);
            if (loose) { return loose; }
        }
        return Object.keys(CFG.languages)[0];
    }

    function matchLang(tag) {
        var keys = Object.keys(CFG.languages);
        for (var i = 0; i < keys.length; i++) {
            if (keys[i].toLowerCase() === tag) { return keys[i]; }
        }
        return '';
    }

    function text(key) {
        var pack = CFG.languages[currentLang] || {};
        if (typeof pack[key] === 'string' && pack[key] !== '') { return pack[key]; }
        var fallback = CFG.languages[CFG.defaultLang] || {};
        return typeof fallback[key] === 'string' ? fallback[key] : '';
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function link(href, label) {
        return '<a href="' + esc(href) + '">' + esc(label) + '</a>';
    }

    function fillTexts() {
        var siteName = CFG.siteName || '';
        var bannerTitle = text('banner_title').replace('%s', siteName);
        var bannerTextRaw = text('banner_text')
            .replace('{manage}', link('#manage', text('link_manage')))
            .replace('{privacy}', CFG.privacyUrl ? link(CFG.privacyUrl, text('link_privacy')) : esc(text('link_privacy')));

        root.querySelectorAll('[data-qsc]').forEach(function (el) {
            var key = el.getAttribute('data-qsc');
            if (key === 'banner_title') {
                el.textContent = bannerTitle;
                return;
            }
            if (key === 'banner_text') {
                el.innerHTML = bannerTextRaw;
                return;
            }
            el.textContent = text(key);
        });

        bindManageLinks();

        var boxes = [langsBox];
        if (bannerLangsBox) { boxes.push(bannerLangsBox); }
        boxes.forEach(function (box) {
            Array.prototype.forEach.call(box.querySelectorAll('.qs-consent-lang'), function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-lang') === currentLang);
            });
        });

        renderCats();
    }

    function bindManageLinks() {
        banner.querySelectorAll('a[href="#manage"]').forEach(function (a) {
            a.addEventListener('click', function (ev) {
                ev.preventDefault();
                openModal();
            });
        });
    }

    function renderCats() {
        catsBox.innerHTML = '';
        CATEGORIES.forEach(function (cat) {
            var row = document.createElement('div');
            row.className = 'qs-consent-cat';

            var wrap = document.createElement('div');
            wrap.className = 'qs-consent-cat-text';

            var name = document.createElement('span');
            name.className = 'qs-consent-cat-name';
            name.textContent = text(cat.nameKey);
            wrap.appendChild(name);

            var desc = document.createElement('span');
            desc.className = 'qs-consent-cat-desc';
            desc.textContent = text(cat.descKey);
            wrap.appendChild(desc);

            var sw = document.createElement('button');
            sw.type = 'button';
            sw.className = 'qs-consent-switch';
            sw.setAttribute('role', 'switch');
            sw.setAttribute('data-cat', cat.key);
            sw.setAttribute('aria-checked', state[cat.key] ? 'true' : 'false');
            if (cat.locked) {
                sw.disabled = true;
                sw.setAttribute('aria-label', text('required_hint'));
            } else {
                sw.addEventListener('click', function () {
                    state[cat.key] = !state[cat.key];
                    sw.setAttribute('aria-checked', state[cat.key] ? 'true' : 'false');
                });
            }

            row.appendChild(wrap);
            row.appendChild(sw);
            catsBox.appendChild(row);
        });
    }

    function renderLangs() {
        var boxes = [langsBox];
        if (bannerLangsBox) { boxes.push(bannerLangsBox); }

        boxes.forEach(function (box) { box.innerHTML = ''; });

        var keys = Object.keys(CFG.languages);
        if (keys.length < 2) { return; }

        keys.forEach(function (code) {
            boxes.forEach(function (box) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'qs-consent-lang';
                btn.setAttribute('data-lang', code);
                btn.textContent = CFG.languages[code].name || code;
                btn.addEventListener('click', function () {
                    currentLang = code;
                    try { localStorage.setItem(LS_LANG, code); } catch (e) {}
                    fillTexts();
                });
                box.appendChild(btn);
            });
        });
    }

    function readCookie(name) {
        var parts = ('; ' + document.cookie).split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift() || '';
        }
        return '';
    }

    function writeCookie(name, value, days) {
        var d = Number(days) > 0 ? Number(days) : 365;
        var exp = new Date(Date.now() + d * 86400000).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value)
            + '; Expires=' + exp + '; Path=/; SameSite=Lax';
    }

    // 跨域部署时浏览器不肯给记录站点种 cookie，标识改由客户端自己保存并随请求带走
    function clientId() {
        var id = readCookie(COOKIE_NAME) || '';
        if (!id) {
            try { id = localStorage.getItem(LS_ID) || ''; } catch (e) {}
        }
        id = String(id || '').replace(/[^A-Za-z0-9\-]/g, '');
        if (id.length < 16) {
            id = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        }
        writeCookie(COOKIE_NAME, id, CFG.cookieDays);
        try { localStorage.setItem(LS_ID, id); } catch (e) {}
        return id;
    }

    function saved() {        try {
            var raw = localStorage.getItem(LS_CHOICE);
            if (!raw) { return null; }
            var obj = JSON.parse(raw);
            return obj && obj.switches ? obj : null;
        } catch (e) { return null; }
    }

    function applyStateFrom(obj) {
        if (!obj || !obj.switches) { return; }
        var s = obj.switches;
        CATEGORIES.forEach(function (cat) {
            if (cat.locked) { return; }
            state[cat.key] = !!s[cat.key];
        });
        state.storage = true;
    }

    function pushGtag() {
        if (typeof window.gtag !== 'function') { return; }
        window.gtag('consent', 'update', {
            ad_storage: state.ads ? 'granted' : 'denied',
            analytics_storage: state.analytics ? 'granted' : 'denied',
            ad_personalization: state.ads ? 'granted' : 'denied',
            ad_user_data: state.ads ? 'granted' : 'denied',
            personalization_storage: state.measure ? 'granted' : 'denied',
            functionality_storage: state.social ? 'granted' : 'denied'
        });
    }

    function submit(action) {
        var payload = {
            action: action,
            id: clientId(),
            lang: currentLang,
            page: location.pathname + location.search,
            switches: {
                storage: true,
                ads: !!state.ads,
                measure: !!state.measure,
                geo: !!state.geo,
                analytics: !!state.analytics,
                social: !!state.social
            },
            policy_version: CFG.policyVersion || '',
            ts: Math.floor(Date.now() / 1000)
        };

        var store = function () {
            try {
                localStorage.setItem(LS_CHOICE, JSON.stringify({
                    switches: payload.switches,
                    action: action,
                    version: CFG.policyVersion || '',
                    at: Date.now(),
                    exp: Date.now() + (Number(CFG.cookieDays) || 365) * 86400000
                }));
            } catch (e) {}
            writeCookie(LS_CHOICE, 'v1', CFG.cookieDays);
            pushGtag();
            closeBanner();
            closeModal();
        };

        if (!CFG.record || !CFG.recordUrl) {
            store();
            return;
        }

        fetch(CFG.recordUrl, {
            method: 'POST',
            mode: 'cors',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (r) {
            return r.json().catch(function () { return { ok: 0 }; });
        }).then(function (res) {
            store();
            if (res && res.id) {
                // 让后续可反查：把服务端分配的标识放到非 HttpOnly cookie 里
                writeCookie(COOKIE_NAME, res.id, CFG.cookieDays);
            }
        }).catch(function () {
            // 网络失败也保留本地选择，避免反复骚扰用户
            store();
        });
    }

    /* ---------------- UI ---------------- */

    var hideTimers = {};

    function syncRoot() {
        root.hidden = banner.hidden && mask.hidden;
    }

    // 显示并触发入场过渡（先挂 hidden=false，再补 is-open，保证动画从起点开始）
    function showEl(el) {
        if (hideTimers[el.id]) {
            clearTimeout(hideTimers[el.id]);
            hideTimers[el.id] = null;
        }
        root.hidden = false;
        el.hidden = false;
        el.classList.remove('is-open');
        void el.offsetWidth; // 强制重排，避免过渡被合并
        el.classList.add('is-open');
    }

    // 退出过渡结束后再隐藏，避免闪断
    function hideEl(el) {
        if (hideTimers[el.id]) {
            clearTimeout(hideTimers[el.id]);
        }
        el.classList.remove('is-open');
        hideTimers[el.id] = setTimeout(function () {
            el.hidden = true;
            hideTimers[el.id] = null;
            syncRoot();
        }, 430);
    }

    function openBanner() {
        showEl(banner);
        if (!mask.hidden) { hideEl(mask); }
    }

    function closeBanner() {
        hideEl(banner);
    }

    function openModal() {
        // 防重入：内联 onclick 与事件委托可能同时触发，300ms 内只执行一次
        if (openModal._t && Date.now() - openModal._t < 300) { return; }
        openModal._t = Date.now();

        var obj = saved();
        if (obj) { applyStateFrom(obj); }
        if (!banner.hidden) { hideEl(banner); }
        showEl(mask);
        renderCats();
    }

    function closeModal() {
        hideEl(mask);
    }

    function acceptAll() {
        state.storage = true;
        state.ads = true;
        state.measure = true;
        state.geo = true;
        state.analytics = true;
        state.social = true;
        submit('accept_all');
    }

    function rejectAll() {
        state.storage = true;
        state.ads = false;
        state.measure = false;
        state.geo = false;
        state.analytics = false;
        state.social = false;
        submit('reject_all');
    }

    function confirmChoice() {
        submit('custom');
    }

    root.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-qsc-act]');
        if (!el) { return; }
        var act = el.getAttribute('data-qsc-act');
        if (act === 'manage') { ev.preventDefault(); openModal(); return; }
        if (act === 'close') { ev.preventDefault(); closeModal(); return; }
        if (act === 'ignore') {
            ev.preventDefault();
            // 忽略：本次不记录，仅当天不再打扰
            var d = new Date();
            d.setTime(d.getTime() + 86400000);
            document.cookie = 'qs_consent_ignore=1; Expires=' + d.toUTCString() + '; Path=/; SameSite=Lax';
            closeBanner();
            closeModal();
            return;
        }
        if (act === 'acceptAll') { ev.preventDefault(); acceptAll(); return; }
        if (act === 'rejectAll') { ev.preventDefault(); rejectAll(); return; }
        if (act === 'confirm') { ev.preventDefault(); confirmChoice(); return; }
    });

    mask.addEventListener('click', function (ev) {
        if (ev.target === mask) { closeModal(); }
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && !mask.hidden) { closeModal(); }
    });

    // 供页脚“重新设置 Cookie 选项”调用
    window.qsConsentOpen = openModal;

    // 捕获阶段监听：即使主题脚本在冒泡阶段 stopPropagation 也拦不住
    document.addEventListener('click', function (ev) {
        var t = ev.target && ev.target.closest ? ev.target.closest('[data-qs-consent-open]') : null;
        if (!t) { return; }
        ev.preventDefault();
        openModal();
    }, true);

    // 再给已存在的入口显式绑定一次，兜住不支持 closest 的旧内核
    (function bindOpenTriggers() {
        var nodes = document.querySelectorAll('[data-qs-consent-open]');
        Array.prototype.forEach.call(nodes, function (el) {
            if (el.__qscOpenBound) { return; }
            el.__qscOpenBound = true;
            el.addEventListener('click', function (ev) {
                ev.preventDefault();
                openModal();
            });
        });
    })();

    /* ---------------- boot ---------------- */

    renderLangs();
    fillTexts();

    var existing = saved();
    var ignoredToday = readCookie('qs_consent_ignore') === '1';

    if (existing) {
        applyStateFrom(existing);
        pushGtag();
        root.hidden = true;
    } else if (ignoredToday) {
        root.hidden = true;
    } else {
        openBanner();
    }
})();
