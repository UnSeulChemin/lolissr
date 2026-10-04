param([string]$PhpPath = (Get-Command php -ErrorAction Stop).Source)
$ErrorActionPreference = 'Stop'
$runner = Join-Path $PSScriptRoot 'update-releases.ps1'
$arguments = '-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File "{0}" -PhpPath "{1}"' -f $runner, $PhpPath
$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument $arguments
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$triggers = @((New-ScheduledTaskTrigger -Daily -At '09:00'), (New-ScheduledTaskTrigger -AtLogOn -User $identity))
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 15)
Register-ScheduledTask -TaskName 'LoliSSR-MangaReleases' -Action $action -Trigger $triggers -Principal $principal -Settings $settings -Force | Select-Object TaskName, State
