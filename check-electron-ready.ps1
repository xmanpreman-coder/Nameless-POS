#!/usr/bin/env powershell

<#
.SYNOPSIS
Pre-Build Checklist for Nameless POS Electron

.DESCRIPTION
Verify everything is ready before running the build

#>

Write-Host "`n╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   NAMELESS POS - ELECTRON BUILD PRE-CHECK              ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$errors = @()
$warnings = @()
$passes = @()

# ============================================
# SYSTEM REQUIREMENTS
# ============================================
Write-Host "🔍 Checking System Requirements..." -ForegroundColor Yellow

# Node.js
if (Get-Command node -ErrorAction SilentlyContinue) {
    $nodeVersion = (node --version)
    $passes += "✅ Node.js installed: $nodeVersion"
} else {
    $errors += "❌ Node.js not found (required)"
}

# NPM
if (Get-Command npm -ErrorAction SilentlyContinue) {
    $npmVersion = (npm --version)
    $passes += "✅ NPM installed: $npmVersion"
} else {
    $errors += "❌ NPM not found (required)"
}

# PHP
if (Get-Command php -ErrorAction SilentlyContinue) {
    $phpVersion = (php --version | Select-Object -First 1)
    $passes += "✅ PHP installed: $phpVersion"
} else {
    $errors += "❌ PHP not found in PATH (required for Laravel)"
}

# ============================================
# PROJECT FILES
# ============================================
Write-Host "`n🗂️  Checking Project Files..." -ForegroundColor Yellow

$requiredFiles = @(
    'package.json',
    'composer.json',
    'vite.config.js',
    'electron/main.js',
    'electron-builder.yml',
    'database/seeders/DefaultAdminSeeder.php',
    'app/Models/User.php',
    '.env.production'
)

foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        $passes += "✅ Found: $file"
    } else {
        $warnings += "⚠️  Missing: $file (non-critical)"
    }
}

# ============================================
# DEPENDENCIES
# ============================================
Write-Host "`n📦 Checking Dependencies..." -ForegroundColor Yellow

if (Test-Path 'node_modules') {
    $count = (Get-ChildItem 'node_modules' -Directory).Count
    $passes += "✅ node_modules exists: $count packages"
} else {
    $warnings += "⚠️  node_modules not found (will install during build)"
}

if (Test-Path 'vendor') {
    $count = (Get-ChildItem 'vendor' -Directory).Count
    $passes += "✅ vendor directory exists: $count packages"
} else {
    $warnings += "⚠️  vendor not found (run composer install first)"
}

# ============================================
# BUILD CONFIGURATION
# ============================================
Write-Host "`n⚙️  Checking Build Configuration..." -ForegroundColor Yellow

# Check vite config
if ((Get-Content 'vite.config.js' -ErrorAction SilentlyContinue) -like '*manualChunks*') {
    $passes += "✅ vite.config.js: Code splitting configured"
} else {
    $warnings += "⚠️  vite.config.js: Code splitting may not be configured"
}

# Check electron-builder.yml
if (Test-Path 'electron-builder.yml') {
    if ((Get-Content 'electron-builder.yml' -ErrorAction SilentlyContinue) -like '*portable*') {
        $passes += "✅ electron-builder.yml: Portable EXE configured"
    }
} else {
    $errors += "❌ electron-builder.yml missing (required)"
}

# Check .env.production
if ((Get-Content '.env.production' -ErrorAction SilentlyContinue) -like '*APP_DEBUG=false*') {
    $passes += "✅ .env.production: Debugbar disabled"
} else {
    $warnings += "⚠️  .env.production: Debugbar might not be disabled"
}

# ============================================
# BUILD SCRIPTS
# ============================================
Write-Host "`n📝 Checking Build Scripts..." -ForegroundColor Yellow

$buildScripts = @(
    'build-electron-optimized.ps1',
    'test-electron-build.ps1'
)

foreach ($script in $buildScripts) {
    if (Test-Path $script) {
        $passes += "✅ Found: $script"
    } else {
        $warnings += "⚠️  Missing: $script"
    }
}

# ============================================
# DISK SPACE
# ============================================
Write-Host "`n💾 Checking Disk Space..." -ForegroundColor Yellow

$drive = (Get-Item -Path '.').PSDrive.Name
$diskInfo = Get-PSDrive -Name $drive
$freeGB = [math]::Round($diskInfo.Free / 1GB, 2)

if ($diskInfo.Free -gt 1GB) {
    $passes += "✅ Disk space: $freeGB GB free (need ~500 MB)"
} else {
    $errors += "❌ Low disk space: Only $freeGB GB free (need 500 MB)"
}

# ============================================
# DATABASE
# ============================================
Write-Host "`n🗄️  Checking Database Setup..." -ForegroundColor Yellow

$dbPath = 'database'
if (Test-Path $dbPath) {
    $passes += "✅ database/ folder exists"
    
    if (Test-Path 'database/migrations') {
        $migrations = (Get-ChildItem 'database/migrations/*.php' -ErrorAction SilentlyContinue).Count
        $passes += "✅ Found $migrations migration files"
    }
} else {
    $errors += "❌ database/ folder not found"
}

if (Test-Path 'database/seeders/DefaultAdminSeeder.php') {
    $passes += "✅ DefaultAdminSeeder.php created"
} else {
    $errors += "❌ DefaultAdminSeeder.php missing"
}

# ============================================
# PORTS
# ============================================
Write-Host "`n🔌 Checking Network Ports..." -ForegroundColor Yellow

$port8000 = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if ($port8000) {
    $warnings += "⚠️  Port 8000 is in use (may need to kill process before running)"
} else {
    $passes += "✅ Port 8000 available (Laravel will use this)"
}

# ============================================
# OUTPUT REPORT
# ============================================
Write-Host "`n" + ("=" * 55) -ForegroundColor Cyan
Write-Host "PRE-CHECK RESULTS" -ForegroundColor Cyan
Write-Host ("=" * 55) -ForegroundColor Cyan

Write-Host "`n✅ PASSED ($($passes.Count)):" -ForegroundColor Green
foreach ($pass in $passes) {
    Write-Host "   $pass" -ForegroundColor Green
}

if ($warnings.Count -gt 0) {
    Write-Host "`n⚠️  WARNINGS ($($warnings.Count)):" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "   $warning" -ForegroundColor Yellow
    }
}

if ($errors.Count -gt 0) {
    Write-Host "`n❌ ERRORS ($($errors.Count)):" -ForegroundColor Red
    foreach ($errorItem in $errors) {
        Write-Host "   $errorItem" -ForegroundColor Red
    }
    
    Write-Host "`n" + ("=" * 55) -ForegroundColor Red
    Write-Host "BUILD NOT READY - Fix errors before proceeding" -ForegroundColor Red
    Write-Host ("=" * 55) -ForegroundColor Red
    exit 1
} else {
    Write-Host "`n" + ("=" * 55) -ForegroundColor Green
    Write-Host "✅ ALL CHECKS PASSED - Ready to build!" -ForegroundColor Green
    Write-Host ("=" * 55) -ForegroundColor Green
    
    Write-Host "`n🚀 Next steps:" -ForegroundColor Cyan
    Write-Host "   1. npm install (if needed)"
    Write-Host "   2. .\build-electron-optimized.ps1"
    Write-Host "   3. Test: .\dist\Nameless-POS-1.0.0-portable.exe`n" -ForegroundColor Cyan
    
    exit 0
}
