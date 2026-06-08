# Upload project files to InfinityFree /htdocs via FTP (reads password from config.php)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$configPath = Join-Path $root 'config.php'
if (-not (Test-Path $configPath)) {
    throw 'Missing config.php — create it from config.example.php first.'
}

$ftpLocal = Join-Path $PSScriptRoot 'ftp.local.ps1'
if (Test-Path $ftpLocal) {
    . $ftpLocal
    $pass = $script:FtpPassword
    $ftpUser = $script:FtpUser
} else {
    throw 'Missing deploy/ftp.local.ps1 — copy deploy/ftp.local.example.ps1 and set your InfinityFree FTP (hosting) password.'
}

$ftpHost = 'ftpupload.net'
$remoteBase = '/htdocs'

$excludeDirs = @('.git', 'deploy', '.idea', '.vscode', 'agent-tools')
$excludeFiles = @('config.example.php', 'config.local.php', 'config.local.example.php', '.gitignore', 'README.md', 'HOSTING.md', 'DEPLOY.md')

function Get-RelativePath([string]$full, [string]$base) {
    return $full.Substring($base.Length).TrimStart('\', '/').Replace('\', '/')
}

function Ensure-FtpDirectory([string]$uri, [System.Net.NetworkCredential]$cred) {
    try {
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.Credentials = $cred
        $req.UsePassive = $true
        $resp = $req.GetResponse()
        $resp.Close()
    } catch {
        # already exists
    }
}

function Upload-FtpFile([string]$localPath, [string]$remoteUri, [System.Net.NetworkCredential]$cred) {
    $bytes = [System.IO.File]::ReadAllBytes($localPath)
    $req = [System.Net.FtpWebRequest]::Create($remoteUri)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.Credentials = $cred
    $req.UseBinary = $true
    $req.UsePassive = $true
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $resp = $req.GetResponse()
    $resp.Close()
    Write-Host "  OK $remoteUri"
}

$cred = New-Object System.Net.NetworkCredential($ftpUser, $pass)

# List current htdocs
Write-Host "Listing $remoteBase ..."
try {
    $listReq = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteBase/")
    $listReq.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $listReq.Credentials = $cred
    $listReq.UsePassive = $true
    $listResp = $listReq.GetResponse()
    $reader = New-Object System.IO.StreamReader($listResp.GetResponseStream())
    $listing = $reader.ReadToEnd()
    $reader.Close()
    $listResp.Close()
    Write-Host $listing
} catch {
    Write-Host "List failed: $_"
}

$files = @()
Get-ChildItem -Path $root -Recurse -File | ForEach-Object {
    $rel = Get-RelativePath $_.FullName $root
    $parts = $rel -split '/'
    if ($parts[0] -in $excludeDirs) { return }
    if ($parts -contains '.git') { return }
    $name = Split-Path $rel -Leaf
    if ($excludeFiles -contains $name -and $parts.Count -eq 1) { return }
    $files += @{ Local = $_.FullName; Rel = $rel }
}

Write-Host "Uploading $($files.Count) files to $remoteBase ..."
foreach ($f in $files) {
    $remotePath = "$remoteBase/$($f.Rel)"
    $dir = Split-Path $remotePath -Parent
    if ($dir -and $dir -ne $remoteBase) {
        $segments = $dir.Trim('/').Split('/')
        $built = ''
        foreach ($seg in $segments) {
            if (-not $seg) { continue }
            $built += "/$seg"
            Ensure-FtpDirectory "ftp://$ftpHost$built/" $cred
        }
    }
    $uri = "ftp://$ftpHost$remotePath"
    Upload-FtpFile $f.Local $uri $cred
}

Write-Host 'Done. Test: https://fbastransmittal.infinityfree.io/'
