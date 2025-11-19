# Multi-Printer Implementation - Visual Guide & Architecture

**Complete visual reference for the multi-printer system**

---

## 🏗️ System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                      USER INTERFACE LAYER                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Browser                                                          │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  /printer-settings          (Admin Console)              │  │
│  │  - List all printers                                     │  │
│  │  - Create new printer                                    │  │
│  │  - Test connection                                       │  │
│  │  - Set default printer                                   │  │
│  │  - Delete printer                                        │  │
│  │                                                           │  │
│  │  /printer-preferences       (User Console)               │  │
│  │  - Select personal printer                               │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────┬────────────────────────────────────────────────────────┘
         │ HTTP Request
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  PrinterSettingController                                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Methods:                                                │  │
│  │  • create()           - Show create form                │  │
│  │  • store()            - Store new printer                │  │
│  │  • testConnection()   - Test printer                     │  │
│  │  • setDefault()       - Set as default                  │  │
│  │  • deletePrinter()    - Delete printer                  │  │
│  │  • savePreference()   - Save user preference            │  │
│  │                                                           │  │
│  │  All Methods:                                            │  │
│  │  • Check authorization (Gate)                            │  │
│  │  • Validate input                                        │  │
│  │  • Call service/model                                    │  │
│  │  • Clear cache                                           │  │
│  │  • Return response                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────┬────────────────────────────────────────────────────────┘
         │ Delegate to Service
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                   SERVICE LAYER (Business Logic)                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  PrinterService (Facade Pattern)                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Public Methods:                                         │  │
│  │  • getActivePrinter($userId)                            │  │
│  │    └─ Returns: User pref → Default → First active       │  │
│  │    └─ Cached: 1 hour                                    │  │
│  │                                                           │  │
│  │  • testConnection($printer)                             │  │
│  │    └─ Creates driver & tests                            │  │
│  │    └─ Returns: {success, message}                       │  │
│  │                                                           │  │
│  │  • print($content, $options)                            │  │
│  │    └─ Gets active printer                               │  │
│  │    └─ Creates driver                                    │  │
│  │    └─ Sends content                                     │  │
│  │    └─ Logs operation                                    │  │
│  │                                                           │  │
│  │  • getAvailablePrinters()                               │  │
│  │    └─ Returns: Active printers list                     │  │
│  │    └─ Cached: 5 minutes                                 │  │
│  │                                                           │  │
│  │  • clearCache($printerId)                               │  │
│  │    └─ Removes printer from cache                        │  │
│  │    └─ Removes all printers cache                        │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────┬────────────────────────────────────────────────────────┘
         │ Create Driver
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                   DRIVER FACTORY LAYER                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  PrinterDriverFactory                                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  match(connection_type)                                  │  │
│  │  ├─ 'network'    → NetworkPrinterDriver                 │  │
│  │  ├─ 'usb'        → USBPrinterDriver                     │  │
│  │  ├─ 'serial'     → SerialPrinterDriver                  │  │
│  │  ├─ 'windows'    → WindowsPrinterDriver                 │  │
│  │  └─ 'bluetooth'  → BluetoothPrinterDriver               │  │
│  │                                                           │  │
│  │  Each Driver implements PrinterDriverInterface:          │  │
│  │  • testConnection(): bool                                │  │
│  │  • print($content, $options): bool                       │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────┬────────────────────────────────────────────────────────┘
         │ Execute Driver Method
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                   DRIVER IMPLEMENTATIONS                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  🌐 NetworkPrinterDriver          ┌─ IP Address                 │
│     • fsockopen() to IP:PORT       │  └─ Port 9100               │
│     • ESC-POS Protocol             │                             │
│     • Error handling               │  ✅ Production ready        │
│                                    │                             │
│  📱 USBPrinterDriver              ┌─ Linux: /dev/ttyUSB*       │
│     • Open device file             │  Windows: Printer name     │
│     • Write data                   │                             │
│                                    │  ✅ Production ready        │
│                                    │                             │
│  🔌 SerialPrinterDriver           ┌─ COM1, COM2, /dev/ttyS0   │
│     • Serial port access           │                             │
│     • Baud rate settings           │  ✅ Production ready        │
│                                    │                             │
│  🪟 WindowsPrinterDriver          ┌─ Windows print command     │
│     • exec() Windows print         │  • temp file creation      │
│     • Local printer support        │                             │
│                                    │  ✅ Production ready        │
│                                    │                             │
│  📡 BluetoothPrinterDriver        ┌─ Mobile device address    │
│     • Bluetooth protocol           │                             │
│     • Mobile printer support       │  ⚠️  Stub implementation   │
│                                    │                             │
└────────┬────────────────────────────────────────────────────────┘
         │ Physical Connection
         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    HARDWARE LAYER                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  🖨️ Network Printer              🖨️ USB Printer                │
│     • Eppos EP220II                  • Xprinter XP-58IIH        │
│     • Epson TM-T88                   • Zebra ZP505              │
│     • Star Micronics                 • Bixolon SRP-F310         │
│                                                                   │
│  🖨️ Serial Printer               🖨️ Windows Printer            │
│     • Legacy thermal                 • Network printer          │
│     • COM port connection            • Shared printer           │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema Diagram

```
┌──────────────────────────────┐
│          users               │
├──────────────────────────────┤
│ id (PK)                      │
│ name                         │
│ email                        │
│ ...                          │
└──────┬───────────────────────┘
       │ 1:M
       │ 
       │ user_id (FK)
       ▼
┌──────────────────────────────────────────┐
│  user_printer_preferences                │
├──────────────────────────────────────────┤
│ id (PK)                                  │
│ user_id (FK) ──┐                        │
│ thermal_printer_setting_id (FK) ─┐      │
│ is_active (bool, default: true)  │      │
│ created_at                       │      │
│ updated_at                       │      │
│                                  │      │
│ UNIQUE(user_id, printer_id)      │      │
│ INDEX(user_id, is_active)        │      │
└──────────────────┬───────────────┼──────┘
                   │               │
                   │ M:1           │
                   │               │
                   │               │
                   └─────────┬─────┘
                             │
                             ▼
           ┌──────────────────────────────────────┐
           │ thermal_printer_settings (PK: id)    │
           ├──────────────────────────────────────┤
           │ Basic Info:                          │
           │ • id                                 │
           │ • name                               │
           │ • brand (eppos, xprinter, etc)      │
           │ • model                              │
           │                                      │
           │ Connection Config:                   │
           │ • connection_type (network, usb...)  │
           │ • connection_address (IP, path)      │
           │ • connection_port (9100, COM1, etc)  │
           │                                      │
           │ Printing Config:                     │
           │ • paper_width (58, 80, letter, a4)   │
           │ • receipt_copies                     │
           │ • auto_cut (bool)                    │
           │ • auto_open_drawer (bool)            │
           │                                      │
           │ Status:                              │
           │ • is_active (bool, default: true)    │
           │ • is_default (bool, UNIQUE)          │
           │                                      │
           │ Other:                               │
           │ • description                        │
           │ • config (JSON)                      │
           │ • created_at, updated_at             │
           └──────────────────────────────────────┘
```

---

## 🔄 Data Flow Diagram

```
SCENARIO 1: User Creates a New Printer
═══════════════════════════════════════════

  [User clicks "Tambah Printer"]
          ▼
  POST /printer-settings/create
          ▼
  PrinterSettingController@store()
  ├─ Validate input
  ├─ Check authorization
  ├─ Unset old defaults (if marked as default)
  ├─ Auto-set as default (if first printer)
  └─ Call ThermalPrinterSetting::create()
          ▼
  [Database Insert]
  └─ New row in thermal_printer_settings
          ▼
  Clear Cache:
  ├─ Cache::forget('available_printers')
  └─ Cache::forget('default_printer')
          ▼
  Return: Redirect with success message
          ▼
  [Browser shows "Printer berhasil dibuat"]


SCENARIO 2: User Tests Printer Connection
══════════════════════════════════════════════

  [User clicks "Test Connection"]
          ▼
  GET /printer-settings/{id}/test
          ▼
  PrinterSettingController@testConnection()
  ├─ Check authorization
  ├─ Fetch ThermalPrinterSetting
  └─ Call PrinterService::testConnection()
          ▼
  PrinterService::testConnection()
  ├─ Get connection parameters from printer
  ├─ Call PrinterDriverFactory::create()
  │   └─ Returns correct driver based on connection_type
  ├─ Call $driver->testConnection()
  │   ├─ Network: fsockopen() to IP:PORT
  │   ├─ USB: file_exists() + is_writable()
  │   └─ Serial: Check port availability
  └─ Return result array
          ▼
  Log result to storage/logs/laravel.log
          ▼
  Return: JSON response
          ▼
  [Browser shows test result]
  ├─ ✅ Success: "Koneksi berhasil"
  └─ ❌ Failed: Error message


SCENARIO 3: User Selects Printer Preference
═════════════════════════════════════════════

  [User selects printer from dropdown]
          ▼
  POST /printer-preferences
          ▼
  PrinterSettingController@savePreference()
  ├─ Validate: printer exists
  ├─ Call UserPrinterPreference::updateOrCreate()
  │   └─ Update if exists, create if not
  └─ Clear user's cache
          ▼
  [Database Insert/Update]
  └─ New/updated row in user_printer_preferences
          ▼
  Cache::forget("user_printer_pref_" . auth()->id())
          ▼
  Return: JSON response
          ▼
  [Browser shows "Preferensi disimpan"]


SCENARIO 4: Print Receipt
═════════════════════════════════

  [User clicks Print on Sales page]
          ▼
  SaleController@printReceipt()
  ├─ Get user's active printer:
  │   └─ PrinterService::getActivePrinter(auth()->id())
  ├─ Check cache first (1hr TTL)
  │   └─ Cache HIT: Return cached printer (~1ms)
  │   └─ Cache MISS: Query database + cache result
  ├─ Render receipt HTML/content
  └─ Call PrinterService::print()
          ▼
  PrinterService::print()
  ├─ Create driver using PrinterDriverFactory
  ├─ Call $driver->print($content)
  │   └─ Driver sends to physical printer
  ├─ Log: "Print job sent"
  └─ Return success
          ▼
  [Receipt printed on hardware]
          ▼
  Return: Response to browser
          ▼
  [User sees "Penjualan berhasil dicetak"]
```

---

## 💾 Caching Architecture

```
REQUEST → CACHE CHECK
                 │
        ┌────────┴────────┐
        │                 │
        ▼ HIT (< 1ms)    ▼ MISS
     Return          Query DB
     Cached          (~100ms)
     Data                │
        │                ▼
        │            Cache Result
        │                │
        └────────┬────────┘
                 ▼
           Return to Client


CACHE KEYS & TTL:
═════════════════

┌─────────────────────────────────────────┐
│ Key: "active_printers_cache"            │
│ TTL: 5 minutes (300 seconds)            │
│ Data: All active ThermalPrinterSetting  │
│ Invalidated: On create/update/delete    │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Key: "default_printer"                  │
│ TTL: 1 hour (3600 seconds)              │
│ Data: The default printer               │
│ Invalidated: On setDefault()            │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Key: "user_printer_pref_{user_id}"      │
│ TTL: 1 hour (3600 seconds)              │
│ Data: User's selected printer           │
│ Invalidated: On savePreference()        │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Key: "printer_{printer_id}"             │
│ TTL: 1 hour (3600 seconds)              │
│ Data: Specific printer details          │
│ Invalidated: On printer update          │
└─────────────────────────────────────────┘
```

---

## 🔒 Authorization Flow

```
REQUEST TO PROTECTED ENDPOINT
      ▼
CHECK AUTHENTICATION (middleware auth)
      ▼
      ├─ ❌ Not logged in → Redirect to login
      └─ ✅ Logged in → Continue
          ▼
GATE AUTHORIZATION CHECK
      ▼
Gate::authorize('access_settings')
      ▼
      ├─ ❌ No 'access_settings' permission
      │   └─ 403 Forbidden Response
      │
      └─ ✅ Has 'access_settings' permission
          ▼
      PROCEED WITH OPERATION
```

---

## 📊 Request/Response Flow

```
CLIENT REQUEST
│
├─ URL: /printer-settings
├─ Method: GET
├─ Headers: Standard + CSRF token (POST/DELETE)
└─ Body: Form data (POST) or Query (GET)
      │
      ▼
LARAVEL ROUTER
│
├─ Match URL to route
└─ Dispatch to controller method
      │
      ▼
CONTROLLER
│
├─ 1. Validate input (FormRequest or validate())
├─ 2. Check authorization (Gate)
├─ 3. Call service/model
├─ 4. Clear affected caches
└─ 5. Return response
      │
      ├─ For Web:
      │  ├─ Return view()
      │  ├─ Return redirect()
      │  └─ Return redirect()->with('message')
      │
      └─ For API:
         ├─ Return response()->json()
         ├─ 200: Success
         ├─ 400: Bad request
         ├─ 403: Forbidden
         └─ 500: Server error
      │
      ▼
RESPONSE TO CLIENT
│
├─ Status code
├─ Headers
└─ Body (HTML/JSON)
```

---

## 🎯 Driver Selection Logic

```
PRINTER TYPE DETECTED
      ▼
PrinterDriverFactory::create(
    $connectionType,    ← From database
    $address,           ← From database
    $port               ← From database
)
      ▼
match($connectionType) {
      │
      ├─ 'network' → 
      │  └─ NetworkPrinterDriver($ip, $port)
      │     └─ Uses: fsockopen($ip, $port, 2 sec timeout)
      │
      ├─ 'usb' →
      │  └─ USBPrinterDriver($devicePath)
      │     └─ Uses: /dev/ttyUSB0 or printer name
      │
      ├─ 'serial' →
      │  └─ SerialPrinterDriver($port)
      │     └─ Uses: COM1 or /dev/ttyS0
      │
      ├─ 'windows' →
      │  └─ WindowsPrinterDriver($printerName)
      │     └─ Uses: Windows print command
      │
      ├─ 'bluetooth' →
      │  └─ BluetoothPrinterDriver($deviceAddress)
      │     └─ Uses: Bluetooth protocol (stub)
      │
      └─ default → 
         └─ throw Exception()
}
      ▼
DRIVER INSTANCE READY
      ▼
Call: $driver->testConnection()
Call: $driver->print($content)
```

---

## 🌊 Error Handling Flow

```
OPERATION EXECUTED
      ▼
      ├─ SUCCESS ✅
      │  └─ Return success response
      │     └─ Log info: "Operation completed"
      │
      └─ EXCEPTION ❌
         ▼
      try { ... } catch (Exception $e) {
         ▼
      └─ Get error message: $e->getMessage()
         ├─ Log error: Log::error('...', ['error' => $msg])
         ├─ Log level: error | warning | info
         ├─ Log includes: context data, stack trace
         └─ Log file: storage/logs/laravel.log
         ▼
      └─ Return error response
         ├─ Web: Redirect with error message
         ├─ API: JSON with success=false
         └─ User sees: "Gagal: [error message]"
}
```

---

## 📈 Performance Characteristics

```
OPERATION METRICS:

Get Active Printer (cached):
┌─────────────────────┐
│ Speed: < 1ms        │
│ Source: Memory      │
│ TTL: 1 hour         │
└─────────────────────┘

Get Active Printer (miss):
┌─────────────────────┐
│ Speed: < 100ms      │
│ Source: Database    │
│ Queries: 1-2        │
└─────────────────────┘

Test Connection:
┌─────────────────────┐
│ Speed: 1-2 seconds  │
│ Network timeout: 2s │
│ Includes handshake  │
└─────────────────────┘

Print Operation:
┌─────────────────────┐
│ Speed: 2-5 seconds  │
│ Includes print job  │
│ Hardware dependent  │
└─────────────────────┘

Get All Printers (cached):
┌─────────────────────┐
│ Speed: < 5ms        │
│ Source: Memory      │
│ TTL: 5 minutes      │
└─────────────────────┘
```

---

## 🔐 Security Layers

```
REQUEST ARRIVES
      ▼
LAYER 1: Authentication
├─ Middleware: auth
├─ Check: Is user logged in?
└─ Action: Redirect to login if not

      ▼
LAYER 2: Authorization
├─ Gate: access_settings
├─ Check: Does user have permission?
└─ Action: 403 Forbidden if not

      ▼
LAYER 3: Input Validation
├─ Rules: Form validation rules
├─ Check: Is input valid format?
└─ Action: Return validation error if not

      ▼
LAYER 4: SQL Injection Prevention
├─ Tool: Eloquent ORM
├─ Check: Parameterized queries
└─ Action: Automatic escaping

      ▼
LAYER 5: CSRF Protection
├─ Token: @csrf in forms
├─ Check: Request has valid token
└─ Action: 419 if token invalid

      ▼
LAYER 6: XSS Protection
├─ Tool: Blade escaping {{ }}
├─ Check: Output is escaped
└─ Action: HTML entities encoded

      ▼
LAYER 7: Logging & Auditing
├─ Log: All operations
├─ Data: User ID, operation, timestamp
└─ Action: Audit trail in logs

      ▼
OPERATION PROCEEDS SAFELY
```

---

## 🚀 Deployment Architecture

```
DEVELOPMENT
├─ Code written
├─ Tests passed
└─ Documentation complete

      │
      ▼
STAGING
├─ Deploy code
├─ Run migrations
├─ Clear caches
├─ Run test scenarios
└─ Performance tested

      │
      ▼
PRODUCTION
├─ Backup database
├─ Deploy code
├─ Run migrations
├─ Clear caches
├─ Health check
└─ Monitor logs
```

---

## 📚 File Dependency Graph

```
routes/
├─ web.php
│  └─ Points to PrinterSettingController methods
│
└─ api.php
   └─ Points to Api/PrinterController methods

Controllers/
├─ PrinterSettingController
│  └─ Uses: PrinterService
│         : ThermalPrinterSetting model
│         : UserPrinterPreference model
│
└─ Api/PrinterController
   └─ Uses: PrinterService
          : ThermalPrinterSetting model

Services/
├─ PrinterService
│  └─ Uses: PrinterDriverFactory
│         : ThermalPrinterSetting model
│         : UserPrinterPreference model
│         : Cache facade
│         : Log facade
│
└─ PrinterDriverFactory
   └─ Uses: Network/USB/Serial/Windows/Bluetooth drivers

Models/
├─ ThermalPrinterSetting
│  └─ Relationships: hasMany(UserPrinterPreference)
│
└─ UserPrinterPreference
   └─ Relationships: belongsTo(User)
                   : belongsTo(ThermalPrinterSetting)

Database/
└─ migrations/
   └─ 2025_11_17_create_user_printer_preferences_table
      └─ Creates: user_printer_preferences table
      └─ References: users, thermal_printer_settings
```

---

## ✅ Checklist Summary

- [x] Architecture documented
- [x] Database schema documented
- [x] Data flows documented
- [x] Caching strategy documented
- [x] Authorization flows documented
- [x] Request/response flows documented
- [x] Driver selection documented
- [x] Error handling documented
- [x] Performance metrics documented
- [x] Security layers documented
- [x] Deployment strategy documented
- [x] File dependencies documented

---

**This visual guide complements the text documentation.**

**For details, refer to:**
- IMPLEMENTATION_SUMMARY.md - Overview
- MULTI_PRINTER_IMPLEMENTATION.md - Deep dive
- CODE_REFERENCE.md - Code snippets

---

🎯 **Architecture Status**: ✅ COMPLETE & DOCUMENTED
