# Nameless.POS Desktop Wrapper (Electron)

This folder contains a minimal Electron wrapper to run the Nameless web app as a desktop application.

Quick start (Windows):

1. Install dependencies (run once):

```powershell
cd electron
npm install
```

2. Run the desktop app (assumes Laravel is running at http://localhost:8000):

```powershell
cd electron
$env:NAMELESS_URL = 'http://localhost:8000'
npm start
```

Notes:
- Developer: set `ELECTRON_DEV=1` to open devtools on start.
- The preload script exposes `window.electronAPI.getPrinters()` and `window.electronAPI.print()` which can be used from the web UI when running inside this wrapper.
