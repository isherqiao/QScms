#!/usr/bin/env bash
# ============================================================
# QScms 文件版 —— 目录权限一键设置（Linux）
#
# 只给运行时要写的目录授权，代码目录保持只读。
# 站点根自动取脚本所在目录的上一级，可用 --root 覆盖。
#
# 用法：
#   sudo ./scripts/fix-permissions.sh
#   sudo ./scripts/fix-permissions.sh --owner www-data
#   sudo ./scripts/fix-permissions.sh --dry-run          # 只打印不执行
#   sudo ./scripts/fix-permissions.sh --minimal          # 只设核心 4 类
#   sudo ./scripts/fix-permissions.sh --group-write      # 目录 775 / 文件 664
#   sudo ./scripts/fix-permissions.sh --root /www/qscms
#
# 退出码：0 成功；1 参数或环境错误
# ============================================================

set -uo pipefail

SCRIPT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
ROOT=$(cd -- "$SCRIPT_DIR/.." && pwd)

OWNER=""
GROUP=""
DRY=0
MINIMAL=0
GROUP_WRITE=0

usage() {
    cat <<'EOF'
QScms 文件版 —— 目录权限一键设置（Linux）

用法：
  sudo ./scripts/fix-permissions.sh [选项]

选项：
  --root <路径>     指定站点根（默认取脚本所在目录的上一级）
  --owner <用户>    指定 web 运行用户（默认自动探测 php-fpm / apache / www-data）
  --group <组>      指定组（默认取该用户的登录组）
  --minimal         只设核心目录（control/data、data/posts、data/pages、upload）
  --group-write     目录 775 / 文件 664（默认 755 / 644）
  --dry-run         只打印将要做什么，不落盘
  -h, --help        显示本帮助
EOF
}

while [ $# -gt 0 ]; do
    case "$1" in
        --root)        ROOT=${2:?--root 需要一个站点根路径}; shift 2 ;;
        --owner)       OWNER=${2:?--owner 需要一个用户名}; shift 2 ;;
        --group)       GROUP=${2:?--group 需要一个组名}; shift 2 ;;
        --dry-run)     DRY=1; shift ;;
        --minimal)     MINIMAL=1; shift ;;
        --group-write) GROUP_WRITE=1; shift ;;
        -h|--help)     usage; exit 0 ;;
        *) echo "未知参数：$1"; usage; exit 1 ;;
    esac
done

# ---------- 校验站点根 ----------
if [ ! -f "$ROOT/index.php" ] || [ ! -d "$ROOT/control" ]; then
    echo "× 这不像 QScms 站点根：$ROOT（缺少 index.php 或 control/）"
    echo "  用 --root 指定正确路径，例如：--root /www/qscms"
    exit 1
fi

# ---------- 探测 web 运行用户 ----------
detect_owner() {
    local u
    if command -v ps >/dev/null 2>&1; then
        for want in php-fpm php-fpm8.5 php-fpm8.4 php-fpm8.3 php-fpm8.2 apache2 httpd nginx; do
            u=$(ps -eo user=,comm= 2>/dev/null | awk -v w="$want" '$2 ~ w {print $1; exit}')
            [ -n "$u" ] && { echo "$u"; return; }
        done
    fi
    for c in www-data apache nginx httpd nobody; do
        if id "$c" >/dev/null 2>&1; then echo "$c"; return; fi
    done
    id -un
}

if [ -z "$OWNER" ]; then
    OWNER=$(detect_owner)
fi

if ! id "$OWNER" >/dev/null 2>&1; then
    echo "× 用户不存在：$OWNER（用 --owner 指定 web 运行用户，如 www-data）"
    exit 1
fi

if [ -z "$GROUP" ]; then
    GROUP=$(id -gn "$OWNER" 2>/dev/null || echo "$OWNER")
fi

# ---------- 后台目录名（可能被改过） ----------
admin_dir_name() {
    local f name=""
    for f in "$ROOT/control/data/admin_dir.php" "$ROOT/control/config/admin_dir.php"; do
        [ -f "$f" ] || continue
        name=$(sed -nE "s/.*return[[:space:]]*'([A-Za-z0-9_-]{1,64})'.*/\1/p" "$f" | head -n 1)
        [ -n "$name" ] && { echo "$name"; return; }
    done
    echo "admin"
}

ADMIN=$(admin_dir_name)

# ---------- 目录清单 ----------
# 核心：不给自己就发不了文章
CORE_DIRS=(
    "control/data"
    "data/posts/data"
    "data/posts/index"
    "data/pages/data"
    "data/pages/index"
    "upload"
)

# 按功能追加
EXTRA_DIRS=(
    "data/ads"
    "static_site"
    "backups"
    "files/theme"
    "files/language"
    "files/plugins"
    "temp_md_import"
    "$ADMIN/tmp"
    "$ADMIN/logs"
    "$ADMIN/data"
)

# 运行时子目录（缺失就建）
SUB_DIRS=(
    "control/data/logs"
    "control/data/consent"
    "control/data/indexnow_log"
    "$ADMIN/tmp"
    "$ADMIN/logs"
    "$ADMIN/data"
)

DIRS=("${CORE_DIRS[@]}")
if [ "$MINIMAL" -eq 0 ]; then
    DIRS+=("${EXTRA_DIRS[@]}")
    SUB_DIRS+=("data/ads" "static_site" "backups" "temp_md_import")
fi

DMODE=755
FMODE=644
if [ "$GROUP_WRITE" -eq 1 ]; then DMODE=775; FMODE=664; fi

echo "=============================================="
echo " QScms 文件版权限设置"
echo " 站点根：$ROOT"
echo " 运行用户：$OWNER:$GROUP"
echo " 后台目录：$ADMIN"
echo " 模式：$([ "$DRY" -eq 1 ] && echo '试运行（不落盘）' || echo '实际执行')$([ "$MINIMAL" -eq 1 ] && echo ' · 仅核心' || echo ' · 核心+可选')"
echo " 权限：目录 $DMODE / 文件 $FMODE"
echo "=============================================="

# ---------- 创建缺失目录 ----------
echo
echo "[1/3] 创建缺失的运行时目录"
for d in "${SUB_DIRS[@]}"; do
    if [ -d "$ROOT/$d" ]; then
        echo "  已存在  $d"
    else
        if [ "$DRY" -eq 1 ]; then
            echo "  [dry] mkdir -p $d"
        else
            mkdir -p "$ROOT/$d" && echo "  已创建  $d" || echo "  × 创建失败 $d"
        fi
    fi
done

# ---------- 授权 ----------
echo
echo "[2/3] 授权（chown + chmod）"
for d in "${DIRS[@]}"; do
    if [ ! -e "$ROOT/$d" ]; then
        echo "  跳过（不存在） $d"
        continue
    fi
    if [ "$DRY" -eq 1 ]; then
        echo "  [dry] chown -R $OWNER:$GROUP $d ; chmod 目录 $DMODE / 文件 $FMODE"
        continue
    fi
    chown -R "$OWNER:$GROUP" "$ROOT/$d" 2>/dev/null || echo "  × chown 失败 $d"
    find "$ROOT/$d" -type d -exec chmod "$DMODE" {} + 2>/dev/null
    find "$ROOT/$d" -type f -exec chmod "$FMODE" {} + 2>/dev/null
    echo "  完成  $d"
done

# ---------- 自检 ----------
echo
echo "[3/3] 自检（以 $OWNER 身份测试可写）"
FAILN=0
for d in "${DIRS[@]}"; do
    if [ ! -d "$ROOT/$d" ]; then
        continue
    fi
    if [ "$DRY" -eq 1 ]; then
        echo "  [dry] 跳过 $d"
        continue
    fi
    if [ "$(id -un)" = "root" ] && command -v runuser >/dev/null 2>&1; then
        if runuser -u "$OWNER" -- test -w "$ROOT/$d" 2>/dev/null; then
            echo "  OK    $d"
        else
            echo "  FAIL  $d"; FAILN=$((FAILN + 1))
        fi
    elif [ -w "$ROOT/$d" ]; then
        echo "  OK    $d（当前用户可写；虽非 $OWNER，一般也可用）"
    else
        echo "  WARN  $d（当前用户不可写，请用 root 执行本脚本自检）"
    fi
done

echo
if [ "$FAILN" -gt 0 ]; then
    echo "× 有 $FAILN 个目录仍不可写。常见原因："
    echo "  1) 父目录属主是 root 且无 x 权限；2) SELinux 未放开（chcon -R -t httpd_sys_rw_content_t 上述目录）"
    exit 1
fi

echo "√ 完成。上线前记得："
echo "  · nginx/Apache 上禁止 upload、data、backups、static_site 等目录通过 URL 执行 PHP"
echo "  · 禁止直接访问 control/ 与 backups/"
echo "  · 安装完成后删掉 install/ 目录"
exit 0
