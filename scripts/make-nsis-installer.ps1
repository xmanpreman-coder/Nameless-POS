<#
  Simple helper to run makensis for the provided NSIS script.
  Usage: .\scripts\make-nsis-installer.ps1
#>

param(
    [string]$ScriptPath = "installer\\nameless-installer.nsi"
)

if (-not (Get-Command makensis -ErrorAction SilentlyContinue)) {
    Write-Error "makensis not found in PATH. Please install NSIS and ensure makensis.exe is in PATH."
    exit 1
}

$current = Get-Location
Write-Host "Running makensis on: $ScriptPath" -ForegroundColor Cyan
makensis $ScriptPath

if ($LASTEXITCODE -eq 0) {
    Write-Host "Installer created. Check releases\ for output." -ForegroundColor Green
} else {
    Write-Error "makensis failed with exit code $LASTEXITCODE"
}
