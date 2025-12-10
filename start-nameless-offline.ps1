# ========================================
# Nameless POS - Offline Mode Launcher
# NO XAMPP NEEDED!
# ========================================

param(
    [switch]$Verbose = $false
)

# Colors
$Color_Info = 'Green'
$Color_Warning = 'Yellow'
$Color_Error = 'Red'
$Color_Success = 'Cyan'

Write-Host ""
Write-Host "========================================" -ForegroundColor $Color_Success
Write-Host "  Nameless POS - Offline Mode Launcher" -ForegroundColor $Color_Success
Write-Host "========================================" -ForegroundColor $Color_Success
Write-Host ""

# Get current directory
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Definition
Set-Location $scriptPath

Write-Host "[INFO] Starting backend (no XAMPP needed)..." -ForegroundColor $Color_Info
Write-Host ""

# Check if PHP exists
try {
    $php = Get-Command php -ErrorAction SilentlyContinue
    if (!$php) {
        Write-Host "[ERROR] PHP not found in PATH!" -ForegroundColor $Color_Error
        Write-Host ""
        Write-Host "Please ensure PHP is installed and added to system PATH." -ForegroundColor $Color_Warning
        Write-Host "Download from: https://www.php.net/downloads" -ForegroundColor $Color_Warning
        Write-Host ""
        Read-Host "Press Enter to exit"
        exit 1
    }
} catch {
    Write-Host "[ERROR] Failed to check PHP: $_" -ForegroundColor $Color_Error
    exit 1
}

Write-Host "[OK] PHP found" -ForegroundColor $Color_Info

# Check if .env exists
if (!(Test-Path ".env")) {
    Write-Host "[INFO] Creating .env file from .env.example..." -ForegroundColor $Color_Info
    
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "[OK] .env created" -ForegroundColor $Color_Success
    } else {
        Write-Host "[WARNING] .env.example not found, creating default .env..." -ForegroundColor $Color_Warning
        $envContent = @"
APP_NAME=Nameless POS
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
DB_DATABASE=./database/app.sqlite
"@
        Set-Content -Path ".env" -Value $envContent
        Write-Host "[OK] Default .env created" -ForegroundColor $Color_Success
    }
}

# Check if database exists
if (!(Test-Path "database/app.sqlite")) {
    Write-Host "[INFO] Creating database..." -ForegroundColor $Color_Info
    & php artisan migrate --force 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Database created" -ForegroundColor $Color_Success
    } else {
        Write-Host "[WARNING] Database migration had issues" -ForegroundColor $Color_Warning
    }
}

# Check for existing process on port 8000
Write-Host "[INFO] Checking for existing servers on port 8000..." -ForegroundColor $Color_Info
$existingProcess = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($existingProcess) {
    Write-Host "[INFO] Found existing server, stopping it..." -ForegroundColor $Color_Warning
    $existingProcess | ForEach-Object {
        Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
    }
    Start-Sleep -Seconds 2
}

# Start PHP development server in background
Write-Host "[INFO] Starting Laravel backend on localhost:8000..." -ForegroundColor $Color_Info

$phpJob = Start-Job -ScriptBlock {
    Set-Location $args[0]
    & php artisan serve --host=localhost --port=8000 2>&1 | Out-Null
} -ArgumentList $scriptPath

Write-Host "[OK] Backend started (Job ID: $($phpJob.Id))" -ForegroundColor $Color_Success

# Wait for backend to be ready
Write-Host "[INFO] Waiting for backend to initialize..." -ForegroundColor $Color_Info

$maxWait = 10
$counter = 0
$backendReady = $false

while ($counter -lt $maxWait) {
    Start-Sleep -Seconds 1
    $counter++
    
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 2 -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            $backendReady = $true
            break
        }
    } catch {
        # Server not ready yet
    }
    
    Write-Host "." -NoNewline -ForegroundColor $Color_Warning
}

Write-Host ""

if ($backendReady) {
    Write-Host "[OK] Backend is ready!" -ForegroundColor $Color_Success
} else {
    Write-Host "[WARNING] Backend startup timeout, but continuing..." -ForegroundColor $Color_Warning
}

Write-Host ""

# Find Chrome
$chromePaths = @(
    "C:\Program Files\Google\Chrome\Application\chrome.exe",
    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe"
)

$chromePath = $null
foreach ($path in $chromePaths) {
    if (Test-Path $path) {
        $chromePath = $path
        break
    }
}

if (!$chromePath) {
    Write-Host "[ERROR] Google Chrome not found!" -ForegroundColor $Color_Error
    Write-Host ""
    Write-Host "Please install Google Chrome from:" -ForegroundColor $Color_Warning
    Write-Host "https://www.google.com/chrome/" -ForegroundColor $Color_Warning
    Write-Host ""
    Write-Host "Or open browser manually and go to: http://localhost:8000" -ForegroundColor $Color_Info
    Write-Host ""
    
    # Keep backend running
    Write-Host "Backend is running on http://localhost:8000" -ForegroundColor $Color_Info
    Write-Host "Press Ctrl+C to stop" -ForegroundColor $Color_Warning
    
    Get-Job | Wait-Job
    exit 1
}

# Launch Chrome
Write-Host "[INFO] Opening Nameless POS in Chrome..." -ForegroundColor $Color_Info
Write-Host "[INFO] Chrome is opening in app mode (fullscreen)..." -ForegroundColor $Color_Info

& "$chromePath" --app=http://localhost:8000 --disable-sync --disable-plugins | Out-Null

Write-Host ""
Write-Host "========================================" -ForegroundColor $Color_Success
Write-Host "   Nameless POS is now running!" -ForegroundColor $Color_Success
Write-Host "========================================" -ForegroundColor $Color_Success
Write-Host ""
Write-Host "Backend:       http://localhost:8000" -ForegroundColor $Color_Info
Write-Host "Mode:          Offline-First PWA" -ForegroundColor $Color_Info
Write-Host "Offline:       YES - Works without internet" -ForegroundColor $Color_Info
Write-Host "XAMPP:         NOT NEEDED" -ForegroundColor $Color_Info
Write-Host ""
Write-Host "Features enabled:" -ForegroundColor $Color_Info
Write-Host "  - Offline mode (works without internet)" -ForegroundColor $Color_Info
Write-Host "  - Auto-sync when online" -ForegroundColor $Color_Info
Write-Host "  - Request queuing" -ForegroundColor $Color_Info
Write-Host "  - Service Worker caching" -ForegroundColor $Color_Info
Write-Host ""
Write-Host "To stop: Close this window OR the Chrome window" -ForegroundColor $Color_Warning
Write-Host ""

# Keep job alive
Get-Job | Wait-Job | Out-Null

# Cleanup
Stop-Job -Job $phpJob -ErrorAction SilentlyContinue
Remove-Job -Job $phpJob -ErrorAction SilentlyContinue

Write-Host "[INFO] Nameless POS closed" -ForegroundColor $Color_Info
