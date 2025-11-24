# Auto Start Nameless POS - Klik Langsung Buka
# This script starts all services and opens the app automatically

Write-Host "🏪 Starting Nameless POS Auto..." -ForegroundColor Green

# Function to check if process is running
function IsProcessRunning($processName) {
    return (Get-Process -Name $processName -ErrorAction SilentlyContinue) -ne $null
}

# Function to check if port is in use
function IsPortInUse($port) {
    $connection = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
    return $connection -ne $null
}

# 1. Check and start Laravel server
if (-not (IsPortInUse 8000)) {
    Write-Host "Starting Laravel server..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-WindowStyle Hidden", "-Command cd '$PWD'; php artisan serve" -WindowStyle Hidden
    Start-Sleep 3
} else {
    Write-Host "Laravel server already running" -ForegroundColor Green
}

# 2. Check and start Vite assets
if (-not (IsPortInUse 5176)) {
    Write-Host "Starting Vite assets..." -ForegroundColor Yellow
    Start-Process powershell -ArgumentList "-WindowStyle Hidden", "-Command cd '$PWD'; npm run dev" -WindowStyle Hidden
    Start-Sleep 2
} else {
    Write-Host "Vite server already running" -ForegroundColor Green
}

# 3. Wait for services to be ready
Write-Host "Waiting for services to start..." -ForegroundColor Yellow
$maxAttempts = 10
$attempt = 0

do {
    Start-Sleep 1
    $attempt++
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000" -TimeoutSec 2 -UseBasicParsing
        $serviceReady = $true
        break
    } catch {
        $serviceReady = $false
    }
} while ($attempt -lt $maxAttempts -and -not $serviceReady)

if ($serviceReady) {
    Write-Host "✅ Services ready! Opening app..." -ForegroundColor Green
    
    # Open in default browser
    Start-Process "http://localhost:8000"
    
    Write-Host "🎉 Nameless POS is now running!" -ForegroundColor Green
    Write-Host "URL: http://localhost:8000" -ForegroundColor Cyan
    Write-Host "" 
    Write-Host "To close all services, run: .\stop-pos.ps1" -ForegroundColor Gray
} else {
    Write-Host "❌ Failed to start services" -ForegroundColor Red
    Write-Host "Please check if PHP and Node.js are installed" -ForegroundColor Yellow
}