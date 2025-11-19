# 📋 Analisis Multiple Printer Support - POS Open Source

**Status**: Analysis Complete | **Date**: November 17, 2025  
**Sumber**: Crater, Triangle POS, Nameless POS implementation analysis

---

## 📑 Daftar Isi

1. [Database Schema](#database-schema)
2. [API Pattern](#api-pattern)
3. [Print Driver Configuration](#print-driver-configuration)
4. [User Settings Page Design](#user-settings-page-design)
5. [Best Practices](#best-practices)
6. [Implementation Guide](#implementation-guide)

---

## 🗄️ Database Schema

### 1. **Thermal Printer Settings Table**

Tabel utama untuk menyimpan konfigurasi printer dengan dukungan multi-brand dan multi-connection.

```sql
CREATE TABLE thermal_printer_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- Identitas Printer
    name VARCHAR(255) DEFAULT 'Default Thermal Printer',
    brand VARCHAR(100),                    -- Eppos, Xprinter, Star, Epson, Citizen, etc
    model VARCHAR(100),                    -- EP220II, XP-80C, TSP143, TM-T20, etc
    
    -- Connection Settings
    connection_type ENUM('usb','ethernet','bluetooth','serial','wifi') DEFAULT 'usb',
    ip_address VARCHAR(15),                -- Untuk ethernet/wifi
    port INT DEFAULT 9100,                 -- Standard ESC/POS port
    bluetooth_address VARCHAR(17),         -- MAC address format
    serial_port VARCHAR(50),               -- COM1, /dev/ttyUSB0, etc
    baud_rate INT DEFAULT 115200,         -- Serial/Bluetooth speed
    
    -- Paper Settings
    paper_width ENUM('58','80','112') DEFAULT '80',  -- mm
    paper_length INT DEFAULT 0,            -- 0 = continuous
    paper_type ENUM('thermal','impact') DEFAULT 'thermal',
    
    -- Print Settings
    print_speed ENUM('1','2','3','4','5') DEFAULT '2',    -- 1=fastest, 5=best quality
    print_density ENUM('1','2','3','4','5') DEFAULT '3',  -- 1=lightest, 5=darkest
    character_set VARCHAR(50) DEFAULT 'PC437',            -- PC437, PC850, PC852, PC858, PC866
    font_size ENUM('small','normal','large') DEFAULT 'normal',
    auto_cut BOOLEAN DEFAULT true,
    buzzer_enabled BOOLEAN DEFAULT false,
    
    -- ESC Commands
    esc_commands JSON,                     -- Custom ESC commands per brand
    init_command VARCHAR(255),             -- Initialization command
    cut_command VARCHAR(255),              -- Default: \x1B\x69
    cash_drawer_command VARCHAR(255),      -- Cash drawer open command
    
    -- Layout Settings
    margin_left INT DEFAULT 0,
    margin_right INT DEFAULT 0,
    margin_top INT DEFAULT 0,
    margin_bottom INT DEFAULT 0,
    line_spacing INT DEFAULT 20,           -- ESC 3 parameter
    char_spacing INT DEFAULT 0,            -- ESC SP parameter
    
    -- Template Settings
    print_logo BOOLEAN DEFAULT false,
    header_text TEXT,
    footer_text TEXT,
    print_barcode BOOLEAN DEFAULT true,
    barcode_position ENUM('top','bottom') DEFAULT 'bottom',
    
    -- Status
    is_active BOOLEAN DEFAULT true,
    is_default BOOLEAN DEFAULT false,      -- Only one can be default
    capabilities JSON,                     -- Capabilities array
    notes TEXT,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    KEY idx_is_default (is_default, is_active),
    KEY idx_connection_type (connection_type),
    UNIQUE KEY unique_default (is_default)  -- Enforce single default
);
```

### 2. **User Printer Preferences Table**

Menyimpan preferensi individual user terhadap printer yang tersedia.

```sql
CREATE TABLE user_printer_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    
    -- User's printer choice
    receipt_printer_name VARCHAR(255),     -- Nama printer yang dipilih user
    receipt_paper_size VARCHAR(50) DEFAULT '80mm',
    
    -- Print behavior
    auto_print_receipt BOOLEAN DEFAULT false,
    print_customer_copy BOOLEAN DEFAULT false,
    
    -- Advanced settings (JSON for flexibility)
    printer_settings JSON,                 -- User overrides
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3. **Printer Settings Table** (System-wide)

Konfigurasi default sistem untuk semua user.

```sql
CREATE TABLE printer_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    -- Default Settings
    receipt_paper_size VARCHAR(50) DEFAULT '80mm',
    auto_print_receipt BOOLEAN DEFAULT false,
    default_receipt_printer VARCHAR(255),
    print_customer_copy BOOLEAN DEFAULT false,
    receipt_copies INT DEFAULT 1,
    
    -- Advanced
    thermal_printer_commands TEXT,
    printer_profiles JSON,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. **Database Schema Diagram**

```
┌─────────────────────────────────┐
│      Users                       │
│─────────────────────────────────│
│ id (PK)                          │
│ name                             │
│ email                            │
└────────────┬────────────────────┘
             │ (1:1)
             │
             ├──────────────────────────────────┐
             │                                   │
             ▼                                   ▼
┌──────────────────────────────────┐  ┌──────────────────────────────┐
│ UserPrinterPreferences           │  │ PrinterSettings (Global)     │
│──────────────────────────────────│  │──────────────────────────────│
│ id (PK)                          │  │ id (PK)                      │
│ user_id (FK)                     │  │ receipt_paper_size           │
│ receipt_printer_name             │  │ default_receipt_printer      │
│ receipt_paper_size               │  │ auto_print_receipt           │
│ auto_print_receipt               │  │ receipt_copies               │
│ printer_settings (JSON)          │  └──────────────────────────────┘
└──────────────────────────────────┘
             │ references
             ▼
┌──────────────────────────────────┐
│ ThermalPrinterSettings           │
│──────────────────────────────────│
│ id (PK)                          │
│ name                             │
│ brand                            │
│ model                            │
│ connection_type                  │
│ ip_address / serial_port         │
│ paper_width                      │
│ print_speed / print_density      │
│ esc_commands (JSON)              │
│ is_default (UNIQUE)              │
│ is_active                        │
└──────────────────────────────────┘
```

---

## 🔌 API Pattern

### 1. **REST API Endpoints**

#### **A. System Printer Settings**

```
GET    /api/printer/system-settings
POST   /api/printer/system-settings
```

**Response:**
```json
{
  "receipt_paper_size": "80mm",
  "auto_print_receipt": false,
  "default_receipt_printer": "EPSON TM-T20II",
  "receipt_copies": 1,
  "available_printers": [
    {
      "id": 1,
      "name": "EPSON TM-T20II",
      "brand": "Epson",
      "connection_type": "usb",
      "is_default": true,
      "is_active": true
    },
    {
      "id": 2,
      "name": "Xprinter XP-80C",
      "brand": "Xprinter",
      "connection_type": "ethernet",
      "is_default": false,
      "is_active": true
    }
  ]
}
```

#### **B. User Printer Preferences**

```
GET    /api/printer/user-preferences
       → Returns user's custom settings or system defaults

POST   /api/printer/user-preferences
       → Save/update user printer preferences

BODY:
{
  "receipt_printer_name": "EPSON TM-T20II",
  "receipt_paper_size": "80mm",
  "auto_print_receipt": true,
  "print_customer_copy": false,
  "printer_settings": {
    "print_copies": 1,
    "auto_print_delay": 500
  }
}
```

#### **C. Available Printer Profiles**

```
GET    /api/printer/profiles
```

**Response:**
```json
{
  "thermal": {
    "paper_sizes": ["58mm", "80mm"],
    "default_settings": {
      "font_family": "Courier New",
      "font_size": "12px",
      "margins": "0",
      "line_spacing": "normal"
    },
    "capabilities": {
      "auto_cut": true,
      "cash_drawer": true,
      "barcode": ["CODE39", "CODE128", "QR"]
    }
  },
  "dot_matrix": {
    "paper_sizes": ["letter", "a4"],
    "default_settings": {
      "font_family": "Courier New",
      "font_size": "10px",
      "margins": "0.5in",
      "line_spacing": "condensed"
    }
  },
  "laser": {
    "paper_sizes": ["letter", "a4"],
    "default_settings": {
      "font_family": "Arial",
      "font_size": "12px",
      "margins": "0.5in",
      "line_spacing": "normal"
    }
  }
}
```

#### **D. Thermal Printer Management**

```
GET    /api/thermal-printer
       → List all thermal printers

GET    /api/thermal-printer/{id}
       → Get specific printer details

POST   /api/thermal-printer
       → Create new printer configuration

PUT    /api/thermal-printer/{id}
       → Update printer settings

DELETE /api/thermal-printer/{id}
       → Delete printer (if not default)

POST   /api/thermal-printer/{id}/set-default
       → Set as default printer

GET    /api/thermal-printer/{id}/test-connection
       → Test printer connection

POST   /api/thermal-printer/{id}/print-test
       → Send test print job
```

### 2. **API Request/Response Examples**

#### **Create Thermal Printer**

```http
POST /api/thermal-printer
Content-Type: application/json

{
  "name": "Receipt Printer #1",
  "brand": "Epson",
  "model": "TM-T20",
  "connection_type": "ethernet",
  "ip_address": "192.168.1.50",
  "port": 9100,
  "paper_width": "80",
  "print_speed": "2",
  "print_density": "3",
  "auto_cut": true,
  "buzzer_enabled": true,
  "is_default": true
}
```

**Response (201 Created):**
```json
{
  "id": 1,
  "name": "Receipt Printer #1",
  "brand": "Epson",
  "model": "TM-T20",
  "connection_type": "ethernet",
  "ip_address": "192.168.1.50",
  "is_default": true,
  "is_active": true,
  "created_at": "2025-11-17T10:30:00Z",
  "message": "Printer created successfully"
}
```

#### **Test Printer Connection**

```http
GET /api/thermal-printer/1/test-connection
```

**Response (Success):**
```json
{
  "status": "success",
  "message": "Network connection successful",
  "printer": {
    "id": 1,
    "name": "Receipt Printer #1",
    "connection_type": "ethernet"
  }
}
```

**Response (USB Error):**
```json
{
  "status": "error",
  "message": "Cannot connect to printer: Device not found",
  "connection_type": "usb"
}
```

### 3. **API Authentication & Hierarchy**

```
Public Routes:
- GET /api/printer/profiles       → No auth needed

Protected Routes (Authenticated Users):
- GET /api/printer/user-preferences    → Own preferences only
- POST /api/printer/user-preferences   → Own preferences only

Admin Only Routes:
- GET /api/printer/system-settings          → View
- POST /api/printer/system-settings         → Update
- GET /api/thermal-printer                  → List all
- POST /api/thermal-printer                 → Create
- PUT /api/thermal-printer/{id}             → Update
- DELETE /api/thermal-printer/{id}          → Delete
- POST /api/thermal-printer/{id}/set-default → Set default
```

---

## ⚙️ Print Driver Configuration

### 1. **Configuration File Structure**

```php
// config/printer.php

return [
    // USB Configuration
    'usb' => [
        'device_path' => env('PRINTER_USB_DEVICE_PATH', '/dev/usb/lp0'),
        'windows_device' => env('PRINTER_WINDOWS_DEVICE'),
    ],

    // Network Configuration
    'network' => [
        'default_port' => env('PRINTER_PORT', 9100),
        'timeout' => env('PRINTER_TIMEOUT', 5),
        'buffer_size' => 65536,
    ],

    // Serial Configuration
    'serial' => [
        'default_baud_rate' => env('PRINTER_BAUD_RATE', 115200),
        'flow_control' => 'none',
        'parity' => 'none',
    ],

    // Bluetooth Configuration
    'bluetooth' => [
        'service_uuid' => '00001101-0000-1000-8000-00805f9b34fb',
        'rfcomm_channel' => 1,
    ],

    // Driver Selection
    'preferred_driver' => env('PRINTER_DRIVER', 'mike42'),
    'allow_system_commands' => env('PRINTER_ALLOW_SYSTEM_COMMANDS', true),

    // Printer Presets
    'presets' => [
        'eppos_ep220ii' => [
            'brand' => 'Eppos',
            'model' => 'EP220II',
            'init_command' => '\x1B\x40\x1B\x32\x1B\x4D\x00',
            'cut_command' => '\x1B\x69',
            'paper_width' => 80,
            'capabilities' => ['auto_cut', 'cash_drawer', 'barcode'],
        ],
        'xprinter_xp80c' => [
            'brand' => 'Xprinter',
            'model' => 'XP-80C',
            'init_command' => '\x1B\x40\x1B\x32',
            'cut_command' => '\x1B\x6D',
            'paper_width' => 80,
            'capabilities' => ['auto_cut', 'cash_drawer', 'barcode'],
        ],
        'epson_tm_t20' => [
            'brand' => 'Epson',
            'model' => 'TM-T20',
            'init_command' => '\x1B\x40',
            'cut_command' => '\x1B\x69',
            'cash_drawer_command' => '\x1B\x70\x00\x19\x19',
            'paper_width' => 80,
            'capabilities' => ['auto_cut', 'cash_drawer', 'barcode'],
        ],
    ],
];
```

### 2. **ESC/POS Command Reference**

Printer thermal menggunakan ESC (Extended Control Character Set) commands.

#### **Initialization Commands**

| Command | Hex | Function | Notes |
|---------|-----|----------|-------|
| ESC @ | 1B 40 | Initialize printer | Resets to default |
| ESC 2 | 1B 32 | Default line spacing | ~1/6 inch |
| ESC 3 | 1B 33 (n) | Set line spacing | n in 1/120" units |
| ESC M | 1B 4D (n) | Font selection | 0=A, 1=B |
| ESC ! | 1B 21 (n) | Print mode | Bold, underline, etc |

#### **Text Format Commands**

| Command | Hex | Function |
|---------|-----|----------|
| ESC a | 1B 61 (n) | Text alignment | 0=Left, 1=Center, 2=Right |
| ESC E | 1B 45 (n) | Bold on/off | 1=ON, 0=OFF |
| ESC - | 1B 2D (n) | Underline | 1=ON, 0=OFF |
| ESC SP | 1B 20 (n) | Character spacing | n in 1/120" |
| ESC d | 1B 64 (n) | Line feed | n lines |

#### **Paper Cut Commands**

| Printer Type | Command | Hex |
|--------------|---------|-----|
| Epson/Generic | ESC i | 1B 69 |
| Star | ESC d | 1B 64 02 |
| Xprinter | ESC m | 1B 6D |
| Citizen | ESC m | 1B 6D |

#### **Cash Drawer Commands**

| Command | Hex | Function |
|---------|-----|----------|
| Epson | ESC p | 1B 70 00 19 19 |
| Star | ESC p | 1B 70 00 19 19 |
| Generic | ESC / | 1B 2F 01 |

### 3. **Driver Integration**

```php
// app/Services/ThermalPrinterService.php

namespace App\Services;

class ThermalPrinterService
{
    private $printer;
    private $settings;
    private $driver;

    public function __construct(ThermalPrinterSetting $settings)
    {
        $this->settings = $settings;
        $this->selectDriver();
        $this->initializePrinter();
    }

    private function selectDriver()
    {
        $preferred = config('printer.preferred_driver', 'mike42');

        if ($preferred === 'mike42' && $this->canUseMike42()) {
            $this->driver = new Mike42Driver($this->settings);
        } else {
            $this->driver = new NativeDriver($this->settings);
        }
    }

    public function print($content, $options = [])
    {
        try {
            $this->driver->initialize();
            $this->driver->write($content);
            
            if ($options['auto_cut'] ?? true) {
                $this->driver->cut();
            }
            
            if ($options['cash_drawer'] ?? false) {
                $this->driver->openCashDrawer();
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function testConnection()
    {
        return $this->settings->testConnection();
    }
}
```

---

## 🎨 User Settings Page Design

### 1. **Printer Settings UI Structure**

#### **Settings Hierarchy**

```
┌─────────────────────────────────────────────────────┐
│ Configuration → Printer Management                  │
└─────────────────────────────────────────────────────┘
         │
         ├─ Printer Settings (Global)
         │  └─ System-wide defaults for all users
         │
         ├─ Thermal Printers
         │  ├─ Add New Printer
         │  ├─ Edit Printer
         │  ├─ Delete Printer
         │  ├─ Test Connection
         │  └─ Set as Default
         │
         └─ User Profile (Per-user)
            └─ My Printer Preferences
               ├─ Select Preferred Printer
               ├─ Paper Size
               └─ Print Options
```

### 2. **System Settings Page** (`/printer-settings`)

```blade
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>System Printer Settings</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('printer-settings.update') }}">
            @csrf
            @method('PATCH')

            <div class="row">
                <!-- Paper Size -->
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Receipt Paper Size *</label>
                        <select name="receipt_paper_size" class="form-control">
                            <option value="58mm">58mm (Small Thermal)</option>
                            <option value="80mm" selected>80mm (Standard Thermal)</option>
                            <option value="letter">Letter (8.5" x 11")</option>
                            <option value="a4">A4 (210mm x 297mm)</option>
                        </select>
                        <small class="form-text text-muted">
                            Default for all receipts
                        </small>
                    </div>
                </div>

                <!-- Default Printer -->
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Default Receipt Printer</label>
                        <select name="default_receipt_printer" class="form-control">
                            <option value="">Select a printer...</option>
                            @foreach($activePrinters as $p)
                                <option value="{{ $p->id }}"
                                    {{ $printerSettings->default_receipt_printer == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->brand }})
                                </option>
                            @endforeach
                        </select>
                        <small>Printer to use when no user preference is set</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Copies -->
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Receipt Copies</label>
                        <select name="receipt_copies" class="form-control">
                            <option value="1">1 Copy</option>
                            <option value="2">2 Copies</option>
                            <option value="3">3 Copies</option>
                        </select>
                    </div>
                </div>

                <!-- Auto Print -->
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="auto_print_receipt" 
                                   id="auto_print_receipt" class="form-check-input" value="1">
                            <label class="form-check-label" for="auto_print_receipt">
                                Auto Print After Sale
                            </label>
                        </div>
                        <small>Automatically print receipt when sale completed</small>
                    </div>
                </div>

                <!-- Customer Copy -->
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="print_customer_copy" 
                                   id="print_customer_copy" class="form-check-input" value="1">
                            <label class="form-check-label" for="print_customer_copy">
                                Print Customer Copy
                            </label>
                        </div>
                        <small>Print additional copy for customer</small>
                    </div>
                </div>
            </div>

            <hr>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <a href="{{ route('home') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
```

### 3. **Thermal Printers Management Page** (`/thermal-printer`)

```blade
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Thermal Printers</h3>
        <a href="{{ route('thermal-printer.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Add Printer
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Brand / Model</th>
                    <th>Connection</th>
                    <th>Paper Size</th>
                    <th>Status</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($printers as $printer)
                <tr>
                    <td>{{ $printer->name }}</td>
                    <td>{{ $printer->brand }} / {{ $printer->model }}</td>
                    <td>
                        <span class="badge badge-info">
                            {{ ucfirst($printer->connection_type) }}
                            @if($printer->connection_type === 'ethernet')
                                {{ $printer->ip_address }}:{{ $printer->port }}
                            @endif
                        </span>
                    </td>
                    <td>{{ $printer->paper_width }}mm</td>
                    <td>
                        @if($printer->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($printer->is_default)
                            <span class="badge badge-primary">✓ Default</span>
                        @else
                            <a href="{{ route('thermal-printer.set-default', $printer) }}" 
                               class="badge badge-light">Set Default</a>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('thermal-printer.edit', $printer) }}" 
                               class="btn btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" 
                                    onclick="testPrinter({{ $printer->id }})"
                                    class="btn btn-outline-info" title="Test">
                                <i class="bi bi-play"></i>
                            </button>
                            <form method="POST" 
                                  action="{{ route('thermal-printer.destroy', $printer) }}"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        title="Delete"
                                        onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function testPrinter(printerId) {
    fetch(`/api/thermal-printer/${printerId}/test-connection`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                alert('✓ ' + data.message);
            } else {
                alert('✗ ' + data.message);
            }
        })
        .catch(e => alert('Error: ' + e));
}
</script>
@endsection
```

### 4. **User Printer Preferences Widget**

```blade
<!-- Dalam user profile settings -->

<div class="card">
    <div class="card-header">
        <h5>My Printer Preferences</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('api.printer.user-preferences') }}"
              data-ajax="true">
            @csrf

            <!-- Select Printer -->
            <div class="form-group">
                <label>Preferred Receipt Printer</label>
                <select name="receipt_printer_name" class="form-control" 
                        id="userPrinterSelect">
                    <option value="">Use System Default</option>
                    @foreach($availablePrinters as $printer)
                        <option value="{{ $printer->name }}"
                            {{ $userPreference->receipt_printer_name === $printer->name ? 'selected' : '' }}>
                            {{ $printer->name }}
                            @if($printer->is_default) (Default) @endif
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    Leave empty to use system default
                </small>
            </div>

            <!-- Paper Size -->
            <div class="form-group">
                <label>Paper Size</label>
                <select name="receipt_paper_size" class="form-control">
                    <option value="58mm"
                        {{ $userPreference->receipt_paper_size === '58mm' ? 'selected' : '' }}>
                        58mm (Small)
                    </option>
                    <option value="80mm"
                        {{ $userPreference->receipt_paper_size === '80mm' ? 'selected' : '' }}>
                        80mm (Standard)
                    </option>
                    <option value="letter"
                        {{ $userPreference->receipt_paper_size === 'letter' ? 'selected' : '' }}>
                        Letter
                    </option>
                    <option value="a4"
                        {{ $userPreference->receipt_paper_size === 'a4' ? 'selected' : '' }}>
                        A4
                    </option>
                </select>
            </div>

            <!-- Print Options -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="auto_print_receipt" value="1"
                           id="userAutoPrint" class="form-check-input"
                           {{ $userPreference->auto_print_receipt ? 'checked' : '' }}>
                    <label class="form-check-label" for="userAutoPrint">
                        Auto Print Receipt
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="print_customer_copy" value="1"
                           id="userCustomerCopy" class="form-check-input"
                           {{ $userPreference->print_customer_copy ? 'checked' : '' }}>
                    <label class="form-check-label" for="userCustomerCopy">
                        Print Customer Copy
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Preferences</button>
        </form>
    </div>
</div>
```

---

## ✅ Best Practices

### 1. **Database Best Practices**

| Aspek | Practice |
|-------|----------|
| **Default Printer** | Gunakan UNIQUE constraint untuk ensure hanya 1 default |
| **Connection Retry** | Store `last_connection_test` timestamp |
| **Audit Trail** | Log semua perubahan printer settings |
| **Soft Delete** | Gunakan soft delete untuk historical tracking |
| **Indexing** | Index pada `is_default`, `is_active`, `connection_type` |

### 2. **API Best Practices**

```
✓ RESTful Design:
  - Use HTTP verbs correctly (GET, POST, PUT, DELETE)
  - Version API endpoints: /api/v1/printer/...
  - Use consistent JSON response format

✓ Error Handling:
  - Return appropriate HTTP status codes
  - Include error details in response body
  - Log errors for debugging

✓ Rate Limiting:
  - Implement rate limiting for printer test endpoints
  - Cache connection test results (1-5 minutes)

✓ Validation:
  - Validate IP addresses, port numbers
  - Test connection before saving
  - Warn about conflicts with system settings
```

**Standard Response Format:**

```json
{
  "success": true|false,
  "message": "Human readable message",
  "data": { /* actual response */ },
  "errors": [ /* validation errors if any */ ]
}
```

### 3. **UI/UX Best Practices**

| Element | Practice |
|---------|----------|
| **Form Validation** | Real-time validation + server-side |
| **Test Button** | Provide immediate feedback |
| **Error Messages** | Clear, actionable messages |
| **Presets** | Pre-populate common printer models |
| **Help Text** | Inline help for technical settings |
| **Visual Feedback** | Status badges, color coding |

### 4. **Security Best Practices**

```
✓ Authentication:
  - All printer endpoints require auth
  - Admin-only for system settings
  - User can only modify own preferences

✓ Authorization:
  - Use Laravel Policies
  - Check user role/permission before allowing changes

✓ Input Validation:
  - Validate all inputs (IP, port, etc.)
  - Sanitize ESC commands
  - Prevent command injection

✓ Data Protection:
  - Don't log sensitive connection details
  - Encrypt stored passwords if needed
  - Use HTTPS for network printers
```

### 5. **Connection Management**

```php
// Connection Priorities
1. User Preference
2. Default Printer (if no user preference)
3. System Default
4. First Active Printer
5. Fall back to PDF printer

// Connection Testing Strategy
- Cache test results (5 minutes)
- Retry failed tests asynchronously
- Notify admins of connection failures
- Maintain connection status history
```

---

## 🚀 Implementation Guide

### 1. **Setup Steps**

```bash
# 1. Create database tables
php artisan migrate

# 2. Create default printer via Tinker
php artisan tinker
>>> App\Models\ThermalPrinterSetting::create([
    'name' => 'Default Thermal Printer',
    'brand' => 'Generic',
    'model' => '80mm',
    'connection_type' => 'usb',
    'paper_width' => '80',
    'is_default' => true,
    'is_active' => true
])

# 3. Seed printer presets
php artisan db:seed --class=PrinterPresetsSeeder
```

### 2. **Priority Selection Logic**

```php
// Bagaimana sistem memilih printer untuk digunakan

public static function selectPrinterForUser(User $user)
{
    // 1. Cek user preference
    $userPref = $user->printerPreference;
    if ($userPref && $userPref->receipt_printer_name) {
        return ThermalPrinterSetting::where('name', $userPref->receipt_printer_name)
            ->where('is_active', true)
            ->first();
    }

    // 2. Cek system default
    $default = ThermalPrinterSetting::where('is_default', true)
        ->where('is_active', true)
        ->first();
    
    if ($default) return $default;

    // 3. Fallback: printer pertama yang aktif
    return ThermalPrinterSetting::where('is_active', true)
        ->orderBy('created_at')
        ->first();
}
```

### 3. **Typical Workflow**

```
User melakukan transaksi penjualan:
│
├─ Receipt dibuat
├─ Cek user printer preference
│  ├─ If exists & active → gunakan
│  ├─ Else cek system default
│  │  ├─ If exists → gunakan
│  │  └─ Else use first active
├─ Prepare print content
├─ Initialize printer (ESC @, etc)
├─ Send content dengan proper encoding
├─ Execute paper cut (jika auto_cut=true)
├─ Open cash drawer (jika setting aktif)
└─ Log print transaction

Error handling:
- Connection failed → queue untuk retry
- Printer offline → show notification
- Command error → fallback ke PDF
```

---

## 📊 Comparison: Triangle POS vs Crater

| Feature | Triangle POS (Nameless) | Crater |
|---------|-------------------------|--------|
| **Multiple Printers** | ✓ Yes (thermal_printer_settings) | ✗ Limited |
| **User Preferences** | ✓ Yes (user_printer_preferences) | ✗ Global only |
| **Connection Types** | ✓ 5 types (USB/Ethernet/Serial/BT/WiFi) | ✓ 2-3 types |
| **ESC/POS Commands** | ✓ Full support + presets | ✓ Basic support |
| **Brand Presets** | ✓ 5+ brands | ✗ Generic |
| **UI Settings Page** | ✓ Comprehensive | ✓ Simple |
| **API Endpoints** | ✓ Full REST API | ✓ Limited |
| **Connection Testing** | ✓ Real-time test | ✓ Manual setup |

---

## 📚 References

- **ESC/POS Specification**: 80MM Thermal Receipt Printer Programmer Manual
- **Framework**: Laravel 10+ with Blade templating
- **API Pattern**: RESTful with JSON responses
- **UI Framework**: Bootstrap 5 with AdminLTE components
- **Database**: MySQL 8.0+

---

**Document Version**: 1.0  
**Last Updated**: November 17, 2025  
**Prepared by**: Analysis Team  
**Status**: Complete ✓
