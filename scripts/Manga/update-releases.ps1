param([string]$PhpPath = 'php')
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot
$logDirectory = Join-Path $projectRoot 'storage/logs'
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$logPath = Join-Path $logDirectory 'manga-releases-sync.log'
"Synchronization $(Get-Date -Format o)" | Set-Content -LiteralPath $logPath -Encoding UTF8
. (Join-Path $PSScriptRoot 'Support/InvokeHiddenPhp.ps1')
$syncExitCode = Invoke-HiddenPhp -PhpPath $PhpPath -ScriptPath (Join-Path $PSScriptRoot 'sync-releases.php') -LogPath $logPath
exit $syncExitCode
