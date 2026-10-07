param([string]$PhpPath = 'php', [int]$OwnerId = 0)
$ErrorActionPreference = 'Stop'
if ($OwnerId -lt 0) { throw 'OwnerId must be zero (all accounts) or positive' }
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
Set-Location -LiteralPath $projectRoot
$logDirectory = Join-Path $projectRoot 'storage/logs'
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$logPath = Join-Path $logDirectory 'manga-recommendations-sync.log'
"Synchronization $(Get-Date -Format o)" | Set-Content -LiteralPath $logPath -Encoding UTF8
$syncArguments = @()
if ($OwnerId -gt 0) { $syncArguments += $OwnerId }
. (Join-Path $PSScriptRoot 'Support/InvokeHiddenPhp.ps1')
$syncExitCode = Invoke-HiddenPhp -PhpPath $PhpPath -ScriptPath (Join-Path $PSScriptRoot 'sync-recommendations.php') -LogPath $logPath -Arguments $syncArguments
exit $syncExitCode
