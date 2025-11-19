# Nameless.POS - AI Agent Coding Instructions

## Project Overview
**Nameless.POS** is a production-ready Point of Sale (POS) system built with **Laravel 10**, structured as a modular application using **`nwidart/laravel-modules`**. Core features: sales/purchase transactions, inventory management, multi-connection thermal printer support, barcode generation, and business reporting.

**Critical Context**: This is a mature, production-deployed system with extensive existing architecture. Changes must be backward-compatible and follow established patterns rigorously.

---

## 🏗️ Architecture Essentials

### Modular Structure
Each module in `/Modules` is **completely self-contained** and must not directly reference other modules' internals:
```
Modules/ModuleName/
├── Entities/              # Eloquent models (ONLY public methods exported)
├── Http/Controllers/      # Request handling (use only via routes)
├── Database/Migrations/   # Schema changes (auto-discovered by Laravel)
├── DataTables/           # Server-side table processing
├── Resources/views/      # Module-scoped Blade templates
├── Routes/web.php        # Web routes (auto-registered via ServiceProvider)
├── Routes/api.php        # API routes (auto-registered via ServiceProvider)
└── Providers/            # Service provider (handles module registration)
```

**Key Modules (Business Domains):**
- `Sale` - POS transactions, real-time Livewire checkout
- `Purchase` - Purchase orders & supplier management
- `Product` - Catalog with barcode/SKU, multi-image support
- `People` - Customer & supplier data
- `Reports` - Analytics dashboard with Chart.js
- `SalesReturn`, `PurchasesReturn` - Return workflows
- `Setting` - Currency, units, system configuration

**⚠️ Module Dependency Rules:**
- Modules communicate via **Models & Routes**, NOT direct controller imports
- Never hardcode module paths—use `module()` helper
- Always check if dependent module exists before using its features
- Database constraints must use cascade/soft deletes for referential integrity

### Core App Files
- **`app/Models/User.php`** - User model with Spatie roles/permissions & printer preference relation
- **`app/Services/PrinterService.php`** - Facade for all printer operations with intelligent caching
- **`app/Services/PrinterDriverFactory.php`** - Factory pattern dispatching to 5 printer driver types
- **`app/Livewire/`** - Real-time components using Livewire 3:
  - `Pos/Checkout.php` - Shopping cart & payment processing
  - `SearchProduct.php` - Product search with real-time filtering
  - `ProductCart.php` - Dynamic cart management (stock validation, calculations)
  - `Barcode/` - Barcode generation & download
- **`app/Http/Controllers/`** - Global controllers:
  - `PrinterSettingController` - CRUD for thermal printer settings
  - `ScannerSettingsController` - External barcode scanner configuration
  - `HomeController` - Dashboard with Chart.js widgets
- **`app/Helpers/helpers.php`** - Custom helpers (auto-loaded via composer.json)

---

## 💾 Database & ORM Patterns

### Key Relationships
**Sales Flow:**
```
Sale (1) → (N) SaleDetails → Product
         → (N) SalePayments → PaymentMethod
         → (1) Customer

User (1) → (N) UserPrinterPreference → (N) ThermalPrinterSetting
```

**Purchase Flow:**
```
Purchase (1) → (N) PurchaseDetails → Product
            → (N) PurchasePayments
            → (1) Supplier
```

### Money Storage Convention
⚠️ **Critical:** All monetary amounts stored as **integers (cents)**. Convert with `/100` when displaying.

Example in `Modules/Sale/Entities/Sale.php`:
```php
protected $casts = [
    'grand_total' => 'integer', // Stored as cents: 10000 = $100.00
];

public function getGrandTotalAttribute($value) {
    return $value / 100; // Convert to dollars/currency
}
```

**When saving**: `$sale->grand_total = $userInput * 100;` (multiply by 100)

### Custom Reference Generation
Models use helper function `make_reference_id()` for unique identifiers:
```php
static::creating(function ($model) {
    $number = Sale::max('id') + 1;
    $model->reference = make_reference_id('SL', $number); // Generates: SL-001, SL-002, etc.
});
```

### Model Scopes (Query Optimization)
Define scopes for common filters to avoid N+1 queries:
```php
// In Modules/Sale/Entities/Sale.php
public function scopeCompleted($query) {
    return $query->where('status', 'Completed')->with('customer', 'details');
}

public function scopeThisMonth($query) {
    return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
}

// Usage:
$revenue = Sale::completed()->thisMonth()->sum('grand_total') / 100;
```

---

## 🔐 Security & Permissions

### Authorization Pattern
Use Spatie `laravel-permission` package. Check permissions in controllers:
```php
abort_if(Gate::denies('access_products'), 403);
```

**Permission Naming:** `action_module` (e.g., `create_products`, `delete_sales`)

### Role Setup
Default roles: **Admin**, **Manager**, **Cashier**, **Owner**

Create new permissions in seeders, then assign to roles.

---

## 📄 Frontend Architecture

### Livewire Components (Real-time UI)
Components in `app/Livewire/` using Livewire 3.x pattern:

**Key Components:**
- **`Pos/Checkout.php`** - Shopping cart with live totals, discount calculations
- **`SearchProduct.php`** - Product search with barcode scanner integration
- **`ProductCart.php`** - Dynamic cart management (stock validation, calculations)
- **`Barcode/Generator.php`** - Barcode generation with download

**Livewire Dispatch Pattern:**
```php
// Child to parent communication
$this->dispatch('productSelected', $product->id);

// Parent listening
protected $listeners = ['selectedCategory' => 'onCategoryChanged'];

// Handle event
public function onCategoryChanged($categoryId) {
    $this->products = Product::where('category_id', $categoryId)->get();
}
```

**Form Handling Pattern:**
```php
public $quantity = 1;
public $price = 0;

#[Computed]
public function total() {
    return $this->quantity * $this->price;
}

public function addToCart() {
    $this->validate([
        'quantity' => 'required|integer|min:1',
    ]);
    
    // Add to cart logic
    $this->dispatch('itemAdded', $this->quantity);
}
```

### Frontend Stack
- **CoreUI 3.2** - Admin dashboard theme (responsive layout)
- **Bootstrap 4.1** - CSS framework
- **DataTables 10** - Server-side table processing (`Modules/*/DataTables/`)
- **Chart.js** - Dashboard analytics & charts
- **SweetAlert2** - User notifications & confirmations
- **Alpine.js** - Lightweight DOM interactions

---

## 🖨️ Printer Integration (Recent Major Feature)

### Multi-Printer Architecture
- **Driver Factory Pattern:** `PrinterDriverFactory` creates drivers for 5 connection types
- **Service Layer:** `PrinterService` handles caching, selection logic, & testing
- **User Preferences:** `UserPrinterPreference` model for personal printer assignments

### Supported Connections
1. **Network** - IP:Port (fsockopen)
2. **USB** - Device files (`/dev/usb*`)
3. **Serial** - COM ports
4. **Windows** - Print command
5. **Bluetooth** - Mobile devices

### Service Methods & Caching
```php
// Intelligent printer selection: User pref → Default → First active
PrinterService::getActivePrinter($userId)

// Get specific printer (cached 1 hour)
PrinterService::getPrinter($printerId)

// Test connection with error handling
PrinterService::testConnection($printer)

// Print with options
PrinterService::print($content, ['user_id' => $userId])

// List all active printers
PrinterService::getAvailablePrinters()

// Clear all printer caches
PrinterService::clearCache()
```

**Cache Strategy:**
- User preferences: 1 hour TTL
- Default printer: 1 hour TTL
- All printers list: 5 minutes TTL
- Single printer: 1 hour TTL

### Routes
```
GET  /printer-settings          - List all printers
POST /printer-settings          - Create printer
GET  /printer-settings/create   - Create form
POST /printer-preferences       - Save user preference
POST /printer-settings/{id}/default - Set as default
GET  /printer-settings/{id}/test    - Test connection
DELETE /printer-settings/{id}   - Delete printer
```

---

## 🚀 Development Workflows

### Running Laravel Commands
```bash
# Migrations
php artisan migrate                    # Run pending migrations
php artisan migrate:refresh            # Reset & seed

# Caching (do this after code changes)
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Create new module
php artisan module:make ModuleName

# Create migration in module
php artisan module:make-migration create_table_name ModuleName

# Database tinker (interactive shell)
php artisan tinker
```

### Testing
Uses PHPUnit (`tests/` directory). Run with:
```bash
php artisan test
```

### Docker Deployment
```bash
docker build -t nameless-pos .
docker compose up
# App at http://localhost:8000
```

### Development Tools
- **Debugbar** - `barryvdh/laravel-debugbar` for request/query analysis
- **Tinker** - Interactive PHP shell with full app context
- **Code examples** - See `/CODE_REFERENCE.md` and `/IMPLEMENTATION_CODE_EXAMPLES.md`

---

## 🔍 Code Patterns & Conventions

### File Naming
- **Models:** PascalCase (`Product.php`)
- **Controllers:** PascalCase + Controller (`ProductController.php`)
- **Views:** kebab-case (`product-list.blade.php`)
- **Routes:** kebab-case (`/product-categories`)
- **Permissions:** snake_case (`access_products`)

### Exception Handling
Throw descriptive exceptions. Service layer catches and returns user-friendly messages:
```php
try {
    $driver->testConnection();
} catch (\Exception $e) {
    Log::error('Printer test failed', ['error' => $e->getMessage()]);
    return ['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()];
}
```

### Model Scope Patterns
Define query scopes for common filters to avoid N+1 queries:
```php
// In Modules/Sale/Entities/Sale.php
public function scopeCompleted($query) {
    return $query->where('status', 'Completed');
}

// Usage:
Sale::completed()->sum('total_amount');
```

### Caching Strategy
Use Laravel Cache with TTLs for high-frequency queries:
```php
Cache::remember("user_printer_pref_{$userId}", 3600, function () {
    return UserPrinterPreference::where('user_id', $userId)->first();
});
```

### Common Patterns & Anti-Patterns
✅ **DO:**
- Use model relationships to fetch data (eager loading)
- Cache query results for frequently accessed data
- Validate input at controller level
- Use service layer for business logic
- Clear caches after modifying data

❌ **DON'T:**
- Import controller methods directly across modules
- Use hardcoded paths (use `module()` helper instead)
- Store money as floats (always use integers in cents)
- Skip authorization checks
- Create untracked globals in controllers

---

## 📚 Essential Helper Functions

### Custom Helpers (in `app/Helpers/helpers.php`)
- **`make_reference_id($prefix, $number)`** - Generate unique IDs like 'SL-001'
- **`toast($message, $type)`** - Session-based notifications

---

## 🔗 Module Dependencies

### Product Module Used By
- **Sale** (select products for sales)
- **Purchase** (select products for purchases)
- **Quotation** (price quotes)
- **Reports** (product analytics)

### Sale Module Used By
- **SalesReturn** (process returns)
- **Reports** (sales reports)
- **Dashboard** (revenue metrics)

### When Modifying
- **Product changes** → Update Product, Sale, Purchase, Reports
- **Sale changes** → Update SalesReturn, Reports
- **User changes** → Update permissions, all modules using User model
- **Currency/Settings changes** → Clear cache: `php artisan cache:clear`

---

## ⚠️ Critical Gotchas

1. **Money Format:** Always divide by 100 when displaying, multiply by 100 when saving
2. **Module Routes:** Each module's routes must be registered (usually auto-loaded by service provider)
3. **Cache Invalidation:** After schema/permission changes, clear all caches
4. **Printer Selection Logic:** User preference > Default printer > First active printer
5. **Authorization Checks:** Always use `Gate::denies()` in controllers; 403 on denial
6. **Livewire Dispatch:** Events are case-sensitive and must match listeners exactly
7. **Module Communication:** Never directly import other module's controllers—use routes or model relationships only
8. **Data Consistency:** Always use transactions for multi-step operations (sales with payments, etc.)

---

## 📞 Documentation Reference

- **Multi-Printer Setup:** See `MULTI_PRINTER_IMPLEMENTATION.md`
- **Deployment:** See `DEPLOYMENT_CHECKLIST.md`
- **Quick Debug:** See `QUICK_DEBUG_COMMANDS.md`
- **Code Examples:** See `CODE_REFERENCE.md`
- **Architecture Details:** See `NAMELESS_POS_ARCHITECTURE.md`

---

## 🎯 When Adding Features

1. **New Business Domain?** → Create a module with `php artisan module:make ModuleName`
2. **Adding to Existing Module?** → Follow module's existing controller/model structure
3. **User-Specific Data?** → Add relationship to `User.php` & implement caching
4. **New Permission Needed?** → Create in seeder, assign to appropriate roles
5. **Database Change?** → Create migration with `php artisan module:make-migration` in the module
6. **Real-time UI?** → Create Livewire component in `app/Livewire/`
7. **Clear Caches** → After any migration or permission change

---

**Last Updated:** November 18, 2025  
**Stack:** Laravel 10 | PHP 8.1+ | MySQL 5.7+ | Livewire 3.0 | Spatie Permissions
