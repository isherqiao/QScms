# QScms AI 发文接口

> 字段契约同时写在 `publish.php` 文件头部注释里——外部 AI 只要读那个文件就能知道传什么、返回什么，不必额外喂说明。注释只对能读到服务器文件的人可见（HTTP 访问会执行代码、不会输出源码）。

给外部 Agent（如「大龙虾」/ OpenClaw）用的写入接口。它把文章直接写进 QScms 的文件库，
落盘结构与 `admin/post-edit.php` 完全一致（只参考，未改动该文件）：

- 正文：`data/posts/data/<id>.php`，内容为 `<?php //` + `serialize($data)` + `?>`
- 索引：`data/posts/index/<state>.php`，内容为 `<?php\n$qs_posts=var_export(...)\n?>`

## 1. 开启与取密钥

后台 → 设置 → **API 页面**（`/admin/set.php`）→ 「AI 发文接口」块：

1. 开关设为 **open**
2. 点「生成随机密钥」，把生成的 `QsisAI-...` 复制下来（数据库里只存 sha256 哈希，页面之后只显示「AI Key 已生成」）
3. 保存设置

## 2. 接口地址

```
POST http://<站点域名>/admin/AI/publish.php
```

鉴权二选一（推荐第一个）：

```
X-QSIS-AI-KEY: QsisAI-xxxx
Authorization: Bearer QsisAI-xxxx
```

> 注意：Apache 默认不把 `Authorization` 透传给 PHP，`Bearer` 在某些配置下取不到，用 `X-QSIS-AI-KEY` 最稳。

## 3. 请求字段

| 字段 | 必填 | 说明 |
| --- | --- | --- |
| `action` | 否 | `create`（默认，新建）或 `update`（按 `id` 覆盖已有文章） |
| `title` | 新建必填 | 标题；同时用于生成文章 id（`shorturl()`）。`update` 时只需在要改标题时传，且不能为空串 |
| `content` | 新建必填 | 正文，支持 HTML（与后台编辑器一致）。`update` 时只需在要改正文时传，且不能为空串 |
| `summary` | 否 | 摘要 |
| `tags` | 否 | 关键词，字符串（逗号/空格分隔）或数组 |
| `state` | 否 | `publish`（默认）或 `draft` |
| `date` | 否 | `YYYY-MM-DD`，默认当天 |
| `time` | 否 | `HH:MM` 或 `HH:MM:SS`，默认当前时间 |
| `id` | 新建时否 / `update` 时是 | 自定义 id（仅字母数字 `-` `_`）；新建时已存在返回 409，`update` 时不存在返回 404 |
| `author` | 否 | **作者登录名（username）**，不是显示名；不传则新建取本站超级管理员登录名、`update` 保留原作者。传显示名（如「管理员」）会被 422 拒绝，可选值见报错信息 |
| `can_comment` | 否 | `1`（默认）或 `0` |

### 3.1 `action=update`：只提交要改的字段

`update` 是**局部更新**，不是整篇重建：

- 只覆盖请求体里**出现过**的字段（`title` / `content` / `summary` / `tags` / `date` / `time` / `author` / `can_comment` / `state`），其余字段原样保留；
- `id` 必填，用来指定改哪一篇；
- 如果顺手传了 `title` 或 `content`，那它俩不能是空字符串（防止误清空）；不传就完全不动；
- 改 `state` 会在 `publish` / `draft` 两个索引间自动迁移，不会两边同时存在。

最小的改作者调用（其余什么都不用给）：

```bash
curl -X POST "http://127.0.0.1:90/admin/AI/publish.php" \
  -H "Content-Type: application/json" \
  -H "X-QSIS-AI-KEY: QsisAI-xxxx" \
  -d '{"action":"update","id":"1nte3i","author":"<作者登录名>"}'
```

> 关于作者：文章记录里存的是**登录名**，前台显示时由 `qs_the_user()` 用 `confp.php` 的 `$sp_config_users[登录名]['displayname']` 映射成显示名。所以接口只认登录名——写显示名会成为一条对不上账号的“幽灵作者”。可选登录名以接口 422 报错列出的为准。

## 4. 调用示例

```bash
curl -X POST "http://127.0.0.1:90/admin/AI/publish.php" \
  -H "Content-Type: application/json" \
  -H "X-QSIS-AI-KEY: QsisAI-xxxx" \
  -d '{
        "title": "文章标题",
        "summary": "一句话摘要",
        "content": "<p>正文内容</p>",
        "tags": ["标签一", "标签二"],
        "state": "publish"
      }'
```

成功返回：

```json
{
  "code": 200,
  "msg": "文章已写入",
  "data": {
    "id": "1nte3i",
    "state": "publish",
    "title": "文章标题",
    "date": "2026-11-12",
    "time": "09:16:45",
    "tags": ["标签一", "标签二"],
    "url": "http://127.0.0.1:90/?article/1nte3i.qshtml"
  }
}
```

表单提交（`application/x-www-form-urlencoded`）同样可用，字段名一致，`tags` 以逗号分隔。

## 5. 返回码

| code | HTTP | 含义 |
| --- | --- | --- |
| 200 | 200 | 写入 / 更新成功 |
| 400 | 400 | 请求体为空或不是 JSON/表单 |
| 401 | 401 | 密钥校验失败 |
| 403 | 403 | 接口未开启，或密钥尚未生成 |
| 404 | 404 | `action=update` 指定的 `id` 不存在 |
| 409 | 409 | 新建时指定的 `id` 已存在 |
| 422 | 422 | 标题/正文为空、`action` 非法、`author` 非登录名、`state` 非法、日期时间格式错、命中屏蔽词 |
| 500 | 500 | 文章目录不可用、索引或正文写入失败 |

## 6. 行为说明

- 写入后会自动调用 `qs_feeds_regenerate()`，与后台保存后重建 RSS/站点地图的行为一致。
- 同一 id 若原本在另一状态（如草稿）索引里，会先从那边移除，避免重复。
- 屏蔽词校验复用后台的 `qs_keyword_check()`，命中直接拒绝。
- 接口不经过 `head.php`（那里有登录守卫），只加载 `control/core.php`，因此无需后台登录态。

## 7. 给「大龙虾」(OpenClaw) 的说明（可直接复制粘贴）

> 下面整段可以直接喂给龙虾当系统说明。

````text
【任务】把写好的文章通过 QScms 的 AI 发文接口，直接写进站点文章库（文件存储，不是数据库）。

【接口】POST http://127.0.0.1:90/admin/AI/publish.php
【鉴权】请求头 X-QSIS-AI-KEY: <密钥>（形如 QsisAI-xxxx）。不要用 Authorization: Bearer，Apache 下常取不到。
【编码】Content-Type: application/json; charset=utf-8，请求体是 JSON。

【字段】
- action   选填，create（默认，新建一篇）或 update（按 id 局部更新已有文章）
- title    新建必填，文章标题（也用来生成文章 id）；update 时只在要改标题时传
- content  新建必填，正文，可以是 HTML（和后台编辑器一致）；update 时只在要改正文时传
- summary  选填，摘要
- tags     选填，关键词：JSON 数组 ["标签一","标签二"] 或逗号/空格分隔的字符串
- state    选填，publish（默认，前台可见）或 draft（草稿，前台不可见）
- date     选填，YYYY-MM-DD，默认当天
- time     选填，HH:MM 或 HH:MM:SS，默认当前时间
- id       新建时选填，自定义文章 id（只允许字母数字 - _），已存在返回 409；
           update 时必填，指要改的哪一篇，不存在返回 404
- author   选填，但请显式传：**作者登录名**（username）。不要传显示名，接口会 422。
- can_comment 选填，1（默认允许评论）/ 0

【update 的语义】只改你传的那几项，没传的保持原样。比如只想改作者：
curl -X POST ".../admin/AI/publish.php" -H "X-QSIS-AI-KEY: <密钥>" -H "Content-Type: application/json" -d '{"action":"update","id":"文章id","author":"<作者登录名>"}'

【返回】成功 code=200，msg 为「文章已写入」或「文章已更新」，data 里有 id / state / title / author / date / time / tags / url（url 是前台文章地址）。
失败同时给 HTTP 状态码：400 请求体空；401 密钥错；403 接口未开或密钥未生成；404 要改的文章不存在；409 id 冲突；422 字段/格式/屏蔽词问题；500 写盘失败。

【示例】
curl -X POST "http://127.0.0.1:90/admin/AI/publish.php" \
  -H "Content-Type: application/json" \
  -H "X-QSIS-AI-KEY: QsisAI-xxxx" \
  -d '{"title":"标题","summary":"一句话摘要","content":"<p>正文</p>","tags":["标签一","标签二"],"author":"<作者登录名>","state":"publish"}'

【要求】
1. 只传上面这些字段，不要自造字段。
2. author 一律写登录名（本站可选值以接口 422 报错里列出的为准）。
3. 成功后在回复里给出返回的 url；失败时把 code 和 msg 原样报出来，不要自行猜测原因。
4. 新建时不要重复提交同一篇：若返回 200 后又重试，会产生多篇文章（带相同 id 会 409）。
5. 改稿一律走 action=update + id，不要另起一篇新文章再留下一篇旧的。
````

## 8. 自检与常见 403

- **`ai_publish_key` 为空时，接口对任何请求都返回 403**（提示「密钥尚未生成」）。开关 open 但没保存密钥就是这个现象。
- 页面上密钥框显示「AI Key 已生成」表示库里已有哈希；显示为空则说明还没生成过（生成后必须点一次「保存设置」）。
- 快速自检：随便发一个空体请求，看返回是 403（未开/未配密钥）、401（密钥不对）还是 400（已通过鉴权）。

