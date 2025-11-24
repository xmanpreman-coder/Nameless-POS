# 🖥️ Nameless POS Desktop Application (.exe) Builder Guide

**Status:** Ready to build Electron desktop application  
**Output:** Single .exe file (portable + installer)  
**Target:** Windows 10/11 64-bit  
**Size:** ~200-300 MB

---

## 📋 Prerequisites

Ensure your system has:

```powershell
# Check Node.js
node --version      # v14+ required

# Check npm
npm --version       # v6+ required

# Check PHP
php --version       # v8.0+ required

# Check Composer
composer --version  # v2.0+ required
```

If missing, download from:
- **Node.js:** https://nodejs.org/ (LTS recommended)
- **PHP:** Already installed (via Laravel dev environment)

---

## 🚀 Quick Build (Recommended)

### Option 1: One-Click Build (PowerShell)

```powershell
cd "D:\project warnet\Nameless"
.\build-exe.ps1
```

This script:
1. ✅ Installs Node dependencies
2. ✅ Installs Electron Builder
3. ✅ Clears Laravel caches
4. ✅ Optimizes Laravel
5. ✅ Builds .exe files
6. ✅ Outputs to `.\dist\` folder

**Time:** 2-5 minutes depending on internet speed

---

## 🔧 Manual Build Steps

If the script doesn't work, follow these steps:

### Step 1: Install Node Dependencies

```powershell
cd "D:\project warnet\Nameless"
npm install
```

### Step 2: Install Electron Builder Globally

```powershell
npm install -g electron-builder
```

### Step 3: Prepare Laravel

```powershell
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize
```

### Step 4: Build .exe

```powershell
npm run dist
```

Or for just portable .exe (faster):

```powershell
electron-builder --win portable
```

### Step 5: Find Your .exe

Output files in:
```
D:\project warnet\Nameless\dist\
├── Nameless POS-1.0.0-portable.exe (single file, no install needed)
├── Nameless POS-1.0.0.exe (installer with setup wizard)
└── Nameless POS Setup 1.0.0.exe (same as above)
```

---

## 📦 What's Included in .exe

✅ **Complete Laravel Application**
- All PHP files
- All database files (SQLite)
- All configuration files
- All module code

✅ **Embedded PHP Server**
- Runs on localhost:8000
- Auto-starts with app
- No external dependencies

✅ **Electron Wrapper**
- Desktop integration
- Auto-updates support
- Printer access
- System tray icon

---

## 💾 Distribution

### Option A: Portable .exe (Single File)

**File:** `Nameless POS-1.0.0-portable.exe`

- Size: ~300 MB
- No installation required
- Double-click to run
- Saves data in `%APPDATA%\Nameless POS\` by default

**Advantages:**
- Simple distribution
- Works from USB drive
- No admin rights needed for launch

**How to use:**
1. Send user the `.exe` file via USB, email, or cloud storage
2. User double-clicks it
3. App starts immediately

### Option B: Installer .exe

**File:** `Nameless POS-1.0.0.exe`

- Size: ~150 MB (compressed)
- Traditional Windows installer
- Desktop shortcut created
- Start Menu entry added

**Advantages:**
- Professional appearance
- Familiar Windows installation
- Auto-update support possible
- Uninstall via Control Panel

**How to use:**
1. Send user the installer `.exe`
2. User runs installer
3. Follows setup wizard
4. Desktop shortcut created automatically

---

## 🔐 First Run Settings

When user opens app for first time:

1. **Database Setup** (automatic)
   - SQLite database created
   - Migrations run automatically
   - Schema initialized

2. **Printer Configuration**
   - User can configure printers (Settings → Printer Settings)
   - Test connection for each printer
   - Set default printer

3. **User Setup**
   - First admin user created via seeder
   - Default username: `admin` / password: `password`
   - Change password on first login

---

## 🔄 Update Process

To create updated .exe:

```powershell
# Update version in package-electron.json
{
  "version": "1.0.1"  # Changed from 1.0.0
}

# Rebuild
npm run dist

# .exe will be named: Nameless POS-1.0.1-portable.exe
```

---

## 🐛 Troubleshooting

### Build Fails with "node-pre-gyp" Error

```powershell
# Install build tools
npm install --global windows-build-tools

# Then retry build
npm run dist
```

### Build Fails with PHP Error

```powershell
# Ensure PHP is in PATH
php --version

# If not found, add to PATH:
# System Properties → Environment Variables → PATH → Add PHP folder
```

### .exe Runs but App Won't Load

```powershell
# Check electron/main.js has correct port:
# Should be: const url = 'http://localhost:8000'

# Check database file exists:
# C:\Users\[Username]\AppData\Roaming\Nameless POS\database\database.sqlite
```

### Can't Find Output .exe

```powershell
# Check dist folder was created
ls ".\dist"

# If empty, build didn't complete:
npm run dist 2>&1 | tee build.log
# Check build.log for errors
```

---

## 📊 Build Output Explanation

```
D:\project warnet\Nameless\dist\
├── Nameless POS-1.0.0-portable.exe     ← Use this for distribution
├── Nameless POS-1.0.0.exe             ← Or this for installer
├── builder-effective-config.yaml       ← Build configuration used
└── [Other build artifacts]
```

**Recommended for distribution:**
- **Small teams:** Portable .exe (single file)
- **Large organizations:** Installer .exe (professional)

---

## 🎯 Next Steps After Build

1. **Test on Clean PC**
   - Run .exe on computer without Laravel/PHP installed
   - Verify all features work
   - Test printer connections

2. **Create Installation Guide**
   - Document printer setup steps
   - Document user creation
   - Create quick-start video

3. **Backup Distribution**
   - Save .exe to cloud storage (OneDrive, Google Drive)
   - Create version-numbered backups
   - Document which version is deployed

4. **Deploy**
   - Distribute .exe to users
   - No installation needed for portable
   - Users run and app auto-initializes

---

## 📝 Configuration After Build

Users can modify these in app:

1. **Database** - SQLite location (default: `AppData\Roaming\Nameless POS\database\`)
2. **Printers** - Add/remove/test thermal printers
3. **Users** - Create staff accounts with roles
4. **Settings** - Currency, units, business info

Everything persists automatically.

---

## 🆘 Need Help?

### Check Logs

```powershell
# Laravel logs
.\app\logs\laravel.log

# Electron dev console (in app)
# Press: Ctrl+Shift+I (if DEV mode enabled)
```

### Enable Debug Mode

```powershell
# Set in .env.production
APP_DEBUG=true
ELECTRON_DEV=1

# Rebuild
npm run dist
```

---

## ✅ Success Indicators

When build completes successfully:

```
✅ Nameless POS-1.0.0-portable.exe (200-300 MB)
✅ Nameless POS-1.0.0.exe (installer, 150 MB)
✅ No errors in console output
✅ Both .exe files are executable
```

---

**Ready?** Run: `.\build-exe.ps1`

**Questions?** Check logs or enable debug mode above.

**Version:** 1.0.0  
**Last Updated:** 2025-11-24
