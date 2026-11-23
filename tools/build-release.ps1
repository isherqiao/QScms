# 构建 QScms 发行包
#
# 用法：
#   powershell -File tools/build-release.ps1                 # 默认打 main 分支
#   powershell -File tools/build-release.ps1 -Branch dev     # 打 dev
#   powershell -File tools/build-release.ps1 -OutDir D:\out  # 指定输出目录
#
# 原理：用 git archive 打包，只输出受版本控制的文件。
# 因此 control/data/ 下的站点私有配置、install/install.lock、upload/、static_site/、backups/ 天然不会进入 zip，
# 下载解压后首次访问会自动跳 install 向导。control/data 仅 permission.php 权限模板随包发布。

param(
    [string]$Branch = 'main',
    [string]$OutDir,
    [string]$Prefix = 'QScms/'
)

$ErrorActionPreference = 'Stop'

# 自定位：脚本位于 tools/ 下，仓库根目录是它的上一级
$root = Split-Path -Parent $PSScriptRoot
$core = Join-Path $root 'control/core.php'

if (-not (Test-Path $core)) {
    throw "找不到 control/core.php，脚本可能不在仓库的 tools/ 目录下：$root"
}

# 从 core.php 读取版本号
$verLine = Select-String -Path $core -Pattern "\`$version\s*=\s*'([^']+)'" | Select-Object -First 1
if (-not $verLine) {
    throw "无法从 control/core.php 读取 \$version"
}
$ver = $verLine.Matches[0].Groups[1].Value

if (-not $OutDir) { $OutDir = Join-Path $root 'dist' }
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null
$zip = Join-Path $OutDir ("QScms-$ver.zip")
if (Test-Path $zip) { Remove-Item $zip -Force }

# 确认分支存在
& git -C $root rev-parse --verify --quiet $Branch | Out-Null
if ($LASTEXITCODE -ne 0) {
    throw "分支不存在：$Branch"
}

& git -C $root archive --format=zip --prefix=$Prefix -o $zip $Branch
if ($LASTEXITCODE -ne 0) {
    throw "git archive 失败（分支 $Branch）"
}

# 校验：发行包不允许出现运行期数据
Add-Type -AssemblyName System.IO.Compression.FileSystem
$entries = 0
$violations = @()
$archive = [IO.Compression.ZipFile]::OpenRead($zip)
try {
    $entries = $archive.Entries.Count
    $violations = $archive.Entries |
        Where-Object {
            ($_.FullName -match '(^|/)install\.lock$') -or
            ($_.FullName -match '(^|/)(upload|static_site|backups)/') -or
            ($_.FullName -match '(^|/)\.git/') -or
            ($_.FullName -match '(^|/)files/theme/Qsiscms/') -or
            (($_.FullName -match '(^|/)control/data/.+') -and ($_.FullName -notmatch '(^|/)control/data/permission\.php$'))
        } |
        Select-Object -First 5
} finally {
    $archive.Dispose()
}

if ($violations) {
    Remove-Item $zip -Force
    throw ("发行包含禁止内容，已删除：" + ($violations[0].FullName))
}

$sizeMB = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host "分支      : $Branch"
Write-Host "版本号    : $ver"
Write-Host "输出      : $zip"
Write-Host "文件数    : $entries"
Write-Host "体积      : ${sizeMB} MB"
Write-Host "校验      : 未包含 install.lock、upload、static_site、backups；control/data 仅 permission.php 模板；官网主题 files/theme/Qsiscms 已排除"
