#!/usr/bin/env powershell

<#
.SYNOPSIS
Nameless POS Electron Build Script (Optimized)

.DESCRIPTION
Complete build process:
1. Frontend optimization (Vite)
2. Backend preparation
3. Electron app packaging
4. EXE creation with electron-builder

.EXAMPLE
.\build-electron-optimized.ps1
#>

param(
    [switch]$Portable,
    [switch]$Installer,
    [switch]$Both,
    [switch]$Clean,
    [switch]$Debug
)

$ErrorActionPreference = 'Stop'
$WarningPreference = 'SilentlyContinue'

# Colors
$colors = @{
    Info = 'Cyan'
    Success = 'Green'
    Warning = 'Yellow'
    Error = 'Red'
}

function Write-Info { Write-Host "[INFO]" $args -ForegroundColor $colors.Info }
function Write-Success { Write-Host "[OK]" $args -ForegroundColor $colors.Success }
function Write-Warn { Write-Host "[WARN]" $args -ForegroundColor $colors.Warning }
function Write-Err { Write-Host "[ERROR]" $args -ForegroundColor $colors.Error }

function Test-Command {
    param([string]$Command)
    $exists = $null -ne (Get-Command $Command -ErrorAction SilentlyContinue)
    return $exists
}

# ============================================
# VALIDATION
# ============================================
Write-Info "Validating prerequisites..."

# Check Node.js
if (-not (Test-Command 'node')) {
    Write-Err "Node.js not found in PATH"
    Write-Info "Install from: https://nodejs.org/en/download/"
    exit 1
}
$nodeVersion = node --version
Write-Success "Node.js: $nodeVersion"

# Check NPM
if (-not (Test-Command 'npm')) {
    Write-Err "NPM not found in PATH"
    exit 1
}
$npmVersion = npm --version
Write-Success "NPM: $npmVersion"

# Check PHP
if (-not (Test-Command 'php')) {
    Write-Err "PHP not found in PATH"
    Write-Info "Required for Laravel server"
    exit 1
}
$phpVersion = php --version | Select-Object -First 1
Write-Success "PHP: $phpVersion"

# Check Composer
if (-not (Test-Command 'composer')) {
    Write-Warn "Composer not found - will try 'php composer.phar'"
}

# ============================================
# CLEANUP
# ============================================
if ($Clean) {
    Write-Info "Cleaning build artifacts..."
    
    $removeItems = @('dist/', 'build/', '.vite/', 'node_modules/.vite')
    foreach ($item in $removeItems) {
        if (Test-Path $item) {
            Write-Info "Removing: $item"
            Remove-Item $item -Recurse -Force -ErrorAction SilentlyContinue
        }
    }
    Write-Success "Cleanup complete"
}

# ============================================
# DEPENDENCIES
# ============================================
Write-Info "Installing dependencies..."

if (-not (Test-Path 'node_modules')) {
    Write-Info "Installing npm packages..."
    npm install --omit=optional
    if ($LASTEXITCODE -ne 0) {
        Write-Err "npm install failed"
        exit 1
    }
    Write-Success "npm packages installed"
} else {
    Write-Success "node_modules exists, skipping npm install"
}

# Check composer.lock exists
if (-not (Test-Path 'composer.lock')) {
    Write-Info "Running composer install..."
    if (Test-Command 'composer') {
        composer install
    } else {
        php composer.phar install
    }
    if ($LASTEXITCODE -ne 0) {
        Write-Err "composer install failed"
        exit 1
    }
    Write-Success "composer packages installed"
} else {
    Write-Success "vendor/ exists (composer.lock found)"
}

# ============================================
# FRONTEND BUILD (VITE)
# ============================================
Write-Info "Building frontend (Vite)..."

$buildStart = Get-Date
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Err "Frontend build failed"
    exit 1
}

$buildTime = (Get-Date) - $buildStart
Write-Success "Frontend built in $($buildTime.TotalSeconds.ToString('F2'))s"

# Verify build output
if (-not (Test-Path 'public/build')) {
    Write-Err "Build output not found at public/build/"
    exit 1
}

$buildSize = (Get-ChildItem 'public/build' -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB
Write-Info "Build size: $($buildSize.ToString('F2')) MB"

# ============================================
# ELECTRON BUILD
# ============================================
if ($Debug) {
    Write-Info "Debug mode - launching with Electron..."
    npm run build:electron:debug
    exit 0
}

Write-Info "Building Electron app..."

$targets = @()
if ($Portable -or $Both) { $targets += 'portable' }
if ($Installer -or $Both) { $targets += 'nsis' }
if ($targets.Count -eq 0) { $targets += 'portable' }

Write-Info "Targets: $($targets -join ', ')"

$buildStart = Get-Date

foreach ($target in $targets) {
    Write-Info "Building: $target..."
    
    if ($target -eq 'portable') {
        npm run dist:portable
    } elseif ($target -eq 'nsis') {
        npm run dist:installer
    }
    
    if ($LASTEXITCODE -ne 0) {
        Write-Err "Build failed for target: $target"
        exit 1
    }
    Write-Success "$target complete"
}

$buildTime = (Get-Date) - $buildStart
Write-Success "Electron build complete in $($buildTime.TotalSeconds.ToString('F2'))s"

# ============================================
# POST-BUILD VERIFICATION
# ============================================
Write-Info "Verifying output..."

$distItems = Get-ChildItem 'dist/' -Filter '*.exe' -ErrorAction SilentlyContinue

if ($distItems.Count -eq 0) {
    Write-Err "No EXE files found in dist/"
    exit 1
}

foreach ($exe in $distItems) {
    $sizeMB = $exe.Length / 1MB
    $modTime = $exe.LastWriteTime.ToString('yyyy-MM-dd HH:mm:ss')
    Write-Success "$($exe.Name) ($($sizeMB.ToString('F2')) MB) - $modTime"
}

# ============================================
# BUILD SUMMARY
# ============================================
Write-Info ""
Write-Host ("=" * 50) -ForegroundColor Green
Write-Success "BUILD COMPLETE!"
Write-Host ("=" * 50) -ForegroundColor Green

Write-Info ""
Write-Info "Location: $(Resolve-Path 'dist/')"
Write-Info ""
Write-Info "Next steps:"
Write-Info "  1. Test the EXE by double-clicking it"
Write-Info "  2. Verify default admin login: admin@nameless.pos / admin123"
Write-Info "  3. Check database auto-initialization"
Write-Info "  4. Verify all features working"
Write-Info ""
Write-Info "Documentation: ELECTRON_BUILD_GUIDE.md"
Write-Info ""

Write-Success "Build script completed successfully!"
