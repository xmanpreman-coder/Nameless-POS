#!/usr/bin/env powershell

<#
.SYNOPSIS
Test Nameless POS Electron Build Quickly

.DESCRIPTION
Quick test untuk build sebelum full release:
1. Build frontend
2. Launch app with Electron
3. Test default login
4. Basic functionality check

#>

param(
    [switch]$DebugMode = $false
)

$ErrorActionPreference = 'Stop'

Write-Host "`n📦 Nameless POS - Electron Quick Test`n" -ForegroundColor Cyan

# Check prerequisites
Write-Host "🔍 Checking prerequisites..." -ForegroundColor Yellow
if (-not (Test-Path 'package.json')) {
    Write-Host "❌ Not in Nameless POS directory" -ForegroundColor Red
    exit 1
}

# Build frontend
Write-Host "`n🔨 Building frontend..." -ForegroundColor Cyan
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Build failed" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Frontend built" -ForegroundColor Green

# Test options
Write-Host "`n🚀 Choose test mode:" -ForegroundColor Cyan
Write-Host "  1. Debug (fast launch, no packaging)"
Write-Host "  2. Build portable EXE (~5 min)"
Write-Host "  3. Both (full test)"

if ($DebugMode) {
    $choice = 1
} else {
    Write-Host "`nEnter choice (1-3): " -NoNewline
    $choice = Read-Host
}

switch ($choice) {
    1 {
        Write-Host "`n🐛 Launching debug mode..." -ForegroundColor Cyan
        Write-Host "`nTODO:"
        Write-Host "  ✓ Wait 3-4 seconds for app to launch"
        Write-Host "  ✓ Login: admin@nameless.pos / admin123"
        Write-Host "  ✓ Check dashboard loads"
        Write-Host "  ✓ Check sidebar menus visible"
        Write-Host "  ✓ Try opening a module (Products, Sales, etc)"
        Write-Host ""
        npm run build:electron:debug
    }
    2 {
        Write-Host "`n📦 Building portable EXE..." -ForegroundColor Cyan
        Write-Host "`nThis will take 3-5 minutes...`n"
        npm run dist:portable
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "`n✅ EXE Created!" -ForegroundColor Green
            Write-Host "`nLocation: .\dist\Nameless-POS-1.0.0-portable.exe`n"
            Write-Host "📋 To test:"
            Write-Host "  1. Double-click the EXE"
            Write-Host "  2. Wait for splash screen"
            Write-Host "  3. App should load in 4-5 seconds"
            Write-Host "  4. Login: admin@nameless.pos / admin123"
            Write-Host ""
        }
    }
    3 {
        Write-Host "`n📦 Full test mode (debug + build EXE)..." -ForegroundColor Cyan
        npm run build:electron:debug
        
        Write-Host "`n`nAfter testing debug mode..."
        Write-Host "Building portable EXE..." -ForegroundColor Cyan
        npm run dist:portable
        
        Write-Host "`n✅ Both tests completed!" -ForegroundColor Green
    }
    default {
        Write-Host "Invalid choice" -ForegroundColor Red
        exit 1
    }
}

Write-Host "`n✅ Test script completed`n" -ForegroundColor Green
Write-Host "📚 For detailed guide, see: ELECTRON_BUILD_GUIDE.md`n" -ForegroundColor Cyan
