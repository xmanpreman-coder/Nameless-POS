# 🔗 Nameless.POS - Module Relationships & Dependencies

## 📊 **Visual Relationship Map**

```
                    ┌─────────────┐
                    │   Setting   │ ◄──────────────┐
                    │  (Core)     │                │
                    └──────┬──────┘                │
                           │                       │
                    ┌─────────────┐                │
                    │    User     │ ◄──────────────┤
                    │ (Auth Core) │                │
                    └──────┬──────┘                │
                           │                       │
              ┌────────────┼────────────┐          │
              │            │            │          │
    ┌─────────▼──┐  ┌──────▼──────┐  ┌──▼────┐    │
    │  People    │  │   Product   │  │ Upload │    │
    │(Customers/ │  │ (Catalog)   │  │(Files) │    │
    │ Suppliers) │  └──────┬──────┘  └────────┘    │
    └─────────┬──┘         │                       │
              │            │                       │
         ┌────▼────┐    ┌──▼──┐                    │
         │Currency │    │Units│                    │
         └─────────┘    └─────┘                    │
              │            │                       │
      ┌───────┴────────────┴───────┐               │
      │                            │               │
┌─────▼──────┐            ┌────────▼────┐          │
│   Sale     │ ◄─────────►│  Purchase   │          │
│ (POS/Sales)│            │ (Ordering)  │          │
└─────┬──────┘            └──────┬──────┘          │
      │                          │                 │
┌─────▼──────┐            ┌──────▼──────┐          │
│SalesReturn │            │PurchaseReturn│          │
└─────┬──────┘            └──────┬──────┘          │
      │                          │                 │
      │     ┌────────────┐        │                 │
      └────►│ Quotation  │◄───────┘                │
            │ (Quotes)   │                         │
            └─────┬──────┘                         │
                  │                                │
            ┌─────▼──────┐                         │
            │Adjustment  │                         │
            │(Inventory) │                         │
            └─────┬──────┘                         │
                  │                                │
            ┌─────▼──────┐                         │
            │  Reports   │─────────────────────────┘
            │(Analytics) │
            └────────────┘
```

## 🎯 **Detailed Module Dependencies**

### **1. Core Foundation Modules**

#### **Setting Module** 🔧
**Purpose**: System configuration and settings
**Dependencies**: None (Core module)
**Used By**: ALL modules

**Key Files:**
- `Entities/Setting.php` - System settings model
- `Controllers/SettingController.php` - Settings management
- `Resources/views/index.blade.php` - Settings interface

**Provides To Other Modules:**
```php
// Currency settings
settings()->default_currency_id
settings()->default_currency_position

// Company information
settings()->company_name
settings()->company_address
settings()->company_email
settings()->company_phone

// Logo management
settings()->site_logo
settings()->login_logo
```

#### **User Module** 👥
**Purpose**: User authentication and management
**Dependencies**: `Setting` (for system config)
**Used By**: All transaction modules

**Key Files:**
- `app/Models/User.php` - User model with roles
- `Controllers/UsersController.php` - User CRUD
- `Controllers/RolesController.php` - Role management

**Relationships:**
```php
// User has printer preferences
User::class -> hasOne(UserPrinterPreference::class)

// User has roles (Spatie Permission)
User::class -> hasRoles()

// User creates transactions
User::class -> hasMany(Sale::class, 'user_id')
User::class -> hasMany(Purchase::class, 'user_id')
```

### **2. Master Data Modules**

#### **Product Module** 📦
**Purpose**: Product catalog and inventory management
**Dependencies**: `Setting`, `Upload`, `Currency`
**Used By**: `Sale`, `Purchase`, `Quotation`, `Adjustment`, `Reports`

**Key Files:**
- `Entities/Product.php` - Main product model
- `Entities/Category.php` - Product categories
- `Controllers/ProductController.php` - Product CRUD
- `Controllers/BarcodeController.php` - Barcode generation

**Relationships:**
```php
// Product belongs to category
Product::class -> belongsTo(Category::class)

// Product has many transaction details
Product::class -> hasMany(SaleDetails::class)
Product::class -> hasMany(PurchaseDetails::class)
Product::class -> hasMany(QuotationDetails::class)

// Product has media (images)
Product::class -> hasMedia('images')
```

**Provides To Other Modules:**
- Product information (name, price, stock)
- Barcode generation
- Category management
- Stock level tracking

#### **People Module** 👫
**Purpose**: Customer and supplier management
**Dependencies**: `Setting`
**Used By**: `Sale`, `Purchase`, `Reports`

**Key Files:**
- `Entities/Customer.php` - Customer model
- `Entities/Supplier.php` - Supplier model
- `Controllers/CustomersController.php` - Customer CRUD
- `Controllers/SuppliersController.php` - Supplier CRUD

**Relationships:**
```php
// Customer has many sales
Customer::class -> hasMany(Sale::class)
Customer::class -> hasMany(SaleReturn::class)

// Supplier has many purchases
Supplier::class -> hasMany(Purchase::class)
Supplier::class -> hasMany(PurchaseReturn::class)
```

### **3. Transaction Modules**

#### **Sale Module** 💰
**Purpose**: Sales transactions and POS functionality
**Dependencies**: `Product`, `People`, `User`, `Setting`
**Used By**: `SalesReturn`, `Reports`

**Key Files:**
- `Entities/Sale.php` - Main sales model
- `Entities/SaleDetails.php` - Sales line items
- `Entities/SalePayment.php` - Payment records
- `Controllers/SaleController.php` - Sales CRUD
- `Controllers/PosController.php` - POS interface
- `app/Livewire/Pos/` - Real-time POS components

**Complex Relationships:**
```php
// Sale main relationships
Sale::class -> belongsTo(User::class, 'user_id')        // Cashier
Sale::class -> belongsTo(Customer::class, 'customer_id') // Customer
Sale::class -> hasMany(SaleDetails::class)              // Line items
Sale::class -> hasMany(SalePayment::class)              // Payments

// Sale details relationships
SaleDetails::class -> belongsTo(Sale::class)
SaleDetails::class -> belongsTo(Product::class, 'product_id')

// Payment relationships
SalePayment::class -> belongsTo(Sale::class)
```

**Business Logic:**
- Calculates totals (subtotal, tax, discount, grand total)
- Updates product stock levels
- Tracks payment status
- Generates receipts
- Handles POS cart functionality

#### **Purchase Module** 🛒
**Purpose**: Purchase orders and supplier management
**Dependencies**: `Product`, `People`, `User`, `Setting`
**Used By**: `PurchasesReturn`, `Reports`

**Key Files:**
- `Entities/Purchase.php` - Purchase order model
- `Entities/PurchaseDetails.php` - Purchase line items
- `Entities/PurchasePayment.php` - Purchase payments
- `Controllers/PurchaseController.php` - Purchase CRUD

**Relationships:**
```php
// Purchase main relationships
Purchase::class -> belongsTo(User::class, 'user_id')      // Buyer
Purchase::class -> belongsTo(Supplier::class, 'supplier_id')
Purchase::class -> hasMany(PurchaseDetails::class)
Purchase::class -> hasMany(PurchasePayment::class)

// Purchase details
PurchaseDetails::class -> belongsTo(Purchase::class)
PurchaseDetails::class -> belongsTo(Product::class, 'product_id')
```

### **4. Secondary Transaction Modules**

#### **SalesReturn Module** 🔄
**Purpose**: Handle sales returns and refunds
**Dependencies**: `Sale`, `Product`, `People`, `User`
**Used By**: `Reports`

**Key Files:**
- `Entities/SaleReturn.php` - Sales return model
- `Entities/SaleReturnDetail.php` - Return line items
- `Controllers/SalesReturnController.php` - Return processing

**Relationships:**
```php
// Returns reference original sales
SaleReturn::class -> belongsTo(Sale::class, 'sale_id')
SaleReturn::class -> belongsTo(Customer::class)
SaleReturn::class -> hasMany(SaleReturnDetail::class)

SaleReturnDetail::class -> belongsTo(Product::class)
```

#### **Quotation Module** 📋
**Purpose**: Price quotations and estimates
**Dependencies**: `Product`, `People`, `User`, `Setting`
**Used By**: `Sale` (quotation to sale conversion)

**Key Files:**
- `Entities/Quotation.php` - Quotation model
- `Entities/QuotationDetails.php` - Quote line items
- `Controllers/QuotationController.php` - Quote management
- `Emails/QuotationMail.php` - Email quotations

**Special Features:**
- Convert quotations to sales
- Email quotations to customers
- PDF generation for quotes

### **5. Supporting Modules**

#### **Reports Module** 📊
**Purpose**: Business intelligence and reporting
**Dependencies**: ALL transaction modules
**Used By**: Management for decision making

**Key Files:**
- `Controllers/ReportsController.php` - Report generation
- `DataTables/*ReportDataTable.php` - Report data tables
- `app/Livewire/Reports/` - Real-time report components

**Data Sources:**
```php
// Sales reporting
SalesReportDataTable -> Sale::class
PaymentsReportDataTable -> SalePayment::class

// Purchase reporting  
PurchasesReportDataTable -> Purchase::class

// Return reporting
SalesReturnReportDataTable -> SaleReturn::class
PurchasesReturnReportDataTable -> PurchaseReturn::class
```

#### **Adjustment Module** ⚖️
**Purpose**: Inventory adjustments and stock corrections
**Dependencies**: `Product`, `User`
**Used By**: Inventory management

**Key Files:**
- `Entities/Adjustment.php` - Adjustment header
- `Entities/AdjustedProduct.php` - Adjustment details
- `Controllers/AdjustmentController.php` - Adjustment processing

#### **Upload Module** 📁
**Purpose**: File upload and media management
**Dependencies**: `Setting` (for storage configuration)
**Used By**: `Product` (images), `User` (avatars), `Setting` (logos)

**Key Files:**
- `Entities/Upload.php` - File metadata
- `Controllers/UploadController.php` - Upload handling

---

## ⚡ **Data Flow Examples**

### **Complete Sales Process**
```
1. POS Interface (Livewire/Pos/ProductList.php)
   ↓
2. Product Selection (Product/Entities/Product.php)
   ↓
3. Cart Management (Livewire/ProductCart.php)
   ↓
4. Customer Selection (People/Entities/Customer.php)
   ↓
5. Checkout Process (Livewire/Pos/Checkout.php)
   ↓
6. Sale Creation (Sale/Controllers/PosController.php)
   ↓
7. Sale Storage (Sale/Entities/Sale.php)
   ↓
8. Stock Update (Product/Entities/Product.php)
   ↓
9. Receipt Generation (Sale/Resources/views/print.blade.php)
```

### **Report Generation Process**
```
1. Report Request (Reports/Controllers/ReportsController.php)
   ↓
2. Data Aggregation (Reports/DataTables/*DataTable.php)
   ↓ 
3. Multi-module Data Query:
   - Sale/Entities/Sale.php (sales data)
   - People/Entities/Customer.php (customer data)
   - Product/Entities/Product.php (product data)
   ↓
4. Report Display (Reports/Resources/views/*.blade.php)
   ↓
5. Export Options (Excel/PDF via DataTables)
```

---

## 🛠️ **Module Modification Guidelines**

### **When Modifying Product Module:**
Must update:
- `Sale` module (for product selection)
- `Purchase` module (for product ordering)
- `Reports` module (for product reporting)
- `Adjustment` module (for stock adjustments)

### **When Modifying Sale Module:**
Must update:
- `Reports` module (for sales reporting)
- `SalesReturn` module (for return processing)
- `Product` module (for stock updates)

### **When Adding New Transaction Module:**
Must integrate with:
- `Product` module (for product selection)
- `People` module (for customer/supplier)
- `User` module (for user assignment)
- `Setting` module (for configuration)
- `Reports` module (for reporting)

---

**📅 Last Updated:** November 2024  
**🔄 Version:** 1.1.0