# Nameless POS Desktop Build Script
# Usage: .\build-desktop.ps1

Write-Host "🚀 Nameless POS Desktop Builder" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green

# Check if Laravel server is running
Write-Host "📡 Checking Laravel server..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 5 -UseBasicParsing
    Write-Host "✅ Laravel server is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Laravel server not running. Starting..." -ForegroundColor Red
    Write-Host "Please run: php artisan serve" -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-Command", "php artisan serve" -WindowStyle Minimized
    Start-Sleep 3
}

# Install Electron dependencies
Write-Host "📦 Installing Electron dependencies..." -ForegroundColor Yellow
Set-Location electron
if (-not (Test-Path "node_modules")) {
    npm install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to install dependencies" -ForegroundColor Red
        exit 1
    }
}
Write-Host "✅ Dependencies installed" -ForegroundColor Green

# Build options menu
Write-Host ""
Write-Host "📋 Build Options:" -ForegroundColor Cyan
Write-Host "1. Run Development Mode"
Write-Host "2. Build Windows Installer"  
Write-Host "3. Build All Platforms"
Write-Host "4. Test Auto-Update"
Write-Host ""

$choice = Read-Host "Choose option (1-4)"

switch ($choice) {
    "1" {
        Write-Host "🎮 Starting Development Mode..." -ForegroundColor Green
        $env:NAMELESS_URL = "http://localhost:8000"
        $env:ELECTRON_DEV = "1"
        npm start
    }
    "2" { 
        Write-Host "🏗️ Building Windows Installer..." -ForegroundColor Green
        npm run build-win
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Build completed! Check dist/ folder" -ForegroundColor Green
            if (Test-Path "dist") {
                explorer dist
            }
        } else {
            Write-Host "❌ Build failed" -ForegroundColor Red
        }
    }
    "3" {
        Write-Host "🌍 Building for All Platforms..." -ForegroundColor Green
        npm run build-all
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ All builds completed!" -ForegroundColor Green
            explorer dist
        }
    }
    "4" {
        Write-Host "🔄 Testing Auto-Update..." -ForegroundColor Green
        Write-Host "This will run app and check for updates" -ForegroundColor Yellow
        $env:NAMELESS_URL = "http://localhost:8000"
        npm start
    }
    default {
        Write-Host "❌ Invalid option" -ForegroundColor Red
        exit 1
    }
}

Set-Location ..
Write-Host "🎉 Process completed!" -ForegroundColor Green