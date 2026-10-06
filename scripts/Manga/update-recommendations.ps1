param([string]$PhpPath = 'php', [int]$OwnerId = 1)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot
$logPath = Join-Path $projectRoot 'storage/logs/manga-recommendations-sync.log'
"Synchronization $(Get-Date -Format o)" | Set-Content -LiteralPath $logPath -Encoding UTF8
& $PhpPath (Join-Path $PSScriptRoot 'sync-recommendations.php') $OwnerId 2>&1 | Out-File -LiteralPath $logPath -Append -Encoding UTF8
exit $LASTEXITCODE
