param([string]$PhpPath = 'php')
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot
$logDirectory = Join-Path $projectRoot 'storage/logs'
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$logPath = Join-Path $logDirectory 'manga-releases-sync.log'
"Synchronization $(Get-Date -Format o)" | Set-Content -LiteralPath $logPath -Encoding UTF8
& $PhpPath (Join-Path $PSScriptRoot 'sync-releases.php') 2>&1 | Out-File -LiteralPath $logPath -Append -Encoding UTF8
exit $LASTEXITCODE
