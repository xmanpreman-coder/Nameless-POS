# setup.ps1 - Quick Setup Script for Windows

Write-Host "================================"
Write-Host "Nameless POS - Docker Setup"
Write-Host "================================"
Write-Host ""

# Check Docker
Write-Host "[1/5] Checking Docker..." -ForegroundColor Cyan
if (!(Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] Docker not installed!" -ForegroundColor Red
    Write-Host "Download: https://www.docker.com/products/docker-desktop"
    exit 1
}
Write-Host "[OK] Docker found" -ForegroundColor Green

# Check docker-compose
Write-Host "[2/5] Checking docker-compose..." -ForegroundColor Cyan
if (!(Get-Command docker-compose -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] docker-compose not installed!" -ForegroundColor Red
    exit 1
}
Write-Host "[OK] docker-compose found" -ForegroundColor Green

# Build Docker image
Write-Host "[3/5] Building Docker image..." -ForegroundColor Cyan
docker-compose -f docker-compose.dev.yml build
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Build failed!" -ForegroundColor Red
    exit 1
}
Write-Host "[OK] Image built successfully" -ForegroundColor Green

# Start containers
Write-Host "[4/5] Starting containers..." -ForegroundColor Cyan
docker-compose -f docker-compose.dev.yml up -d
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to start containers!" -ForegroundColor Red
    exit 1
}
Write-Host "[OK] Containers started" -ForegroundColor Green

# Wait for app to be ready
Write-Host "[5/5] Waiting for app to be ready..." -ForegroundColor Cyan
Start-Sleep -Seconds 5

Write-Host ""
Write-Host "================================"
Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host "================================"
Write-Host ""
Write-Host "Open browser: http://localhost:8000"
Write-Host ""
Write-Host "Docker commands:"
Write-Host "  docker-compose -f docker-compose.dev.yml ps        # See containers"
Write-Host "  docker-compose -f docker-compose.dev.yml logs -f   # View logs"
Write-Host "  docker-compose -f docker-compose.dev.yml down      # Stop containers"
Write-Host ""
