# ⚡ Nameless.POS - Quick Developer Reference

## 🎯 **Fast Access Guide for Common Tasks**

---

## 🔧 **Quick File Locations**

### **🏠 Main Pages:**
| Page | Controller | View | Route |
|------|------------|------|-------|
| Dashboard | `app/Http/Controllers/HomeController.php` | `resources/views/home.blade.php` | `/home` |
| Users | `Modules/User/Http/Controllers/UsersController.php` | `Modules/User/Resources/views/users/index.blade.php` | `/users` |
| Products | `Modules/Product/Http/Controllers/ProductController.php` | `Modules/Product/Resources/views/products/index.blade.php` | `/products` |
| POS | `Modules/Sale/Http/Controllers/PosController.php` | `Modules/Sale/Resources/views/pos.blade.php` | `/pos` |
| Sales | `Modules/Sale/Http/Controllers/SaleController.php` | `Modules/Sale/Resources/views/index.blade.php` | `/sales` |
| Settings | `Modules/Setting/Http/Controllers/SettingController.php` | `Modules/Setting/Resources/views/index.blade.php` | `/settings` |

### **📊 Reports:**
| Report | Controller | DataTable | View |
|--------|------------|-----------|------|
| Sales Report | `Modules/Reports/Http/Controllers/ReportsController@salesReport` | `Modules/Reports/DataTables/SalesReportDataTable.php` | `Modules/Reports/Resources/views/sales/index.blade.php` |
| Purchase Report | `Modules/Reports/Http/Controllers/ReportsController@purchasesReport` | `Modules/Reports/DataTables/PurchasesReportDataTable.php` | `Modules/Reports/Resources/views/purchases/index.blade.php` |

---

## 🗂️ **Critical Models & Relationships**

### **🏗️ Core Models:**
```php
// User Model
app/Models/User.php
    ->hasMany(Sale::class)
    ->hasMany(Purchase::class)
    ->hasOne(UserPrinterPreference::class)

// Product Model  
Modules/Product/Entities/Product.php
    ->belongsTo(Category::class)
    ->hasMany(SaleDetails::class)
    ->hasMany(PurchaseDetails::class)

// Sale Model
Modules/Sale/Entities/Sale.php
    ->belongsTo(User::class)
    ->belongsTo(Customer::class) 
    ->hasMany(SaleDetails::class)
    ->hasMany(SalePayment::class)
```

---

## 🔍 **Common Debugging Locations**

### **🚨 When Things Break:**

#### **DataTable Issues:**
1. **Check**: Browser Network tab for 500 errors
2. **Log**: `storage/logs/laravel.log`
3. **Permission**: Controller permission gates
4. **Query**: DataTable `query()` method

#### **Livewire Not Working:**
1. **Component**: `app/Livewire/` directory
2. **View**: `resources/views/livewire/`
3. **Events**: Check `$listeners` array
4. **Console**: Browser JavaScript errors

#### **Images Not Showing:**
1. **Storage Link**: `php artisan storage:link`
2. **File Path**: `storage/app/public/`
3. **Database**: Check image field in database
4. **Permissions**: 755 folders, 644 files

#### **Login Problems:**
1. **User Table**: Check user exists and active
2. **Roles**: `model_has_roles` table
3. **Permissions**: Via Spatie package
4. **Session**: Clear browser cookies

---

## 📱 **API Endpoints Reference**

### **🖨️ Printer API:**
```php
GET  /api/system-printer-settings     // Get system printer config
GET  /api/user-printer-preferences    // Get user printer settings  
POST /api/user-printer-preferences    // Save user printer settings
```

### **🔍 Search APIs:**
```php
// Via Livewire (real-time)
app/Livewire/Pos/ProductList.php     // Product search in POS
app/Livewire/SearchProduct.php       // General product search
```

---

## 🗄️ **Database Quick Reference**

### **📋 Important Tables:**
| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `users` | User management | → `sales`, `purchases` |
| `products` | Product catalog | → `sale_details`, `purchase_details` |
| `sales` | Sales transactions | ← `users`, `customers` → `sale_details` |
| `sale_details` | Sales line items | ← `sales`, `products` |
| `customers` | Customer management | → `sales`, `sales_returns` |
| `settings` | System config | Used globally |
| `printer_settings` | Printer config | System settings |

### **🔑 Key Foreign Keys:**
```sql
sales.user_id -> users.id
sales.customer_id -> customers.id  
sale_details.sale_id -> sales.id
sale_details.product_id -> products.id
products.category_id -> categories.id
```

---

## ⚙️ **Configuration Files**

### **📁 Key Config Locations:**
```php
config/modules.php           // Module configuration
config/datatables.php        // DataTables settings
config/livewire.php          // Livewire configuration  
config/permission.php        // Spatie permissions
config/filesystems.php       // File storage config
.env                        // Environment variables
```

### **🔧 Environment Variables:**
```bash
APP_NAME="Nameless.POS"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=nameless_pos
MAIL_MAILER=smtp
```

---

## 🎨 **Frontend Files**

### **📱 Layouts:**
```php
resources/views/layouts/app.blade.php      // Main layout
resources/views/layouts/sidebar.blade.php  // Sidebar with logo
resources/views/layouts/menu.blade.php     // Navigation menu
resources/views/layouts/header.blade.php   // Top header
```

### **💅 Assets:**
```php
resources/sass/app.scss       // SCSS source
resources/js/app.js           // JavaScript source
public/css/app.css           // Compiled CSS
public/js/app.js             // Compiled JS  
public/js/printer-detection.js  // Custom printer JS
```

### **🎯 Livewire Components:**
```php
app/Livewire/ProductCart.php           // Shopping cart
app/Livewire/Pos/ProductList.php       // Product listing
app/Livewire/Pos/Checkout.php          // Checkout process
app/Livewire/SearchProduct.php         // Product search
```

---

## 🔐 **Permission System**

### **🎭 Roles:**
- **Admin**: Full access
- **Manager**: Sales, purchases, reports
- **Cashier**: POS only
- **Owner**: Reports only

### **🔑 Key Permissions:**
```php
// Product permissions
'access_products', 'create_products', 'edit_products', 'delete_products'

// Sales permissions  
'access_sales', 'create_sales', 'edit_sales', 'delete_sales'

// User permissions
'access_users', 'create_users', 'edit_users', 'delete_users'

// Settings permissions
'access_settings', 'edit_settings'

// Reports permissions
'access_reports'
```

### **🚪 Gate Usage:**
```php
// In controllers
abort_if(Gate::denies('access_products'), 403);

// In views
@can('create_sales')
    <a href="/sales/create">Create Sale</a>
@endcan
```

---

## 🛠️ **Common Artisan Commands**

### **🔧 Development:**
```bash
# Module management
php artisan module:list              # List all modules
php artisan module:make ModuleName   # Create new module
php artisan module:enable ModuleName # Enable module

# Database
php artisan migrate                  # Run migrations
php artisan db:seed                  # Run seeders
php artisan migrate:fresh --seed     # Fresh database

# Cache management
php artisan cache:clear              # Clear application cache
php artisan view:clear               # Clear compiled views
php artisan config:cache             # Cache configuration

# Storage
php artisan storage:link             # Create storage symlink

# Livewire
php artisan livewire:make ComponentName  # Create Livewire component
```

---

## 📦 **Package Management**

### **🔧 Key Packages:**
```bash
# DataTables
composer require yajra/laravel-datatables-oracle

# Livewire  
composer require livewire/livewire

# Permissions
composer require spatie/laravel-permission

# Excel Export
composer require maatwebsite/excel

# PDF Export  
composer require barryvdh/laravel-snappy

# Shopping Cart
composer require darryldecode/shopping-cart
```

---

## 🔍 **Troubleshooting Checklist**

### **✅ General Issues:**
- [ ] Check `storage/logs/laravel.log`
- [ ] Verify database connection (`.env`)
- [ ] Clear cache (`php artisan cache:clear`)
- [ ] Check file permissions (755/644)
- [ ] Verify storage link exists

### **✅ DataTable Issues:**
- [ ] Check controller permission gates
- [ ] Verify model relationships
- [ ] Check AJAX URL in browser Network tab
- [ ] Verify DataTable query method

### **✅ Image/File Issues:**
- [ ] Storage symlink created (`php artisan storage:link`)
- [ ] File exists in `storage/app/public/`
- [ ] Correct database path stored
- [ ] Web server has read permissions

### **✅ POS/Cart Issues:**
- [ ] Livewire components loaded
- [ ] Session working properly
- [ ] Product stock levels sufficient
- [ ] JavaScript console for errors

---

## 🚀 **Performance Tips**

### **⚡ Optimization:**
- Use eager loading: `->with(['relation'])`
- Cache frequently accessed data
- Optimize database queries
- Use proper indexing on foreign keys
- Paginate large datasets

### **🗄️ Database Optimization:**
```sql
-- Add indexes for better performance
ALTER TABLE sales ADD INDEX idx_date (date);
ALTER TABLE sale_details ADD INDEX idx_sale_id (sale_id);
ALTER TABLE products ADD INDEX idx_status (product_status);
```

---

**📅 Last Updated:** November 2024  
**⚡ Purpose:** Quick reference for developers  
**🔧 Usage:** Keep this open while coding