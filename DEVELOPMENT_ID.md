# Nameless.POS — Panduan Development

Dokumen ini adalah panduan development terpadu untuk Nameless.POS berdasarkan repo yang ada, modul-modul saat ini, dan dokumentasi internal. Dokumen ini mengumpulkan instruksi setup, tech stack, deskripsi arsitektur/alur, referensi modul, dan panduan developer sehingga contributor baru dapat cepat memahami sistem.

---

## Daftar Isi
1. Mulai cepat (lokal & docker)
2. Tech Stack & Dependensi
3. Ringkasan Arsitektur
4. Modul & file-file penting
5. Routes dan API
6. Detail POS / Livewire
7. Thermal printing & integrasi printer
8. Database & Seeder
9. Testing & Menjalankan tests
10. Debugging & Logging
11. Contributing & Code Style
12. Perintah berguna
13. Tempat mencari bantuan di repo

---

## 1) Mulai cepat

### Clone & instalasi lokal
```powershell
cd D:\project warnet
git clone https://github.com/FahimAnzamDip/triangle-pos.git Nameless
cd "d:\project warnet\Nameless"
composer install
npm install
npm run dev
copy .env.example .env
php artisan key:generate
# Konfigurasi .env DB (SQLite direkomendasikan untuk dev lokal)
# Pastikan database/database.sqlite ada (jangan dihapus) dan set DB_CONNECTION=sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
# Buka http://127.0.0.1:8000
```

> Catatan: repo ini menyertakan dokumentasi ekstensif tentang sistem printer, alur POS, dan scanning. Lihat `GLOBAL_THERMAL_PRINTER_SYSTEM.md`, `NAMELESS_POS_ARCHITECTURE.md`, dan file dokumentasi modul.

### Docker
Repo memiliki setup Docker dan `docker-compose.yaml`. Gunakan untuk menjalankan environment MySQL untuk testing atau CI.

---

## 2) Tech stack
- Framework: Laravel 10
- Frontend: Blade, Livewire (3.x), Bootstrap, jQuery, DataTables
- Modul: nwidart/laravel-modules untuk organisasi kode modular
- Autentikasi: Spatie permissions
- Library printer: mike42/escpos-php, custom `ThermalPrinterService`
- PDF: barryvdh/laravel-snappy (bergantung wkhtmltopdf)

Package-package utama di `composer.json`: `livewire/livewire`, `nwidart/laravel-modules`, `spatie/laravel-permission`, `yajra/laravel-datatables`.

---

## 3) Ringkasan arsitektur
Lihat `NAMELESS_POS_ARCHITECTURE.md` untuk tata letak arsitektur lengkap. Secara singkat:
- Aplikasi Laravel dengan Modul untuk domain bisnis (Sale, Purchase, Product, People, dll.)
- Komponen Livewire di `app/Livewire/` mengontrol perilaku real-time POS
- Thermal printing dipisahkan ke `app/Services/ThermalPrinterService.php`, dengan controller API `app/Http/Controllers/Api/ThermalPrintController.php` untuk endpoint print REST
- DataTables digunakan untuk daftar besar sisi-server (`Modules/*/DataTables`)
- **Menu Sidebar:** Navigasi utama di `resources/views/layouts/menu.blade.php` dengan bagian "Configuration" yang dikonsolidasikan untuk pengaturan sistem (lihat `SIDEBAR_MENU_OPTIMIZATION_ID.md` untuk detail)

---

## 4) Modul & file-file penting
Sebagian besar modul berada di `Modules/` dan mengikuti struktur serupa. Beberapa modul kunci untuk diketahui:

### Modul Sale
- `Modules/Sale/Entities/Sale.php` - Model penjualan
- `Modules/Sale/Entities/SaleDetails.php` - Item penjualan (detail baris)
- `Modules/Sale/Http/Controllers/PosController.php` - API backend POS & route penyimpanan
- `Modules/Sale/Resources/views/pos/index.blade.php` - UI POS

### Modul Product
- `Modules/Product/Entities/Product.php` - Model produk
- `Modules/Product/Http/Controllers/BarcodeController.php` - Generate barcode

### Modul People
- `Modules/People/Entities/Customer.php` dan `Supplier.php` - Model pelanggan & supplier

### Thermal Printer
- `app/Services/ThermalPrinterService.php` - Generasi ESC/POS level rendah
- `app/Http/Controllers/Api/ThermalPrintController.php` - Endpoint print

Modul lainnya dijelaskan di `MODULE_RELATIONSHIPS.md` dan `NAMELESS_POS_ARCHITECTURE.md`.

---

## 5) Routes & API
- Gunakan `php artisan route:list` untuk melihat semua routes.
- Routes thermal & print API (lihat `routes/web.php` & `routes/api.php`):
  - POST `api/thermal/print-sale` — `Api\ThermalPrintController@printSale`
  - POST `api/thermal/print-test/{printer}` — `Api\ThermalPrintController@printTest`
  - POST `api/thermal/open-cash-drawer/{printer}` — `Api\ThermalPrintController@openCashDrawer`
- Route POS store: `app.pos.store` ditangani `PosController@store` (dipicu AJAX oleh Livewire checkout)

---

## 6) Alur POS & komponen Livewire
- Komposisi UI POS ada di `Modules/Sale/Resources/views/pos/index.blade.php`:
  - Komponen Livewire `app/Livewire/Pos/ProductList`, `app/Livewire/Pos/Checkout`, dan `livewire:search-product`
  - Livewire `Checkout` memiliki listener `resetCart` dan dipicu oleh frontend JS setelah AJAX sukses.
- Alur checkout:
  1. Tambah produk ke keranjang via Livewire `SearchProduct` dan `ProductList`.
  2. Tekan `Proceed` untuk membuka `checkoutModal` yang menggunakan jQuery AJAX POST ke `PosController@store`.
  3. Sukses: tutup modal, `openPrintWindow('sales', sale_id)`, dan `Livewire.dispatch('resetCart')` → reset keranjang menggunakan `Checkout::resetCart()`.

---

## 7) Thermal printing & integrasi printer
- Service printing: `app/Services/ThermalPrinterService.php` — generate perintah ESC/POS dan mendukung berbagai jenis koneksi (network/usb/serial)
- Pengaturan printer disimpan di `printer_settings` dan dapat dikontrol dari UI (`PrinterSettingController`) — dirujuk oleh `ThermalPrinterService` via `ThermalPrinterSetting::getDefault()`.
- `getReceiptCopies()` menggunakan fallback `PrinterSetting` atau config untuk menentukan jumlah salinan.
- Repo menyertakan helpers untuk inject perintah ESC ke print job, dan `public/js/thermal-printer-commands.js` untuk injection level halaman.
- Lihat `GLOBAL_THERMAL_PRINTER_SYSTEM.md` dan `THERMAL_PRINTER_SETUP.md` untuk detail printer dan perintah ESC.

---

## 8) Database & Seeder
- File seeder utama: `database/seeders/DummyDataSeeder.php` yang membuat contoh kategori, unit, produk, pelanggan, supplier, pengeluaran, pembelian, penjualan, dan quotasi.
- Repo ini telah diperkuat untuk menghindari seeder failure di SQLite (blok try/catch ditambahkan dengan pola fallback `pluck('id')`).
- Untuk seed DB lokal (non-destruktif):
```powershell
php artisan db:seed --class=DummyDataSeeder
```
- Jangan hapus `database/database.sqlite` per batasan dev lokal Anda.

---

## 9) Testing & Menjalankan tests
- Unit & feature tests berada di `tests/`.
- Jalankan PHPUnit lokal:
```powershell
php artisan test
# atau
vendor\bin\phpunit
```
- Untuk browser atau integration tests, periksa komponen Livewire dengan menjalankan Dusk atau menggunakan scenario manual.

---

## 10) Debugging & Logging
- Logs: `storage/logs/laravel.log` — error termasuk masalah `DATE_FORMAT` runtime dilaporkan di sini.
- Tools debug cepat:
  - `php artisan route:list` - lihat routes saat ini
  - `php artisan tinker` - pengecekan manual
  - `php artisan module:list` - verifikasi modul
  - `php artisan config:clear`, `view:clear`, `cache:clear` untuk cache
- Debug printing: `public/js/thermal-printer-test.js` dan `ThermalPrinterService::printTestPage()` berguna untuk diagnosis masalah printer

---

## 11) Contributing & Code Style
- Gunakan branch `main` untuk kode stabil; buat feature branches untuk perubahan
- Panduan penamaan:
  - Controllers: `PascalCaseController`
  - Models: `PascalCase`
  - Komponen Livewire: `App\Livewire`
- Tambahkan tests untuk feature dan submit PRs.

---

## 12) Perintah berguna
```powershell
# Common artisan & composer
php artisan serve
php artisan migrate --seed
php artisan db:seed --class=DummyDataSeeder
php artisan route:list
php artisan tinker
npm run dev
composer install

# Pengecekan modul
php artisan module:list

# Test printer
php artisan tinker --execute="app(App\Services\ThermalPrinterService::class)->printTestPage()"
```

---

## 13) Tempat mencari bantuan
- Dokumentasi tingkat tinggi: `NAMELESS_POS_ARCHITECTURE.md`, `MODULE_RELATIONSHIPS.md`
- Dokumentasi thermal: `GLOBAL_THERMAL_PRINTER_SYSTEM.md`, `THERMAL_PRINTER_SETUP.md`, `SOLUSI_PRINTER_EPPOS_EP220II.md`
- Dokumentasi scanner: file `EXTERNAL_SCANNER_*.md` di root
- Test & debug: `public/js/thermal-printer-test.js`, `public/js/printer-detection.js`

---

## Referensi (dokumentasi yang ada di repository)
Repository berisi serangkaian file dokumentasi khusus. Baca ini untuk informasi lebih mendalam dan instruksi spesifik vendor.

- `README.md` — instruksi project tingkat tinggi dan quick start
- `NAMELESS_POS_ARCHITECTURE.md` — arsitektur dan ringkasan modul
- `MODULE_RELATIONSHIPS.md` — dependensi modul tingkat tinggi
- `GLOBAL_THERMAL_PRINTER_SYSTEM.md` — arsitektur thermal printing dan alur ESC/POS
- `THERMAL_PRINTER_SETUP.md` — instruksi setup thermal printer
- `PRINTER_INTEGRATION.md` — bagaimana library Mike42 ESC/POS diintegrasikan
- `SOLUSI_PRINTER_EPPOS_EP220II.md` — panduan spesifik printer
- `POS_SCANNER_FINAL_SUCCESS.md` — detail tentang integrasi scanner
- `EXTERNAL_SCANNER_QUICK_REFERENCE.md` — quick reference scanner
- `EXTERNAL_SCANNER_TROUBLESHOOTING.md` — panduan troubleshooting scanner
- `QUICK_REFERENCE.md` — quick reference developer umum
- `QUICK_DEBUG_COMMANDS.md` — perintah debug cepat untuk developer
- `FILE_DEPENDENCY_MAP.md` — peta dependensi level file
- `IMMEDIATE_DEBUG_COMMANDS.md` — perintah untuk cepat memeriksa status sistem

Ada juga saved conversation summaries dan saved changes di `saved_conversations/` yang mendokumentasikan session debugging, printer fixes, dan keputusan development.

---

## Opsi Selanjutnya
Anda dapat memilih:
- (A) Membuat folder `docs/` yang memisahkan bagian ke dalam file terpisah; atau
- (B) Memperluas `DEVELOPMENT_ID.md` dengan panduan step-by-step yang lebih mendalam untuk setiap modul.

Beri tahu mana yang Anda lebih suka dan saya akan melanjutkan.

---

*Auto-generated dari analisis repo — terakhir diperbarui: 2025-11-17.*