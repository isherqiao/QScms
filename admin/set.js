/**
 * set.js - 完整后台设置脚本
 * 功能：Tab分页 | 二级菜单动态添加(插入父级后) | 拖拽排序 | 删除/升级逻辑
 */
// --- 1. 验证码检查逻辑 ---
function checkcode() {
    var nick_code = document.getElementById("nick_code");
    var nick_code_src = document.getElementById("nick_code_src");
    if (!nick_code || !nick_code_src) return;
    
    var el1 = document.getElementById("nick_code_chanage");
    var el2 = document.getElementById("nick_code_chanage1");
    var el3 = document.getElementById("password_chanage");
    if (nick_code.value != nick_code_src.value) {
        if(el1) el1.style.display = "block";
        if(el2) el2.style.display = "block";
        if(el3) el3.style.display = "none";
    } else {
        if(el1) el1.style.display = "none";
        if(el2) el2.style.display = "none";
    }
}

//Tab延时定时器全局变量
let tabHoverTimer = null;
const TAB_HOVER_DELAY = 1000; //2秒延时

// --- 2. 页面加载初始化 ---
window.onload = function() {
    // A. 初始化验证码
    if(document.getElementById("nick_code")) {
        checkcode();
    }
    // B. 初始化 Tab 分页 (选项卡切换)
    // 查找 ID 为 fenye 的容器
    var oTab = document.getElementById("fenye");
    if (oTab) {
        var aH3 = oTab.getElementsByTagName("h3");
        var aDiv = oTab.getElementsByTagName("ta"); // 自定义标签 ta
        
        if (aH3.length > 0 && aDiv.length > 0) {
            //真正执行切换的函数
            var switchTab = function(index) {
                // 1. 重置所有
                for (var j = 0; j < aH3.length; j++) {
                    aH3[j].className = ""; 
                    if (aDiv[j]) aDiv[j].style.display = "none";
                }
                // 2. 激活当前
                aH3[index].className = "active";
                if (aDiv[index]) aDiv[index].style.display = "block";
            };

            for (var i = 0; i < aH3.length; i++) {
                aH3[i].index = i;
                
                //鼠标移入：开启2秒倒计时
                aH3[i].onmouseover = function(){
                    //先清除上一个未执行的定时器，防止多个任务堆积
                    clearTimeout(tabHoverTimer);
                    var selfIndex = this.index;
                    tabHoverTimer = setTimeout(function(){
                        switchTab(selfIndex);
                    },TAB_HOVER_DELAY);
                };
                //鼠标移出：立刻取消延时切换
                aH3[i].onmouseout = function(){
                    clearTimeout(tabHoverTimer);
                };
                //点击：立刻切换，不受延时影响
                aH3[i].onclick = function(){
                    clearTimeout(tabHoverTimer);
                    switchTab(this.index);
                };
            }
        }
    }
    // C. 初始化拖拽事件绑定
    // 延迟一点执行，确保 DOM 已完全渲染
    setTimeout(initDraggables, 100);
};
// --- 3. 核心功能：动态添加行 (支持插入到指定元素后) ---
/**
 * 通用添加行函数
 * @param {string} containerId - 容器ID
 * @param {string} rowClass - 基础类名 (linkdiv 或 links_menu)
 * @param {string} nameInputName - 名称 input name
 * @param {string} urlInputName - 链接 input name
 * @param {string} tarInputName - 目标 input name
 * @param {string} parentIdName - 父ID input name
 * @param {string} selfIdName - 自身ID input name
 * @param {object} labels - 语言标签对象 {name, link, delete, sub, promote}
 * @param {string} parentId - 父级ID值
 * @param {number} level - 层级 (1=顶级, 2=子级)
 * @param {string} insertAfterId - [可选] 如果存在，新元素将插入到这个ID的元素之后
 */
function addDynamicRow(containerId, rowClass, nameInputName, urlInputName, tarInputName, parentIdName, selfIdName, labels, parentId, level, insertAfterId) {
    var container = document.getElementById(containerId);
    if (!container) {
        console.error("Container not found: " + containerId);
        return;
    }
    var div = document.createElement("div");
    var uniqueId = "new_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
    div.id = rowClass + "_" + uniqueId;
    
    // 设置类名：基础类 + (如果是子级则加 sub-menu-row)
    // 注意：所有样式由 CSS 控制，这里不写 style=""
    div.className = rowClass + (level === 2 ? ' sub-menu-row' : '');
    div.setAttribute("data-new", "true");
    div.setAttribute("draggable", "true");
    // 构建 Hidden Inputs
    var selfInput = '<input type="hidden" name="' + selfIdName + '" value="' + div.id + '">';
    var parentVal = parentId ? parentId : "0";
    var parentInput = '<input type="hidden" name="' + parentIdName + '" value="' + parentVal + '">';
    // 构建操作按钮 HTML
    var actionButtons = '';
    if (level === 1) {
        // 顶级菜单：显示“添加子菜单”
        var onclickStr = "addChildClick('" + div.id + "', '" + containerId + "', '" + rowClass + "', '" + 
                         nameInputName + "', '" + urlInputName + "', '" + tarInputName + "', '" + 
                         parentIdName + "', '" + selfIdName + "')";
        actionButtons += '<a href="javascript:void(0)" onclick="' + onclickStr + '" class="action-link btn-sub">➕ ' + (labels.sub || 'Sub') + '</a> ';
    } else {
        // 子菜单：显示“升级”
        actionButtons += '<a href="javascript:void(0)" onclick="promoteToTop(\'' + div.id + '\', \'' + parentIdName + '\')" class="action-link btn-promote">⬆️ ' + (labels.promote || 'Promote') + '</a> ';
    }
    // 确定图标类名 (menu-icon 或 link-icon)
    var iconClass = (rowClass.indexOf('links_menu') > -1) ? 'link-icon' : 'menu-icon';
    var iconChar = (level === 2 ? '└' : '●');
    // 构建完整 HTML
    div.innerHTML = 
        selfInput +
        parentInput +
        '<span class="' + iconClass + '">' + iconChar + '</span>' +
        '<label>' + (labels.name || 'Name') + ':</label>' +
        '<input type="text" name="' + nameInputName + '" value="" placeholder="Name"> ' +
        '<label>' + (labels.link || 'Link') + ':</label>' +
        '<input type="text" name="' + urlInputName + '" value="" placeholder="Link"> ' +
        '<select name="' + tarInputName + '"><option value="_blank">_blank</option><option value="_self">_self</option></select> ' +
        '| <a href="javascript:void(0)" onclick="deleteItem(\'' + div.id + '\')" class="action-link btn-delete">' + (labels.delete || 'Delete') + '</a> ' +
        actionButtons +
        '<span class="drag-handle" title="Drag to sort">⇧⇩</span>';
    // 绑定拖拽事件
    bindDragEvents(div);
    // --- 关键逻辑：插入位置 ---
    if (insertAfterId) {
        var referenceNode = document.getElementById(insertAfterId);
        if (referenceNode && referenceNode.parentNode === container) {
            // 插入到参考节点的下一个兄弟节点之前 (即紧跟在参考节点后面)
            container.insertBefore(div, referenceNode.nextSibling);
        } else {
            // 如果找不到参考节点， fallback 到底部
            container.appendChild(div);
        }
    } else {
        // 默认添加到底部
        container.appendChild(div);
    }
}
/**
 * 点击“添加子菜单”按钮的入口函数
 * 逻辑：获取当前行ID，调用 addDynamicRow 并传入 insertAfterId
 */
function addChildClick(currentRowId, containerId, rowClass, nameInput, urlInput, tarInput, parentIdInput, selfIdInput) {
    // 获取语言标签 (从任意一个配置按钮读取)
    var sampleBtn = document.querySelector('[data-label-name]');
    var labels = {
        name: sampleBtn ? sampleBtn.getAttribute('data-label-name') : 'Name',
        link: sampleBtn ? sampleBtn.getAttribute('data-label-link') : 'Link',
        delete: sampleBtn ? sampleBtn.getAttribute('data-label-delete') : 'Delete',
        sub: 'Sub',
        promote: 'Promote'
    };
    // 调用添加函数
    // 最后一个参数 currentRowId 告诉函数：插在这个 ID 后面
    addDynamicRow(
        containerId, 
        rowClass, 
        nameInput, 
        urlInput, 
        tarInput, 
        parentIdInput, 
        selfIdInput, 
        labels, 
        currentRowId, // parentId
        2,            // level (子级)
        currentRowId  // insertAfterId (插在父级后)
    );
}
/**
 * 将子菜单升级为顶级菜单
 */
function promoteToTop(rowId, parentIdName) {
    var row = document.getElementById(rowId);
    if (!row) return;
    
    // 1. 移除子菜单类，CSS 会自动恢复样式
    row.classList.remove('sub-menu-row');
    
    // 2. 修改 Parent ID 为 0
    var pInput = row.querySelector('input[name="' + parentIdName + '"]');
    if(pInput) pInput.value = "0";
    
    // 3. 修改图标 (●)
    var iconSpan = row.querySelector('.menu-icon, .link-icon');
    if(iconSpan) iconSpan.innerHTML = "●";
    
    // 4. 替换按钮：把“升级”换成“添加子菜单”
    var oldBtn = row.querySelector('.btn-promote');
    if (oldBtn) {
        // 重新构建参数有点复杂，这里简化处理：
        // 解析 row 内的 input name 来重建 onclick
        var nameInput = row.querySelector('input[name$="[]"]');
        if (nameInput) {
            var nName = nameInput.name;
            // 简单推断其他 name (假设命名规范一致)
            var uName = nName.replace('name', 'url'); 
            var tName = nName.replace('name', 'tar');
            var sName = nName.replace('name', 'seSelfid'); // 假设 selfid 命名规则
            
            // 如果推断失败，尝试更通用的匹配
            if(uName === nName) uName = nName.replace('menuname', 'menuurl').replace('link_name', 'link_url');
            if(tName === nName) tName = nName.replace('menuname', 'menutar').replace('link_name', 'link_menutar');
            if(sName === nName) sName = nName.replace('menuname', 'menuseSelfid').replace('link_name', 'linkseSelfid');
            var containerId = row.parentNode.id;
            var rClass = row.classList.contains('links_menu') ? 'links_menu' : 'linkdiv';
            var newOnclick = "addChildClick('" + rowId + "', '" + containerId + "', '" + rClass + "', '" + 
                             nName + "', '" + uName + "', '" + tName + "', '" + parentIdName + "', '" + sName + "')";
            
            oldBtn.onclick = newOnclick;
            oldBtn.className = "action-link btn-sub";
            oldBtn.innerHTML = "➕ Sub";
            oldBtn.title = "Add Sub Menu";
        } else {
            oldBtn.outerHTML = "<span style='color:#999; font-size:12px;'> (Refresh)</span>";
        }
    }
}
// --- 4. 删除与批量清理 ---
function deleteItem(id) {
    var element = document.getElementById(id);
    if (!element) return;
    // 警告：如果有子菜单指向此 ID
    var allInputs = document.querySelectorAll('input[type="hidden"]');
    var hasChildren = false;
    for (var i = 0; i < allInputs.length; i++) {
        if (allInputs[i].name.indexOf('parentid') > -1 && allInputs[i].value === id) {
            hasChildren = true;
            break;
        }
    }
    if (hasChildren) {
        if(!confirm("Warning: This item has sub-menus. Deleting it may leave orphaned sub-menus. Continue?")) {
            return;
        }
    }
    if (element.parentNode) {
        element.parentNode.removeChild(element);
    }
}
function deletelinks(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var newItems = container.querySelectorAll('[data-new="true"]');
    // 倒序删除以防索引变化
    for (var i = newItems.length - 1; i >= 0; i--) {
        if (newItems[i] && newItems[i].parentNode) {
            newItems[i].parentNode.removeChild(newItems[i]);
        }
    }
}
// --- 5. 统一入口：处理“添加主菜单/链接”按钮点击 ---
function handleAddClick(btnElement) {
    var containerId = btnElement.getAttribute('data-container');
    var rowClass = btnElement.getAttribute('data-class');
    var nameInput = btnElement.getAttribute('data-names');
    var urlInput = btnElement.getAttribute('data-urls');
    var tarInput = btnElement.getAttribute('data-tars');
    var parentIdInput = btnElement.getAttribute('data-parent-id-name') || 'parent_id[]';
    var selfIdInput = btnElement.getAttribute('data-self-id-name') || 'self_id[]';
    
    var labels = {
        name: btnElement.getAttribute('data-label-name'),
        link: btnElement.getAttribute('data-label-link'),
        delete: btnElement.getAttribute('data-label-delete'),
        sub: 'Sub',
        promote: 'Promote'
    };
    if (!containerId || !nameInput) {
        console.error("Missing data attributes on Add button");
        return;
    }
    // 添加顶级菜单 (level=1, 无 insertAfterId)
    addDynamicRow(
        containerId,
        rowClass,
        nameInput,
        urlInput,
        tarInput,
        parentIdInput,
        selfIdInput,
        labels,
        null, 
        1,
        null 
    );
}
// --- 6. 拖拽排序逻辑 (Drag & Drop) ---
var dragSrcEl = null;
function initDraggables() {
    var selectors = '.linkdiv, .links_menu';
    var items = document.querySelectorAll(selectors);
    items.forEach(function(item) {
        if (!item.hasAttribute('data-drag-bound')) {
            bindDragEvents(item);
            item.setAttribute('data-drag-bound', 'true');
        }
    });
}
function bindDragEvents(element) {
    element.addEventListener('dragstart', handleDragStart, false);
    element.addEventListener('dragenter', handleDragEnter, false);
    element.addEventListener('dragover', handleDragOver, false);
    element.addEventListener('dragleave', handleDragLeave, false);
    element.addEventListener('drop', handleDrop, false);
    element.addEventListener('dragend', handleDragEnd, false);
}
function handleDragStart(e) {
    this.style.opacity = '0.4';
    e.dataTransfer.effectAllowed = 'move';
    dragSrcEl = this;
    // 传输数据 (可选)
    e.dataTransfer.setData('text/html', this.innerHTML);
}
function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}
function handleDragEnter(e) {
    this.classList.add('over');
}
function handleDragLeave(e) {
    this.classList.remove('over');
}
function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    if (dragSrcEl !== this) {
        // 1. 交换 HTML 内容 (包含 inputs 和 buttons)
        var tempHtml = dragSrcEl.innerHTML;
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = tempHtml;
        // 2. 交换 Class (处理 sub-menu-row)
        var tempClass = dragSrcEl.className;
        dragSrcEl.className = this.className;
        this.className = tempClass;
        // 3. 交换图标内容 (● vs └)
        var srcIcon = dragSrcEl.querySelector('.menu-icon, .link-icon');
        var targetIcon = this.querySelector('.menu-icon, .link-icon');
        if(srcIcon && targetIcon) {
            var tempIcon = srcIcon.innerHTML;
            srcIcon.innerHTML = targetIcon.innerHTML;
            targetIcon.innerHTML = tempIcon;
        }
        
        // 注意：不需要交换 style，因为样式完全由 class (.sub-menu-row) 控制
        // 只要 class 交换了，样式自然就跟过去了
    }
    return false;
}
function handleDragEnd(e) {
    this.style.opacity = '1';
    var items = document.querySelectorAll('.linkdiv, .links_menu');
    items.forEach(function(item) {
        item.classList.remove('over');
    });
}
