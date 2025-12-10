<#
Helper script to build release: builds electron targets and packages NSIS installer if makensis present.
#>

param(
    [switch]$Clean
)

Write-Host "Building Nameless.POS release" -ForegroundColor Cyan

if ($Clean) {
    & .\build-electron-optimized.ps1 -Clean
}

# Run full build
& .\build-electron-optimized.ps1 -Both

if ($LASTEXITCODE -ne 0) { Write-Error "Electron build failed"; exit 1 }

# Optionally run makensis if available
if (Get-Command makensis -ErrorAction SilentlyContinue) {
    Write-Host "makensis found - compiling NSIS installer" -ForegroundColor Green
    Push-Location installer
    makensis nameless-installer.nsi
    Pop-Location
} else {
    Write-Host "makensis not found - skipping NSIS compilation" -ForegroundColor Yellow
}

Write-Host "Release build completed. Check dist/ and releases/ folders." -ForegroundColor Green
