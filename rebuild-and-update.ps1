# Rebuild and Update Electron App
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Rebuilding and Updating Electron App" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Build Vite assets
Write-Host "[1/4] Building Vite assets..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -eq 0) {
    Write-Host "  Vite build completed" -ForegroundColor Green
} else {
    Write-Host "  Vite build failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 2: Check if dist folder exists
Write-Host "[2/4] Checking dist folder..." -ForegroundColor Yellow
if (Test-Path "dist\win-unpacked") {
    Write-Host "  Dist folder found" -ForegroundColor Green
    
    # Copy updated build files
    Write-Host "  Copying updated build files..." -ForegroundColor Yellow
    if (Test-Path "public\build") {
        Copy-Item -Path "public\build\*" -Destination "dist\win-unpacked\public\build\" -Recurse -Force
        Write-Host "  Build files copied" -ForegroundColor Green
    }
    
    # Copy updated view files
    Write-Host "  Copying updated view files..." -ForegroundColor Yellow
    if (Test-Path "resources\views\includes\main-css.blade.php") {
        $viewDest = "dist\win-unpacked\resources\views\includes\"
        if (-not (Test-Path $viewDest)) {
            New-Item -ItemType Directory -Path $viewDest -Force | Out-Null
        }
        Copy-Item -Path "resources\views\includes\main-css.blade.php" -Destination $viewDest -Force
        Write-Host "  View files copied" -ForegroundColor Green
    }
} else {
    Write-Host "  Dist folder not found, need to build Electron" -ForegroundColor Yellow
    Write-Host "[3/4] Building Electron app..." -ForegroundColor Yellow
    npm run dist:portable
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  Electron build completed" -ForegroundColor Green
    } else {
        Write-Host "  Electron build failed" -ForegroundColor Red
        exit 1
    }
}
Write-Host ""

# Step 3: Verify files
Write-Host "[3/4] Verifying files..." -ForegroundColor Yellow
$checks = @(
    @{Path="dist\win-unpacked\public\build\manifest.json"; Name="Vite Manifest"},
    @{Path="dist\win-unpacked\resources\views\includes\main-css.blade.php"; Name="Updated View"},
    @{Path="dist\win-unpacked\database\database.sqlite"; Name="Database"},
    @{Path="dist\win-unpacked\.env.production"; Name="Environment File"}
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

# Step 4: Summary
Write-Host "[4/4] Summary" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
if ($allOk) {
    Write-Host "All files verified successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "You can now test the app:" -ForegroundColor Yellow
    Write-Host "  cd dist" -ForegroundColor Cyan
    Write-Host "  .\Nameless POS 1.0.0.exe" -ForegroundColor Cyan
} else {
    Write-Host "Some files are missing. Please check the build." -ForegroundColor Red
}
Write-Host ""

