#!/usr/bin/env pwsh

# Manual Electron Build for Nameless POS
# This script forces a fresh build without interruptions

$projectPath = "d:\project warnet\Nameless"
$distPath = Join-Path $projectPath "dist"

Write-Host "=== Nameless POS Electron Build ===" -ForegroundColor Cyan
Write-Host "Project: $projectPath`n" -ForegroundColor Gray

# Step 1: Kill all processes
Write-Host "[1/5] Clearing processes..." -ForegroundColor Yellow
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process electron -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process "Nameless*" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3

# Step 2: Clean dist
Write-Host "[2/5] Cleaning dist folder..." -ForegroundColor Yellow
if (Test-Path $distPath) {
    Remove-Item $distPath -Recurse -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
}

# Step 3: Clear npm cache
Write-Host "[3/5] Clearing npm caches..." -ForegroundColor Yellow
Set-Location $projectPath
npm cache clean --force 2>&1 | Out-Null

# Step 4: Install dependencies
Write-Host "[4/5] Installing dependencies..." -ForegroundColor Yellow
npm install --force 2>&1 | Out-Null

# Step 5: Build
Write-Host "[5/5] Building EXE (this may take 2-5 minutes)..." -ForegroundColor Yellow
Write-Host ""

# Run build and capture output
$buildOutput = npm run dist 2>&1

# Print build output
$buildOutput | ForEach-Object { Write-Host $_ }

# Check result
Write-Host ""
$exeFiles = Get-ChildItem "$distPath\Nameless*.exe" -ErrorAction SilentlyContinue

if ($exeFiles) {
    Write-Host "✅ Build successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Generated files:" -ForegroundColor Cyan
    $exeFiles | ForEach-Object {
        $sizeMB = [math]::Round($_.Length / 1MB, 2)
        Write-Host "  📦 $($_.Name) ($sizeMB MB)" -ForegroundColor Green
        Write-Host "     Path: $($_.FullName)" -ForegroundColor Gray
    }
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "  1. Users must have PHP installed and in PATH"
    Write-Host "  2. Run: Nameless POS 1.0.0.exe"
    Write-Host "  3. Laravel server will start automatically"
    Write-Host ""
} else {
    Write-Host "❌ Build failed or incomplete" -ForegroundColor Red
    Write-Host "Check the output above for errors" -ForegroundColor Yellow
}
