# 🐳 Docker Development Guide

## Quick Start

### 1. **Prerequisite (Install Docker Desktop)**
   - Windows: [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop)
   - Mac: [Docker Desktop for Mac](https://www.docker.com/products/docker-desktop)
   - Linux: `sudo apt-get install docker.io docker-compose`

### 2. **Clone/Copy Project**
```bash
# Copy folder ke PC manapun
cp -r Nameless /path/to/destination
cd Nameless
```

### 3. **Start Docker**
```bash
# Build dan run container
docker-compose -f docker-compose.dev.yml up -d

# Atau singkat:
docker-compose -f docker-compose.dev.yml up
```

Output:
```
nameless-pos  | [Mon Nov 24 10:00:00 2025] PHP 8.2.0 Development Server
nameless-pos  | Listening on http://localhost:8000
```

### 4. **Akses Aplikasi**
- **App**: http://localhost:8000
- **PHPMyAdmin** (optional): http://localhost:8080

### 5. **Edit File**
Buka folder project di VS Code atau editor favorit. **Semua perubahan langsung terlihat di browser!**

```
Nameless/
├── app/
├── Modules/
├── routes/
├── resources/views/
└── ... (edit langsung, auto-reload)
```

---

## 📝 Common Commands

### Check Container Status
```bash
docker-compose -f docker-compose.dev.yml ps
```

### Stop Container
```bash
docker-compose -f docker-compose.dev.yml down
```

### View Logs
```bash
docker-compose -f docker-compose.dev.yml logs -f app
```

### Run Artisan Commands
```bash
# Inside container
docker-compose -f docker-compose.dev.yml exec app php artisan migrate
docker-compose -f docker-compose.dev.yml exec app php artisan tinker
docker-compose -f docker-compose.dev.yml exec app php artisan cache:clear
```

### Restart Container
```bash
docker-compose -f docker-compose.dev.yml restart app
```

---

## 🔑 Key Features

✅ **Portable**: Copy folder ke PC lain, langsung run `docker-compose up`
✅ **No Install Needed**: Tidak perlu install PHP, Composer, Apache di PC
✅ **Live Reload**: Edit file, perubahan langsung terlihat
✅ **Database Persistent**: SQLite disimpan di folder lokal
✅ **Volume Mounting**: PC file = Container file (sync otomatis)

---

## ⚙️ Customize

### Change Port
**docker-compose.dev.yml:**
```yaml
ports:
  - "8080:80"  # Ubah 8080 jadi port apapun
```

### Add More Services
```yaml
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
```

### Environment Variables
Edit `.env` di folder lokal:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
```

---

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Cari process yang pakai port 8000
lsof -i :8000

# Atau ubah port di docker-compose.dev.yml
ports:
  - "8001:80"  # Gunakan 8001 instead
```

### Permission Denied
```bash
# Fix file permissions
docker-compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache
```

### Database Error
```bash
# Recreate database
docker-compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed
```

---

## 🎯 Workflow

1. **Start**: `docker-compose -f docker-compose.dev.yml up -d`
2. **Edit Files** di VS Code (local)
3. **Test** di Browser (localhost:8000)
4. **Changes Auto-Sync** via volumes
5. **Stop**: `docker-compose -f docker-compose.dev.yml down`

**Itu saja!** Tidak perlu install apapun di PC! 🎉
