# 🖥️ Nameless POS Desktop Application Guide

## 📋 Overview

Aplikasi Nameless POS sudah dilengkapi dengan **Electron wrapper** yang memungkinkan:
- ✅ Berjalan sebagai aplikasi desktop native
- ✅ Auto-update tanpa install ulang
- ✅ Akses printer thermal langsung
- ✅ Offline capability (dengan SQLite)
- ✅ Cross-platform (Windows, Mac, Linux)

## 🚀 Quick Start

### Method 1: Simple Start (Recommended)
```powershell
.\start-pos-desktop.ps1
```
Script ini akan:
1. Start Laravel server otomatis
2. Start Vite untuk assets
3. Install Electron dependencies (jika belum)
4. Launch desktop app

### Method 2: Manual Start
```powershell
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Assets  
npm run dev

# Terminal 3: Desktop App
cd electron
npm install  # first time only
npm start
```

## 🏗️ Build Production App

### Build Windows Installer
```powershell
.\build-desktop.ps1
# Pilih option 2
```

Output: `electron/dist/Nameless POS Setup 1.0.0.exe`

## 📦 Deployment Options

### Option 1: Single Computer (Warnet/Kasir)
```
1. Install Laravel + Database di komputer kasir
2. Build desktop app: .\build-desktop.ps1
3. Install: electron/dist/Nameless POS Setup 1.0.0.exe
4. App berjalan offline penuh
```

### Option 2: Client-Server (Multiple Cashier)
```
Server: 
- Install Laravel + MySQL
- php artisan serve --host=0.0.0.0 --port=8000

Client (Kasir):
- Install desktop app saja
- Set NAMELESS_URL=http://server-ip:8000
- Multiple kasir akses database yang sama
```

### Option 3: Cloud Deployment
```
Server: Deploy Laravel ke VPS/Cloud
Client: Desktop app akses via internet
Update: Otomatis via GitHub releases
```

## 🔄 Auto-Update System

### Setup GitHub Auto-Update (FREE)
1. **Push ke GitHub**:
```powershell
git add .
git commit -m "Desktop app v1.0.0"
git tag v1.0.0
git push origin main --tags
```

2. **Create GitHub Release**:
   - Go to GitHub → Releases → New Release
   - Tag: `v1.0.0`
   - Upload file: `Nameless POS Setup 1.0.0.exe`
   - Publish release

3. **Update package.json**:
```json
"publish": {
  "provider": "github",
  "owner": "your-username", 
  "repo": "nameless-pos"
}
```

4. **Users get auto-update**:
   - App checks for update every startup
   - Downloads new version automatically
   - Prompts user to restart and install

### Setup Local Network Update
```json
// electron/package.json
"publish": {
  "provider": "generic",
  "url": "http://192.168.1.100/updates/"
}
```

Upload new installer ke web server lokal.

## ⚙️ Configuration

### Environment Variables
```powershell
# Development
$env:NAMELESS_URL = "http://localhost:8000"
$env:ELECTRON_DEV = "1"

# Production  
$env:NAMELESS_URL = "http://server-ip:8000"

# Kiosk Mode
$env:KIOSK_MODE = "1"
```

### Custom Settings
Edit `electron/main.js`:
```javascript
const win = new BrowserWindow({
  width: 1440,
  height: 900,
  fullscreen: true,    // Full screen
  kiosk: true,        // Kiosk mode 
  frame: false,       // No title bar
  alwaysOnTop: true   // Stay on top
});
```

## 🎯 Best Practices for Warnet/Business

### 1. Performance Optimization
```powershell
# Use SQLite for single cashier
DB_CONNECTION=sqlite

# Enable Laravel caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Security Setup
```powershell
# Disable debug in production
APP_DEBUG=false

# Use HTTPS if online
APP_URL=https://your-domain.com

# Restrict access
# Add IP whitelist in .env
```

### 3. Backup Strategy
```powershell
# Auto backup database daily
# Backup ke cloud storage
# Backup ke USB drive
```

### 4. Update Strategy
```
1. Test update di development dulu
2. Deploy ke staging server
3. Update production saat jam sepi
4. Monitor untuk errors
```

## 🛠️ Maintenance

### Daily Tasks
- Check log files: `storage/logs/laravel.log`
- Monitor disk space
- Backup database

### Weekly Tasks  
- Update dependencies: `composer update`
- Clear old logs: `php artisan log:clear`
- Check for app updates

### Monthly Tasks
- Security updates
- Performance optimization
- Feature updates

## 🚨 Troubleshooting

### App Won't Start
```powershell
# Check if Laravel running
curl http://localhost:8000

# Check Electron logs
$env:ELECTRON_DEV = "1"
cd electron && npm start
```

### Update Not Working
```powershell
# Check GitHub release
# Verify internet connection
# Check console logs for errors
```

### Database Issues
```powershell
# Check database file
php artisan migrate:status

# Reset if needed
php artisan migrate:fresh --seed
```

### Performance Issues
```powershell
# Clear all caches
php artisan optimize:clear

# Restart services
Restart-Service MySQL
```

## 📊 Monitoring & Analytics

### Built-in Monitoring
- Laravel logs: `storage/logs/`
- Electron crash reports
- Database query logs

### Performance Metrics
- App startup time
- Database response time
- Memory usage
- Network latency (if client-server)

## 🎉 Benefits Summary

**For Business Owners:**
- ✅ No monthly subscription fees
- ✅ Complete data control
- ✅ Offline operation capability
- ✅ Easy updates without downtime

**For Developers:**
- ✅ Modern web technologies
- ✅ Easy customization
- ✅ Comprehensive APIs
- ✅ Modular architecture

**For Users:**
- ✅ Native desktop experience
- ✅ Fast thermal printing
- ✅ Barcode scanner integration  
- ✅ Auto-update functionality

---

🎯 **Next Steps:**
1. Test desktop app: `.\start-pos-desktop.ps1`
2. Build installer: `.\build-desktop.ps1` 
3. Setup auto-update via GitHub
4. Deploy to production environment