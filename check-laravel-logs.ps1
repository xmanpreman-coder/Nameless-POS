# Check Laravel Logs and Status
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Laravel Error Log Check" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$distPath = "D:\project warnet\Nameless\dist\win-unpacked"

# 1. Check Laravel log file
Write-Host "[1] Checking Laravel error logs..." -ForegroundColor Yellow
$logPaths = @(
    "$distPath\storage\logs\laravel.log",
    "$distPath\storage\logs\laravel-$(Get-Date -Format 'Y-m-d').log",
    "$distPath\resources\app\storage\logs\laravel.log"
)

$logFound = $false
foreach ($logPath in $logPaths) {
    if (Test-Path $logPath) {
        Write-Host "  Found log: $logPath" -ForegroundColor Green
        Write-Host "  Last 30 lines:" -ForegroundColor Cyan
        Write-Host ""
        Get-Content $logPath -Tail 30 | ForEach-Object {
            Write-Host "    $_" -ForegroundColor Gray
        }
        $logFound = $true
        break
    }
}

if (-not $logFound) {
    Write-Host "  No Laravel log file found" -ForegroundColor Red
    Write-Host "  Searched in:" -ForegroundColor Gray
    foreach ($logPath in $logPaths) {
        Write-Host "    - $logPath" -ForegroundColor Gray
    }
}
Write-Host ""

# 2. Check database file
Write-Host "[2] Checking database..." -ForegroundColor Yellow
$dbPaths = @(
    "$distPath\database\database.sqlite",
    "$distPath\resources\app\database\database.sqlite"
)

$dbFound = $false
foreach ($dbPath in $dbPaths) {
    if (Test-Path $dbPath) {
        $dbSize = (Get-Item $dbPath).Length
        Write-Host "  Found database: $dbPath" -ForegroundColor Green
        Write-Host "  Size: $([math]::Round($dbSize/1KB, 2)) KB" -ForegroundColor Gray
        $dbFound = $true
        break
    }
}

if (-not $dbFound) {
    Write-Host "  No database file found" -ForegroundColor Red
    Write-Host "  Database may need to be initialized" -ForegroundColor Yellow
}
Write-Host ""

# 3. Check storage permissions
Write-Host "[3] Checking storage directories..." -ForegroundColor Yellow
$storagePaths = @(
    "$distPath\storage",
    "$distPath\storage\logs",
    "$distPath\storage\framework",
    "$distPath\storage\framework\cache",
    "$distPath\storage\framework\views",
    "$distPath\bootstrap\cache"
)

foreach ($storagePath in $storagePaths) {
    if (Test-Path $storagePath) {
        $isWritable = Test-Path $storagePath -PathType Container
        if ($isWritable) {
            Write-Host "  ✓ $storagePath" -ForegroundColor Green
        } else {
            Write-Host "  ✗ $storagePath (not writable)" -ForegroundColor Red
        }
    } else {
        Write-Host "  ✗ $storagePath (not found)" -ForegroundColor Red
    }
}
Write-Host ""

# 4. Check .env file
Write-Host "[4] Checking .env configuration..." -ForegroundColor Yellow
$envPath = "$distPath\.env.production"
if (Test-Path $envPath) {
    Write-Host "  Found .env.production" -ForegroundColor Green
    $appKey = Select-String -Path $envPath -Pattern "^APP_KEY=" | ForEach-Object { $_.Line }
    $appEnv = Select-String -Path $envPath -Pattern "^APP_ENV=" | ForEach-Object { $_.Line }
    $appDebug = Select-String -Path $envPath -Pattern "^APP_DEBUG=" | ForEach-Object { $_.Line }
    
    if ($appKey) {
        Write-Host "  $appKey" -ForegroundColor Gray
    } else {
        Write-Host "  ✗ APP_KEY not found" -ForegroundColor Red
    }
    
    if ($appEnv) {
        Write-Host "  $appEnv" -ForegroundColor Gray
    }
    
    if ($appDebug) {
        Write-Host "  $appDebug" -ForegroundColor Gray
    }
} else {
    Write-Host "  ✗ .env.production not found" -ForegroundColor Red
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan

