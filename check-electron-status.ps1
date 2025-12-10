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
        Write-Host "  ✓ Process: $($_.Name) - PID: $($_.Id) - Memory: $([math]::Round($_.WorkingSet64/1MB, 2)) MB" -ForegroundColor Green
    }
} else {
    Write-Host "  ✗ No Nameless processes found" -ForegroundColor Red
}
Write-Host ""

# 2. Check diagnostic log
Write-Host "[2] Checking diagnostic logs..." -ForegroundColor Yellow
$logPaths = @(
    "$env:APPDATA\Nameless POS\laravel-server-diagnostics.log",
    "$env:LOCALAPPDATA\Nameless POS\laravel-server-diagnostics.log",
    "$env:TEMP\laravel-server-diagnostics.log"
)

$logFound = $false
foreach ($logPath in $logPaths) {
    if (Test-Path $logPath) {
        Write-Host "  ✓ Found log: $logPath" -ForegroundColor Green
        Write-Host "  Last 20 lines:" -ForegroundColor Cyan
        Get-Content $logPath -Tail 20 | ForEach-Object {
            Write-Host "    $_" -ForegroundColor Gray
        }
        $logFound = $true
        break
    }
}

if (-not $logFound) {
    Write-Host "  ✗ No diagnostic log found" -ForegroundColor Red
    Write-Host "  Searched in:" -ForegroundColor Gray
    foreach ($logPath in $logPaths) {
        Write-Host "    - $logPath" -ForegroundColor Gray
    }
}
Write-Host ""

# 3. Check ports 8000-8100
Write-Host "[3] Checking ports 8000-8100..." -ForegroundColor Yellow
$openPorts = @()
for ($port = 8000; $port -le 8100; $port++) {
    $connection = Test-NetConnection -ComputerName localhost -Port $port -WarningAction SilentlyContinue -InformationLevel Quiet -ErrorAction SilentlyContinue
    if ($connection) {
        $openPorts += $port
        Write-Host "  ✓ Port $port is OPEN" -ForegroundColor Green
    }
}

if ($openPorts.Count -eq 0) {
    Write-Host "  ✗ No open ports found in range 8000-8100" -ForegroundColor Red
} else {
    Write-Host "  Found $($openPorts.Count) open port(s): $($openPorts -join ', ')" -ForegroundColor Green
}
Write-Host ""

# 4. Check if PHP is available in packaged app
Write-Host "[4] Checking for bundled PHP..." -ForegroundColor Yellow
$distPath = "D:\project warnet\Nameless\dist"
if (Test-Path $distPath) {
    $phpPaths = @(
        "$distPath\win-unpacked\resources\app\php\php.exe",
        "$distPath\win-unpacked\resources\php\php.exe",
        "$distPath\win-unpacked\php\php.exe"
    );
    
    $phpFound = $false
    foreach ($phpPath in $phpPaths) {
        if (Test-Path $phpPath) {
            Write-Host "  ✓ Found PHP: $phpPath" -ForegroundColor Green
            $phpVersion = & $phpPath -v 2>&1 | Select-Object -First 1
            Write-Host "    Version: $phpVersion" -ForegroundColor Gray
            $phpFound = $true
            break
        }
    }
    
    if (-not $phpFound) {
        Write-Host "  ✗ No bundled PHP found" -ForegroundColor Red
        Write-Host "  Checking system PHP..." -ForegroundColor Yellow
        $systemPhp = Get-Command php -ErrorAction SilentlyContinue
        if ($systemPhp) {
            Write-Host "  ✓ System PHP found: $($systemPhp.Source)" -ForegroundColor Green
            $phpVersion = & php -v 2>&1 | Select-Object -First 1
            Write-Host "    Version: $phpVersion" -ForegroundColor Gray
        } else {
            Write-Host "  ✗ No system PHP found in PATH" -ForegroundColor Red
        }
    }
} else {
    Write-Host "  ⚠ Dist folder not found at: $distPath" -ForegroundColor Yellow
}
Write-Host ""

# 5. Check .env file in dist
Write-Host "[5] Checking .env file..." -ForegroundColor Yellow
$envPaths = @(
    "$distPath\win-unpacked\.env",
    "$distPath\win-unpacked\.env.production",
    "$distPath\win-unpacked\resources\app\.env",
    "$distPath\win-unpacked\resources\app\.env.production"
);

$envFound = $false
foreach ($envPath in $envPaths) {
    if (Test-Path $envPath) {
        Write-Host "  ✓ Found .env: $envPath" -ForegroundColor Green
        $appKey = Select-String -Path $envPath -Pattern "^APP_KEY=" | ForEach-Object { $_.Line }
        if ($appKey) {
            $keyPreview = if ($appKey.Length -gt 50) { $appKey.Substring(0, 50) + "..." } else { $appKey }
            Write-Host "    $keyPreview" -ForegroundColor Gray
            if ($appKey -match "base64:") {
                Write-Host "    ✓ APP_KEY format looks valid (base64:...)" -ForegroundColor Green
            } else {
                Write-Host "    ✗ APP_KEY format may be invalid" -ForegroundColor Red
            }
        } else {
            Write-Host "    ✗ APP_KEY not found in .env" -ForegroundColor Red
        }
        $envFound = $true
        break
    }
}

if (-not $envFound) {
    Write-Host "  ✗ No .env file found" -ForegroundColor Red
}
Write-Host ""

# 6. Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Summary" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
if ($processes -and $openPorts.Count -gt 0) {
    Write-Host "✓ App is running and server is accessible" -ForegroundColor Green
} elseif ($processes -and $openPorts.Count -eq 0) {
    Write-Host "⚠ App is running but server port is not accessible" -ForegroundColor Yellow
    Write-Host "  Possible issues:" -ForegroundColor Yellow
    Write-Host "  - Server failed to start (check logs above)" -ForegroundColor Gray
    Write-Host "  - PHP not found or not working" -ForegroundColor Gray
    Write-Host "  - APP_KEY invalid or missing" -ForegroundColor Gray
    Write-Host "  - Database initialization failed" -ForegroundColor Gray
} elseif (-not $processes) {
    Write-Host "✗ App is not running" -ForegroundColor Red
}
Write-Host ""

