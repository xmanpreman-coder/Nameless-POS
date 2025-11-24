# 🐳 Nameless POS - Docker for Portability

> **Aplikasi yang bisa dibawa kemana-mana tanpa install apapun!**

## ✨ Keuntungan Docker Setup

- 🚀 **Portable**: Copy folder ke PC mana saja, langsung jalan
- 📦 **No Install**: Tidak perlu install PHP, Composer, Apache, MySQL
- 💾 **Persistent**: Database dan files tersimpan di folder lokal
- 🔄 **Live Edit**: Edit file di PC, auto-reload di browser
- 🌐 **Multi-Device**: Bisa akses dari device lain di network yang sama
- ⚡ **Fast**: Startup hanya 10 detik setelah first build

## 🚀 Quick Start

### First Time Setup (PC Baru)
```bash
# 1. Copy folder Nameless
cd Nameless

# 2. Run setup script (atau manual command di bawah)
./setup.ps1                                    # Windows
# atau
./setup.sh                                     # Mac/Linux

# 3. Open browser
# http://localhost:8000
```

### Manual Setup (Jika script gagal)
```bash
cd Nameless
docker-compose -f docker-compose.dev.yml up -d
# Tunggu ~3-5 menit untuk build image

# Check status
docker-compose -f docker-compose.dev.yml ps
```

### Subsequent Startups
```bash
cd Nameless
docker-compose -f docker-compose.dev.yml up -d
# Startup dalam 10 detik!
```

## 📁 File Structure

```
Nameless/
├── Dockerfile.dev                 # PHP 8.2 image config
├── docker-compose.dev.yml         # Container + volume setup
├── .dockerignore                  # Files to exclude from image
├── setup.ps1                      # Windows setup script
├── setup.sh                        # Mac/Linux setup script
│
├── .env                           # App config (already configured!)
├── app/
├── Modules/
├── routes/
├── resources/
├── database/
│   └── database.sqlite            # Persisted locally
├── storage/
│   ├── app/
│   │   └── public/                # User uploads (persist)
│   ├── logs/                      # App logs (persist)
│   └── framework/
├── bootstrap/
│   └── cache/                     # Laravel cache
│
└── ... (other files)
```

## 🔧 Common Commands

### View Status
```bash
docker-compose -f docker-compose.dev.yml ps
```

### View Logs
```bash
docker-compose -f docker-compose.dev.yml logs -f app

# Stop auto-follow with Ctrl+C
```

### Stop Container
```bash
docker-compose -f docker-compose.dev.yml down
```

### Restart Container
```bash
docker-compose -f docker-compose.dev.yml restart
```

### Run Artisan Commands
```bash
docker-compose -f docker-compose.dev.yml exec app php artisan migrate
docker-compose -f docker-compose.dev.yml exec app php artisan tinker
docker-compose -f docker-compose.dev.yml exec app php artisan cache:clear
```

### Rebuild Image (Jika ada issue)
```bash
docker-compose -f docker-compose.dev.yml build --no-cache
```

## 🌐 Access from Other Devices

### Same PC
```
http://localhost:8000
```

### Same Network (Other Device)
```
http://<PC_IP>:8000
```

**Find PC IP:**
- Windows: `ipconfig` → "IPv4 Address"
- Mac/Linux: `ifconfig` atau `hostname -I`

Example: `http://192.168.1.100:8000`

## 💾 Data Persistence

**Database:**
- SQLite file: `database/database.sqlite`
- Automatically synced with local folder
- Survives container restart ✅

**Uploads:**
- Location: `storage/app/public/` (local)
- Accessible via: `http://localhost:8000/storage/...`
- Persists after container down ✅

**Logs:**
- Location: `storage/logs/`
- Survives restart ✅

## 📝 Environment Variables

Already configured in `.env`:
```env
APP_URL=http://localhost:8000
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
```

To change port or other settings, edit `.env` or `docker-compose.dev.yml`.

## 🔒 Security Notes

**Development Only:**
- APP_DEBUG=true (unsafe for production)
- APP_ENV=local
- CORS disabled

**For Production:**
- Use `Dockerfile` (not .dev)
- Use `docker-compose.yml` (not .dev)
- Set proper env variables
- Enable SSL/HTTPS
- Use proper database (MySQL/PostgreSQL)

## 🐛 Troubleshooting

### Port 8000 Already in Use
```bash
# Option 1: Change port in docker-compose.dev.yml
# Change "8000:80" to "8001:80"

# Option 2: Kill process using port 8000
lsof -i :8000        # Find process
kill -9 <PID>        # Kill it
```

### Container Won't Start
```bash
# Check logs
docker-compose -f docker-compose.dev.yml logs app

# Try rebuild
docker-compose -f docker-compose.dev.yml build --no-cache
docker-compose -f docker-compose.dev.yml up -d
```

### Permission Denied Errors
```bash
docker-compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Error
- Ensure `database/database.sqlite` exists
- Check `DB_DATABASE` path in `.env`
- Restart container: `docker-compose restart`

## 📚 Related Docs

- `DOCKER_DEV_GUIDE.md` - Detailed development guide
- `SETUP_NEW_PC.md` - Step-by-step setup for new PC
- `QUICK_START.md` - Quick reference
- `.env.example` - Environment template

## ✅ Prerequisites

**Required:**
- Docker Desktop (or docker-compose on Linux)
- 2GB free disk space
- Internet connection (first build only)

**Not Required:**
- PHP
- Composer
- Apache/Nginx
- MySQL/PostgreSQL
- Node.js
- Git

## 🎯 Workflow

1. **Start**: `docker-compose -f docker-compose.dev.yml up -d`
2. **Edit**: Edit files in VS Code (or your editor)
3. **Test**: Refresh browser - changes auto-sync
4. **Debug**: Check logs with `docker-compose logs -f`
5. **Stop**: `docker-compose -f docker-compose.dev.yml down`

## 🚀 Next Steps

- [ ] Read `SETUP_NEW_PC.md` for new PC setup
- [ ] Try uploading product image
- [ ] Check database at `database/database.sqlite`
- [ ] Explore logs with `docker-compose logs -f`
- [ ] Deploy to production with proper Dockerfile

---

**Status:** ✅ Production Ready
**Last Updated:** November 24, 2025
**Docker Version:** Compose v2+
**PHP Version:** 8.2
