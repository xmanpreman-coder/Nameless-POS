# Setup Guide - Nameless.POS Desktop

## Langkah-langkah Setup

### 1. Install Dependencies

```bash
cd electron-app
npm install
```

### 2. Konfigurasi GitHub Releases (untuk Auto-Update)

#### A. Buat GitHub Repository

1. Buat repository baru di GitHub (misalnya: `nameless-pos-desktop`)
2. Copy repository URL

#### B. Edit package.json

Edit bagian `publish` di `package.json`:

```json
"publish": [
  {
    "provider": "github",
    "owner": "YOUR_GITHUB_USERNAME",
    "repo": "nameless-pos-desktop",
    "releaseType": "release"
  }
]
```

#### C. Buat GitHub Personal Access Token

1. Buka GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token dengan scope: `repo`
3. Copy token

#### D. Set Environment Variable

**Windows (PowerShell):**
```powershell
$env:GH_TOKEN="your_github_token_here"
```

**Windows (CMD):**
```cmd
set GH_TOKEN=your_github_token_here
```

**Linux/Mac:**
```bash
export GH_TOKEN=your_github_token_here
```

### 3. Build Aplikasi

```bash
npm run build:win
```

File installer akan ada di folder `dist/`:
- `Nameless.POS Setup 1.0.0.exe` (Installer)
- `Nameless.POS-1.0.0-portable.exe` (Portable)

### 4. Publish ke GitHub Releases

Setelah build, publish otomatis ke GitHub:

```bash
npm run build
```

Atau manual upload ke GitHub Releases:
1. Buka GitHub repository
2. Klik "Releases" → "Create a new release"
3. Tag version: `v1.0.0`
4. Upload file:
   - `Nameless.POS Setup 1.0.0.exe`
   - `latest.yml` (dari folder dist)

### 5. Testing Auto-Update

1. Install aplikasi dari installer
2. Jalankan aplikasi
3. Untuk test update:
   - Buat release baru di GitHub dengan version lebih tinggi (misalnya v1.0.1)
   - Upload installer baru
   - Aplikasi akan otomatis detect dan download update

## Konfigurasi Laravel URL

### Development
Default: `http://localhost:8000`

Untuk mengubah, edit `main.js`:
```javascript
const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost:8000';
```

Atau set environment variable:
```bash
export LARAVEL_URL=http://localhost:8000
npm start
```

### Production
Untuk production, pastikan Laravel application:
1. Berjalan di server yang accessible
2. URL sudah benar di `main.js`
3. SSL certificate valid (jika menggunakan HTTPS)

## Icon Aplikasi

1. Siapkan icon file:
   - `icon.ico` (untuk Windows, 256x256 atau lebih besar)
   - `icon.png` (untuk macOS/Linux, 512x512 atau lebih besar)

2. Letakkan di folder `build/`:
   ```
   build/
   ├── icon.ico
   └── icon.png
   ```

3. Icon akan otomatis digunakan saat build

## Update Version

Untuk update version aplikasi:

1. Edit `package.json`:
```json
"version": "1.0.1"
```

2. Build ulang:
```bash
npm run build:win
```

3. Publish ke GitHub Releases dengan tag yang sesuai

## Troubleshooting

### Error: Cannot find module 'electron'

```bash
npm install electron --save-dev
```

### Error: electron-builder not found

```bash
npm install electron-builder --save-dev
```

### Build gagal dengan error code signing

Edit `package.json`, tambahkan:
```json
"win": {
  "verifyUpdateCodeSignature": false
}
```

### Auto-update tidak bekerja

1. Pastikan `latest.yml` ada di GitHub Releases
2. Pastikan file installer ada di GitHub Releases
3. Pastikan version di `package.json` lebih kecil dari version di GitHub
4. Cek console untuk error messages

### Aplikasi tidak bisa connect ke Laravel

1. Pastikan Laravel sudah running
2. Cek URL di `main.js`
3. Cek firewall/antivirus
4. Coba akses URL di browser terlebih dahulu

## Development Tips

### Hot Reload (Development)

Install `electron-reloader`:
```bash
npm install --save-dev electron-reloader
```

Tambahkan di `main.js` (hanya untuk development):
```javascript
try {
  require('electron-reloader')(module);
} catch (err) {}
```

### Debug Mode

Untuk melihat DevTools saat development, edit `main.js`:
```javascript
if (isDev) {
  mainWindow.webContents.openDevTools();
}
```

## Production Checklist

- [ ] Icon aplikasi sudah disiapkan
- [ ] Version number sudah benar
- [ ] GitHub token sudah di-set
- [ ] Laravel URL sudah benar
- [ ] Build berhasil tanpa error
- [ ] Installer bisa diinstall
- [ ] Aplikasi bisa connect ke Laravel
- [ ] Auto-update sudah ditest
- [ ] File `latest.yml` ada di GitHub Releases

