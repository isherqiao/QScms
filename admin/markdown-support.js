/* QsCms Markdown 粘贴支持
 * 依赖 marked.min.js（同目录）
 * 粘贴时检测剪贴板文本是否含 Markdown 语法，若是则用 marked 转成 HTML 后插入编辑器。
 * 兼容两种编辑器：
 *   - Quill:  qsMarkdown.enableQuill(quill)
 *   - xheditor: 初始化配置里传 onPaste:function(h){return qsMarkdown.processPastedHtml(h);}
 */
window.qsMarkdown = (function () {
    "use strict";

    // 判断一段文本是否“很像” Markdown
    function isLikely(text) {
        if (!text || String(text).length < 3) return false;
        var lines = String(text).split('\n');
        var score = 0;
        var n = Math.min(lines.length, 200);
        for (var i = 0; i < n; i++) {
            var l = lines[i].trim();
            if (/^#{1,6}\s/.test(l)) score += 2;                        // 标题 #
            else if (/^```/.test(l)) score += 2;                       // 围栏代码块
            else if (/^[-*+]\s+/.test(l)) score += 1;                  // 无序列表
            else if (/^\d+[.、)]\s+/.test(l)) score += 1;               // 有序列表
            else if (/^>\s?/.test(l)) score += 1;                      // 引用
            else if (/\*\*[^*]+\*\*|__[^_]+__/.test(l)) score += 1;    // 加粗
            else if (/`[^`]+`/.test(l)) score += 1;                    // 行内代码
            else if (/\[[^\]]+\]\([^)]+\)/.test(l)) score += 1;        // 链接
        }
        // 至少出现 2 处典型标记才判定为 Markdown，避免把普通文本误转
        return score >= 2;
    }

    // Markdown -> HTML
    function toHtml(text) {
        text = String(text || '');
        if (window.marked && typeof window.marked.parse === 'function') {
            try { return window.marked.parse(text); }
            catch (e) { /* fallthrough */ }
        }
        // 无 marked 时兜底：按空行分段
        return text.split(/\n{2,}/).map(function (p) {
            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
        }).join('');
    }

    // 把 HTML 插入 Quill（替换当前选中，否则在光标处插入）
    function insertIntoQuill(quill, html) {
        if (!quill) return;
        var range = quill.getSelection() || { index: quill.getLength(), length: 0 };
        var index = range.index, length = range.length;
        var delta = quill.clipboard.convert({ html: html });
        if (length > 0) quill.deleteText(index, length, 'silent');
        quill.updateContents(delta, 'silent');
        quill.setSelection(index, 0, 'silent');
    }

    // 为 Quill 编辑器启用“粘贴即识别 Markdown”
    function enableQuill(quill) {
        if (!quill || !quill.root) return;
        var root = quill.root;
        var handler = function (e) {
            var cd = e.clipboardData || window.clipboardData;
            var text = cd ? (cd.getData('text/plain') || cd.getData('Text') || '') : '';
            if (!text || !isLikely(text)) return;
            e.preventDefault();
            e.stopPropagation();
            insertIntoQuill(quill, toHtml(text));
        };
        // capture 阶段拦截，先于 Quill 自身的粘贴处理
        root.addEventListener('paste', handler, true);
    }

    // xheditor 的 onPaste 回调：接收粘贴后的 HTML，若像 Markdown 则转换后返回
    function processPastedHtml(html) {
        var div = document.createElement('div');
        div.innerHTML = html || '';
        var text = div.textContent || '';
        if (!text || !isLikely(text)) return html;
        return toHtml(text);
    }

    return { isLikely: isLikely, toHtml: toHtml, enableQuill: enableQuill, processPastedHtml: processPastedHtml };
})();
