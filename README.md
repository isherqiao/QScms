# QScms (Qsiscms)

**Multilingual CMS · Refined over 10 years**

QScms (formerly QsisCMS) is a lightweight, open-source content management system that stores everything in files instead of a database. Articles, pages, menus, ads and site settings live in plain, human-readable PHP data files — copy the whole directory and you have migrated the site. One codebase has been refined for ten years; it ships with 18 front-end themes and 31 language packs for both the admin and the public UI, so a multilingual site works out of the box.

- Official site: https://cms.qsis.cn
- Author: Qiao Shuo (isher / isherqiao) <qiaoshuo@gmail.com>
- Main site: https://qsis.cn
- License: GPL-3.0-or-later (see `COPYING`)
- Version: see `$version` in `control/core.php`
- 中文说明: [README_zh.md](README_zh.md)

## Features

| Area | What you get |
|---|---|
| Content | Articles, pages, categories/tags, draft & published states; Markdown import/export |
| Sites | Single site, multi-site (one install serving several domains), multi-site static generation |
| Static | Built-in SSG: renders the whole site to plain HTML for CDN / object storage / GitHub Pages; the origin can stay edit-only |
| Themes | 18 front-end themes, one-click switching; theme source editable in the browser |
| Plugins | 13 bundled plugins (captcha, dark mode, copy bar / control bar, theme colors, redirect, ...), toggleable in admin |
| Operations | Ad slots, links, guestbook, polls, image library & image host, payment gateway |
| Compliance | Cookie consent, footer info, privacy policy as separate settings |
| Publishing | AI publishing API (JSON/form + key auth), GitHub sync, IndexNow & search-engine pings, RSS/Sitemap |
| Speech | Article TTS narration (configurable endpoint) |
| Security | Admin directory can be renamed to anything (auto-detected at runtime), role levels, argon2id password hashing, admin action log |
| i18n | 31 admin language packs and 31 public-UI language packs, editable in admin |
| Backup | Local backup, FTP upload, remote backup |

## Where it fits

1. **Personal blogs / independent media** — writing, tags, RSS, TTS narration; runs on shared hosting without a database.
2. **Company & institution sites** — brand, product and case-study sites; business, case and consulting themes included.
3. **Knowledge bases / documentation** — hierarchical pages plus menu management.
4. **News & vertical publishers** — high posting frequency, AI publishing API, IndexNow submission.
5. **Fully static sites** — enable SSG, deploy the HTML to CDN / object storage / GitHub Pages.
6. **Multi-site clusters** — one install bound to several domains, sharing themes, plugins and admin.
7. **Constrained environments** — shared hosting, no MySQL privilege, read-only database.
8. **A base for custom builds** — GPL-licensed, theme + plugin architecture, suited to industry-specific CMS work.

## Pro edition (MySQL backend)

There is also **QScms Pro**: the same front-end themes and admin workflow, with the data layer moved to **MySQL** (articles, pages, users and permissions all in tables). It targets large datasets, SQL-based reporting, multi-user collaboration and multi-server deployments. Data is not interchangeable between the two editions, so pick the one that matches the job.

| | File edition (this repo) | Pro edition |
|---|---|---|
| Storage | PHP files (`data/`, `control/data/`) | MySQL |
| Deployment | Copy the directory; no database needed | Requires MySQL |
| Best for | Small/medium sites, static generation, shared hosting | Large datasets, teams, SQL workloads |

## 1. Requirements

- PHP 8.2+ (installer checks `mbstring`, `gd`, `curl`, `bcmath`, `exif`, `zip`, `unzip`)
- Apache 2.4 (`.htaccess` rewrite) or an equivalent Nginx config
- Writable directories: `control/data/`, `data/`, `upload/`, `static_site/` (the last two are created on first run)

## 2. Directory layout

```
├─ index.php              Front controller / routing, also checks the install lock
├─ control/                Core layer
│  ├─ core.php            Config loading, constants, version ($version)
│  ├─ functions.php        Helpers exposed to themes and plugins
│  ├─ verify.php           Session and permission checks
│  ├─ data/               Runtime site config (created by the installer)
│  │   ├─ permission.php   Permission template (shipped with the program)
│  │   └─ conf.php        Site settings + admin account (argon2id hash)
│  ├─ vote_lib.php        Poll data layer
│  └─ ...
├─ admin/                 Admin area (default admin, renameable; the URL prefix is the directory name)
│  ├─ set.php             Admin home
│  ├─ post.php / vote.php / ...
│  └─ head.php            Admin navigation and guards
├─ data/                  Content data (PHP files, not a database)
│  ├─ posts/{index,data}/ Article index and bodies
│  └─ pages/{index,data}/ Page index and bodies
├─ files/                 Themes, language packs, plugins, vendored libraries
├─ install/               Installer (install.php, three steps)
│  ├─ preset/             Install presets (config templates, empty credentials)
│  └─ install.lock        Install lock
├─ static_site/           Static output (generated at runtime)
├─ upload/                Uploads (generated at runtime)
└─ backups/               System backups (generated at runtime)
```

The empty skeletons under `data/` (`posts/index/*.php`, `pages/index/*.php`, `payments.php`) must stay with the program: the front controller `require`s those index files directly and fails if they are missing.

## 3. Installation

1. Unpack the program into the web root; make sure `control/`, `data/` and `install/` are writable.
2. Visit your domain: you are redirected to `install/install.php` (the trigger is a missing `install/install.lock`).
3. Follow the wizard: site name, site URL, language, admin account and password.
4. The installer writes `control/data/conf.php` (site settings + admin account), fills the rest of `control/data/` from `install/preset/`, and creates `install/install.lock`. Delete that lock and revisit `install/install.php` to reinstall.
5. **Do this first after installing: rename the admin directory** (default `admin`). Any 6–10 character random name works; no code change is needed — the app detects the directory from the current URL and records it in `control/data/admin_dir.php`. If you skip this, scanners probing `/admin/` will land on your login page.

## 4. Internationalization

Admin language packs live in `files/language/admin/`, public-UI packs in `files/language/UI/` — 31 locales each (`lang_zh-CN.php`, `lang_en.php`, `lang_ja.php`, ...). Admin strings go through `L('key')`; when adding a locale, fill every key or the locale falls back to English. Voting-related keys share the `vote_` prefix.

## 5. License

GPL-3.0-or-later. Free to use, modify and redistribute; derivative distributions must be released under the same license.
