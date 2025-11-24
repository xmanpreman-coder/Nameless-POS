# Nameless POS Docker Deployment Script
param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("sqlite", "mysql", "production")]
    [string]$Mode
)

Write-Host "Docker Deployment for Nameless POS" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green

# Check if Docker is running
try {
    docker version | Out-Null
    Write-Host "Docker is running" -ForegroundColor Green
} catch {
    Write-Host "Docker is not running! Please start Docker Desktop" -ForegroundColor Red
    exit 1
}

# Stop any existing containers
Write-Host "Stopping existing containers..." -ForegroundColor Yellow
docker-compose down 2>$null

switch ($Mode) {
    "sqlite" {
        Write-Host "Deploying SQLite single container setup..." -ForegroundColor Cyan
        
        # Ensure database file exists
        if (-not (Test-Path "database/database.sqlite")) {
            Write-Host "Creating SQLite database file..." -ForegroundColor Yellow
            New-Item -ItemType File -Path "database/database.sqlite" -Force
        }
        
        # Build and run
        docker-compose -f docker-compose.sqlite.yaml up -d --build
        
        Write-Host "SQLite setup completed!" -ForegroundColor Green
        Write-Host "Access app at: http://localhost:8000" -ForegroundColor Cyan
    }
    
    "mysql" {
        Write-Host "Deploying MySQL multi-container setup..." -ForegroundColor Cyan
        
        # Check .env file
        if (-not (Test-Path ".env")) {
            Write-Host "Please create .env file first!" -ForegroundColor Red
            exit 1
        }
        
        # Build and run
        docker-compose up -d --build
        
        Write-Host "MySQL setup completed!" -ForegroundColor Green
        Write-Host "Access app at: http://localhost:8000" -ForegroundColor Cyan
        Write-Host "MySQL at: localhost:3306" -ForegroundColor Cyan
    }
    
    "production" {
        Write-Host "Deploying production setup..." -ForegroundColor Cyan
        
        # Security checks
        Write-Host "Checking production readiness..." -ForegroundColor Yellow
        
        $envContent = Get-Content .env -Raw
        if ($envContent -match "APP_DEBUG=true") {
            Write-Host "WARNING: APP_DEBUG should be false in production!" -ForegroundColor Yellow
        }
        
        if ($envContent -match "APP_KEY=`"`"") {
            Write-Host "ERROR: APP_KEY is empty! Run: php artisan key:generate" -ForegroundColor Red
            exit 1
        }
        
        # Build and run
        docker-compose -f docker-compose.production.yaml up -d --build
        
        Write-Host "Production setup completed!" -ForegroundColor Green
        Write-Host "Access app at: http://localhost" -ForegroundColor Cyan
    }
}

# Show running containers
Write-Host "`nRunning containers:" -ForegroundColor Yellow
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

Write-Host "`nUseful commands:" -ForegroundColor Cyan
Write-Host "View logs: docker-compose logs -f" -ForegroundColor Gray
Write-Host "Stop: docker-compose down" -ForegroundColor Gray
Write-Host "Restart: docker-compose restart" -ForegroundColor Gray
Write-Host "Shell access: docker-compose exec web bash" -ForegroundColor Gray