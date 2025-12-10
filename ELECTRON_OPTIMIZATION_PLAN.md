# Electron Optimization Plan - Nameless POS

**Status:** Electron sudah ada, tinggal optimize + database seeding

## 🎯 Objektif

1. **Performance** - Startup < 5 detik, Memory < 300MB
2. **Database** - Seeding dengan default admin login
3. **Build Size** - EXE < 300MB
4. **UX** - Loading screen, progress indicator

---

## 📋 Checklist Implementasi

### Phase 1: Frontend Optimasi
- [ ] Code splitting & lazy loading routes
- [ ] Tree-shaking & minification
- [ ] Image optimization
- [ ] CSS/JS bundling optimization
- [ ] Remove debugbar di production

### Phase 2: Backend Optimasi
- [ ] SQLite instead of MySQL
- [ ] Disable debugbar
- [ ] Cache config
- [ ] Optimize autoload

### Phase 3: Electron Setup
- [ ] Update electron/main.js - splash screen
- [ ] Update electron/preload.js - IPC setup
- [ ] Add database seeding on first run
- [ ] Implement SQLite database management
- [ ] Update electron-builder.yml

### Phase 4: Database Initialization
- [ ] Create seeder dengan default admin
- [ ] Auto-run migration pada first launch
- [ ] Create .sqlite file jika tidak ada

### Phase 5: Build & Testing
- [ ] Build EXE
- [ ] Test startup time
- [ ] Test database initialization
- [ ] Test default admin login
- [ ] Verify memory usage

---

## 💾 Database Seeding Strategy

**Struktur Database Kosong dengan Default Admin:**

```
Database: app.sqlite (baru)

Users:
- Email: admin@nameless.pos
- Password: admin123 (hash bcrypt)
- Role: Admin
- Status: Active

Settings:
- Currency: IDR
- Date Format: DD-MM-YYYY
- Default Tax Rate: 10%

Permissions:
- Semua permissions untuk Admin role
```

---

## ⚡ Performance Targets

| Metrik | Target | Method |
|--------|--------|--------|
| Startup Time | < 5 detik | Lazy loading, async initialization |
| Memory Usage | < 300 MB | Code splitting, asset optimization |
| Build Size | < 300 MB | Exclude node_modules, minify |
| Database Load | < 1 detik | SQLite preloaded at startup |

---

## 📁 File yang akan dimodifikasi

1. **vite.config.js** - Code splitting config
2. **electron/main.js** - Splash screen, database init
3. **electron/preload.js** - IPC handlers
4. **electron-builder.yml** - Build optimization
5. **package.json** - Build scripts
6. **.env.production** - Production config (debugbar off)
7. **database/seeders/** - Buat seeder untuk default admin

---

## 🔧 Implementasi Dimulai dari:

1. **Frontend Code Splitting** (vite.config.js)
2. **Electron Enhancement** (main.js + preload.js)
3. **Database Seeding** (Artisan migration + seeder)
4. **Build Configuration** (electron-builder.yml)
5. **Testing & Verification**

