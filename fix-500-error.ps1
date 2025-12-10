# Fix 500 Error - Complete Cleanup
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fixing 500 Error - Complete Cleanup" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$distPath = "D:\project warnet\Nameless\dist\win-unpacked"
$sourcePath = "D:\project warnet\Nameless"

# 1. Copy updated view file
Write-Host "[1/5] Copying updated view file..." -ForegroundColor Yellow
$viewSource = "$sourcePath\resources\views\includes\main-css.blade.php"
$viewDest = "$distPath\resources\views\includes\main-css.blade.php"

if (Test-Path $viewSource) {
    if (-not (Test-Path "$distPath\resources\views\includes")) {
        New-Item -ItemType Directory -Path "$distPath\resources\views\includes" -Force | Out-Null
    }
    Copy-Item -Path $viewSource -Destination $viewDest -Force
    Write-Host "  View file copied" -ForegroundColor Green
} else {
    Write-Host "  Source view not found!" -ForegroundColor Red
}
Write-Host ""

# 2. Remove ALL compiled views
Write-Host "[2/5] Removing compiled views..." -ForegroundColor Yellow
$viewsDir = "$distPath\storage\framework\views"
if (Test-Path $viewsDir) {
    Get-ChildItem $viewsDir -Filter "*.php" -ErrorAction SilentlyContinue | Remove-Item -Force
    Write-Host "  Compiled views removed" -ForegroundColor Green
} else {
    Write-Host "  Views directory not found" -ForegroundColor Yellow
}
Write-Host ""

# 3. Clear all Laravel caches
Write-Host "[3/5] Clearing Laravel caches..." -ForegroundColor Yellow
$phpPath = "C:\xampp\php\php.exe"
if (Test-Path $phpPath) {
    Push-Location $distPath
    & $phpPath artisan config:clear 2>&1 | Out-Null
    & $phpPath artisan cache:clear 2>&1 | Out-Null
    & $phpPath artisan view:clear 2>&1 | Out-Null
    & $phpPath artisan route:clear 2>&1 | Out-Null
    Pop-Location
    Write-Host "  All caches cleared" -ForegroundColor Green
} else {
    Write-Host "  PHP not found, skipping cache clear" -ForegroundColor Yellow
}
Write-Host ""

# 4. Verify manifest
Write-Host "[4/5] Verifying Vite manifest..." -ForegroundColor Yellow
$manifestPath = "$distPath\public\build\manifest.json"
if (Test-Path $manifestPath) {
    $manifest = Get-Content $manifestPath | ConvertFrom-Json
    Write-Host "  Manifest found" -ForegroundColor Green
    if ($manifest.'style.css') {
        $cssFile = $manifest.'style.css'.file
        Write-Host "  CSS file: $cssFile" -ForegroundColor Gray
        if (Test-Path "$distPath\public\build\$cssFile") {
            Write-Host "  CSS file exists" -ForegroundColor Green
        } else {
            Write-Host "  CSS file MISSING!" -ForegroundColor Red
        }
    } else {
        Write-Host "  style.css entry not found in manifest" -ForegroundColor Red
    }
} else {
    Write-Host "  Manifest NOT found!" -ForegroundColor Red
}
Write-Host ""

# 5. Final verification
Write-Host "[5/5] Final verification..." -ForegroundColor Yellow
$checks = @(
    @{Path="$distPath\resources\views\includes\main-css.blade.php"; Name="View File"},
    @{Path="$distPath\public\build\manifest.json"; Name="Manifest File"},
    @{Path="$distPath\storage\framework\views"; Name="Views Directory"}
)

$allOk = $true
foreach ($check in $checks) {
    if (Test-Path $check.Path) {
        Write-Host "  $($check.Name): OK" -ForegroundColor Green
    } else {
        Write-Host "  $($check.Name): MISSING" -ForegroundColor Red
        $allOk = $false
    }
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
if ($allOk) {
    Write-Host "All fixes applied!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "  1. Restart the Electron app" -ForegroundColor Cyan
    Write-Host "  2. Try logging in again" -ForegroundColor Cyan
    Write-Host "  3. Error 500 should be fixed" -ForegroundColor Green
} else {
    Write-Host "Some files are missing. Please check." -ForegroundColor Red
}
Write-Host ""

