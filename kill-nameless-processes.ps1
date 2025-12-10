# Kill all Nameless POS processes
Write-Host "Stopping all Nameless POS processes..." -ForegroundColor Yellow
$processes = Get-Process -Name "Nameless*" -ErrorAction SilentlyContinue
if ($processes) {
    $processes | ForEach-Object {
        Write-Host "  Killing process: $($_.Name) - PID: $($_.Id)" -ForegroundColor Yellow
        Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue
    }
    Start-Sleep -Seconds 2
    Write-Host "  All processes stopped" -ForegroundColor Green
} else {
    Write-Host "  No processes found" -ForegroundColor Gray
}

