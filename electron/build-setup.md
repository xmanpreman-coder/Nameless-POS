# Nameless POS Desktop - Build & Update Setup

## 🚀 Quick Start Desktop App

### 1. Install Dependencies
```powershell
cd electron
npm install
```

### 2. Run Desktop App (Development)
```powershell
cd electron
$env:NAMELESS_URL = 'http://localhost:8000'
npm start
```

## 📦 Build for Production

### 1. Build Windows Installer
```powershell
cd electron
npm run build-win
```
Output: `dist/Nameless POS Setup 1.0.0.exe`

### 2. Build for All Platforms
```powershell
cd electron
npm run build-all
```

## 🔄 Auto-Update System

### Setup Update Server Options:

#### Option A: GitHub Releases (FREE)
1. Push code ke GitHub repository
2. Create release dengan tag `v1.0.0`
3. Upload `.exe` file ke GitHub release
4. Users akan dapat update otomatis

#### Option B: Custom Update Server
1. Setup web server untuk host update files
2. Modify `publish` config di `package.json`
3. Upload new versions ke server

#### Option C: Local Network Update
```json
// Di package.json, ganti publish config:
"publish": {
  "provider": "generic", 
  "url": "http://your-server.local/updates/"
}
```

## 📋 Update Process Flow

1. **User opens app** → Auto-check for updates after 3 seconds
2. **Update found** → Download in background + show notification  
3. **Download complete** → Ask user to restart or update later
4. **User clicks restart** → App closes, installs update, reopens

## 🎯 Deployment Strategy

### For Warnet/Business:
```powershell
# 1. Build installer
npm run build-win

# 2. Install di komputer kasir
./dist/Nameless POS Setup 1.0.0.exe

# 3. Update via:
#    - USB drive (copy new installer)
#    - Network share  
#    - GitHub releases (if online)
```

## 🔧 Advanced Configuration

### Environment Variables:
- `NAMELESS_URL`: Laravel server URL (default: http://localhost:8000)
- `ELECTRON_DEV=1`: Open DevTools on start
- `ELECTRON_IS_PACKAGED`: Check if running as packaged app

### Custom Window Settings:
```javascript
// Di main.js, modify createWindow():
const win = new BrowserWindow({
  width: 1440,
  height: 900,
  fullscreen: true,        // Fullscreen mode
  kiosk: true,            // Kiosk mode (can't minimize)
  frame: false,           // Borderless window
  alwaysOnTop: true,      // Stay on top
});
```

## ⚡ Performance Tips

1. **Preload Laravel**: Start Laravel server before Electron
2. **Local SQLite**: Use SQLite instead of MySQL for better performance
3. **Cache static assets**: Enable Laravel asset caching
4. **Minimize window**: Hide when minimized to system tray

## 🛡️ Security

- ✅ Context isolation enabled
- ✅ Node integration disabled  
- ✅ Secure update mechanism
- ✅ Code signing (optional, for trusted installer)

## 🚨 Troubleshooting

**Issue: Update not working**
- Check internet connection
- Verify GitHub/server URL in package.json
- Check console logs for errors

**Issue: App won't start**  
- Ensure Laravel server is running at correct URL
- Check NAMELESS_URL environment variable
- Try running with ELECTRON_DEV=1 to see errors