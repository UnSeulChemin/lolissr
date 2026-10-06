param([string]$PhpPath = 'php', [int]$OwnerId = 0)
$ErrorActionPreference = 'Stop'
if ($OwnerId -lt 0) { throw 'OwnerId must be zero (all accounts) or positive' }
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot
$logPath = Join-Path $projectRoot 'storage/logs/manga-recommendations-sync.log'
"Synchronization $(Get-Date -Format o)" | Set-Content -LiteralPath $logPath -Encoding UTF8
$syncArguments = @((Join-Path $PSScriptRoot 'sync-recommendations.php'))
if ($OwnerId -gt 0) { $syncArguments += $OwnerId }
& $PhpPath @syncArguments 2>&1 | Out-File -LiteralPath $logPath -Append -Encoding UTF8
exit $LASTEXITCODE
