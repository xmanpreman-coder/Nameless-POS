# Nameless.POS Desktop Application

Aplikasi desktop untuk Nameless.POS yang dibangun menggunakan Electron. Aplikasi ini membungkus aplikasi Laravel yang berjalan di localhost menjadi aplikasi desktop dengan fitur auto-update.

## Fitur

- ✅ Membungkus aplikasi Laravel (localhost:8000) ke dalam aplikasi desktop
- ✅ Auto-update menggunakan electron-updater
- ✅ Pengecekan update otomatis setiap 1 menit
- ✅ Download dan install update otomatis
- ✅ Build installer .exe menggunakan electron-builder
- ✅ UI yang profesional dan konsisten

## Prasyarat

- Node.js (v16 atau lebih baru)
- npm atau yarn
- Aplikasi Laravel harus berjalan di `http://localhost:8000`

## Instalasi

1. Install dependencies:
```bash
npm install
```

2. Untuk development, jalankan:
```bash
npm start
```

## Build Aplikasi

### Build untuk Windows (Installer .exe)

```bash
npm run build:win
```

Atau untuk portable version:
```bash
npm run build:win:portable
```

File installer akan berada di folder `dist/`.

## Konfigurasi Auto-Update

### Menggunakan GitHub Releases

1. Edit `package.json` dan ubah bagian `publish`:
```json
"publish": [
  {
    "provider": "github",
    "owner": "YOUR_GITHUB_USERNAME",
    "repo": "YOUR_REPO_NAME",
    "releaseType": "release"
  }
]
```

2. Set environment variable `GH_TOKEN` dengan GitHub Personal Access Token:
```bash
export GH_TOKEN=your_github_token
```

3. Build dan publish:
```bash
npm run build
```

### Menggunakan Server Kustom

Jika ingin menggunakan server sendiri, edit `main.js` dan konfigurasi `autoUpdater.setFeedURL()` dengan URL server Anda.

## Struktur Proyek

```
electron-app/
├── main.js              # Electron main process
├── preload.js           # Preload script untuk security
├── updater.html         # UI untuk update notification
├── package.json         # Dependencies dan build config
├── build/               # Build resources (icons, etc.)
└── dist/                # Output build files
```

## Konfigurasi

### Mengubah URL Laravel

Edit `main.js` dan ubah variabel `LARAVEL_URL`:
```javascript
const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost:8000';
```

Atau set environment variable:
```bash
export LARAVEL_URL=http://localhost:8000
npm start
```

### Mengubah Interval Update Check

Edit `main.js` dan ubah `UPDATE_CHECK_INTERVAL`:
```javascript
const UPDATE_CHECK_INTERVAL = 60000; // 1 menit (dalam milliseconds)
```

## Development

Untuk development dengan auto-reload, install `electron-reloader`:
```bash
npm install --save-dev electron-reloader
```

## Troubleshooting

### Aplikasi tidak bisa connect ke Laravel

1. Pastikan Laravel application sudah running di `http://localhost:8000`
2. Cek firewall settings
3. Pastikan URL di `main.js` sudah benar

### Auto-update tidak bekerja

1. Pastikan `publish` config di `package.json` sudah benar
2. Pastikan GitHub token sudah di-set (jika menggunakan GitHub)
3. Pastikan file `latest.yml` ada di GitHub Releases
4. Cek console untuk error messages

### Build error

1. Pastikan semua dependencies sudah terinstall: `npm install`
2. Pastikan electron-builder sudah terinstall: `npm install -g electron-builder`
3. Cek log error untuk detail

## Security

- Aplikasi menggunakan `contextIsolation` dan `nodeIntegration: false` untuk keamanan
- Preload script digunakan untuk expose API yang aman
- External links dibuka di browser default, bukan di Electron window

## License

MIT

