# QScms / QScms-MySQL 部署与目录权限说明

> 适用：QScms 文件版（后台目录 `admin` 或改名后的目录）、QScms-MySQL 版（后台目录 `scc`）
> 版本基准：26112303 时期实际代码（`control/core.php`、`control/consent_lib.php`、`admin/backup_lib.php` 等）
> 原则：**只给运行时要写的目录写权限，代码目录一律只读。不要整站 777。**

---

## 一、先回答三个问题

1. **要不要整个项目目录给权限？** 不要。整站可写等于给了「改代码」的权限，一旦某个上传点被绕过就是直接改 PHP 后门。
2. **要不要整个 QScms 目录给权限？** 不要。只给下面清单里的目录。
3. **是不是只有 `data` 和 `control/data` 两处？** 不是。文章/页面内容目录、`upload`、静态输出、备份、主题/语言/插件这几处按功能也要写。最小部署只要 4 个，全功能部署要 12 个左右。

---

## 二、核心必写目录（文件版）

| 目录 | 用途 | 为什么必须可写 |
|---|---|---|
| `control/data/` | 全站配置与运行时数据（`conf.php`、`confp.php`、`permission.php`、`menu_conf.php`、`groups.php`、`plugin_conf.php`、`static_config.php`、`ads.php`、`admin_dir.php`、`license.php`、`backup_token.php`…） | 后台每次改设置都整份重写 PHP 文件；`admin_dir.php` 由 `core.php` 在后台首次访问时自动写回 |
| `control/data/logs/` | 运行日志（`DATA_LOG_PATH`） | 出错要写日志 |
| `control/data/consent/` | Cookie 同意记录（`consent_lib.php` 里 `__DIR__ . '/data/consent/'`） | 访客同意一次就落一条 |
| `control/data/indexnow_log/` | IndexNow 推送日志 | 提交搜索引擎时写 |
| `data/posts/data/` | 每篇文章正文，`<id>.php`（`<?php // serialize ?>` 形式） | 发文/改文时写 |
| `data/posts/index/` | 文章索引 `publish.php`（列表页、RSS、归档都读它） | 发文/改文/改状态时写 |
| `data/pages/data/` | 每个页面正文 | 页面编辑写 |
| `data/pages/index/` | 页面索引 `publish.php` | 页面编辑写 |
| `upload/` | 上传的图片附件（按月目录，如 `upload/month_2611/`；主题图片另有 `upload/images/`、`upload/bookpic/`） | 后台任何上传 |
| `static_site/` | 静态站输出（`index.html`、`sitemap.xml`、`rss.xml`、`urls.txt`、`search-index.json`…） | 一键静态化；输出目录可在「静态化设置」里改成别的路径 |

> 这 10 个里，`control/data`、`data/posts`、`data/pages`、`upload` 是「不给自己就发不了文章」的四件套；`static_site` 只在不做静态化时可以不给。

---

## 三、按功能追加的目录（文件版）

| 目录 | 什么时候需要 | 说明 |
|---|---|---|
| `backups/` | 用后台备份功能 | 备份 zip + `pack_log.txt` 都写这里；目标目录在「备份设置」可改（默认 `/backups/`） |
| `files/theme/<主题>/` | 后台「主题代码编辑器」「主题选项」 | 编辑器会 `file_put_contents` 覆盖主题文件，还会在同目录留 `.bak` 备份；主题选项上传的图片存在 `control/data/<主题>/` |
| `files/language/admin/`、`files/language/UI/` | 后台「语言包编辑」 | `lang_editor.php` 直接覆盖语言文件 |
| `files/plugins/` | 后台装/删插件 | 插件解压到 `files/plugins/`，开关写 `control/data/plugin_conf.php` |
| `admin/tmp/`、`admin/logs/`、`admin/data/posts/` | 后台临时文件、后台日志、后台自用数据 | 后台自己的临时区与日志，可写最省事 |
| `temp_md_import/` | Markdown 批量导入 | 导入中转文件落这里 |
| `data/ads/` | 后台编辑广告位素材 | `ad_*.js` 广告 JS；SSG 会把它拷进静态输出 |
| `install/` + 根目录 `install.lock` | **只在安装时** | 装完立刻删掉 `install/`（或改名+只读），否则等于留了个能重写配置的入口 |

**只读就够、不要给写权限**：`files/images/`（内置图片）、`files/vendor/`（依赖）、`files/mail_tpl/`（发信时由 `functions.php` 读取模板，后台没有改写入口，要改请手工编辑）、`files/cookie-consent/`（组件资源，记录写 `control/data/consent/`）、`control/` 下除 `data` 外的全部 PHP、`index.php`、`register.php`、`.htaccess`。

---

## 四、QScms-MySQL 版的差异（重点）

MySQL 版配置目录**不是** `control/data`，而是 `control/config/`：

| 目录 | 用途 |
|---|---|
| `control/config/` | `admin_dir.php`、`authorization.php`、`permission.php`、`groups.php`、`plugin_conf.php`、`static_config.php`、`adv_config.php`、`logs/`、`indexnow_log/`、`consent/`（其中 `logs/`、`consent/` 首次用到时自动创建，只要父目录可写即可）。主配置（站点名、菜单、主题、SSG 参数）已进 MySQL 的 `qs_config` 表，不再依赖 `control/config/conf.php` |
| `control/data/` | **仍然需要可写**：`consent_lib.php` 里 Cookie 同意配置固定写 `control/data/cookie_consent.php`，同意记录写 `control/data/consent/` |

MySQL 版内容目录同样只剩一层 `data`：

- `data/posts/data/`、`data/pages/data/` —— 正文仍落文件；**没有 `index` 子目录**（索引/元信息在 MySQL 的 posts、pages 表里，不进 `publish.php`）
- 其余 `upload/`、`static_site/`、`backups/`、`files/theme|language|plugins|mail_tpl/`、`temp_md_import/` 与文件版一致
- 数据本身在 MySQL 里，所以**额外要给数据库用户读写权限**（这个不在文件系统范围，但部署时别忘了）

MySQL 版必写清单（最小）：`control/config/`、`control/data/`、`data/posts/data/`、`data/pages/data/`、`upload/`。
MySQL 版全功能再加：`static_site/`、`backups/`、`files/theme/`、`files/language/`、`files/plugins/`、`temp_md_import/`、`admin|scc/tmp`、`admin|scc/logs`。

---

## 五、Linux 推荐做法（PHP-FPM 以 `www-data` 运行）

```bash
cd /www/qscms            # 换成你的站点根

# 1) 整站归属 web 用户（代码由部署者维护，用 root 或部署账号也行，下面给两种）

# 方案 A：整站属主都是 www-data（简单，推荐共享主机/单站）
chown -R www-data:www-data .
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# 方案 B：代码属主 root、只给运行时目录 www-data（更严格）
chown -R root:root .
chown -R www-data:www-data control/data data upload static_site backups \
        files/theme files/language files/plugins \
        temp_md_import admin/tmp admin/logs admin/data

# MySQL 版把 control/data 换成：
# chown -R www-data:www-data control/config control/data data upload static_site backups ...
```

要点：

- **目录 755 + 文件 644 + 属主是 web 用户**就足够写，不需要 777。
- 777 只在无法 `chown` 的共享主机上作为最后手段，且必须同时做第六节的「禁止执行」。
- 不要用 `chmod -R 777`；出现「Permission denied」先查 `ls -ld` 的属主，而不是直接加 777。
- SELinux 环境（CentOS/RHEL）还要：`chcon -R -t httpd_sys_rw_content_t control/data data upload static_site backups`。

---

## 六、Web 服务器安全规则（比权限数值更重要）

这三个可写目录里存的都是 **.php 文件**（`control/data/*.php`、`data/posts/data/*.php`），所以「可写」和「可执行」必须分开：

1. **可写目录禁止通过 URL 执行 PHP**
2. **禁止 HTTP 直接访问 `control/`**（配置里有授权信息、备份令牌；PHP 内部 `require` 不受影响）
3. **禁止直访 `backups/`**（里面是整站 zip）

nginx 片段：

```nginx
# 可写/数据目录：一律不许 URL 执行 PHP
location ~* ^/(upload|data|backups|static_site|temp_md_import)/.*\.(php|phtml|phar)$ {
    deny all;
}
# 后台主题/语言/插件目录里的 PHP 也不许 URL 执行（模板由程序 include，不靠 URL）
location ~* ^/files/(theme|language|plugins|mail_tpl)/.*\.(php|phtml|phar)$ {
    deny all;
}
# 配置目录整目录禁止外部访问
location ^~ /control/ { deny all; }
# 备份不许直下
location ^~ /backups/ { deny all; }
```

Apache（`.htaccess` 或 vhost）：

```apache
<DirectoryMatch "^/www/qscms/(control|backups)">
    Require all denied
</DirectoryMatch>
<DirectoryMatch "^/www/qscms/(upload|data|static_site|temp_md_import)">
    <FilesMatch "\.(php|phtml|phar)$">
        Require all denied
    </FilesMatch>
</DirectoryMatch>
```

安装完成后：

- 删除 `install/` 目录（或改成 `install_disabled/` 并 `chmod 000`）；
- 后台目录名改掉（`admin` → 自定义），QScms 的 `core.php` 会通过 `control/data/admin_dir.php` 自动记住新名字；
- `control/data/license.php`、`authorization.php` 只需可读即可对外，**不要**挂到 URL 上。

---

## 七、自检清单（部署完跑一遍）

```bash
# 文件版
for d in control/data control/data/logs control/data/consent data/posts/data data/posts/index \
         data/pages/data data/pages/index upload static_site backups; do
  sudo -u www-data test -w "$d" && echo "OK   $d" || echo "FAIL $d"
done

# MySQL 版（logs/、consent/ 首次用到才创建，缺失时先确认 control/config 本身可写）
for d in control/config control/data control/data/consent \
         data/posts/data data/pages/data upload static_site backups; do
  sudo -u www-data test -w "$d" && echo "OK   $d" || echo "FAIL $d"
done
```

再手工点一遍：后台登录 → 发一篇测试文章 → 传一张图 → 改一次站点设置 → 点一次静态化 → 做一次备份。五步都过，权限就配齐了。

---

## 八、常见报错对照表

| 报错 | 缺权限的目录 |
|---|---|
| `control/data 目录不存在且无法创建` / `control/data 目录不可写` | `control/data/` |
| `保存失败，请检查 control/data/` | `control/data/`（保存配置时） |
| `主题目录无写入权限` | `files/theme/` |
| `无法创建存储目录` / `写入失败，请检查 control/data 目录权限` | `control/data/<主题>/`（主题选项图片） |
| `无法创建备份目录：…` / `备份目录不可写：…` | `backups/` |
| `（请确认 control/ 目录可写）` | MySQL 版 `control/config/` |
| 上传图片成功但列表里是空白 | `upload/`（及按月子目录） |
| 静态化跑完但 `static_site` 没文件 | `static_site/`（或设置里配的输出目录） |
| 插件装了没生效 | `files/plugins/` + `control/data/plugin_conf.php`（MySQL 版为 `control/config/plugin_conf.php`） |
| 改了语言包保存报错 | `files/language/` |

---

## 九、两种部署姿态（按需要选）

- **最小（只发文+后台）**：`control/data`、`data/posts`、`data/pages`、`upload`（MySQL 版：`control/config`、`control/data`、`data/posts`、`data/pages`、`upload`）
- **完整（带静态化/备份/主题/语言/插件）**：再加 `static_site`、`backups`、`files/theme`、`files/language`、`files/plugins`、`temp_md_import`、`admin/tmp`、`admin/logs`

无论哪种，**Web 服务器层面的「禁止执行 + 禁止直访」都要做**，这比 777 更管用。

---

## 十、一键脚本（推荐直接用）

仓库里带了两份脚本，按系统选一个跑，不用手抄清单：

| 文件 | 系统 | 说明 |
|---|---|---|
| `scripts/fix-permissions.sh` | Linux | 自动探测站点根与 web 运行用户，chown + chmod |
| `scripts/fix-permissions.bat` | Windows | 用 icacls 给清单内目录授予「修改」并递归 |

**Linux**

```bash
sudo ./scripts/fix-permissions.sh              # 核心 + 可选目录
sudo ./scripts/fix-permissions.sh --minimal    # 只给核心目录（最小部署）
sudo ./scripts/fix-permissions.sh --dry-run    # 先看会做什么，不落盘
sudo ./scripts/fix-permissions.sh --owner www-data --group www-data
sudo ./scripts/fix-permissions.sh --group-write   # 目录 775 / 文件 664（多人协作改文件时用）
```

**Windows**（建议右键「以管理员身份运行」，或在命令行里跑）

```bat
scripts\fix-permissions.bat
scripts\fix-permissions.bat --owner *S-1-5-32-545        :: 默认就是 Users 组
scripts\fix-permissions.bat --minimal
scripts\fix-permissions.bat --dry-run
scripts\fix-permissions.bat --root D:\www\qscms
```

脚本做的事：

- 站点根 = 脚本所在目录的上一级（可用 `--root` 覆盖），不写死路径；
- 自动读 `admin_dir.php` 认出改过名的后台目录（`admin`/`scc` 改成别的也能对上）；
- 缺的运行时子目录自动创建（`control/data/logs`、`control/data/consent`、`admin/tmp` 等）；
- 只对第二节/第三节清单里的目录 `chown` + `chmod`，代码目录不动；
- 最后以 web 用户身份逐个测可写，逐行输出 `OK` / `FAIL`，有失败时退出码 1。

脚本**不会**做的事：不整站 777、不改代码目录属主、不碰数据库授权、不改 nginx/Apache 配置（这两项按第六节手工配一次即可）。

---

## 十一、安装向导自己就会查权限

`install/install.php` 的第 2 步（账号验证之后的环境检测）现在会：

1. 先按清单把**缺失的目录建出来**（`control/data`、`data/posts|pages`、`upload`、`install` 等，`mkdir -p` 语义）；
2. 再对每个目录**实写一个 `.qs_install_probe.tmp` 探针文件**（比 `is_writable()` 可靠，能发现 ACL/SELinux 造成的假可写），验完自动删掉；
3. 分两档给出结论：**必需目录**（7 个）任一不可写 → 结论为「未通过」，页面底部只剩「重新检测」按钮，**不给进下一步**；**可选目录**（10 个）不可写只提示「站点照样能跑，对应功能会报错」；
4. 表格下面有「目录权限明细」折叠表，逐个列出目录/用途/必需或可选/可写状态，并附修复命令（`scripts/fix-permissions.sh` / `.bat`）。

另外，直接手输 `?step=3` 跳过检测也没用：进第三步前服务端会**再验一次必需目录**，有坏目录就退回第 2 步并给出不可写清单。

也就是说，安装失败最常见的原因（目录不可写）在**开始写配置之前**就被拦住了，不会出现「装到一半才发现配置写不进去」。

MySQL 版清单的必需 7 项是：`control`（写 `db_config.php`）、`control/config`、`control/data`（Cookie 同意固定写这里）、`data/posts/data`、`data/pages/data`、`upload`、`install`；内容目录没有 `index`（索引在数据库里）。

