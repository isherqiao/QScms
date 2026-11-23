@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem ============================================================
rem  QScms 文件版 —— 目录权限一键设置（Windows）
rem  只给运行时要写的目录授权（icacls 授予「修改」），代码目录不动
rem  站点根自动取本脚本所在目录的上一级，可用 --root 覆盖
rem
rem  用法：
rem    scripts\fix-permissions.bat
rem    scripts\fix-permissions.bat --owner *S-1-5-32-545
rem    scripts\fix-permissions.bat --root D:\www\qscms --dry-run
rem    scripts\fix-permissions.bat --minimal
rem ============================================================

set "ROOT=%~dp0.."
for %%I in ("%ROOT%") do set "ROOT=%%~fI"
set "ACCOUNT=*S-1-5-32-545"
set "DRY=0"
set "MINIMAL=0"

:parse
if "%~1"=="" goto begin
if /i "%~1"=="--root" (
    set "ROOT=%~2"
    shift
    shift
    goto parse
)
if /i "%~1"=="--owner" (
    set "ACCOUNT=%~2"
    shift
    shift
    goto parse
)
if /i "%~1"=="--dry-run" (
    set "DRY=1"
    shift
    goto parse
)
if /i "%~1"=="--minimal" (
    set "MINIMAL=1"
    shift
    goto parse
)
if /i "%~1"=="-h" goto usage
if /i "%~1"=="--help" goto usage
echo 未知参数：%~1
goto usage

:usage
echo 用法: scripts\fix-permissions.bat [--root 站点根] [--owner 账户或SID] [--minimal] [--dry-run]
echo   --root     指定站点根（默认取脚本所在目录的上一级）
echo   --owner    授予「修改」权限的账户，默认 *S-1-5-32-545（Users 组）
echo              也可填 *S-1-5-32-544（管理员）、NT AUTHORITY\SYSTEM、域名\用户名
echo   --minimal  只设核心目录（control\data、data\posts、data\pages、upload）
echo   --dry-run  只打印将要做什么，不改权限
exit /b 0

:begin
for %%I in ("%ROOT%") do set "ROOT=%%~fI"

if not exist "%ROOT%\index.php" goto badroot
if not exist "%ROOT%\control" goto badroot

set "ADMIN=csc"
if exist "%ROOT%\control\data\admin_dir.php" (
    for /f "tokens=2 delims='" %%a in ('findstr /r /c:"return '" "%ROOT%\control\data\admin_dir.php"') do set "ADMIN=%%a"
)

set "DIRS=control\data data\posts\data data\posts\index data\pages\data data\pages\index upload"
if "%MINIMAL%"=="0" set "DIRS=%DIRS% data\ads static_site backups files\theme files\language files\plugins temp_md_import %ADMIN%\tmp %ADMIN%\logs %ADMIN%\data"
set "SUBS=control\data\logs control\data\consent control\data\indexnow_log"

net session >nul 2>&1
if errorlevel 1 echo [提示] 未以管理员身份运行；如果改了系统盘或 Program Files 下的站点，建议右键「以管理员身份运行」。

echo ==============================================
echo  QScms 文件版权限设置（Windows）
echo  站点根：%ROOT%
echo  账户：%ACCOUNT%
echo  后台目录：%ADMIN%
echo  模式：%DRY% （1=试运行）
echo ==============================================
echo.

echo [1/3] 创建缺失目录
for %%D in (%DIRS%) do (
    if not exist "%ROOT%\%%D" (
        if "%DRY%"=="1" (
            echo   [dry] mkdir %%D
        ) else (
            mkdir "%ROOT%\%%D" >nul 2>&1
            if exist "%ROOT%\%%D" (echo   已创建 %%D) else (echo   × 创建失败 %%D)
        )
    )
)
for %%D in (%SUBS%) do (
    if not exist "%ROOT%\%%D" (
        if "%DRY%"=="1" (
            echo   [dry] mkdir %%D
        ) else (
            mkdir "%ROOT%\%%D" >nul 2>&1
            if exist "%ROOT%\%%D" (echo   已创建 %%D) else (echo   × 创建失败 %%D)
        )
    )
)

echo.
echo [2/3] 授权（icacls 授予「修改」并递归）
for %%D in (%DIRS%) do (
    if not exist "%ROOT%\%%D" (
        echo   跳过（不存在） %%D
    ) else (
        if "%DRY%"=="1" (
            echo   [dry] icacls "%ROOT%\%%D" /grant "%ACCOUNT%:(OI)(CI)M" /T /Q
        ) else (
            icacls "%ROOT%\%%D" /grant "%ACCOUNT%:(OI)(CI)M" /T /Q >nul 2>&1
            if errorlevel 1 (echo   × 授权失败 %%D) else (echo   完成 %%D)
        )
    )
)

echo.
echo [3/3] 自检（写测试文件）
set /a FAILN=0
for %%D in (%DIRS%) do (
    if exist "%ROOT%\%%D" (
        if "%DRY%"=="1" (
            echo   [dry] 跳过 %%D
        ) else (
            if exist "%ROOT%\%%D\.perm_test.tmp" del /f /q "%ROOT%\%%D\.perm_test.tmp" >nul 2>&1
            > "%ROOT%\%%D\.perm_test.tmp" echo ok 2>nul
            if exist "%ROOT%\%%D\.perm_test.tmp" (
                del /f /q "%ROOT%\%%D\.perm_test.tmp" >nul 2>&1
                echo   OK    %%D
            ) else (
                echo   FAIL  %%D
                set /a FAILN+=1
            )
        )
    )
)

echo.
if not "%FAILN%"=="0" (
    echo × 有 %FAILN% 个目录写不进去。检查：站点是否在受保护目录（Program Files / 系统盘根）、是否以管理员运行、杀软是否拦截。
    exit /b 1
)

echo √ 完成。上线前记得：
echo   · IIS/Apache 禁止 upload、data、backups、static_site 等目录执行 PHP
echo   · 禁止通过 URL 直接访问 control\ 与 backups\
echo   · 安装完成后删掉 install\ 目录
exit /b 0

:badroot
echo × 这不像 QScms 站点根：%ROOT%（缺少 index.php 或 control\）
echo   用 --root 指定正确路径，例如：scripts\fix-permissions.bat --root D:\www\qscms
exit /b 1
