# Quick Start Script for Nameless POS Desktop
# Usage: .\start-pos-desktop.ps1

Write-Host "Starting Nameless POS Desktop..." -ForegroundColor Green

# Start Laravel server if not running
$laravelRunning = $false
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 2 -UseBasicParsing
    $laravelRunning = $true
    Write-Host "Laravel server already running" -ForegroundColor Green
} catch {
    Write-Host "Starting Laravel server..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-Command", "cd '$PWD'; php artisan serve" -WindowStyle Minimized
    Write-Host "Waiting for Laravel to start..." -ForegroundColor Yellow
    Start-Sleep 5
}

# Start Vite for assets (if not running)
try {
    $response = Invoke-WebRequest -Uri "http://localhost:5176" -TimeoutSec 2 -UseBasicParsing
    Write-Host "Vite server already running" -ForegroundColor Green
} catch {
    Write-Host "Starting Vite for assets..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-Command", "cd '$PWD'; npm run dev" -WindowStyle Minimized
    Start-Sleep 3
}

# Check if Electron is built
if (-not (Test-Path "electron/node_modules")) {
    Write-Host "Installing Electron dependencies..." -ForegroundColor Yellow
    Set-Location electron
    npm install
    Set-Location ..
}

# Start Desktop App
Write-Host "Starting Desktop App..." -ForegroundColor Green
Set-Location electron
$env:NAMELESS_URL = "http://localhost:8000"
npm start
Set-Location ..

Write-Host "Desktop app started!" -ForegroundColor Green