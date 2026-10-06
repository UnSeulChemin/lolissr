param([string]$PhpPath = (Get-Command php -ErrorAction Stop).Source, [int]$OwnerId = 1)
$ErrorActionPreference = 'Stop'
if ($OwnerId -lt 1) { throw 'OwnerId must be positive' }
$runner = Join-Path $PSScriptRoot 'update-recommendations.ps1'
$arguments = '-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File "{0}" -PhpPath "{1}" -OwnerId {2}' -f $runner, $PhpPath, $OwnerId
$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument $arguments
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$triggers = @((New-ScheduledTaskTrigger -Daily -At '09:15'), (New-ScheduledTaskTrigger -AtLogOn -User $identity))
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 20)
Register-ScheduledTask -TaskName 'LoliSSR-MangaRecommendations' -Action $action -Trigger $triggers -Principal $principal -Settings $settings -Force | Select-Object TaskName, State
