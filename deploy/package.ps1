# Build FBAStransmittal-upload.zip for InfinityFree htdocs upload
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$outDir = $PSScriptRoot
$zipPath = Join-Path $outDir 'FBAStransmittal-upload.zip'

$excludeDirs = @('.git', 'deploy', '.idea', '.vscode')
$excludeFiles = @('config.example.php', '.gitignore', 'README.md', 'HOSTING.md', 'DEPLOY.md')

$staging = Join-Path $env:TEMP ("fbas-deploy-" + [guid]::NewGuid().ToString('n'))
New-Item -ItemType Directory -Path $staging -Force | Out-Null

function Copy-ProjectItem {
    param([string]$RelativePath)
    $src = Join-Path $root $RelativePath
    $dest = Join-Path $staging $RelativePath
    if (-not (Test-Path $src)) { return }
    $destParent = Split-Path $dest -Parent
    if (-not (Test-Path $destParent)) {
        New-Item -ItemType Directory -Path $destParent -Force | Out-Null
    }
    Copy-Item -Path $src -Destination $dest -Recurse -Force
}

Get-ChildItem -Path $root -Force | ForEach-Object {
    $name = $_.Name
    if ($excludeDirs -contains $name) { return }
    if ($_.PSIsContainer) {
        Copy-ProjectItem $name
        return
    }
    if ($excludeFiles -contains $name) { return }
    Copy-Item -Path $_.FullName -Destination (Join-Path $staging $name) -Force
}

# Include config.php if present (for convenience; never commit config.php to Git)
$configSrc = Join-Path $root 'config.php'
if (Test-Path $configSrc) {
    Copy-Item $configSrc (Join-Path $staging 'config.php') -Force
}

if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zipPath -Force
Remove-Item $staging -Recurse -Force

Write-Host "Created: $zipPath"
Write-Host "Upload all files inside the ZIP to InfinityFree htdocs, then fix config.php db_host in File Manager."
