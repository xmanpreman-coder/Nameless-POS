<#
.SYNOPSIS
  Helper script: build EXE and NSIS installer for Nameless.POS

.DESCRIPTION
  Memanggil skrip build Electron yang ada (`build-electron-optimized.ps1`) dengan opsi
  untuk membuat portable EXE dan NSIS installer. Script ini memeriksa prerequisite
  dasar dan menyalin hasil build ke folder `releases\` dengan timestamp.

  Jalankan sebagai Administrator jika perlu (untuk pembuatan installer kadang memerlukan akses)

.EXAMPLE
  .\scripts\build-installer.ps1
#>

[CmdletBinding()]
param(
    [switch]$Clean,
    [string]$OutputDir = "releases"
)

$ErrorActionPreference = 'Stop'

function Test-Command { param([string]$c) return $null -ne (Get-Command $c -ErrorAction SilentlyContinue) }

Write-Host "== Nameless.POS Build Helper ==" -ForegroundColor Cyan

Write-Host "Checking prerequisites..." -ForegroundColor Yellow
if (-not (Test-Command 'node')) { Write-Error 'Node.js not found in PATH'; exit 1 }
if (-not (Test-Command 'npm'))  { Write-Error 'npm not found in PATH'; exit 1 }
if (-not (Test-Command 'php'))  { Write-Error 'PHP not found in PATH'; exit 1 }

Write-Host "Prerequisites ok." -ForegroundColor Green

if ($Clean) {
    Write-Host "Cleaning previous build artifacts..." -ForegroundColor Yellow
    & .\build-electron-optimized.ps1 -Clean
}

Write-Host "Starting electron optimized build (portable + installer)..." -ForegroundColor Yellow

# call existing build script with both targets
& .\build-electron-optimized.ps1 -Both

if ($LASTEXITCODE -ne 0) {
    Write-Error "Build script failed. Check output above for errors."
    exit 1
}

# create releases dir
if (-not (Test-Path $OutputDir)) { New-Item -ItemType Directory -Path $OutputDir | Out-Null }

# copy dist artifacts to releases/<timestamp>
$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$targetDir = Join-Path $OutputDir $timestamp
New-Item -ItemType Directory -Path $targetDir | Out-Null

Write-Host "Collecting artifacts..." -ForegroundColor Yellow
Get-ChildItem -Path .\dist -Recurse -Include '*.exe','*.nsis','*.zip','*.7z' -ErrorAction SilentlyContinue | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $targetDir -Force
}

Write-Host "Build + packaging complete. Artifacts copied to: $targetDir" -ForegroundColor Green
Write-Host "Tip: Jika installer NSIS gagal dibuat, pastikan 'makensis' ada di PATH (install NSIS)." -ForegroundColor Cyan
