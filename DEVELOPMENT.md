# Nameless.POS — Development Guide

This document is a consolidated development guide for Nameless.POS based on the existing repo, current modules, and internal documentation. It collects setup instructions, the tech stack, architecture/flow descriptions, module references, and developer guidelines so new contributors can onboard quickly.

---

## Table of Contents
1. Quick start (local & docker) 
2. Tech Stack & Dependencies
3. Architecture Overview
4. Modules & key files
5. Routes and APIs
6. POS / Livewire details
7. Thermal printing & printer integration
8. Database & Seeders
9. Testing & Running tests
10. Debugging & Logging
11. Contributing & Code Style
12. Useful commands
13. Where to find help in the repo

---

## 1) Quick start

### Clone & local install
```powershell
cd D:\project warnet
git clone https://github.com/FahimAnzamDip/triangle-pos.git Nameless
cd "d:\project warnet\Nameless"
composer install
npm install
npm run dev
copy .env.example .env
php artisan key:generate
# Configure .env DB settings (sqlite recommended for local dev)
# Ensure database/database.sqlite exists (do not delete) and set DB_CONNECTION=sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
# Visit http://127.0.0.1:8000
```

> Note: this repo includes an extensive set of markdown docs about the printer system, POS flows, and scanning. See `GLOBAL_THERMAL_PRINTER_SYSTEM.md`, `NAMELESS_POS_ARCHITECTURE.md`, and the `modules` documentation files.

### Docker
The repo has a Docker setup and a `docker-compose.yaml`. Use it to spin a MySQL environment for CI-like parity.

---

## 2) Tech stack
- Framework: Laravel 10
- Frontend: Blade, Livewire (3.x), Bootstrap, jQuery, DataTables
- Modules: nwidart/laravel-modules for modular code organization
- Authentication: Spatie permissions
- Printer libs: mike42/escpos-php, custom `ThermalPrinterService`
- PDF: barryvdh/laravel-snappy (wkhtmltopdf dependency)

Key packages in `composer.json`: `livewire/livewire`, `nwidart/laravel-modules`, `spatie/laravel-permission`, `yajra/laravel-datatables`.

---

## 3) Architecture overview
Refer to `NAMELESS_POS_ARCHITECTURE.md` for the full architecture layout. In brief:
- Laravel application with Modules for business domains (Sale, Purchase, Product, People, etc.)
- Livewire components in `app/Livewire/` control the POS realtime behaviour
- Thermal printing is separated into `app/Services/ThermalPrinterService.php`, with API controller `app/Http/Controllers/Api/ThermalPrintController.php` for REST print endpoints
- DataTables is used for server-side large lists (
`Modules/*/DataTables`)
- **Sidebar Menu:** Main navigation in `resources/views/layouts/menu.blade.php` with consolidated "Configuration" section for system settings (see `SIDEBAR_MENU_OPTIMIZATION.md` for details)

---

## 4) Modules & key files
Most modules live under `Modules/` and follow similar structure. Several key modules to know:
- Sale
  - `Modules/Sale/Entities/Sale.php` - Sale model
  - `Modules/Sale/Entities/SaleDetails.php` - sale line items
  - `Modules/Sale/Http/Controllers/PosController.php` - POS back-end API & storage route
  - `Modules/Sale/Resources/views/pos/index.blade.php` - POS UI
- Product
  - `Modules/Product/Entities/Product.php`
  - `Modules/Product/Http/Controllers/BarcodeController.php` - barcode printing
- People
  - `Modules/People/Entities/Customer.php` and `Supplier.php`
- Thermal
  - `app/Services/ThermalPrinterService.php` - low-level ESC/POS generation
  - `app/Http/Controllers/Api/ThermalPrintController.php` - print endpoint

Other modules are described in `MODULE_RELATIONSHIPS.md` and `NAMELESS_POS_ARCHITECTURE.md`.

---

## 5) Routes & API
- Use `php artisan route:list` to view routes.
- Thermal & print API routes (see `routes/web.php` & `routes/api.php`) include:
  - POST `api/thermal/print-sale` — `Api\ThermalPrintController@printSale`
  - POST `api/thermal/print-test/{printer}` — `Api\ThermalPrintController@printTest`
  - POST `api/thermal/open-cash-drawer/{printer}` — `Api\ThermalPrintController@openCashDrawer`
- POS store route: `app.pos.store` is handled by `PosController@store` (Ajax-triggered by Livewire checkout)

---

## 6) POS flow & Livewire components
- The POS UI composition is in `Modules/Sale/Resources/views/pos/index.blade.php`:
  - Livewire components `app/Livewire/Pos/ProductList`, `app/Livewire/Pos/Checkout`, and `livewire:search-product`
  - Livewire `Checkout` has `resetCart` listener and is invoked by frontend JS after AJAX success.
- Checkout flow:
  1. Add products to cart via Livewire `SearchProduct` and `ProductList`.
  2. Press `Proceed` to open the `checkoutModal` which uses a jQuery AJAX post to `PosController@store`.
  3. On success: close modal, `openPrintWindow('sales', sale_id)`, and `Livewire.dispatch('resetCart')` → resets cart using `Checkout::resetCart()`.

---

## 7) Thermal printing & printer integration
- Printing service: `app/Services/ThermalPrinterService.php` — generates ESC/POS commands and supports multiple connection types (network/usb/serial)
- Printer settings are stored in `printer_settings` and can be controlled from the UI (`PrinterSettingController`) — these are referenced by `ThermalPrinterService` via `ThermalPrinterSetting::getDefault()`.
- `getReceiptCopies()` uses a `PrinterSetting` fallback or config to determine number of copies.
- The repository includes helpers for injecting ESC commands into the print job, and `public/js/thermal-printer-commands.js` for page-level injection.
- See `GLOBAL_THERMAL_PRINTER_SYSTEM.md` and `THERMAL_PRINTER_SETUP.md` for printers and ESC commands details.

---

## 8) Database & Seeder
- Main seeder file: `database/seeders/DummyDataSeeder.php` which creates sample categories, units, products, customers, suppliers, expenses, purchases, sales, and quotations.
- This repo has been hardened to avoid seeder failures on SQLite (try/catch blocks added with fallback `pluck('id')` patterns).
- To seed local DB (non-destructive):
```powershell
php artisan db:seed --class=DummyDataSeeder
```
- Do not delete `database/database.sqlite` per your local dev constraints.

---

## 9) Tests & Run tests
- Unit & feature tests are under `tests/`.
- Run PHPUnit locally:
```powershell
php artisan test
# or
vendor\bin\phpunit
```
- For browser or integration tests, check Livewire components by running Dusk or using manual scenarios.

---

## 10) Debugging & Logging
- Logs: `storage/logs/laravel.log` — errors including `DATE_FORMAT` runtime issues are reported here.
- Quick debug tools:
  - `php artisan route:list` - view current routes
  - `php artisan tinker` - manual checks
  - `php artisan module:list` - verify modules
  - `php artisan config:clear`, `view:clear`, `cache:clear` for caches
- Printing debug: `public/js/thermal-printer-test.js` and `ThermalPrinterService::printTestPage()` are useful for diagnosing printer issues

---

## 11) Contributing & Code Style
- Use `main` branch for stable code; create feature branches for changes
- Naming guidelines:
  - Controllers: `PascalCaseController`
  - Models: `PascalCase`
  - Livewire components: `App\Livewire`
- Add tests for features and submit PRs.

---

## 12) Useful commands
```
# Common artisan & composer
php artisan serve
php artisan migrate --seed
php artisan db:seed --class=DummyDataSeeder
php artisan route:list
php artisan tinker
npm run dev
composer install

# Module checks
php artisan module:list

# Printer tests
php artisan tinker --execute="app(App\Services\ThermalPrinterService)::printTestPage()"
```

---

## 13) Where to find help
- High-level docs: `NAMELESS_POS_ARCHITECTURE.md`, `MODULE_RELATIONSHIPS.md`
- Thermal docs: `GLOBAL_THERMAL_PRINTER_SYSTEM.md`, `THERMAL_PRINTER_SETUP.md`, `SOLUSI_PRINTER_EPPOS_EP220II.md`
- Scanner docs: `EXTERNAL_SCANNER_*.md` files in root
- Test & debug: `public/js/thermal-printer-test.js`, `public/js/printer-detection.js`

---

## References (existing docs in repository)
The repository contains a set of additional, specialized documentation files. Read these for deeper information and vendor-specific instructions.

- `README.md` — project high‑level instructions and quick start
- `NAMELESS_POS_ARCHITECTURE.md` — architecture and module overview
- `MODULE_RELATIONSHIPS.md` — high-level module dependencies
- `GLOBAL_THERMAL_PRINTER_SYSTEM.md` — thermal printing architecture and ESC/POS flow
- `THERMAL_PRINTER_SETUP.md` — setup instructions for thermal printers
- `PRINTER_INTEGRATION.md` — how the Mike42 ESC/POS library is integrated
- `SOLUSI_PRINTER_EPPOS_EP220II.md` — printer-specific guide
- `POS_SCANNER_FINAL_SUCCESS.md` — details about the scanner integration
- `EXTERNAL_SCANNER_QUICK_REFERENCE.md` — scanner quick reference
- `EXTERNAL_SCANNER_TROUBLESHOOTING.md` — scanner troubleshooting guide
- `QUICK_REFERENCE.md` — general developer quick reference
- `QUICK_DEBUG_COMMANDS.md` — quick debug commands for developers
- `FILE_DEPENDENCY_MAP.md` — file-level dependency map
- `IMMEDIATE_DEBUG_COMMANDS.md` — commands to quickly check system status

There are also saved conversation summaries and saved changes under `saved_conversations/` that document debugging sessions, printer fixes, and development decisions.


If you want, I can: 
- (A) Create a `docs/` sub-folder that splits sections into separate files; or
- (B) Expand the `DEVELOPMENT.md` with deeper step-by-step guides for each module.

Tell me which you prefer and I’ll continue.  

---

*Auto-generated by the repo analysis — last updated: 2025-11-17.*