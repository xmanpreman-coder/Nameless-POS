# Build Nameless POS to .exe
# Run: .\build-exe.ps1

Write-Host "🔨 Building Nameless POS Desktop Application..." -ForegroundColor Cyan

# 1. Install Node dependencies
Write-Host "`n1️⃣  Installing Node dependencies..." -ForegroundColor Yellow
npm install

# 2. Install Electron Builder globally
Write-Host "`n2️⃣  Installing Electron Builder..." -ForegroundColor Yellow
npm install electron-builder -g

# 3. Clear Laravel caches
Write-Host "`n3️⃣  Clearing Laravel caches..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 4. Optimize Laravel
Write-Host "`n4️⃣  Optimizing Laravel..." -ForegroundColor Yellow
php artisan optimize

# 5. Build .exe
Write-Host "`n5️⃣  Building .exe file (this may take 2-5 minutes)..." -ForegroundColor Yellow
npm run dist

# 6. Check if build was successful
if (Test-Path ".\dist\*.exe") {
    Write-Host "`n✅ Build Complete!" -ForegroundColor Green
    Write-Host "`n📦 Output files:" -ForegroundColor Cyan
    Get-ChildItem ".\dist\*.exe" | ForEach-Object {
        Write-Host "   - $($_.Name) ($([math]::Round($_.Length/1MB, 2)) MB)" -ForegroundColor Green
    }
    Write-Host "`n🚀 You can now distribute the .exe file!" -ForegroundColor Green
} else {
    Write-Host "`n❌ Build failed. Check output above for errors." -ForegroundColor Red
}
