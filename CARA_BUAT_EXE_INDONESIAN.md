# 🚀 Cara Membuat .EXE Nameless POS (Langkah Demi Langkah)

**Bahasa:** Indonesian  
**Status:** Siap untuk dijalankan  
**Output:** File .exe yang bisa langsung didistribusikan  

---

## 📋 Apa Yang Dibutuhkan

### Persiapan Awal

Pastikan sudah menginstall:

1. **Node.js** (LTS) dari https://nodejs.org/
   ```powershell
   node --version      # harus v14 atau lebih tinggi
   npm --version       # harus v6 atau lebih tinggi
   ```

2. **PHP** (sudah ada di project)
   ```powershell
   php --version       # harus v8.0 atau lebih tinggi
   ```

3. **Git** (opsional, tapi recommended)

### Check Instalasi

Buka PowerShell di folder project:
```powershell
cd "D:\project warnet\Nameless"

# Cek versi
node --version
npm --version
php --version
```

---

## ✅ Proses Build .EXE (Cara Termudah)

### Step 1: Buka PowerShell di Project Root

```powershell
cd "D:\project warnet\Nameless"
```

### Step 2: Jalankan Build Script

```powershell
# Kasih permission
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Jalankan script
.\build-exe.ps1
```

**Script ini akan:**
- Install Node dependencies ✅
- Install Electron Builder ✅
- Clear Laravel caches ✅
- Optimize Laravel ✅
- Build .exe file ✅

**Waktu:** 2-5 menit (tergantung internet)

### Step 3: Tunggu Selesai

Output akan seperti ini:
```
✅ Build Complete!

📦 Output files:
   - Nameless POS-1.0.0-portable.exe (250 MB)
   - Nameless POS-1.0.0.exe (120 MB)

🚀 You can now distribute the .exe file!
```

### Step 4: Test .EXE

Cari file di:
```
D:\project warnet\Nameless\dist\
├── Nameless POS-1.0.0-portable.exe    ← Pakai ini (single file)
├── Nameless POS-1.0.0.exe             ← Atau ini (installer)
```

Double-klik dan test aplikasinya.

---

## 🔧 Build Manual (Jika Script Gagal)

### Step 1: Install Node Packages

```powershell
cd "D:\project warnet\Nameless"
npm install
```

**Output:** Folder `node_modules` tercipta

### Step 2: Install Electron Builder

```powershell
npm install -g electron-builder
```

### Step 3: Siapkan Laravel

```powershell
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize
```

### Step 4: Build dengan npm

```powershell
npm run dist
```

Atau untuk portable saja (lebih cepat):
```powershell
electron-builder --win portable
```

---

## 📦 Jenis File Output

### 1. Portable .EXE (Recommended)

```
Nameless POS-1.0.0-portable.exe (250-300 MB)
```

**Karakteristik:**
- ✅ Single file (tidak perlu install)
- ✅ Bisa dijalankan dari mana saja (USB, cloud, etc)
- ✅ Data disimpan di `C:\Users\[Username]\AppData\Roaming\Nameless POS`
- ✅ Lebih mudah untuk distribution

**Cara pakai:**
1. Kirim file .exe ke user (via email, USB, cloud)
2. User double-click
3. Aplikasi langsung jalan

### 2. Installer .EXE

```
Nameless POS-1.0.0.exe (120 MB installer)
```

**Karakteristik:**
- ✅ Traditional Windows installer
- ✅ Membuat desktop shortcut otomatis
- ✅ Add to Start Menu otomatis
- ✅ Support uninstall dari Control Panel

**Cara pakai:**
1. Kirim installer .exe ke user
2. User run installer
3. Ikuti setup wizard
4. Desktop shortcut otomatis terbuat

---

## 💡 Perbedaan Singkat

| Aspek | Portable | Installer |
|-------|----------|-----------|
| File Size | 250-300 MB | 120 MB (+ 150 MB install) |
| Install | Tidak perlu | Perlu install |
| Shortcut | Manual create | Otomatis |
| Distribution | Lebih mudah | Lebih profesional |
| USB Ready | Ya | Ya (setelah install) |

**Rekomendasi:** Pakai Portable untuk kemudahan, Installer untuk profesionalitas.

---

## 🎯 Langkah Selanjutnya Setelah Build

### 1. Test di PC Lain (PENTING!)

**Test di PC tanpa Laravel/PHP terinstall:**
- Copy .exe ke komputer lain
- Double-click
- Pastikan semua fitur jalan:
  - Login berhasil
  - Database terbuat
  - Printer settings accessible
  - Checkout function works

### 2. Create Installation Guide untuk User

Buat dokumen untuk user berisi:
- Cara install/run .exe
- Cara setup printer
- Cara create user baru
- Cara customize settings

### 3. Database & First-Time Setup

Ketika .exe jalan pertama kali:
- ✅ Database SQLite otomatis terbuat
- ✅ Migrations otomatis jalan
- ✅ Admin user otomatis create (username: admin, password: password)

**User harus:**
1. Login dengan admin/password
2. Change password di Profile
3. Setup printer (Settings → Printer Settings)
4. Create staff accounts

### 4. Backup & Version Control

```powershell
# Rename berdasarkan versi
ren "Nameless POS-1.0.0-portable.exe" "Nameless POS v1.0 (2025-11-24).exe"

# Copy ke backup location
cp "Nameless POS v1.0 (2025-11-24).exe" "D:\Backup\Nameless POS\"

# Upload ke cloud (OneDrive, Google Drive, etc)
```

---

## 🔍 Troubleshooting

### Error: "npm is not recognized"

**Solusi:**
1. Install Node.js dari https://nodejs.org/
2. Restart PowerShell
3. Check: `npm --version`

### Error: "php is not recognized"

**Solusi:**
1. PHP sudah ada di project
2. Pastikan Command Prompt bisa akses PHP
3. Atau add PHP path ke Environment Variables:
   - System Properties → Environment Variables → PATH → Add PHP folder

### Error: "node-pre-gyp build failed"

**Solusi:**
```powershell
# Install Windows Build Tools
npm install -g windows-build-tools

# Tunggu ~5 menit
# Retry build
npm run dist
```

### .EXE Terbuat tapi App Tidak Load

**Check:**
1. Database file ada di `%APPDATA%\Nameless POS\database\database.sqlite`
2. Cek console log (Ctrl+Shift+I dalam app)
3. Set `ELECTRON_DEV=1` untuk debug mode

### Build Hang / Tidak Progress

**Solusi:**
```powershell
# Stop process
Ctrl+C

# Clear cache dan retry
npm cache clean --force
npm run dist 2>&1 | tee build.log

# Check build.log untuk detail error
```

---

## 🚀 Optimization Tips

### 1. Reduce Build Time

```powershell
# Build hanya portable (faster)
electron-builder --win portable

# Hasilnya: Nameless POS-1.0.0-portable.exe
```

### 2. Customize App Name

Edit `package-electron.json`:
```json
{
  "productName": "Nameless POS - Resto Edition",
  "name": "nameless-pos-resto"
}
```

Rebuild dengan nama baru.

### 3. Add Custom Icon

1. Buat icon 512x512 PNG
2. Convert ke .ico format
3. Copy ke `assets/icon.ico`
4. Rebuild

### 4. Increase Version

Edit `package-electron.json`:
```json
{
  "version": "1.0.1"  // Changed from 1.0.0
}
```

Rebuild:
```powershell
npm run dist
# Output: Nameless POS-1.0.1-portable.exe
```

---

## 📊 Build Output Breakdown

Ketika build selesai, di folder `dist/` ada:

```
dist/
├── Nameless POS-1.0.0-portable.exe     ← GUNAKAN INI (single file)
├── Nameless POS-1.0.0.exe              ← ATAU INI (installer)
├── Nameless POS Setup 1.0.0.exe        ← Sama dengan .exe
├── builder-effective-config.yaml       ← Build config (ignore)
├── win-unpacked/                       ← Unpacked files (ignore)
└── [other artifacts]
```

**Yang penting:** `.exe` files saja

---

## ✅ Checklist Sebelum Distribution

- [ ] Build selesai tanpa error
- [ ] .exe file ada di `dist/` folder
- [ ] .exe file bisa di-execute di PC lain
- [ ] Database terbuat otomatis saat pertama run
- [ ] Admin user bisa login
- [ ] Semua module terbuka (Sale, Purchase, Product, etc)
- [ ] Printer settings accessible
- [ ] File upload working (profile avatar, product images)

---

## 🎓 Cara Update Aplikasi

Untuk rilis versi baru:

### Version 1.0.0 → 1.0.1

```powershell
# 1. Edit version di package-electron.json
"version": "1.0.1"

# 2. Jalankan build
npm run dist

# 3. Output baru:
# Nameless POS-1.0.1-portable.exe

# 4. User hanya perlu download file baru
# Data mereka tetap ada di AppData folder
```

---

## 📞 Support

### Check Logs

```powershell
# Laravel logs
.\storage\logs\

# Browser console (dalam app)
Ctrl+Shift+I

# Electron console
npm start ELECTRON_DEV=1
```

### Enable Debug Mode

Edit `.env.production`:
```
APP_DEBUG=true
ELECTRON_DEV=1
```

Rebuild dan lihat console lebih detail.

---

## 🎉 Success!

Jika semuanya berjalan lancar:

```
✅ Nameless POS-1.0.0-portable.exe siap
✅ Ukuran file: 250-300 MB
✅ Tidak perlu install external
✅ Bisa langsung didistribusikan ke user
```

**Next Step:** Double-click .exe dan test semuanya! 🚀

---

**Versi:** 1.0.0  
**Dibuat:** 2025-11-24  
**Bahasa:** Indonesian
