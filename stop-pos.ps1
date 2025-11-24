# Stop Nameless POS Services
Write-Host "🛑 Stopping Nameless POS services..." -ForegroundColor Yellow

# Stop PHP artisan serve
$phpProcesses = Get-Process -Name "php" -ErrorAction SilentlyContinue
if ($phpProcesses) {
    Write-Host "Stopping PHP processes..." -ForegroundColor Yellow
    $phpProcesses | Where-Object { $_.CommandLine -like "*artisan serve*" } | Stop-Process -Force
}

# Stop Node.js (Vite) processes
$nodeProcesses = Get-Process -Name "node" -ErrorAction SilentlyContinue
if ($nodeProcesses) {
    Write-Host "Stopping Node.js processes..." -ForegroundColor Yellow
    $nodeProcesses | Where-Object { $_.CommandLine -like "*vite*" -or $_.CommandLine -like "*npm*" } | Stop-Process -Force
}

Write-Host "✅ All services stopped" -ForegroundColor Green