# 📋 CHECKLIST - MENJALANKAN NAMELESS POS DI PC LAIN

## 📦 FILE YANG DIPERLUKAN

**File utama yang di-copy ke PC lain:**
```
Nameless POS 1.0.0.exe  (233.78 MB)
```

**Itu saja!** EXE adalah portable - tidak perlu installation.

---

## ✅ REQUIREMENT PC LAIN

### Hardware
- **OS:** Windows 10 atau 11 (64-bit)
- **RAM:** 2 GB minimum (recommended 4GB+)
- **Disk:** 1 GB free space (untuk database + temp files)
- **Processor:** Intel/AMD, tidak perlu khusus

### Software yang HARUS di-install

#### 1. **PHP 8.1+** (REQUIRED)
Download & install salah satu:

**Option A - XAMPP (Recommended - paling mudah)**
- Download: https://www.apachefriends.org/
- File: xampp-windows-x64-8.2.x-installer.exe
- Langkah: Download → Install (next-next-next) → Done
- Hasil: PHP + MySQL + Apache semua terinstall

**Option B - WampServer (Alternative)**
- Download: https://www.wampserver.com/
- File: wampserver64.exe
- Langkah: Download → Install → Run
- Hasil: PHP + MySQL + Apache

**Option C - Standalone PHP**
- Download: https://windows.php.net/download/
- File: php-8.2.x-Win32-vs16-x64-nts.zip
- Langkah: Extract → Add to PATH
- Tips: Lebih susah, tidak recommended

#### 2. **MySQL 5.7+** (REQUIRED)
**Included dengan XAMPP/WampServer!** Tidak perlu install terpisah.

Jika install standalone:
- Download: https://dev.mysql.com/downloads/mysql/
- File: mysql-8.0.x-winx64.msi

#### 3. **.NET Runtime** (Optional tapi recommended)
Beberapa library mungkin butuh ini:
- Download: https://dotnet.microsoft.com/download/dotnet-framework
- File: .NET 6.0 Runtime (atau lebih baru)

---

## 🚀 CARA SETUP DI PC LAIN

### Step 1: Install PHP + MySQL (10 menit)

```
1. Download XAMPP dari https://www.apachefriends.org/
2. Install dengan default settings
3. Buka XAMPP Control Panel
4. Klik "Start" untuk Apache dan MySQL
5. Tunggu sampai hijau (running)
```

**Verify:**
```bash
# Buka command prompt/PowerShell
php --version
# Harusnya muncul: PHP 8.1.x or higher

mysql --version
# Harusnya muncul: MySQL version...
```

### Step 2: Copy EXE ke PC

```
1. Copy file: Nameless POS 1.0.0.exe
2. Paste ke folder yang diinginkan (misal: C:\POS)
3. Double-click untuk run
4. Tunggu 6-8 detik sampai login screen muncul
```

### Step 3: Login Pertama Kali

```
Email:    super.admin@test.com
Password: 12345678
```

**PENTING:** Change password setelah login!

---

## ⚙️ KONFIGURASI DATABASE

### Automatic (Recommended)
```
1. App akan auto-detect MySQL running
2. Akan create database jika belum ada
3. Migrasi table otomatis
4. Ready to use!
```

### Manual (Jika ada masalah)
```
1. Buka XAMPP Control Panel
2. Klik "Admin" button next to MySQL
3. phpMyAdmin akan terbuka
4. Create new database: nameless_pos
5. Return to app dan refresh
```

---

## 🔧 FILE KONFIGURASI (.env)

**File:** `.env.production` (inside EXE)

Jika perlu custom:
```
APP_NAME=Nameless POS
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=sqlite  (atau mysql)
DB_HOST=127.0.0.1
DB_DATABASE=nameless_pos
DB_USERNAME=root
DB_PASSWORD=
```

---

## ✅ VERIFICATION CHECKLIST

Sebelum pakai aplikasi, pastikan:

- [ ] Windows 10/11 installed
- [ ] XAMPP/WampServer installed
- [ ] MySQL running (green di XAMPP panel)
- [ ] Apache running (green di XAMPP panel)
- [ ] PHP --version muncul versi
- [ ] MySQL --version muncul versi
- [ ] EXE di-copy ke PC lain
- [ ] EXE bisa di-double-click
- [ ] Login page muncul (6-8 detik)
- [ ] Bisa login dengan default credentials

---

## 🐛 TROUBLESHOOTING

### ❌ "Application Error - Failed to start Laravel server"

**Penyebab:** PHP atau MySQL tidak terinstall/tidak running

**Solusi:**
```bash
1. Buka XAMPP Control Panel
2. Klik "Start" Apache (jika belum)
3. Klik "Start" MySQL (jika belum)
4. Tunggu sampai kedua-duanya hijau
5. Close app dan re-run EXE
```

### ❌ "localhost refused to connect"

**Penyebab:** Server tidak terdeteksi

**Solusi:**
```bash
# Check PHP running
php artisan serve --host=127.0.0.1 --port=8000

# Check MySQL running
mysql -u root

# If error, reinstall XAMPP
```

### ❌ "Database connection failed"

**Penyebab:** MySQL belum setup atau password salah

**Solusi:**
```bash
1. Buka XAMPP phpMyAdmin
2. Create database: nameless_pos
3. Update .env DB_PASSWORD jika perlu
4. Restart app
```

### ❌ App running tapi lambat (> 10 detik)

**Penyebab:** Cache belum optimal atau disk lambat

**Solusi:**
```bash
# Run optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart app
```

---

## 📊 SYSTEM REQUIREMENTS SUMMARY

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| OS | Windows 10 | Windows 11 |
| RAM | 2 GB | 4 GB+ |
| Disk | 1 GB | 2 GB+ |
| PHP | 8.1 | 8.2+ |
| MySQL | 5.7 | 8.0+ |
| Processor | 2 Core | 4 Core |

---

## 🎯 QUICK SETUP SUMMARY

```
1. Install XAMPP (includes PHP + MySQL)
2. Start Apache & MySQL
3. Copy Nameless POS 1.0.0.exe to PC
4. Double-click to run
5. Login with super.admin@test.com / 12345678
6. Change password
7. Done! 🎉
```

**Total waktu:** ~15 menit (termasuk XAMPP download & install)

---

## 📞 COMMON QUESTIONS

**Q: Harus install di C drive?**  
A: Tidak. Bisa di drive/folder manapun.

**Q: Bisa di-portable ke USB?**  
A: EXE bisa. Tapi PHP & MySQL harus di-install di PC tujuan.

**Q: Bisa multi-user?**  
A: Ya. Database bisa multiple connections. Tapi EXE hanya satu instance running.

**Q: Apakah internet diperlukan?**  
A: Tidak. Setelah setup, semuanya offline (lokal).

**Q: Gimana backup data?**  
A: Database SQLite ada di folder `database/`. Bisa di-backup setiap hari.

---

## 📦 DISTRIBUTION CHECKLIST

Jika ingin distribute ke banyak PC:

```
Prepare:
✓ Nameless POS 1.0.0.exe (main file)
✓ Checklist ini (print atau soft copy)
✓ XAMPP installer link (or USB)
✓ Installation guide

To User:
1. Kirim checklist ini
2. User install XAMPP terlebih dahulu
3. User copy EXE ke PC mereka
4. Support sesuai troubleshooting guide
```

---

**Version:** 1.0.0  
**Date:** November 27, 2025  
**Status:** Ready for Distribution ✅
