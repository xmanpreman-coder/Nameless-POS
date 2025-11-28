#!/usr/bin/env pwsh

Write-Host "Building Nameless POS Electron EXE..." -ForegroundColor Cyan

# Navigate to project
Set-Location "d:\project warnet\Nameless"

# Kill any running processes
Write-Host "Clearing running processes..." -ForegroundColor Yellow
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process electron -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Build
Write-Host "Running npm run dist..." -ForegroundColor Yellow
npm run dist

# Check result
if (Test-Path "dist\Nameless POS*.exe") {
    Write-Host "`nBuild successful!" -ForegroundColor Green
    Get-ChildItem "dist\Nameless*.exe" | ForEach-Object {
        $sizeMB = [math]::Round($_.Length / 1MB, 2)
        Write-Host "  - $($_.Name): $sizeMB MB" -ForegroundColor Green
    }
} else {
    Write-Host "`nBuild failed or incomplete." -ForegroundColor Red
}
