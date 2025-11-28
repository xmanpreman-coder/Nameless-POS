# ⚡ Nameless POS - Slow Startup? Quick Fixes

## 🐢 App Takes Too Long to Open?

Jika aplikasi lambat membuka, coba solusi berikut (dari cepat ke kompleks):

---

## 1. ✅ **QUICK FIX - Clear App Cache** (30 seconds)

```bash
cd C:\path\to\Nameless-POS

# Run these commands:
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Then restart app
```

**Waktu startup akan:**
- Sebelum: 10-12 seconds
- Sesudah: 6-8 seconds

---

## 2. 🔌 **Check Database Connection** (1 minute)

Jika database lambat:

```bash
# Test database connection
php artisan tinker

# In tinker prompt:
>>> DB::connection()->getPdo()
>>> exit
```

Jika error → **Database offline, please install MySQL**

**Fix:** Download & install:
- XAMPP (includes MySQL + PHP)
- OR WampServer
- OR Standalone MySQL

---

## 3. 📊 **Check System Resources** (2 minutes)

App melambat jika:
- **RAM penuh** → Close other apps
- **Disk penuh** → Free up space (need 1GB minimum)
- **PHP overloaded** → Increase PHP memory_limit

```bash
# Check PHP memory limit:
php -i | findstr "memory_limit"

# Should be 256M or higher
```

---

## 4. 🔧 **Livewire Performance Tuning** (Intermediate)

Livewire components memang real-time, jadi perlu lebih banyak resource.

### If Dashboard Too Slow:

**Option A: Disable specific Livewire components**

Edit `resources/views/dashboard.blade.php`:
```blade
{{-- Comment out heavy components --}}
{{-- @livewire('chart-component') --}}
{{-- @livewire('realtime-sales') --}}
```

Then: `php artisan config:cache`

**Option B: Use lazy loading**

Edit component:
```php
// In your Livewire component
public $defer = true; // Load later, not on page init
```

**Option C: Disable Livewire entirely**

Remove from composer:
```bash
composer remove livewire/livewire
php artisan config:cache
```

---

## 5. 🚀 **Advanced Optimization** (Expert)

### A. Use Redis for Cache (Fast)
```bash
# Install Redis via XAMPP or WSL2
# Then update .env:

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### B. Increase PHP Memory
```bash
# Edit PHP ini file:
# XAMPP: C:\xampp\php\php.ini

memory_limit = 512M  # Increase from 256M
max_execution_time = 300
```

### C. Use Database Cache
```bash
# Create cache table
php artisan cache:table
php artisan migrate

# Update .env:
CACHE_DRIVER=database
```

---

## ❓ **FAQ**

**Q: Kok Livewire masih ada? Apa itu?**  
A: Livewire adalah library real-time untuk Laravel. Ini membuat dashboard bisa update otomatis tanpa refresh. Normal untuk aplikasi POS.

**Q: Bisa di-remove?**  
A: Ya, tapi kemudian dashboard tidak akan real-time. Follow "Option C" di atas.

**Q: Berapa normal startup time?**  
A: 6-8 detik adalah normal untuk Electron + Laravel. Jika > 15 detik, ada masalah.

**Q: Apa yang paling sering lambat?**  
A: Database connection (MySQL not running) atau disk I/O lambat.

---

## 🧪 **Test Startup Speed**

```bash
# Run startup test
test-startup.bat

# Or manually:
php artisan serve --host=127.0.0.1 --port=8000
# Then open browser to http://127.0.0.1:8000
# Check network tab in browser (F12) for timing
```

Expected:
- Server startup: 2-3 seconds
- Page load: 1-2 seconds
- Total: 3-5 seconds

---

## 📞 **Still Slow? Diagnostic Steps**

1. **Check PHP version:**
   ```bash
   php --version
   # Should be 8.1 or higher
   ```

2. **Check MySQL running:**
   ```bash
   mysql --version
   # Should show version
   ```

3. **Check Laravel logs:**
   ```bash
   type storage\logs\laravel.log
   # Look for errors
   ```

4. **Check disk space:**
   ```bash
   dir C:\ /s  # Very rough estimate
   # Or use Windows Storage Settings
   ```

5. **Report issue with logs:**
   - Share: `storage/logs/laravel.log`
   - Share: Performance test output
   - Share: PHP info: `php -i`

---

## ✅ **Checklist for Fast Startup**

- [ ] MySQL running (or database configured)
- [ ] RAM available (2GB+ free)
- [ ] Disk not full (20% free space)
- [ ] PHP version 8.1+ (`php --version`)
- [ ] Cache cleared (`php artisan config:cache`)
- [ ] Caches pre-built (route, view, config)

---

## 🎯 **Bottom Line**

If app still slow after caching:
1. **99% of the time:** Database problem (MySQL not installed/running)
2. **1% of the time:** System resource issue (low RAM/disk)

**Solution:** Make sure MySQL is running, then app will be fast!

---

**Last Updated:** November 27, 2025  
**Version:** 1.0.0  
**For Help:** Check PERFORMANCE_OPTIMIZATION_GUIDE.md for detailed technical info
