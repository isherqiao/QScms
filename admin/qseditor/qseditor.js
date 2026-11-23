/* QS Editor - 轻量自绘富文本编辑器 v1.1
 * 零第三方依赖：内置 Markdown 解析器、图片上传、粘贴/拖拽上传
 * 用法: QSEditor.init(textareaId, options)
 */
(function () {
    'use strict';

    var instances = {};

    function esc(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    /* ====== 内置 Markdown 解析器（零依赖） ====== */
    function mdInline(text) {
        text = esc(text);
        text = text.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
        text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
        text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/__([^_]+)__/g, '<strong>$1</strong>');
        text = text.replace(/~~([^~]+)~~/g, '<del>$1</del>');
        text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
        text = text.replace(/(^|\W)\*([^*\n]+)\*/g, '$1<em>$2</em>');
        return text;
    }

    function mdToHtml(md) {
        var lines = String(md || '').split('\n');
        var html = [];
        var inCode = false, inUl = false, inOl = false, inQuote = false;

        function closeBlocks() {
            if (inUl) { html.push('</ul>'); inUl = false; }
            if (inOl) { html.push('</ol>'); inOl = false; }
            if (inQuote) { html.push('</blockquote>'); inQuote = false; }
        }

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            var t = line.trim();

            if (/^```/.test(t)) {
                if (inCode) { html.push('</code></pre>'); inCode = false; }
                else { closeBlocks(); html.push('<pre><code>'); inCode = true; }
                continue;
            }
            if (inCode) { html.push(esc(line)); continue; }

            if (t === '') { closeBlocks(); continue; }

            var h = t.match(/^(#{1,6})\s+(.*)/);
            if (h) {
                closeBlocks();
                html.push('<h' + h[1].length + '>' + mdInline(h[2]) + '</h' + h[1].length + '>');
                continue;
            }

            if (/^(-{3,}|\*{3,}|_{3,})$/.test(t)) { closeBlocks(); html.push('<hr>'); continue; }

            if (/^>\s?/.test(t)) {
                if (!inQuote) { closeBlocks(); html.push('<blockquote>'); inQuote = true; }
                html.push('<p>' + mdInline(t.replace(/^>\s?/, '')) + '</p>');
                continue;
            }

            if (/^[-*+]\s+/.test(t)) {
                if (inOl) { html.push('</ol>'); inOl = false; }
                if (!inUl) { closeBlocks(); html.push('<ul>'); inUl = true; }
                html.push('<li>' + mdInline(t.replace(/^[-*+]\s+/, '')) + '</li>');
                continue;
            }

            if (/^\d+[.、)]\s+/.test(t)) {
                if (inUl) { html.push('</ul>'); inUl = false; }
                if (!inOl) { closeBlocks(); html.push('<ol>'); inOl = true; }
                html.push('<li>' + mdInline(t.replace(/^\d+[.、)]\s+/, '')) + '</li>');
                continue;
            }

            closeBlocks();
            html.push('<p>' + mdInline(line) + '</p>');
        }
        if (inCode) html.push('</code></pre>');
        closeBlocks();
        return html.join('\n');
    }

    /* ====== 编辑器初始化 ====== */
    function init(textareaId, opts) {
        opts = opts || {};
        var ta = document.getElementById(textareaId);
        if (!ta) return null;

        var wrap = document.createElement('div');
        wrap.className = 'qse-wrap';

        var tb = document.createElement('div');
        tb.className = 'qse-toolbar';

        var buttons = [
            { cmd: 'undo', label: '↺', title: '撤销' },
            { cmd: 'redo', label: '↻', title: '重做' },
            { sep: true },
            { cmd: 'bold', label: '<b>B</b>', title: '加粗' },
            { cmd: 'italic', label: '<i>I</i>', title: '斜体' },
            { cmd: 'underline', label: '<u>U</u>', title: '下划线' },
            { cmd: 'strikeThrough', label: '<s>S</s>', title: '删除线' },
            { sep: true },
            { cmd: 'formatBlock', arg: 'h2', label: 'H2', title: '二级标题' },
            { cmd: 'formatBlock', arg: 'h3', label: 'H3', title: '三级标题' },
            { cmd: 'formatBlock', arg: 'p', label: '¶', title: '正文' },
            { sep: true },
            { cmd: 'insertUnorderedList', label: '• 列表', title: '无序列表' },
            { cmd: 'insertOrderedList', label: '1. 列表', title: '有序列表' },
            { cmd: 'formatBlock', arg: 'blockquote', label: '❝ 引用', title: '引用' },
            { cmd: 'formatBlock', arg: 'pre', label: '<> 代码', title: '代码块' },
            { sep: true },
            { cmd: 'createLink', label: '🔗 链接', title: '插入链接' },
            { cmd: 'unlink', label: '⛓', title: '移除链接' },
            { sep: true },
            { cmd: 'justifyLeft', label: '左', title: '左对齐' },
            { cmd: 'justifyCenter', label: '中', title: '居中对齐' },
            { cmd: 'justifyRight', label: '右', title: '右对齐' },
            { cmd: 'indent2', label: '　　首行缩进', title: '首行缩进两字符' },
            { sep: true },
            { cmd: 'image', label: '🖼 图片', title: '上传图片' },
            { cmd: 'clean', label: '🧹', title: '清除格式' },
            { sep: true },
            { cmd: 'toggleMd', label: '📝 MD', title: '切换 Markdown 模式' }
        ];

        buttons.forEach(function (b) {
            if (b.sep) {
                var s = document.createElement('span');
                s.className = 'qse-sep';
                tb.appendChild(s);
                return;
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = b.label;
            btn.title = b.title;
            btn.setAttribute('data-cmd', b.cmd);
            if (b.arg) btn.setAttribute('data-arg', b.arg);
            tb.appendChild(btn);
        });

        // WYSIWYG 编辑区
        var body = document.createElement('div');
        body.className = 'qse-body';
        body.contentEditable = 'true';
        body.setAttribute('data-placeholder', opts.placeholder || '请输入内容...');

        // Markdown 分屏容器（默认隐藏）
        var mdPane = document.createElement('div');
        mdPane.className = 'qse-mdpane';
        mdPane.style.display = 'none';
        var mdTa = document.createElement('textarea');
        mdTa.className = 'qse-mdta';
        mdTa.placeholder = '在这里写 Markdown...';
        var mdPreview = document.createElement('div');
        mdPreview.className = 'qse-mdprev';
        mdPane.appendChild(mdTa);
        mdPane.appendChild(mdPreview);

        // 状态栏
        var status = document.createElement('div');
        status.className = 'qse-status';
        status.innerHTML = '<span class="qse-wordcount">0 字</span><span>QS Editor v1.1</span>';

        wrap.appendChild(tb);
        wrap.appendChild(body);
        wrap.appendChild(mdPane);
        wrap.appendChild(status);

        ta.style.display = 'none';
        ta.parentNode.insertBefore(wrap, ta);
        body.innerHTML = ta.value || '';

        var mdMode = false;

        function sync() {
            if (mdMode) {
                ta.value = mdToHtml(mdTa.value);
            } else {
                ta.value = body.innerHTML;
            }
            var text = mdMode ? mdTa.value : (body.innerText || '');
            var wc = status.querySelector('.qse-wordcount');
            if (wc) wc.textContent = text.trim().length + ' 字';
        }

        function toggleMd() {
            mdMode = !mdMode;
            if (mdMode) {
                // 进入 MD 模式：把当前 HTML 转成纯文本塞进 mdTa
                var tmp = document.createElement('div');
                tmp.innerHTML = body.innerHTML;
                mdTa.value = tmp.innerText || tmp.textContent || '';
                body.style.display = 'none';
                mdPane.style.display = 'flex';
                mdPreview.innerHTML = mdToHtml(mdTa.value);
                mdTa.focus();
            } else {
                // 退出 MD 模式：转成 HTML 塞回 body
                body.innerHTML = mdToHtml(mdTa.value);
                mdPane.style.display = 'none';
                body.style.display = 'block';
                body.focus();
            }
            sync();
        }

        mdTa.addEventListener('input', function () {
            mdPreview.innerHTML = mdToHtml(mdTa.value);
            sync();
        });

        // 工具栏点击
        tb.addEventListener('mousedown', function (e) {
            e.preventDefault();
            var btn = e.target.closest('button');
            if (!btn) return;
            var cmd = btn.getAttribute('data-cmd');
            var arg = btn.getAttribute('data-arg') || null;

            if (cmd === 'toggleMd') { toggleMd(); return; }
            if (mdMode) return; // MD 模式下禁用富文本按钮

            if (cmd === 'image') { triggerImageUpload(); return; }
            if (cmd === 'indent2') {
                var sel = window.getSelection();
                if (sel.rangeCount > 0) {
                    var node = sel.anchorNode;
                    var blk = node.nodeType === 3 ? node.parentNode : node;
                    while (blk && blk !== body && !/^(P|DIV|H[1-6]|LI|BLOCKQUOTE)$/.test(blk.tagName)) {
                        blk = blk.parentNode;
                    }
                    if (blk && blk !== body) {
                        var txt = blk.textContent || '';
                        if (txt.indexOf('　　') !== 0) {
                            blk.insertBefore(document.createTextNode('　　'), blk.firstChild);
                        }
                    }
                }
                body.focus();
                sync();
                return;
            }
            if (cmd === 'createLink') {
                var url = prompt('请输入链接地址:', 'https://');
                if (url) document.execCommand('createLink', false, url);
                return;
            }
            if (cmd === 'clean') {
                document.execCommand('removeFormat', false, null);
                document.execCommand('formatBlock', false, 'p');
                return;
            }
            document.execCommand(cmd, false, arg);
            body.focus();
            sync();
        });

        body.addEventListener('input', sync);
        body.addEventListener('blur', sync);

        // 粘贴图片自动上传（WYSIWYG 模式）
        body.addEventListener('paste', function (e) {
            var items = (e.clipboardData || window.clipboardData).items;
            if (!items) return;
            for (var i = 0; i < items.length; i++) {
                if (items[i].kind === 'file' && items[i].type.indexOf('image/') === 0) {
                    e.preventDefault();
                    uploadImage(items[i].getAsFile());
                    break;
                }
            }
        });

        body.addEventListener('dragover', function (e) { e.preventDefault(); });
        body.addEventListener('drop', function (e) {
            e.preventDefault();
            var f = e.dataTransfer.files;
            if (f && f[0] && f[0].type.indexOf('image/') === 0) uploadImage(f[0]);
        });

        function triggerImageUpload() {
            var fi = document.createElement('input');
            fi.type = 'file';
            fi.accept = 'image/*';
            fi.onchange = function () { if (fi.files[0]) uploadImage(fi.files[0]); };
            fi.click();
        }

        function uploadImage(file) {
            var fd = new FormData();
            fd.append('filedata', file);
            wrap.classList.add('qse-loading');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'supldaa.php', true);
            xhr.onload = function () {
                wrap.classList.remove('qse-loading');
                if (xhr.status !== 200) { alert('上传失败'); return; }
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.err) { alert('上传错误: ' + res.err); return; }
                    body.focus();
                    document.execCommand('insertImage', false, res.msg.url);
                    sync();
                } catch (e) { console.error(e); }
            };
            xhr.onerror = function () { wrap.classList.remove('qse-loading'); alert('上传请求失败'); };
            xhr.send(fd);
        }

        // 表单提交前确保同步
        var form = ta.form;
        if (form) {
            form.addEventListener('submit', sync);
        }

        sync();

        var instance = {
            body: body,
            textarea: ta,
            mdTa: mdTa,
            sync: sync,
            toggleMd: toggleMd,
            getHTML: function () { sync(); return ta.value; },
            setHTML: function (html) { body.innerHTML = html; sync(); }
        };
        instances[textareaId] = instance;
        return instance;
    }

    window.QSEditor = {
        init: init,
        get: function (id) { return instances[id] || null; },
        mdToHtml: mdToHtml
    };
})();
