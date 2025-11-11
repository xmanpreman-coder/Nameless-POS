# Quick Start Guide

## Instalasi Cepat

### 1. Install Dependencies

```bash
cd electron-app
npm install
```

### 2. Test Aplikasi (Development)

Pastikan Laravel application sudah running di `http://localhost:8000`, lalu:

```bash
npm start
```

### 3. Build Installer

```bash
npm run build:win
```

File installer akan ada di folder `dist/`.

## Konfigurasi Auto-Update (Opsional)

### Untuk Development
Auto-update **TIDAK AKTIF** saat development (mode dev).

### Untuk Production

1. Edit `package.json`, ubah bagian `publish`:
```json
"publish": [
  {
    "provider": "github",
    "owner": "YOUR_USERNAME",
    "repo": "YOUR_REPO"
  }
]
```

2. Set GitHub token:
```bash
# Windows PowerShell
$env:GH_TOKEN="your_token"

# Windows CMD
set GH_TOKEN=your_token

# Linux/Mac
export GH_TOKEN=your_token
```

3. Build dan publish:
```bash
npm run build
```

## Mengubah URL Laravel

Edit `main.js`, baris 7:
```javascript
const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost:8000';
```

Atau set environment variable:
```bash
export LARAVEL_URL=http://your-laravel-url.com
npm start
```

## Troubleshooting Cepat

**Aplikasi tidak bisa connect ke Laravel?**
- Pastikan Laravel sudah running
- Cek URL di `main.js`
- Coba akses URL di browser dulu

**Build error?**
- Pastikan sudah `npm install`
- Cek Node.js version (minimal v16)

**Auto-update tidak bekerja?**
- Pastikan sudah di production mode (bukan dev)
- Pastikan GitHub token sudah di-set
- Pastikan `latest.yml` ada di GitHub Releases

## File Penting

- `main.js` - Main Electron process
- `preload.js` - Security bridge
- `updater.html` - Update UI
- `package.json` - Dependencies & build config

## Next Steps

Lihat `SETUP.md` untuk konfigurasi lengkap dan `README.md` untuk dokumentasi lengkap.

