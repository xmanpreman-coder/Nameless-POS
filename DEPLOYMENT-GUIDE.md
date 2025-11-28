# Nameless POS - Deployment Guide

## Quick Start

### Option 1: Browser-Based (Recommended for Now)
1. Ensure **PHP 8.0+** is installed on your system
   - Check: Open CMD and type `php -v`
   - If not found, install XAMPP or WampServer
   
2. Double-click: **`START-NAMELESS-POS.bat`**
   - Laravel server starts automatically
   - Browser opens to `http://127.0.0.1:8000`
   - Done! ✓

### Option 2: Manual Start
```bash
cd d:\project warnet\Nameless
php artisan serve --host=127.0.0.1 --port=8000
```
Then open browser to: `http://127.0.0.1:8000`

---

## System Requirements

- **PHP 8.0 or higher** (required)
- **MySQL 5.7+** or compatible database
- **Windows 7+**, **macOS**, or **Linux**
- **2GB RAM minimum**
- **500MB free disk space**

### PHP Installation Options

#### Option A: XAMPP (Easiest)
1. Download: https://www.xampp.com/download.html
2. Install to default location
3. Start XAMPP Control Panel
4. Click "Start" next to Apache & MySQL

#### Option B: Standalone PHP
1. Download: https://windows.php.net/download/
2. Extract to folder (e.g., `C:\PHP`)
3. Add to PATH: `Windows Key + Pause → Advanced → Environment Variables → PATH → Add PHP folder`
4. Restart CMD and verify: `php -v`

#### Option C: WampServer
1. Download: https://www.wampserver.com/en/
2. Install and start WampServer
3. PHP will be available in PATH automatically

---

## Architecture

### Current Status:
- ✅ **LaravelServer**: Detects system PHP and starts `php artisan serve`
- ✅ **Dynamic Ports**: Tries ports 8000-8010 to avoid conflicts
- ✅ **Browser Launch**: Automatically opens app in default browser
- ✅ **Dev Mode Works**: `npm start` successfully starts the app

### Pending (Electron Build Issues):
- ⏳ **Electron EXE**: Build system being interrupted by signing process
  - Temporary workaround: Use batch script launcher instead
  - Will be fixed once build environment issue is resolved

---

## Troubleshooting

### "PHP not found" error
**Solution:** PHP must be in system PATH
```cmd
REM Verify PHP is in PATH
php -v

REM If not found, add PHP folder to PATH environment variable
```

### Port 8000 already in use
**Solution:** Kill the process using port 8000
```cmd
netstat -ano | findstr ":8000"
taskkill /pid {PID} /f
```
Then try again.

### "Connection refused" or "Cannot connect"
**Solution:** Laravel server not started
1. Check that batch script doesn't show error
2. Verify MySQL is running
3. Check `.env.production` database settings

### Database errors
**Solution:** Run migrations
```cmd
php artisan migrate:fresh --seed
```

---

## Directory Structure

```
Nameless/
├── START-NAMELESS-POS.bat      ← Double-click to start
├── electron/
│   ├── main.js                 ← Electron entry point
│   ├── LaravelServer.js        ← PHP artisan launcher
│   └── preload.js              ← Security preload
├── app/                        ← Laravel app directory
├── Modules/                    ← Business modules
├── config/                     ← Configuration
├── database/                   ← Migrations & seeders
├── routes/                     ← API & web routes
├── .env.production             ← Production environment
└── artisan                     ← Laravel CLI tool
```

---

## Development

### Run in Development Mode
```bash
cd d:\project warnet\Nameless
npm start
```

This:
1. Starts Electron window
2. Detects system PHP
3. Launches `php artisan serve`
4. Loads app in Electron window
5. Logs all startup info to console

### Run Laravel Server Manually
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Run in Browser Only
```bash
php artisan serve
```
Then open: `http://127.0.0.1:8000`

---

## Build Status

### What Works ✅
- Dev mode: `npm start` → Electron window launches successfully
- Laravel server: Starts on available port (8000-8010)
- Browser launcher: Batch script successfully opens app in browser
- Path detection: LaravelServer correctly finds artisan file

### Build Issue ⏳
- Electron portable EXE build interrupted by signing process (SIGINT)
- Temporary solution: Use batch script launcher for distribution
- Expected fix: Resolution of build environment issue

### Release Plan
1. **Immediate**: Distribute via batch script (`START-NAMELESS-POS.bat`)
2. **Next**: Resolve Electron build signing issue
3. **Final**: Release as standalone portable EXE

---

## Distribution

### For Users (Current Method)
1. Provide folder: `Nameless/`
2. Users double-click: `START-NAMELESS-POS.bat`
3. App launches in browser

### Requirements for Users
- PHP 8.0+ installed on their system
- MySQL database setup
- Windows/macOS/Linux

---

## Support

For issues or questions:
- Check troubleshooting section above
- Review Laravel logs: `storage/logs/laravel.log`
- Check Electron logs: Opens in console when running `npm start`

---

**Last Updated:** November 27, 2025  
**Stack:** Laravel 10 | PHP 8.1+ | Electron 39 | MySQL 5.7+
