function Invoke-HiddenPhp {
    param([string]$PhpPath, [string]$ScriptPath, [string]$LogPath, [int[]]$Arguments = @())
    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = $PhpPath
    $startInfo.Arguments = '"' + $ScriptPath + '"'
    foreach ($argument in $Arguments) { $startInfo.Arguments += ' ' + $argument }
    $startInfo.WorkingDirectory = (Get-Location).Path
    $startInfo.UseShellExecute = $false
    $startInfo.CreateNoWindow = $true
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    try {
        if (-not $process.Start()) { throw 'Cannot start PHP synchronization.' }
        # Drain both pipes concurrently, including large error output.
        $stdout = $process.StandardOutput.ReadToEndAsync()
        $stderr = $process.StandardError.ReadToEndAsync()
        $process.WaitForExit()
        $stdout.GetAwaiter().GetResult() | Out-File -LiteralPath $LogPath -Append -Encoding UTF8
        $stderr.GetAwaiter().GetResult() | Out-File -LiteralPath $LogPath -Append -Encoding UTF8
        return $process.ExitCode
    } catch {
        $_.Exception.Message | Out-File -LiteralPath $LogPath -Append -Encoding UTF8
        throw
    } finally {
        $process.Dispose()
    }
}
