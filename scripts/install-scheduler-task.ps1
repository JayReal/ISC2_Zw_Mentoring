param(
    [string]$ApplicationPath = 'C:\inetpub\wwwroot\Other\isc2chapter-zimbabwe.org',
    [string]$TaskName = 'ISC2 Mentoring Scheduler',
    [string]$PhpPath = ''
)

$ErrorActionPreference = 'Stop'
$resolvedApplicationPath = (Resolve-Path -LiteralPath $ApplicationPath).Path
$resolvedPhpPath = if ($PhpPath) { (Resolve-Path -LiteralPath $PhpPath).Path } else { (Get-Command php -ErrorAction Stop).Source }
$artisanPath = Join-Path $resolvedApplicationPath 'artisan'

if (-not (Test-Path -LiteralPath $artisanPath -PathType Leaf)) {
    throw "Laravel artisan was not found at $artisanPath"
}

$action = New-ScheduledTaskAction -Execute $resolvedPhpPath -Argument 'artisan schedule:run' -WorkingDirectory $resolvedApplicationPath
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 10)

Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -User 'SYSTEM' -RunLevel Highest -Force | Out-Null

Write-Output "Scheduled task '$TaskName' now runs Laravel's scheduler every minute."
Write-Output "PHP: $resolvedPhpPath"
Write-Output "Application: $resolvedApplicationPath"
