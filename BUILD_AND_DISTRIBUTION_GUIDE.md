# Nameless POS - Desktop Application

Point of Sale system dengan Laravel 10 + Electron, tersedia dalam 2 format distribusi:

## 📦 Distribution Methods

### 1. **Portable EXE** ⭐ (Recommended)
- **File:** `dist/Nameless POS 1.0.0.exe`
- **Size:** ~234 MB
- **Installation:** None - just run!
- **Requirements:** PHP 8.1+ & MySQL installed on system
- **Build Command:** `npm run dist:portable`

**How to Use:**
1. Download PHP (standalone or XAMPP)
2. Install MySQL database
3. Run `Nameless POS 1.0.0.exe`
4. Electron window opens with Laravel app on `http://127.0.0.1:8000`

### 2. **Development Mode**
- **Command:** `npm start`
- **Port:** Dynamic (8000-8010)
- **Use:** Development & testing

### 3. **Browser + Batch Script** (Alternative)
- **File:** `START-NAMELESS-POS.bat`
- **How:** Double-click → Opens browser at `http://127.0.0.1:8000`
- **Requires:** PHP & Laravel already set up

---

## 🚀 Building Portable EXE

### Prerequisites
- Node.js 18+ (npm 10+)
- Electron 39.2.3
- electron-builder 26.0.12

### Build Steps

```powershell
# 1. Clean previous builds
rm -r dist -Force -ErrorAction SilentlyContinue

# 2. Set environment (skip signing)
$env:CSC_KEY_PASSWORD=''
$env:CSC_LINK=''

# 3. Build portable EXE
npm run dist:portable
```

### Build Configuration
Key files:
- `package.json` - build config
- `electron-builder.yml` - signing & packaging settings
- `customSign.js` - stub signer (skip actual signing)
- `build-nosigint.js` - custom build script with signal handling

### ⚠️ SIGINT Issues
If build is interrupted by "cancelled by SIGINT":

1. **Disable Windows Defender real-time protection** (temporarily during build)
2. **Close other applications** using disk I/O heavily
3. **Use stub signer** instead of signtool.exe (already configured)
4. **Run build script:** `npm run dist:nosigint`

See: `SIGINT_DISABLE_GUIDE.md` for detailed troubleshooting

---

## 📋 Project Structure

```
Nameless-POS/
├── electron/
│   ├── main.js                 # Electron entry point
│   ├── LaravelServer.js        # Laravel server manager
│   └── preload.js              # Context isolation
├── app/                        # Laravel app
├── Modules/                    # Business modules
├── dist/                       # Built EXE output
├── package.json                # npm config & build settings
├── electron-builder.yml        # Electron builder config
├── customSign.js              # Stub signer
└── build-nosigint.js          # Custom build script
```

---

## 🔧 Development

### Start Dev Mode
```powershell
npm start
```
Opens Electron window with Laravel dev server (port 8000-8010)

### Build Web Assets
```powershell
npm run build
```

### Run Tests
```bash
php artisan test
```

---

## 📦 Installation for End Users

### Method 1: Portable EXE (Easiest)
1. Download `Nameless POS 1.0.0.exe`
2. Install PHP 8.1+ on your system
3. Install MySQL database
4. Configure `.env.production` with database details
5. Run EXE

### Method 2: XAMPP/WampServer
1. Install XAMPP (includes PHP + MySQL)
2. Extract `Nameless-POS` to htdocs folder
3. Configure database in `.env.production`
4. Run the batch launcher: `START-NAMELESS-POS.bat`
5. Browser opens to app

### Method 3: Manual Setup
1. Install PHP 8.1+, MySQL
2. Run: `php artisan serve --host=127.0.0.1 --port=8000`
3. Open browser: `http://127.0.0.1:8000`

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| EXE won't start | Check PHP installed & in PATH |
| Port 8000 busy | LaravelServer tries 8000-8010 automatically |
| Database connection error | Update `.env.production` with DB details |
| "No app found" message | Run from project directory with artisan file present |

---

## 📚 Documentation

- **Architecture:** `NAMELESS_POS_ARCHITECTURE.md`
- **Build Issues:** `SIGINT_DISABLE_GUIDE.md`
- **Deployment:** `DEPLOYMENT-GUIDE.md`
- **Printer Setup:** `MULTI_PRINTER_IMPLEMENTATION.md`

---

## ✅ Build Status

- ✅ Portable EXE: Working
- ✅ Dev mode: Working  
- ✅ Laravel server integration: Working
- ✅ Path detection (packaged): Tested
- ✅ Port conflict handling: Tested
- ❌ Code signing: Disabled (not required for portable)

---

**Last Updated:** November 27, 2025  
**Stack:** Laravel 10 | Electron 39 | PHP 8.2+ | Node.js 18+
