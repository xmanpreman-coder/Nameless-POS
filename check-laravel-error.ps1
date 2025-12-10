# Check Laravel Error Logs
$distPath = "D:\project warnet\Nameless\dist\win-unpacked"

Write-Host "Checking Laravel logs..." -ForegroundColor Yellow
Write-Host ""

# Check Laravel log
$logPath = "$distPath\storage\logs\laravel.log"
if (Test-Path $logPath) {
    Write-Host "Found log file: $logPath" -ForegroundColor Green
    Write-Host ""
    Write-Host "Last 50 lines:" -ForegroundColor Cyan
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Get-Content $logPath -Tail 50
    Write-Host "----------------------------------------" -ForegroundColor Gray
} else {
    Write-Host "Log file not found: $logPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Checking alternative locations..." -ForegroundColor Yellow
    $altPaths = @(
        "$distPath\resources\app\storage\logs\laravel.log",
        "$distPath\storage\logs\laravel-$(Get-Date -Format 'Y-m-d').log"
    )
    foreach ($altPath in $altPaths) {
        if (Test-Path $altPath) {
            Write-Host "Found: $altPath" -ForegroundColor Green
            Get-Content $altPath -Tail 50
            break
        }
    }
}

Write-Host ""
Write-Host "Checking database..." -ForegroundColor Yellow
$dbPath = "$distPath\database\database.sqlite"
if (Test-Path $dbPath) {
    $size = (Get-Item $dbPath).Length
    Write-Host "Database found: $dbPath ($([math]::Round($size/1KB, 2)) KB)" -ForegroundColor Green
} else {
    Write-Host "Database not found: $dbPath" -ForegroundColor Red
}

