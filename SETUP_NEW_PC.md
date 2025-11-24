# ✅ COMPLETE SETUP CHECKLIST - PC Baru

## 📋 Pre-Setup Checklist

### Di PC LAMA (Sebelum Copy)
- [x] `.env` sudah dikonfigurasi dengan APP_URL=http://localhost:8000
- [x] Database `database/database.sqlite` ada
- [x] Folder `storage/` lengkap dengan isi
- [x] Folder `bootstrap/cache/` ada (kosong OK)
- [x] Semua file sudah di-commit ke git (optional, untuk safety)

### Di PC BARU - Step 1: Install Docker
- [ ] Install Docker Desktop
  - Windows: https://www.docker.com/products/docker-desktop
  - Mac: https://www.docker.com/products/docker-desktop
  - Linux: `sudo apt-get install docker.io docker-compose`
- [ ] Test Docker: Buka terminal, ketik `docker --version`
- [ ] Test docker-compose: Ketik `docker-compose --version`

### Di PC BARU - Step 2: Copy Project
- [ ] Copy folder `Nameless` dari PC lama
  - Pakai USB/Cloud/Git
  - Atau: `scp -r user@oldpc:~/Nameless ~/`
- [ ] Verify folder ada dengan struktur lengkap:
  ```
  Nameless/
  ├── .env                          ✅
  ├── Dockerfile.dev                ✅
  ├── docker-compose.dev.yml        ✅
  ├── database/database.sqlite      ✅
  ├── storage/                      ✅
  ├── bootstrap/cache/              ✅
  ├── app/
  ├── Modules/
  └── ... (files lainnya)
  ```

### Di PC BARU - Step 3: Run Setup

**Windows PowerShell:**
```powershell
cd Nameless
./setup.ps1
```

**Mac/Linux Terminal:**
```bash
cd Nameless
chmod +x setup.sh
./setup.sh
```

**Atau Manual:**
```bash
cd Nameless
docker-compose -f docker-compose.dev.yml up -d
```

- [ ] Build image selesai (tunggu 2-5 menit)
- [ ] Containers started successfully
- [ ] Check status: `docker-compose -f docker-compose.dev.yml ps`

### Di PC BARU - Step 4: Verify

- [ ] Buka browser: http://localhost:8000
- [ ] Login dengan credentials yang sama dari PC lama
- [ ] Check database: Lihat data yang sama di halaman produk/user
- [ ] Try upload: Upload foto di profile atau product
- [ ] Check file: File tersimpan di `storage/app/public/`

---

## 🎯 Apa yang PERLU Ada di Folder

**HARUS Ada (Penting):**
- `.env` - Database path dan APP_URL
- `docker-compose.dev.yml` - Container config
- `Dockerfile.dev` - PHP image config
- `database/database.sqlite` - Database file
- `storage/` - Upload files, cache
- `composer.json` - PHP dependencies list
- `composer.lock` - Exact versions

**Tidak Wajib:**
- `.git/` - Git history (buat size kecil bisa di-exclude)
- `node_modules/` - Front-end (di-ignore di Docker)
- `.vscode/` - Editor config
- `*.log` - Logs (auto-generated)

---

## 🐳 Files Docker yang Perlu di-Setup

Sudah ada di folder:
- ✅ `Dockerfile.dev` - PHP 8.2 + extensions
- ✅ `docker-compose.dev.yml` - Volume mounts + ports
- ✅ `.dockerignore` - Exclude unnecessary files
- ✅ `setup.ps1` - Windows automation script
- ✅ `setup.sh` - Mac/Linux automation script

---

## 📱 Akses dari Device Lain

### Dari PC yang Sama (Lokal)
```
http://localhost:8000
```

### Dari Device Lain (Sama Network)
```
http://<PC-IP>:8000
```

**Cari IP PC:**
- Windows: `ipconfig` → cari "IPv4 Address"
- Mac/Linux: `ifconfig` atau `ip addr`

Contoh: `http://192.168.1.100:8000`

---

## 🆘 Common Issues & Fix

| Issue | Solution |
|-------|----------|
| Docker not found | Install Docker Desktop |
| Port 8000 in use | Ubah port di docker-compose.dev.yml |
| Build failed | Check logs: `docker-compose logs app` |
| Database error | Database file di `database/database.sqlite` harus ada |
| File permission denied | `docker-compose exec app chmod -R 775 storage` |
| Container won't start | `docker-compose restart app` |

---

## ⏱️ Expected Time

| Step | Duration |
|------|----------|
| Install Docker | 10 menit |
| Copy Project | 2 menit |
| Build Image | 3-5 menit (first time only) |
| Start Containers | 1 menit |
| **Total** | **~20 menit** |

Subsequent startups: **10 detik!**

---

## 🎉 Success Indicators

✅ Docker ps shows 1 container running (nameless-pos)
✅ http://localhost:8000 loads app
✅ Can login with existing credentials
✅ Database data visible (products, users)
✅ Can upload files
✅ No errors in browser console

---

## 📞 Support

Jika ada masalah:
1. Check logs: `docker-compose -f docker-compose.dev.yml logs -f app`
2. Restart: `docker-compose -f docker-compose.dev.yml restart`
3. Rebuild: `docker-compose -f docker-compose.dev.yml build --no-cache`

---

**Last Updated:** November 24, 2025
**Status:** Ready for Production ✅
