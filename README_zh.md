# QScms (Qsiscms)

**多语言 CMS 系统 · 十年打磨**

QScms（Qsiscms）是一个**以文件为存储介质**的轻量级开源内容管理系统（CMS）：文章、页面、菜单、广告、站点参数全部保存在可直接阅读的 PHP 数据文件里，不依赖任何数据库，整站拷走即可迁移。同一套程序十年持续打磨，前台内置 31 套主题，后台与前台各 31 种语言包，开箱即是多语言站点。

- 官方网址：https://cms.qsis.cn
- 作者：乔硕（isher / isherqiao）<qiaoshuo@gmail.com>
- 主站：https://qsis.cn
- 许可：GPL-3.0-or-later（见 `COPYING`）
- 当前版本：`26112303`（版本号取自 `control/core.php` 的 `$version`）
- English: [README.md](README.md)

## 主要特性

| 分类 | 内容 |
|---|---|
| 内容 | 文章 / 页面 / 分类标签，草稿与发布双态；Markdown 导入导出 |
| 站点 | 单站、多站点（一套程序绑定多个域名）、多站静态化 |
| 静态化 | 内置 SSG，整站生成纯静态 HTML，可直接托管在 CDN / 对象存储 / GitHub Pages，源站只做编辑 |
| 主题 | 31 套前台主题，后台一键切换；主题代码可在线编辑 |
| 插件 | 13 个内置插件（验证码、暗色模式、复制条/控制栏、主题配色、跳转等），后台启停 |
| 运营 | 广告位、友情链接、留言板、投票、图片库与图片床、支付接口 |
| 合规 | Cookie 同意、页脚信息、隐私政策独立设置 |
| 发布 | AI 发文接口（JSON/表单 + Key 鉴权）、GitHub 同步、IndexNow 与搜索引擎主动推送、RSS/Sitemap |
| 语音 | 文章 TTS 朗读（接口可配置） |
| 安全 | 后台目录可任意改名（运行时自动识别）、权限分级、argon2id 口令哈希、后台操作日志 |
| 多语言 | 后台与前台界面各 31 种语言包，后台可视化编辑 |
| 备份 | 本地备份、FTP 上传、远程备份 |

## 适用模式

1. **个人博客 / 自媒体**——写作、标签、RSS、TTS 朗读，虚拟主机即可跑，不需要数据库。
2. **企业 / 机构官网**——品牌站、产品站、案例站、律所/咨询类站点（内置商务、案例、咨询等主题）。
3. **知识库 / 文档站**——层级页面 + 菜单管理，适合产品手册、规范文档。
4. **新闻 / 垂直媒体**——高频发文、AI 发文接口、IndexNow 收录推送。
5. **纯静态站点**——开启 SSG 后生成纯静态 HTML，部署到 CDN / 对象存储 / GitHub Pages。
6. **多站点集群**——一套程序绑定多个域名，共用主题、插件与后台。
7. **受限环境部署**——共享虚拟主机、无 MySQL 权限、只读数据库等场景。
8. **二次开发基座**——GPL 开源，主题 + 插件机制，适合做行业 CMS 定制。

## Pro 版（MySQL 架构）

另有 **QScms Pro**：前台主题与后台操作习惯与文件版一致，数据层改为 **MySQL** 存储，文章、页面、用户、权限全部入库，适合数据量大、需要 SQL 查询分析、多人协作或多机部署的场景。两版数据不通用，按需求选型：

| | 文件版（本仓库） | Pro 版 |
|---|---|---|
| 存储 | PHP 文件（`data/`、`control/data/`） | MySQL |
| 部署 | 拷目录即迁移，无数据库依赖 | 需 MySQL 服务 |
| 适合 | 中小站点、静态化、虚拟主机 | 数据量大、多人协作、需 SQL |

## 一、环境要求

- PHP 8.2 及以上（安装向导会检测 `mbstring`、`gd`、`curl`、`bcmath`、`exif`、`zip`、`unzip`）
- Apache 2.4（`.htaccess` 伪静态）或 Nginx 等价配置
- 可写目录：`control/data/`、`data/`、`upload/`、`static_site/`（后两者首次运行自动创建）

## 二、目录结构

```
├─ index.php              前台入口/路由分发，兼安装锁检测
├─ control/               核心层
│  ├─ core.php            配置装载、常量、版本号（$version）
│  ├─ functions.php       主题与插件可调用的公共函数
│  ├─ verify.php          登录态、权限校验
│  ├─ data/               站点运行配置（不入库，安装时生成）
│  │   ├─ permission.php  功能权限模板（唯一随包发布的文件）
│  │   └─ conf.php        站点参数 + 后台账号（argon2id 密码哈希）
│  ├─ vote_lib.php        投票数据层
│  └─ ...
├─ admin/                 后台（默认 admin，可任意改名；URL 前缀即目录名）
│  ├─ set.php             后台首页
│  ├─ post.php / vote.php / ...
│  └─ head.php            后台导航、权限守卫
├─ data/                  内容数据（PHP 文件，非数据库）
│  ├─ posts/{index,data}/ 文章索引与正文
│  └─ pages/{index,data}/ 页面索引与正文
├─ files/                 主题、语言包、插件、本地化第三方库（vendor）
├─ install/               安装向导（install.php 三步）
│  ├─ preset/             安装预设（配置模板，账号密码为空）
│  └─ install.lock        安装完成锁（不入库）
├─ static_site/           静态化输出（不入库）
├─ upload/                上传附件（不入库）
└─ backups/               系统备份（不入库）
```

## 三、安装

1. 把发行包解压到站点根目录，确保 `control/`、`data/`、`install/` 可写。
2. 访问站点域名，会自动跳转到 `install/install.php`（判据是 `install/install.lock` 不存在）。
3. 跟随向导填写：站点名称、链接、语言、后台账号与密码。
4. 安装完成后会生成：
   - `control/data/conf.php`（站点参数 + 后台账号）
   - `control/data/` 其余配置（由 `install/preset/` 铺底）
   - `install/install.lock`（“已安装”的唯一凭证；删掉它再访问 `install/install.php` 即可重装）
5. **装完第一件事：给后台目录改名**（默认 `admin`）。改成 6～10 位无规律名称即可，无需改任何代码——程序在运行时按当前 URL 自动识别后台目录，并把结果写进 `control/data/admin_dir.php`。不做的后果是扫描器一探 `/admin/` 就找到登录页。

## 四、仓库不含哪些内容

以下均为运行期产物或每套部署的私有数据，已写入 `.gitignore`，不会进入版本库：

| 路径 | 原因 |
|---|---|
| `control/data/`（除 `permission.php`） | 站点配置与后台账号密码，每套部署各自私有；`permission.php` 是纯权限模板，随包发布 |
| `install/install.lock` | 安装产物；入库会导致下载包“看似已安装”而跳过向导 |
| `static_site/`、`upload/`、`backups/` | 运行期生成，体积大且无复用价值 |
| `rss.xml`、`sitemap.xml`、`*.log` | 自动生成 |
| `data/posts/data/`、`data/pages/data/` 下的用户内容 | 站点内容，不属于程序代码 |

另有**入库但不发行**的目录：`files/theme/Qsiscms/`（本 CMS 官网自用主题），由 `.gitattributes` 的 `export-ignore` 在打包时排除，详见第五节。

`data/` 下的**空骨架**（`posts/index/*.php`、`pages/index/*.php`、`payments.php`）必须随包保留：前台 `index.php` 直接 `require` 这些索引文件，缺失会直接报错。

## 五、分支与发版

本仓库只有一条分支 `main`：所有提交直接落到 `main`，发版就是在 `main` 上打 tag、生成发行包。

发行包由 `git archive` 生成，只含受版本控制的文件，因此天然不含站点私有配置（`control/data` 下只有 `permission.php` 这一份权限模板）和 `install.lock`、`upload/`、`static_site/`。

维护者发版流程：

```powershell
# 1. 升版本号：control/core.php 的 $version
#    规则 YYMMDD + 当天两位序号（01 起、跨天重置）；例：26112301 = 11 月 23 日第 1 版，26112303 = 当天第 3 版

# 2. 打 tag 并推送（tag 名 = v + 版本号）
git tag v26112303
git push origin v26112303

# 3. 生成发行包
powershell -File tools/build-release.ps1
```

`tools/build-release.ps1` 用 `git archive` 打包，并校验包里不含 `install.lock`、`upload/`、`static_site/`、`backups/`，`control/data` 仅保留 `permission.php`。

**官网主题 `files/theme/Qsiscms/`**：入库留在版本控制里（有历史、可回滚），但 `.gitattributes` 给它设了 `export-ignore`，`git archive` 不会把它打进发行包；`tools/build-release.ps1` 也会校验，包里一旦出现该目录就直接报错并删掉 zip。以后新增这类“只给自己站点用、不对外发行”的目录，照这两处加规则即可。

## 六、多语言

后台语言包位于 `files/language/admin/`，前台界面语言包位于 `files/language/UI/`，各 31 个语种（`lang_zh-CN.php`、`lang_en.php`、`lang_ja.php` 等）。后台页面文案统一走 `L('key')`，新增语种时需同步补齐对应键，否则该语种会回退英文。投票管理相关键统一以 `vote_` 为前缀。

## 七、许可证

GPL-3.0-or-later。你可以自由使用、修改、再分发，但分发衍生作品时须以同一许可证开放源代码。
