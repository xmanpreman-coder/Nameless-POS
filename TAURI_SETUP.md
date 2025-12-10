# Nameless POS - Tauri Desktop App Setup

## Overview

Tauri adalah alternatif dari Electron untuk membuat aplikasi desktop.

| | Electron | Tauri |
|---|----------|-------|
| **Engine** | Chromium (bundled) | System WebView |
| **Backend** | Node.js | Rust |
| **Bundle Size** | ~150MB | ~10MB |
| **Memory** | ~100MB+ | ~30MB |
| **Build Time** | Fast | Slower (Rust compile) |

## Requirements

1. **Rust** - https://rustup.rs/
2. **PHP** - For Laravel backend (sama seperti Electron)
3. **Node.js** - For npm scripts

## Commands

### Development Mode
Start Laravel + Tauri in dev mode:
```bash
npm run tauri:dev
```

Ini akan:
1. Jalankan `php artisan serve` di background
2. Buka window Tauri yang load http://127.0.0.1:8000

### Build Production
Build executable (.exe installer):
```bash
npm run tauri:build
```

Output: `src-tauri/target/release/bundle/`

## Comparison with Electron

| Command | Electron | Tauri |
|---------|----------|-------|
| Dev | `npm start` | `npm run tauri:dev` |
| Build | `npm run dist` | `npm run tauri:build` |
| Output | `dist/` | `src-tauri/target/release/bundle/` |

## File Structure

```
Nameless/
├── electron/           # Electron files (existing)
│   ├── main.js
│   └── LaravelServer.js
│
├── src-tauri/          # Tauri files (new)
│   ├── src/
│   │   ├── main.rs     # Rust entry point
│   │   └── lib.rs
│   ├── tauri.conf.json # Config
│   ├── Cargo.toml      # Rust dependencies
│   └── icons/          # App icons
│
└── package.json        # Contains both Electron & Tauri scripts
```

## Configuration

**File:** `src-tauri/tauri.conf.json`

```json
{
  "build": {
    "devUrl": "http://127.0.0.1:8000",
    "beforeDevCommand": "php artisan serve"
  },
  "app": {
    "windows": [{
      "title": "Nameless POS - Tauri",
      "width": 1200,
      "height": 800
    }]
  }
}
```

## Notes

- Tauri uses **system WebView** (Edge WebView2 on Windows)
- PHP masih dibutuhkan di PATH (sama seperti Electron)
- First build akan lama karena Rust compile (~5-10 menit)
- Subsequent builds lebih cepat

## Troubleshooting

### WebView2 not found
Install Microsoft Edge WebView2:
https://developer.microsoft.com/en-us/microsoft-edge/webview2/

### Rust compile error
Update Rust:
```bash
rustup update
```

### Port 8000 already in use
Stop Laravel server yang sedang berjalan, atau edit port di `.env`
