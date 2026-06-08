$raw = Get-Content (Join-Path (Split-Path -Parent $PSScriptRoot) 'config.php') -Raw
if ($raw -match "'db_pass'\s*=>\s*'([^']*)'") { $pass = $Matches[1] } else { throw 'no pass' }
$cred = New-Object System.Net.NetworkCredential('if0_42101552', $pass)
$req = [System.Net.FtpWebRequest]::Create('ftp://ftpupload.net/htdocs/config.php')
$req.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
$req.Credentials = $cred
$req.UsePassive = $true
$resp = $req.GetResponse()
$stream = $resp.GetResponseStream()
$reader = New-Object System.IO.StreamReader($stream)
$content = $reader.ReadToEnd()
$reader.Close()
$resp.Close()
$out = Join-Path (Split-Path -Parent $PSScriptRoot) 'deploy\remote-config.php'
Set-Content -Path $out -Value $content -Encoding UTF8
Write-Host "Saved to $out"
Write-Host $content
