# Visual Guide - Nameless POS Offline Architecture

## 📊 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        USER COMPUTER                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌───────────────────────────────────────────────────────┐   │
│  │          DESKTOP SHORTCUT (Double-click)              │   │
│  │  start-nameless-offline.bat or .ps1                  │   │
│  └────────────────┬────────────────────────────────────┘   │
│                   │                                          │
│                   ▼                                          │
│  ┌───────────────────────────────────────────────────────┐   │
│  │       LAUNCHER SCRIPT (3-5 seconds)                   │   │
│  │                                                        │   │
│  │  1. Check PHP installed                              │   │
│  │  2. Check Chrome installed                           │   │
│  │  3. Create database if needed                        │   │
│  │  4. Start PHP backend (localhost:8000)              │   │
│  │  5. Open Chrome in app mode                         │   │
│  └────────────────┬────────────────────────────────────┘   │
│                   │                                          │
│        ┌──────────┴──────────┐                              │
│        ▼                     ▼                              │
│  ┌──────────────┐      ┌──────────────┐                    │
│  │ PHP Backend  │      │  Chrome PWA  │                    │
│  │ (L:8000)     │◄────►│              │                    │
│  │              │      │  Offline:    │                    │
│  │ • Routes     │      │  - Cache API │                    │
│  │ • API        │      │  - Service   │                    │
│  │ • Logic      │      │    Worker    │                    │
│  │ • SQLite     │      │  - Queue     │                    │
│  │   DB         │      │  - Sync      │                    │
│  └──────────────┘      └──────────────┘                    │
│        │                      │                             │
│        ▼                      ▼                             │
│  ┌──────────────────────────────────┐                      │
│  │   SQLite Database                 │                      │
│  │  (database/app.sqlite)            │                      │
│  │                                   │                      │
│  │  • All data local                │                      │
│  │  • Encrypted                      │                      │
│  │  • File-based (no server)        │                      │
│  │  • Auto-backup                    │                      │
│  └──────────────────────────────────┘                      │
│                                                               │
│  [COMPLETELY OFFLINE CAPABLE - NO INTERNET NEEDED]         │
│                                                               │
└─────────────────────────────────────────────────────────────┘

        ▼ (OPTIONAL - When Internet Available)

┌─────────────────────────────────────────────────────────────┐
│              CLOUD SERVER (Backend API)                      │
│                                                               │
│  • Central database                                         │
│  • User management                                          │
│  • Reporting                                                │
│  • Multi-device sync                                        │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Data Flow - Offline Mode

```
USER OFFLINE (No Internet)
═══════════════════════════════════════════════════════════════

User Action (e.g., Create Sale)
    │
    ▼
┌─────────────────────────┐
│ Check Online Status?    │
│                         │
│ If ONLINE → Send to API │
│ If OFFLINE → Queue      │
└────────┬────────────────┘
         │
         ▼ (OFFLINE PATH)
    ┌─────────────────────────────┐
    │ Add to Request Queue        │
    │ (Stored in LocalStorage)    │
    │                             │
    │ Queue Item:                 │
    │ • Endpoint                  │
    │ • Method (POST/PUT)         │
    │ • Data payload              │
    │ • Timestamp                 │
    │ • Status: pending           │
    └────────┬────────────────────┘
             │
             ▼
    ┌─────────────────────────────┐
    │ Save to SQLite DB           │
    │                             │
    │ Transaction created but     │
    │ marked as "unsynced"        │
    └────────┬────────────────────┘
             │
             ▼
    ┌─────────────────────────────┐
    │ Show UI Indicator           │
    │                             │
    │ ⚠️ "Pending - Offline"      │
    │                             │
    │ Show pending requests count │
    └────────┬────────────────────┘
             │
             ▼
    ┌─────────────────────────────┐
    │ User Continues Working      │
    │ (No interruption!)          │
    │                             │
    │ Can create more items       │
    │ Can edit data               │
    │ Can view reports            │
    └─────────────────────────────┘
```

---

## 🔄 Data Flow - Sync (When Online)

```
INTERNET AVAILABLE - AUTO SYNC
═══════════════════════════════════════════════════════════════

App detects online status
    │
    ▼
Check if queue has pending items
    │
    └─ If no pending → Continue normal
    │
    └─ If pending → Start sync
        │
        ▼
    ┌──────────────────────────┐
    │ FOR EACH pending request:│
    └──────────────┬───────────┘
                   │
                   ▼
        ┌─────────────────────────┐
        │ Send to Cloud API       │
        │                         │
        │ POST /api/sales         │
        │ Body: {sale data}       │
        └────────┬────────────────┘
                 │
                 ▼
        ┌─────────────────────────┐
        │ Server Processes        │
        │                         │
        │ • Validate data         │
        │ • Merge with cloud data │
        │ • Save to cloud DB      │
        │ • Return success/error  │
        └────────┬────────────────┘
                 │
                 ▼
        ┌─────────────────────────┐
        │ Update Queue Item       │
        │                         │
        │ Status: synced ✅       │
        │ Response: saved         │
        └────────┬────────────────┘
                 │
                 ▼
        ┌─────────────────────────┐
        │ Remove from Queue       │
        │                         │
        │ Delete from pending     │
        │ Update UI               │
        └──────────┬──────────────┘
                   │
                   └─ Continue with next item

When all items synced:
    │
    ▼
┌──────────────────────┐
│ Sync Complete ✅     │
│                      │
│ • All data synced    │
│ • Queue empty        │
│ • Pending count: 0   │
│ • Last sync: [time]  │
└──────────────────────┘
```

---

## 🎯 Component Relationships

```
┌─────────────────────────────────────────────────────────┐
│                                                          │
│  OfflineStore (Pinia Store)                             │
│  ═════════════════════════════                          │
│  • isOnline / isOffline state                           │
│  • pendingRequests array                                │
│  • syncInProgress boolean                               │
│  • lastSyncTime timestamp                               │
│                                                          │
│      ▲        ▲        ▲                                │
│      │        │        │                                │
│      │        │        │                                │
│  ┌───┴─┐  ┌───┴─┐  ┌───┴─┐                            │
│  │     │  │     │  │     │                             │
│  │  1  │  │  2  │  │  3  │                             │
│  │     │  │     │  │     │                             │
│  └─────┘  └─────┘  └─────┘                            │
│    │        │        │                                 │
│    │        │        │                                 │
│    ▼        ▼        ▼                                 │
│  ┌───────────────────────────────┐                    │
│  │ OfflineIndicator Component    │                    │
│  │ • Show online/offline status  │                    │
│  │ • Show pending requests count │                    │
│  │ • Manual sync button          │                    │
│  │ • Retry failed requests       │                    │
│  └───────────┬───────────────────┘                    │
│              │                                         │
│              ▼                                         │
│  ┌───────────────────────────────┐                    │
│  │ User Interface                │                    │
│  │ • Transaction forms           │                    │
│  │ • Reports & dashboards        │                    │
│  │ • Real-time indicators        │                    │
│  └───────────────────────────────┘                    │
│                                                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                                                          │
│  OfflineApi Service                                     │
│  ══════════════════════                                 │
│  • offlineApi.get(url)                                 │
│  • offlineApi.post(url, data)                          │
│  • offlineApi.put(url, data)                           │
│  • offlineApi.delete(url)                              │
│  • downloadForOffline()                                │
│                                                          │
│      ▲                                                 │
│      │ Used by components                              │
│      │                                                 │
│  ┌───┴────────────────────────────┐                   │
│  │                                │                   │
│  ▼                                ▼                   │
│  API Logic                    Queue Management         │
│  • Check online              • Add pending request    │
│  • Make request              • Track sync status      │
│  • Handle response           • Retry on failure       │
│  • Error handling            • Store in LocalStorage  │
│                                                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                                                          │
│  Service Worker (PWA)                                   │
│  ════════════════════                                   │
│  • Cache static assets                                 │
│  • Cache API responses                                 │
│  • Serve from cache offline                            │
│  • Sync in background                                  │
│  • Handle network requests                             │
│                                                          │
│      ▼                                                 │
│  Cache Strategies                                      │
│  ═════════════════                                     │
│  • Network First (API)                                 │
│    Try network first, fallback to cache               │
│                                                          │
│  • Cache First (Images)                                │
│    Use cache, update in background                    │
│                                                          │
│  • Stale While Revalidate (HTML)                       │
│    Serve cached, update in background                 │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 🔄 Sync Decision Tree

```
                        Internet Check
                              │
                  ┌───────────┴───────────┐
                  │                       │
              ONLINE                  OFFLINE
                  │                       │
                  ▼                       ▼
        ┌──────────────────┐   ┌──────────────────┐
        │ Try API Request  │   │ Queue Request    │
        └──────────┬───────┘   │                  │
                   │           │ • Store in       │
                   ▼           │   LocalStorage   │
        ┌──────────────────┐   │ • Mark as        │
        │ Success?         │   │   pending        │
        └──┬─────────┬─────┘   │ • Show indicator │
           │         │         └──────┬───────────┘
        YES│       NO│                │
           │         │                │
           ▼         ▼                ▼
      ┌────────┐ ┌─────────────┐ ┌──────────┐
      │Remove  │ │ Timeout?    │ │Continue  │
      │from    │ │             │ │Working   │
      │queue   │ │ YES │ NO    │ │(User can│
      │Return  │ │  │    │     │ │keep working)
      │result  │ │  ▼    ▼     │ │          │
      └────────┘ │Queue Retry  │ └──────────┘
                 │             │
                 │ Mark as     │
                 │ failed      │
                 │             │
                 │ Wait for    │
                 │ user retry  │
                 │ or next     │
                 │ online      │
                 │ event       │
                 └─────────────┘
```

---

## 📱 UI State Diagram

```
┌───────────────────────────────────────────────────────────┐
│                    APP STATES                             │
├───────────────────────────────────────────────────────────┤
│                                                             │
│                                                             │
│   ┌─────────────────┐              ┌─────────────────┐   │
│   │   ONLINE IDLE   │              │ OFFLINE QUEUED  │   │
│   │                 │              │                 │   │
│   │ ✅ Online       │              │ ⚠️ Offline      │   │
│   │ 🟢 Backend OK   │              │ ⏳ Pending: N   │   │
│   │ 0 Pending       │              │ 🔴 No backend   │   │
│   │                 │              │                 │   │
│   └────────┬────────┘              └────────┬────────┘   │
│            │                              │             │
│   Online → │                              │ ← Offline   │
│   Action   │                              │ Action      │
│            │                              │             │
│            ▼                              ▼             │
│   ┌──────────────────┐         ┌──────────────────┐   │
│   │   SYNCING DATA   │         │  OFFLINE WORKING │   │
│   │                  │         │                  │   │
│   │ 🔄 Syncing...   │         │ ⚠️ Offline still │   │
│   │ Progress: N/M   │         │ ✎ Can keep work  │   │
│   │ Please wait...  │         │ 🔄 Queued: N+1  │   │
│   │                  │         │                  │   │
│   └────────┬─────────┘         └────────┬─────────┘   │
│            │                           │              │
│   ┌────────┴───────┐         ┌────────┴───────┐      │
│   │                │         │                │      │
│   ▼                ▼         ▼                ▼      │
│ ┌──────┐        ┌────────┐ ┌──────┐       ┌──────┐ │
│ │Error │        │Complete│ │Retry │       │Clear │ │
│ │Retry │        │Go back │ │Wait  │       │Queue │ │
│ │queue │        │to IDLE │ │online│       │wait  │ │
│ └──────┘        └────────┘ └──────┘       └──────┘ │
│                                                       │
└───────────────────────────────────────────────────────┘
```

---

## 📊 Memory & Storage Usage

```
TYPICAL INSTALLATION
═══════════════════════════════════════════════════════════

Application Files
├─ Frontend (Vue PWA)           ~50 MB
├─ Backend (Laravel)            ~100 MB
├─ Database (SQLite empty)      ~5 MB
├─ Dependencies (node_modules)  ~200 MB (dev only)
└─ Assets (CSS/JS/Images)       ~100 MB
                                ─────────
TOTAL ON DISK                   ~450-500 MB


RUNTIME MEMORY USAGE
═══════════════════════════════════════════════════════════

Chrome Browser                  ~100-150 MB
  ├─ Vue App & PWA              ~30 MB
  ├─ JavaScript Runtime         ~40 MB
  ├─ Cache & Service Worker     ~30 MB
  └─ Overhead                   ~20 MB

PHP Process                     ~50-100 MB
  ├─ Laravel Framework          ~40 MB
  ├─ Database Driver            ~10 MB
  └─ Loaded modules             ~10 MB

System                          ~100-200 MB
  ├─ Windows base               ~50 MB
  ├─ PHP runtime                ~30 MB
  └─ Other processes            ~20 MB

                                ─────────
TOTAL MEMORY                    ~250-450 MB
                                (compared to XAMPP: 800+ MB)


STORAGE GROWTH
═══════════════════════════════════════════════════════════

Database Growth Rate:
• Small store: +1 MB / month
• Medium store: +5 MB / month
• Large store: +20 MB / month

Cache Growth:
• Stable at ~10-50 MB
• Auto-cleaned periodically
• Manageable with service worker

Backup Size:
• Auto-backup before sync
• Compressed: ~2-10 MB
• 5 backups = ~50 MB max
```

---

## ✅ Offline Readiness Checklist

```
BEFORE LAUNCH
═════════════════════════════════════════════════════════════

Frontend
  ☐ OfflineIndicator in layout
  ☐ All API calls using offlineApi
  ☐ Offline state management working
  ☐ Service Worker generating
  ☐ Cache strategy configured
  ☐ Tested in DevTools offline mode

Backend
  ☐ SQLite database setup
  ☐ Migrations working offline
  ☐ API endpoints returning data
  ☐ Error handling proper
  ☐ Database auto-backup setup
  ☐ Sync endpoints working

Launchers
  ☐ start-nameless-offline.bat tested
  ☐ start-nameless-offline.ps1 tested
  ☐ PHP auto-detection working
  ☐ Chrome auto-detection working
  ☐ Port management proper
  ☐ Error messages clear


AFTER LAUNCH
═════════════════════════════════════════════════════════════

Monitoring
  ☐ Error logs reviewed
  ☐ Sync failures tracked
  ☐ User feedback collected
  ☐ Performance metrics good
  ☐ Memory usage normal
  ☐ Crash reports resolved

Support
  ☐ FAQ updated
  ☐ Troubleshooting guide ready
  ☐ Email support active
  ☐ Documentation complete
  ☐ Video tutorial published
  ☐ User training done
```

---

**Visual Guide Created:** December 8, 2025  
**Format:** Markdown with ASCII Diagrams  
**For:** Understanding Offline Architecture
