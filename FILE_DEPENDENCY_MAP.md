# 📁 Nameless.POS - Complete File Dependency Mapping

## 🎯 **File-by-File Relationship Guide**

### **Untuk Developer: Tracking exact file relationships dan dependencies**

---

## 🗂️ **Core Laravel Files Dependencies**

### **`app/Http/Controllers/HomeController.php`**
**Dependencies (Files this file imports/uses):**
```php
use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Modules\Product\Entities\Product; 
use Modules\People\Entities\Customer;
use Modules\Purchase\Entities\Purchase;
```

**Used By (Files that call this file):**
- `routes/web.php` (line 25: `Route::get('/home', [HomeController::class, 'index'])`)

**Database Tables Accessed:**
- `sales` (via Sale model)
- `products` (via Product model) 
- `customers` (via Customer model)
- `purchases` (via Purchase model)

---

## 👥 **User Module File Dependencies**

### **`Modules/User/Http/Controllers/UsersController.php`**
**Dependencies:**
```php
use App\Models\User;
use Spatie\Permission\Models\Role;
use Modules\User\DataTables\UsersDataTable;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\Upload\Entities\Upload;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
```

**Used By:**
- `Modules/User/Routes/web.php` (all user routes)
- `Modules/User/Resources/views/users/*.blade.php` (form actions)

**Related Files:**
- Model: `app/Models/User.php` (with Spatie Media Library)
- DataTable: `Modules/User/DataTables/UsersDataTable.php`
- Views: `Modules/User/Resources/views/users/index.blade.php`
- Requests: 
  - `Modules/User/Http/Requests/StoreUserRequest.php`
  - `Modules/User/Http/Requests/UpdateUserRequest.php`
- Upload System:
  - `Modules/Upload/Entities/Upload.php`
  - `Modules/Upload/Http/Controllers/UploadController.php`

**Avatar Upload Flow:**
```
FilePond Upload (temp storage) 
↓ 
User Form Submit 
↓ 
Spatie Media Library (permanent storage)
↓
Database: media table + file: storage/app/public/media/{id}/
```

### **`Modules/User/DataTables/UsersDataTable.php`**
**Dependencies:**
```php
use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
```

**Used By:**
- `Modules/User/Http/Controllers/UsersController.php@index`

**Frontend Files:**
- JavaScript: Auto-generated DataTable JS
- CSS: `public/vendor/datatables/` styles
- AJAX Endpoint: Same controller (`UsersController@index`)

---

## 🛒 **Product Module File Dependencies**

### **`Modules/Product/Entities/Product.php`**
**Model Relationships (Other files this connects to):**
```php
public function category() {
    return $this->belongsTo(Category::class, 'category_id');
    // File: Modules/Product/Entities/Category.php
}

public function saleDetails() {
    return $this->hasMany(SaleDetails::class, 'product_id');
    // File: Modules/Sale/Entities/SaleDetails.php
}

public function purchaseDetails() {
    return $this->hasMany(PurchaseDetails::class, 'product_id');
    // File: Modules/Purchase/Entities/PurchaseDetails.php
}
```

**Used By (Files that import/use this model):**
- `Modules/Product/Http/Controllers/ProductController.php`
- `Modules/Product/DataTables/ProductDataTable.php`
- `Modules/Sale/Http/Controllers/PosController.php`
- `app/Livewire/Pos/ProductList.php`
- `app/Livewire/ProductCart.php`
- `Modules/Sale/Entities/SaleDetails.php`
- `Modules/Purchase/Entities/PurchaseDetails.php`
- `Modules/Reports/DataTables/SalesReportDataTable.php`

### **`Modules/Product/Http/Controllers/ProductController.php`**
**Dependencies:**
```php
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Category;
use Modules\Product\DataTables\ProductDataTable;
use Modules\Product\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
```

**Storage Dependencies:**
- Image Upload: `storage/app/public/products/`
- File Operations: Laravel Storage facade

---

## 💰 **Sales/POS Module Dependencies**

### **`app/Livewire/Pos/ProductList.php`**
**Dependencies:**
```php
use Livewire\Component;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\Category;
```

**Used By:**
- `Modules/Sale/Resources/views/pos.blade.php` (Livewire component tag)

**Frontend Dependencies:**
- View: `resources/views/livewire/pos/product-list.blade.php`
- CSS: Livewire styles + custom POS styles
- JavaScript: Livewire JavaScript + custom interactions

### **`app/Livewire/ProductCart.php`**
**Dependencies:**
```php
use Livewire\Component;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Modules\Product\Entities\Product;
```

**External Package Dependencies:**
- `darryldecode/shopping-cart` package
- Session storage for cart data

**Connected Components:**
- Listens to: `addToCart` events from ProductList
- Emits to: `cartUpdated` events to Checkout component

### **`Modules/Sale/Http/Controllers/PosController.php`**
**Dependencies:**
```php
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Modules\Product\Entities\Product;
use Modules\People\Entities\Customer;
use Illuminate\Support\Facades\DB;
use Darryldecode\Cart\Facades\CartFacade as Cart;
```

**Transaction Files (Database operations):**
- Creates: `sales` table records
- Creates: `sale_details` table records  
- Creates: `sale_payments` table records
- Updates: `products` table (stock quantities)

---

## 📊 **Reports Module Dependencies**

### **`Modules/Reports/DataTables/SalesReportDataTable.php`**
**Data Source Dependencies:**
```php
use Modules\Sale\Entities\Sale;
use Modules\People\Entities\Customer;
use App\Models\User;
```

**Query Relationships:**
```php
public function query(Sale $model) {
    return $model->newQuery()
        ->with(['customer:id,customer_name', 'user:id,name'])
        // Accesses: customers table, users table
        ->when(request('start_date'), function($query) {
            // Filter logic
        });
}
```

**Export Dependencies:**
- Excel: `maatwebsite/excel` package
- PDF: `barryvdh/laravel-snappy` package  
- Print: Custom CSS + JavaScript

---

## ⚙️ **Settings Module Dependencies**

### **`Modules/Setting/Http/Controllers/SettingController.php`**
**Dependencies:**
```php
use Modules\Setting\Entities\Setting;
use Modules\Currency\Entities\Currency;
use Modules\Setting\Http\Requests\StoreSettingsRequest;
use Illuminate\Support\Facades\Storage;
```

**File Storage Dependencies:**
- Logo Upload: `storage/app/public/logos/`
- Old File Cleanup: Automatic deletion of previous logos

**Global Usage (Files that access settings):**
- `resources/views/layouts/sidebar.blade.php` (company logo)
- `resources/views/auth/login.blade.php` (login logo)
- All modules that need: currency, company info, system config

---

## 🔄 **Frontend File Dependencies**

### **`resources/views/layouts/app.blade.php`** (Main Layout)
**CSS Dependencies:**
```html
<!-- CoreUI CSS -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
<!-- Custom CSS -->
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
```

**JavaScript Dependencies:**
```html
<!-- Core JS -->
<script src="{{ asset('js/app.js') }}"></script>
<!-- DataTables JS -->
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<!-- Livewire -->
@livewireScripts
<!-- Custom JS -->
<script src="{{ asset('js/printer-detection.js') }}"></script>
```

### **`resources/views/layouts/sidebar.blade.php`**
**Data Dependencies:**
```php
@php
    $settings = \Modules\Setting\Entities\Setting::first();
    // Accesses: settings table for company logo/name
@endphp
```

**Connected Files:**
- Logo Images: From `storage/app/public/logos/`
- Menu Structure: `resources/views/layouts/menu.blade.php`
- Permission Checks: Via Spatie Permission package

### **`resources/views/layouts/menu.blade.php`**
**Permission Dependencies:**
```php
@can('access_products')
    <!-- Product menu items -->
@endcan
```

**Route Dependencies:**
- Links to all module routes
- Dynamic active state based on current route

---

## 🗄️ **Database File Relationships**

### **Migration Files Dependencies:**
```
database/migrations/
├── 2014_10_12_000000_create_users_table.php
├── 2021_07_16_220524_create_permission_tables.php (Spatie)
├── Modules/Product/Database/Migrations/
├── Modules/Sale/Database/Migrations/
└── ...
```

**Foreign Key Relationships:**
```sql
-- sales table
sale.user_id -> users.id
sale.customer_id -> customers.id

-- sale_details table  
sale_details.sale_id -> sales.id
sale_details.product_id -> products.id

-- products table
products.category_id -> categories.id
```

---

## 📦 **Package Dependencies Map**

### **Composer Dependencies (PHP):**
```json
{
    "laravel/framework": "^10.0",
    "livewire/livewire": "^3.0",
    "spatie/laravel-permission": "^5.5",
    "yajra/laravel-datatables-oracle": "^10.0",
    "nwidart/laravel-modules": "^10.0",
    "maatwebsite/excel": "^3.1",
    "barryvdh/laravel-snappy": "^1.0"
}
```

### **NPM Dependencies (JavaScript):**
```json
{
    "@coreui/coreui": "^4.2.0",
    "bootstrap": "^5.0.0",
    "jquery": "^3.6.0", 
    "datatables.net": "^1.13.0",
    "chart.js": "^3.0.0",
    "select2": "^4.1.0"
}
```

---

## 🔍 **Debugging File Tracking**

### **When Feature X Breaks, Check These Files:**

#### **User Login Issues:**
1. `routes/web.php` (auth routes)
2. `app/Http/Controllers/Auth/LoginController.php`
3. `resources/views/auth/login.blade.php`
4. `app/Models/User.php`
5. Database: `users`, `model_has_roles`, `roles` tables
6. Config: `config/auth.php`, `config/permission.php`

#### **DataTable Not Loading:**
1. Module Controller (`*Controller@index`)
2. DataTable Class (`*DataTable.php`)
3. Route definition (`Routes/web.php`)
4. Model relationships
5. Database permissions
6. Frontend: DataTables JS/CSS includes

#### **Image Upload Problems:**
1. Controller upload logic
2. Storage path (`storage/app/public/`)
3. Symbolic link (`public/storage -> storage/app/public`)
4. File permissions (755 for directories, 644 for files)
5. Model attribute (image path storage)
6. View display logic

#### **POS Cart Issues:**
1. `app/Livewire/ProductCart.php`
2. `app/Livewire/Pos/ProductList.php`
3. `darryldecode/cart` package
4. Session configuration
5. Product model stock updates
6. Frontend Livewire components

---

## 🔗 **External Service Dependencies**

### **File Storage:**
- **Local**: `storage/app/public/` (logos, products, avatars)
- **Public Access**: Via `public/storage/` symlink
- **Fallback**: `public/images/` (default logos)

### **Email Services:**
- Configuration: `config/mail.php`
- Views: `resources/views/emails/`
- Used by: Quotation module for sending quotes

### **PDF Generation:**
- Package: `barryvdh/laravel-snappy`
- Binary: `wkhtmltopdf` (system dependency)
- Used by: Reports for PDF export

---

## 🚀 **Performance Dependencies**

### **Caching Files:**
- `storage/framework/cache/` (application cache)
- `storage/framework/views/` (compiled Blade templates)
- `bootstrap/cache/` (config/route cache)

### **Session Files:**
- `storage/framework/sessions/` (file-based sessions)
- Used by: Cart functionality, authentication

### **Asset Compilation:**
- `public/css/app.css` (compiled from `resources/sass/`)
- `public/js/app.js` (compiled from `resources/js/`)
- Build tool: Laravel Vite

---

**📅 Last Updated:** November 2024  
**🎯 Purpose:** Complete file dependency tracking  
**👨‍💻 For:** Debugging & Development