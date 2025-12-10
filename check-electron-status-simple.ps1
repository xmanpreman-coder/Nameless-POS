# Check Electron App Status and Diagnostics
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Electron App Status Check" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Check if app is running
Write-Host "[1] Checking running processes..." -ForegroundColor Yellow
$processes = Get-Process -Name "Nameless*" -ErrorAction SilentlyContinue
if ($processes) {
    $processes | ForEach-Object {
        Write-Host "  Process: $($_.Name) - PID: $($_.Id) - Memory: $([math]::Round($_.WorkingSet64/1MB, 2)) MB" -ForegroundColor Green
    }
} else {
    Write-Host "  No Nameless processes found" -ForegroundColor Red
}
Write-Host ""

# 2. Check diagnostic log
Write-Host "[2] Checking diagnostic logs..." -ForegroundColor Yellow
$logPath1 = "$env:APPDATA\Nameless POS\laravel-server-diagnostics.log"
$logPath2 = "$env:LOCALAPPDATA\Nameless POS\laravel-server-diagnostics.log"
$logPath3 = "$env:TEMP\laravel-server-diagnostics.log"

$logFound = $false
if (Test-Path $logPath1) {
    Write-Host "  Found log: $logPath1" -ForegroundColor Green
    Write-Host "  Last 20 lines:" -ForegroundColor Cyan
    Get-Content $logPath1 -Tail 20
    $logFound = $true
} elseif (Test-Path $logPath2) {
    Write-Host "  Found log: $logPath2" -ForegroundColor Green
    Write-Host "  Last 20 lines:" -ForegroundColor Cyan
    Get-Content $logPath2 -Tail 20
    $logFound = $true
} elseif (Test-Path $logPath3) {
    Write-Host "  Found log: $logPath3" -ForegroundColor Green
    Write-Host "  Last 20 lines:" -ForegroundColor Cyan
    Get-Content $logPath3 -Tail 20
    $logFound = $true
}

if (-not $logFound) {
    Write-Host "  No diagnostic log found" -ForegroundColor Red
}
Write-Host ""

# 3. Check ports 8000-8010 (quick check)
Write-Host "[3] Checking ports 8000-8010..." -ForegroundColor Yellow
$openPorts = @()
for ($port = 8000; $port -le 8010; $port++) {
    $connection = Test-NetConnection -ComputerName localhost -Port $port -WarningAction SilentlyContinue -InformationLevel Quiet -ErrorAction SilentlyContinue
    if ($connection) {
        $openPorts += $port
        Write-Host "  Port $port is OPEN" -ForegroundColor Green
    }
}

if ($openPorts.Count -eq 0) {
    Write-Host "  No open ports found in range 8000-8010" -ForegroundColor Red
} else {
    Write-Host "  Found $($openPorts.Count) open port(s): $($openPorts -join ', ')" -ForegroundColor Green
}
Write-Host ""

# 4. Check if PHP is available
Write-Host "[4] Checking for PHP..." -ForegroundColor Yellow
$distPath = "D:\project warnet\Nameless\dist"
$phpPath1 = "$distPath\win-unpacked\resources\app\php\php.exe"
$phpPath2 = "$distPath\win-unpacked\resources\php\php.exe"
$phpPath3 = "$distPath\win-unpacked\php\php.exe"

$phpFound = $false
if (Test-Path $phpPath1) {
    Write-Host "  Found PHP: $phpPath1" -ForegroundColor Green
    $phpFound = $true
} elseif (Test-Path $phpPath2) {
    Write-Host "  Found PHP: $phpPath2" -ForegroundColor Green
    $phpFound = $true
} elseif (Test-Path $phpPath3) {
    Write-Host "  Found PHP: $phpPath3" -ForegroundColor Green
    $phpFound = $true
}

if (-not $phpFound) {
    Write-Host "  No bundled PHP found" -ForegroundColor Red
    $systemPhp = Get-Command php -ErrorAction SilentlyContinue
    if ($systemPhp) {
        Write-Host "  System PHP found: $($systemPhp.Source)" -ForegroundColor Green
    } else {
        Write-Host "  No system PHP found in PATH" -ForegroundColor Red
    }
}
Write-Host ""

# 5. Check .env file
Write-Host "[5] Checking .env file..." -ForegroundColor Yellow
$envPath1 = "$distPath\win-unpacked\.env"
$envPath2 = "$distPath\win-unpacked\.env.production"
$envPath3 = "$distPath\win-unpacked\resources\app\.env"
$envPath4 = "$distPath\win-unpacked\resources\app\.env.production"

$envFound = $false
if (Test-Path $envPath1) {
    Write-Host "  Found .env: $envPath1" -ForegroundColor Green
    $envFound = $true
} elseif (Test-Path $envPath2) {
    Write-Host "  Found .env: $envPath2" -ForegroundColor Green
    $envFound = $true
} elseif (Test-Path $envPath3) {
    Write-Host "  Found .env: $envPath3" -ForegroundColor Green
    $envFound = $true
} elseif (Test-Path $envPath4) {
    Write-Host "  Found .env: $envPath4" -ForegroundColor Green
    $envFound = $true
}

if (-not $envFound) {
    Write-Host "  No .env file found" -ForegroundColor Red
}
Write-Host ""

# 6. Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Summary" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
if ($processes -and $openPorts.Count -gt 0) {
    Write-Host "App is running and server is accessible" -ForegroundColor Green
} elseif ($processes -and $openPorts.Count -eq 0) {
    Write-Host "App is running but server port is not accessible" -ForegroundColor Yellow
    Write-Host "  Check diagnostic logs above for details" -ForegroundColor Gray
} elseif (-not $processes) {
    Write-Host "App is not running" -ForegroundColor Red
}
Write-Host ""

