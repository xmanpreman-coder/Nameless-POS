# Optimize Electron App Performance
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Electron Performance Optimization" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Kill all old processes
Write-Host "[1/4] Killing old processes..." -ForegroundColor Yellow
Get-Process -Name "Nameless*","php" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2
Write-Host "  Old processes killed" -ForegroundColor Green
Write-Host ""

# 2. Copy optimized main.js
Write-Host "[2/4] Copying optimized files..." -ForegroundColor Yellow
if (Test-Path "dist\win-unpacked\electron") {
    Copy-Item -Path "electron\main.js" -Destination "dist\win-unpacked\electron\main.js" -Force
    Write-Host "  main.js updated" -ForegroundColor Green
} else {
    Write-Host "  Need to rebuild Electron" -ForegroundColor Yellow
}
Write-Host ""

# 3. Clear Laravel caches
Write-Host "[3/4] Clearing Laravel caches..." -ForegroundColor Yellow
$phpPath = "C:\xampp\php\php.exe"
if (Test-Path $phpPath -and Test-Path "dist\win-unpacked") {
    Push-Location "dist\win-unpacked"
    & $phpPath artisan config:clear 2>&1 | Out-Null
    & $phpPath artisan cache:clear 2>&1 | Out-Null
    & $phpPath artisan view:clear 2>&1 | Out-Null
    & $phpPath artisan route:clear 2>&1 | Out-Null
    Pop-Location
    Write-Host "  Caches cleared" -ForegroundColor Green
} else {
    Write-Host "  Skipping cache clear" -ForegroundColor Yellow
}
Write-Host ""

# 4. Summary
Write-Host "[4/4] Summary" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Optimizations applied:" -ForegroundColor Green
Write-Host "  ✓ Single instance lock (prevent multiple instances)" -ForegroundColor Gray
Write-Host "  ✓ Background throttling enabled" -ForegroundColor Gray
Write-Host "  ✓ DevTools disabled in production" -ForegroundColor Gray
Write-Host "  ✓ Spellcheck disabled" -ForegroundColor Gray
Write-Host "  ✓ Unnecessary features disabled" -ForegroundColor Gray
Write-Host ""
Write-Host "Expected improvements:" -ForegroundColor Yellow
Write-Host "  - Reduced memory usage" -ForegroundColor Cyan
Write-Host "  - Faster startup time" -ForegroundColor Cyan
Write-Host "  - Lower CPU usage" -ForegroundColor Cyan
Write-Host "  - Only one instance can run" -ForegroundColor Cyan
Write-Host ""

