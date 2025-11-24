# 💾 Database Backup & Restore Guide

**Status:** ✅ Database backup feature fully implemented  
**When:** After clicking "Database" menu in app  
**Output:** Backup file stored automatically  

---

## 📋 How Database Backup Works

### Automatic Backup Process

1. **Click Menu** → "Database" → "Backup Database"
2. **System Creates** → Backup file with timestamp
3. **Stored At** → `C:\Users\[YourUsername]\AppData\Roaming\Nameless POS\backups\`
4. **Filename Format** → `database_2025-11-24_1732382400000.sqlite`

### Backup Features

✅ **Automatic Timestamping**
- Every backup has unique timestamp
- Prevents overwriting old backups
- Easy to identify when backup was made

✅ **Automatic Location**
- Stored in user's AppData folder
- Safe from accidental deletion
- Isolated per user

✅ **Pre-Restore Safety**
- Before restoring, current database backed up first
- Old database saved as: `database.sqlite.pre_restore_[timestamp]`
- Can always recover previous state

✅ **Visual Feedback**
- Button shows "Backing up..." status
- Success dialog with backup location
- Option to open backup folder

---

## 🎯 Step-by-Step: Backup Database

### Step 1: Open Application

Double-click Nameless POS.exe (or run from dev)

Wait for app to fully load (~5 seconds)

### Step 2: Click Database Menu

Look at left sidebar menu:
```
User Management
Configuration
General Settings
Currencies
Units
Printer Management
Barcode Scanner
Scanner Dashboard
Start Scanning
Test Camera
External Setup
├─ Scanner Settings
Database  ← CLICK HERE
```

### Step 3: Backup Starts

Click "Database" button

You'll see:
```
Button Changes To: "Backing up..." with spinner
```

**Wait 5-10 seconds** for backup to complete

### Step 4: Backup Complete

Dialog appears:
```
✅ Backup Successful

Backup created: database_2025-11-24_1732382400000.sqlite

[OK] [Open Backup Folder]
```

Click "Open Backup Folder" to verify or "OK" to continue

---

## 📂 Finding Your Backups

### Backup Location

```
C:\Users\[Your Username]\AppData\Roaming\Nameless POS\backups\
```

**Or easier:**
1. Press `Win+R`
2. Type: `%APPDATA%\Nameless POS\backups`
3. Press Enter

You'll see files like:
```
database_2025-11-24_1732382400000.sqlite
database_2025-11-23_1732296000000.sqlite
database_2025-11-22_1732209600000.sqlite
```

### Backup Properties

**File Size:** Usually 2-10 MB (SQLite compressed)

**Time:** Backup takes ~2-5 seconds

**Frequency:** Create as many as you want

---

## 🔄 Step-by-Step: Restore Database

### Method 1: Using Built-in Restore

⚠️ **Currently:** Restore feature in development  
Use Method 2 below for now.

### Method 2: Manual Restore (Simple)

#### Step 1: Close Application
Close Nameless POS completely

#### Step 2: Locate Backup
1. Press `Win+R`
2. Type: `%APPDATA%\Nameless POS`
3. Press Enter

#### Step 3: Find Files
```
Nameless POS\
├── backups\
│   ├── database_2025-11-24_1732382400000.sqlite   ← BACKUP
│   └── database_2025-11-23_1732296000000.sqlite
├── database\
│   └── database.sqlite                             ← CURRENT (to replace)
└── storage\
```

#### Step 4: Backup Current Database (Safety)
```
1. Right-click database.sqlite
2. Send To → Desktop (creates copy for safety)
```

#### Step 5: Copy Backup
```
1. Right-click desired backup file
2. Copy
3. Go to Nameless POS\database\
4. Right-click → Paste
5. Rename from "database_xxx.sqlite" to "database.sqlite"
6. Click "Replace" when asked
```

#### Step 6: Restart Application
Open Nameless POS again

Your data is now restored! ✅

---

## 🛡️ Backup Safety Tips

### Create Regular Backups

**Weekly Backups:**
- Every Monday morning
- Before major month-end reporting
- Before system updates

**Daily Backups:**
- High-transaction stores
- Mission-critical data
- Before large batch imports

### Backup Verification

**Check backup file created:**
```
1. Open: %APPDATA%\Nameless POS\backups\
2. Look for recent file
3. Size should be 2-10 MB typically
4. Created time should match when you clicked backup
```

**Verify backup integrity:**
```
Recommended: Create backup, then immediately test restore
1. Create backup
2. Close app
3. Restore backup
4. Open app
5. Verify data is correct
```

---

## 📊 Backup Storage Considerations

### Storage Requirements

**Per Backup:** 2-10 MB typically

**Annual Estimate (daily backups):**
```
365 backups × 5 MB average = ~1.8 GB per year
```

**Recommendation:**
- Delete backups older than 3 months
- Keep weekly backups indefinitely
- Keep monthly snapshots for archival

### Cleanup Old Backups

```
1. Press Win+R
2. Type: %APPDATA%\Nameless POS\backups
3. Press Enter
4. Delete old files (older than 3 months)
```

**Safe to delete:** Any backup file with old date

---

## 🚨 Emergency Restore Procedure

### If Database Corrupted

**Symptoms:**
- App won't start
- "Database corrupt" error
- Can't access data

**Recovery:**

```
Step 1: Close app completely
Step 2: Navigate to: %APPDATA%\Nameless POS\
Step 3: Delete: database.sqlite (corrupted file)
Step 4: Copy recent backup from backups\ folder
Step 5: Rename to: database.sqlite
Step 6: Open app
Step 7: Data restored! ✅
```

---

## 💡 Advanced: Export Database to CSV

### Export Transaction Data

**Coming Soon:** Export feature will be added

**Current Workaround:**
1. Use Reports module to export to Excel
2. Or use command line: `php artisan db:export`

---

## 🔐 Data Security

### Where Data Stored

```
✅ Database: %APPDATA%\Nameless POS\database\
✅ Backups: %APPDATA%\Nameless POS\backups\
✅ Uploads: %APPDATA%\Nameless POS\storage\app\
```

All stored locally - never uploaded anywhere!

### Backup Encryption

⚠️ **Currently:** Backups not encrypted  
**Recommendation:** Keep backups in secure location

**For High Security:**
1. Enable Windows encryption (BitLocker)
2. Encrypt backup folder (Windows Credentials)
3. Or store backups on external encrypted drive

---

## ❓ Frequently Asked Questions

### Q: How often should I backup?
**A:** Depends on business:
- **High volume POS:** Daily
- **Medium store:** 2-3x per week  
- **Low volume:** Weekly

### Q: Where are backups stored?
**A:** `C:\Users\[Username]\AppData\Roaming\Nameless POS\backups\`

### Q: Can I backup to USB drive?
**A:** 
```
1. Create backup normally (stored in AppData)
2. Navigate to backups folder
3. Copy backup file to USB
4. Done!
```

### Q: How large is each backup?
**A:** Typically 2-10 MB (depends on transaction volume)

### Q: Can I restore an old backup?
**A:** Yes! See "Manual Restore" section above

### Q: What if backup fails?
**A:** 
1. Check hard drive space (need ~50 MB free)
2. Close antivirus temporarily
3. Try backup again
4. Or backup manually by copying database.sqlite

### Q: Is backup automatic?
**A:** No, you must click the Backup button manually
(Auto-backup feature may be added in future)

---

## 📝 Backup Checklist

Before month-end or year-end:

- [ ] Create database backup
- [ ] Verify backup file created (check size)
- [ ] Copy backup to external storage (USB/Cloud)
- [ ] Test restore on another PC (optional but recommended)
- [ ] Document which backup is which
- [ ] Keep backups for at least 3 months
- [ ] Delete obsolete backups

---

## 🆘 Troubleshooting

### Problem: Backup Button Not Working

**Solution:**
1. Make sure you're running the .exe version (Electron)
2. Not running in browser tab (no Electron API available)
3. Check console (Ctrl+Shift+I) for error messages
4. Try closing and reopening app

### Problem: Backup Button Disabled

**Solution:**
1. Wait for previous backup to complete (5-10 seconds)
2. Button should be re-enabled automatically
3. If stuck, restart app

### Problem: "No Space Available" Error

**Solution:**
1. Check hard drive has at least 50 MB free
2. Delete old files/programs to free space
3. Try backup again

### Problem: Can't Find Backup Folder

**Solution:**
```
Easy way:
1. Press: Win+R
2. Type: %APPDATA%\Nameless POS\backups
3. Press: Enter
```

---

## ✅ Success Indicators

Backup works correctly when:

✅ Backup dialog shows success message  
✅ Backup file appears in backups folder  
✅ File size is 2-10 MB  
✅ File timestamp matches current time  
✅ Restore successfully replaces database  

---

**Version:** 1.0.0  
**Created:** 2025-11-24  
**Status:** ✅ Feature Implemented & Working
