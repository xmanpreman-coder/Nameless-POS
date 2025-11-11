# Instruksi Build Aplikasi Electron

## Status Build

Aplikasi Electron sudah siap untuk di-build. Berikut adalah langkah-langkah untuk membuat file installer .exe:

## Langkah 1: Install Dependencies

```bash
cd electron-app
npm install
```

## Langkah 2: Pastikan Electron Terinstall

Jika ada error "Cannot compute electron version", install electron secara eksplisit:

```bash
npm install electron@27.1.0 --save-dev
```

## Langkah 3: Build Aplikasi

### Opsi A: Menggunakan npx (Recommended)

```bash
npx electron-builder --win
```

### Opsi B: Menggunakan npm script

```bash
npm run build:win
```

### Opsi C: Build Portable (tidak perlu install)

```bash
npm run build:win:portable
```

## Catatan Penting

### Icon Aplikasi

File `build/icon.ico` diperlukan untuk build. Jika belum ada:

1. Siapkan icon file (format .ico, minimal 256x256)
2. Letakkan di folder `build/icon.ico`
3. Atau hapus referensi icon dari `package.json` untuk build tanpa icon

### File Output

Setelah build berhasil, file akan ada di folder `dist/`:
- `Nameless.POS Setup 1.0.0.exe` (Installer NSIS)
- `Nameless.POS-1.0.0-portable.exe` (Portable version)
- `latest.yml` (untuk auto-update)

### Troubleshooting

**Error: electron-builder not found**
```bash
npm install electron-builder --save-dev
```

**Error: Cannot compute electron version**
```bash
npm install electron@27.1.0 --save-dev
```

**Error: Icon not found**
- Hapus baris `"icon": "build/icon.ico"` dari `package.json` bagian `win`
- Atau buat file icon.ico di folder `build/`

**Build terlalu lama**
- Proses build akan download Electron binary pertama kali (sekitar 100MB+)
- Pastikan koneksi internet stabil

## Quick Build Command

Untuk build cepat tanpa icon:

```bash
cd electron-app
npm install
npx electron-builder --win --config.win.icon=null
```

## Testing Aplikasi

Setelah build, test aplikasi:

1. Pastikan Laravel application running di `http://localhost:8000`
2. Jalankan file installer atau portable .exe
3. Aplikasi akan membuka window dengan Laravel app

## Next Steps

Setelah build berhasil:
1. Test installer di Windows
2. Setup GitHub Releases untuk auto-update (lihat SETUP.md)
3. Distribute file installer ke users

