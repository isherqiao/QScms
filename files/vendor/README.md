# files/vendor —— 本地化的第三方前端库

本目录存放 QScms 前端（主题 + 后台编辑器）用到的第三方 JS/CSS。**页面不再请求任何外部 CDN**，全部走本地文件。

## 为什么要本地化

原来主题头部是同步加载 `https://cdn.bootcdn.net/.../swiper-bundle.min.js`。实测（2026-11-22，本机）：

| 资源 | 结果 |
|---|---|
| `cdn.bootcdn.net/.../swiper-bundle.min.js` | **25 秒超时，0 字节**（code=000） |
| `cdn.bootcdn.net/.../jquery.min.js` | 1.53 秒 |
| `cdn.bootcdn.net/.../swiper-bundle.min.css` | 0.19 秒 |
| `cdn.jsdelivr.net/.../jquery.qrcode.min.js` | 0.66 秒 |
| 本站 `style.css`（本地） | 0.005 秒 |

`<script>` 是同步的，放在 `<head>` 里：浏览器必须等它下载完才继续解析 HTML，所以 CDN 一抖动，整页就白屏几十秒。本地化后这部分时间变为 0。

## 目录与清单

| 目录 | 版本 | 文件 | 许可证 | 用途 | 引用位置 |
|---|---|---|---|---|---|
| `swiper/8.4.7/` | 8.4.7 | `swiper-bundle.min.js`、`swiper-bundle.min.css` | MIT（Vladimir Kharlampidi） | 首页轮播 | `files/theme/Qsis_swbj/header.php` |
| `jquery/3.7.1/` | 3.7.1 | `jquery.min.js` | MIT（OpenJS Foundation） | 主题脚本依赖 | `files/theme/Qsis_swbj/footer.php` |
| `jquery-qrcode/1.0.3/` | 1.0.3 | `jquery.qrcode.min.js` | MIT（Lars Jung） | 二维码生成 | `files/theme/Qsis_swbj/footer.php` |
| `opencc-js/1.0.5/` | 1.0.5 | `full.js` | MIT（nk2028 Project） | 后台文章编辑器简繁转换 | `admin/post-edit.php` |

每个库目录内保留各自的 `LICENSE`（`jquery-qrcode` 为 `LICENSE.md`，按其 MIT 声明重写）。这些许可证均为 MIT，与 QScms 自身的 GPL-3.0-or-later 兼容，可自由分发。

## 引用写法

```html
<link rel="stylesheet" href="/files/vendor/swiper/8.4.7/swiper-bundle.min.css">
<script src="/files/vendor/swiper/8.4.7/swiper-bundle.min.js"></script>
```

用根相对路径（与主题里 `/files/theme/...` 的写法一致），动态站与静态站都可用。

## 如何更新某个库

1. 新建版本目录（如 `swiper/9.0.0/`），把新文件与 `LICENSE` 放进去；
2. 改对应模板里的两行路径；
3. 旧版本目录可保留（便于回滚）或删除；
4. 递增 `control/core.php` 的 `$version` 并提交。

## 下载来源

- `https://registry.npmmirror.com/<包>/<版本>/files/<文件>`（文件与 npm 包内一致）
- 备注：`jquery.qrcode@1.0.3` 从 npm 镜像取包内文件返回 403，改用仓库内 `files/theme/blog_default/jquery.qrcode.min.js`（同版本 13994 字节，与 CDN 版一致）。
