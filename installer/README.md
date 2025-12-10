# NSIS Installer (Nameless.POS)

Panduan singkat menggunakan script installer NSIS yang ada di folder ini.

Prasyarat
- NSIS: download dan install dari https://nsis.sourceforge.io/Download (pastikan `makensis.exe` ada di PATH)
- Pastikan Anda sudah membangun aplikasi Electron/EXE dan hasilnya ada di folder `dist/`

Langkah membuat installer
1. Pastikan hasil build (portable EXE dan file pendukung) berada di `dist/` pada root project.
2. Buka terminal di root project (`D:\project warnet\Nameless`).
3. Jalankan:
```powershell
cd installer
makensis nameless-installer.nsi
```

Hasilnya: file installer akan dibuat di folder `releases\` (lokasi OutFile di script).

Fitur installer ini
- Menyalin semua file dari `dist/` ke folder instalasi (default: `C:\Program Files\NamelessPOS`).
- Membuat shortcut di Start Menu dan Desktop.
- Membuat Scheduled Task `NamelessPOS_AutoStart` yang menjalankan EXE pada setiap login (agar auto-launch).
- Pada uninstall, scheduled task dan file dihapus.

Catatan keamanan & hak akses
- Installer meminta hak Administrator (RequestExecutionLevel admin) karena membuat scheduled task dan menulis ke Program Files.
- Jika Anda ingin menghindari request admin, ubah target instalasi ke folder user dan hapus pembuatan scheduled task.

Customisasi
- Ubah nama EXE di baris `!define APP_EXE` jika nama file berbeda.
- Anda bisa mengubah OutFile path untuk menyimpan installer di lokasi lain.

Jika Anda mau, saya bisa:
- Tambahkan langkah NSIS untuk otomatis menginstal NSSM dan mendaftarkan sebagai Windows Service.
- Alternatif: buat installer yang juga mengeksekusi migrasi database pertama kali.
