param([string]$RemotePath = '/htdocs/config.local.php')
$root = Split-Path -Parent $PSScriptRoot
$raw = Get-Content (Join-Path $root 'config.php') -Raw
if ($raw -match "'db_pass'\s*=>\s*'([^']*)'") { $pass = $Matches[1] } else { throw 'no pass' }
$cred = New-Object System.Net.NetworkCredential('if0_42101552', $pass)
$req = [System.Net.FtpWebRequest]::Create("ftp://ftpupload.net$RemotePath")
$req.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
$req.Credentials = $cred
$req.UsePassive = $true
try {
    $resp = $req.GetResponse()
    $resp.Close()
    Write-Host "Deleted $RemotePath"
} catch {
    Write-Host "Skip delete $RemotePath : $_"
}
