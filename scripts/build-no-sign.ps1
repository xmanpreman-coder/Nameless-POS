<#
Build script that sets env vars to avoid signing and runs electron-builder.
Run PowerShell as Administrator for best results.
#>
param(
    [switch]$Clean
)

if ($Clean) {
    Write-Host "Cleaning dist folder..." -ForegroundColor Yellow
    Remove-Item -LiteralPath "dist" -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Host "Setting environment variables to skip signing..." -ForegroundColor Cyan
$env:CSC_IDENTITY_AUTO_DISCOVERY = "false"
$env:CSC_LINK = ""
$env:CSC_KEY_PASSWORD = ""
$env:WIN_CSC_LINK = ""
$env:WIN_CSC_KEY_PASSWORD = ""
$env:SKIP_SIGNING = "true"
$env:SKIP_NOTARIZATION = "true"
$env:DEBUG = "electron-builder"

Write-Host "Optional: Unblocking executables (node_modules, build, dist)..." -ForegroundColor Cyan
& powershell -ExecutionPolicy Bypass -File ".\scripts\unblock-files.ps1"

# Ensure production env has a valid APP_KEY before packaging. If a local .env exists
# and .env.production is missing or contains the placeholder key, copy .env to
# .env.production so the packaged app won't boot with an invalid APP_KEY.
if (Test-Path ".env") {
    $prodPath = ".env.production"
    $shouldCopy = $false
    if (-not (Test-Path $prodPath)) { $shouldCopy = $true }
    else {
        $content = Get-Content $prodPath -Raw -ErrorAction SilentlyContinue
        if ($content -match "your-app-key-here") { $shouldCopy = $true }
    }

    if ($shouldCopy) {
        Write-Host "Copying local .env -> .env.production to ensure valid APP_KEY in packaged build" -ForegroundColor Yellow
        Copy-Item -Path ".env" -Destination $prodPath -Force
    }
}

Write-Host "Running electron-builder (portable)..." -ForegroundColor Cyan
# Run electron-builder directly to keep env vars in the same session
npx electron-builder --win portable

$exitCode = $LASTEXITCODE
if ($exitCode -eq 0) {
    Write-Host "Build finished successfully." -ForegroundColor Green
} else {
    Write-Host "Build failed with exit code: $exitCode" -ForegroundColor Red
}

exit $exitCode
