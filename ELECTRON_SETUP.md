# Nameless POS - Electron + Laravel Desktop App Setup

## How PHP Integration Works

### Requirements for Users:
- **PHP 8.0+** must be installed and accessible from system PATH
- Windows users can use: XAMPP, WampServer, or standalone PHP portable

### How It Works:
1. **Electron app starts** → calls `LaravelServer.js`
2. **LaravelServer detects:**
   - Searches for system PHP (`php` command in PATH)
   - Tries to start Laravel dev server on ports 8000-8010 (picks first available)
3. **On success:**
   - Returns the port number to `main.js`
   - Electron window loads `http://127.0.0.1:{port}`
4. **On failure:**
   - Shows error dialog
   - User must install PHP and add it to PATH

### Current Architecture:

```
electron/main.js
    ↓
    └─→ LaravelServer.js
         ├─→ Finds system PHP from PATH
         ├─→ Runs: php artisan serve --host=127.0.0.1 --port=PORT
         ├─→ Tries ports 8000-8010 (if conflicts)
         └─→ Returns port number back to main.js
    ↓
    └─→ main.js loads http://127.0.0.1:{port}
```

## Files Modified

### 1. `electron/LaravelServer.js`
- Added `getPort()` method to return active port
- Uses system PHP only (bundled PHP removed)
- Tries multiple ports to handle conflicts
- Proper error handling and logging

### 2. `electron/main.js`
- Updated to use `laravelServer.getPort()` instead of hardcoded port
- Loads correct URL: `http://127.0.0.1:${port}`

## Building the Portable EXE

### For Portable/Standalone Build:
```powershell
npm run dist
```

This creates:
- `dist/Nameless POS 1.0.0.exe` - Portable executable (no installer)
- `dist/Nameless POS Setup 1.0.0.exe` - Windows installer

### Build Notes:
- The EXE **does NOT include PHP** - users must have PHP installed
- Future: Could bundle PHP using `build/php/` folder if configured
- Currently uses system PHP for maximum compatibility

## Development Testing

### Start in Dev Mode:
```powershell
npm start
```

Watch for startup messages:
```
[LaravelServer] Using system PHP from PATH
[LaravelServer] Starting Laravel server...
[LaravelServer:8000] INFO  Server running on [http://127.0.0.1:8000].
[LaravelServer] Started on port 8000
Laravel server started successfully on port 8000
```

Then the Electron window will open and load the app.

## Troubleshooting

### Error: "Failed to start Laravel server"
**Cause:** PHP not installed or not in PATH

**Solution:**
1. Install PHP (XAMPP, WampServer, or portable PHP)
2. Add PHP to system PATH:
   - Windows: Set Environment Variable `PATH` to include PHP folder
   - Verify: Open CMD and run `php -v`

### Error: "Failed to listen on 127.0.0.1:8000"
**Cause:** Port 8000-8010 are all in use

**Solution:**
1. Check what's using the ports: `netstat -aon | find ":8000"`
2. Kill the process: `taskkill /PID {process_id} /F`
3. Or wait for processes to release the port

### Electron window shows blank/error
**Cause:** Laravel server didn't start properly

**Solution:**
1. Check database configuration in `.env.production`
2. Run migrations: `php artisan migrate:fresh --seed`
3. Check logs: `storage/logs/laravel.log`

## Next Steps

### Option 1: Bundle PHP (For True Portable App)
- Download PHP portable from windows.php.net
- Place in `build/php/` folder
- Update `LaravelServer.js` to use bundled PHP with error fallback to system PHP
- Would make EXE fully standalone (300MB+)

### Option 2: Use Electron-Builder Native Dependencies
- Configure electron-builder to include PHP in the ASAR archive
- More complex but keeps app self-contained

### Option 3: Current Setup (Recommended)
- Keep using system PHP (what we have now)
- Users must install PHP once
- Minimal EXE size (~370MB)
- Maximum compatibility and updates

## File Structure

```
Nameless/
├── electron/
│   ├── main.js                 # App entry point, starts Laravel server
│   ├── LaravelServer.js        # Manages PHP + artisan serve process
│   ├── DatabaseManager.js      # Database initialization (optional)
│   └── preload.js             # Security preload script
├── build/
│   ├── php/                   # (Optional) Bundled PHP for portable builds
│   └── (other assets)
├── package.json               # Electron build config
├── artisan                    # Laravel CLI
└── (Laravel app files)
```

## Environment

- **PHP:** System PHP from PATH (8.0+)
- **Node:** For building only
- **Electron:** 39.2.3
- **Laravel:** 10.x
- **Build Tool:** Electron Builder 26.0.12
