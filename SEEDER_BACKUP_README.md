# Database Seeder Backup Documentation

## Overview
File `CurrentDataSeeder.php` dibuat berdasarkan data real dari database production untuk mencegah kehilangan data saat migrate:fresh.

## Data yang Dibackup
- **Users**: 2 users (Super Admin, Test User)
- **Categories**: 8 categories (Minuman, Makanan, Rokok, dll)
- **Products**: 5 sample products
- **Customers**: 3 customers (termasuk "Umum")
- **Settings**: 7 core settings (company info, receipt settings)

## Cara Penggunaan

### Restore Data Lengkap
```bash
php artisan migrate:fresh --seed
```

### Restore Data Tertentu Saja
```bash
php artisan db:seed --class=CurrentDataSeeder
```

## File yang Dimodifikasi
- `database/seeders/CurrentDataSeeder.php` - **BARU**
- `database/seeders/DatabaseSeeder.php` - **DIUPDATE** untuk menggunakan CurrentDataSeeder

## Perbedaan dengan Seeder Lama
- **SuperUserSeeder**: Diganti dengan user data real
- **SettingDatabaseSeeder**: Diganti dengan settings real
- **ProductDatabaseSeeder**: Diganti dengan products real
- **CurrentDataSeeder**: Menggunakan data production yang sebenarnya

## Keamanan
✅ Password tetap di-hash dengan bcrypt
✅ Data sensitif tidak disimpan dalam plain text
✅ ID primary key tetap konsisten

## Catatan
- Seeder ini dibuat otomatis pada tanggal: $(Get-Date)
- Berdasarkan database state pada saat pembuatan
- Jika ada perubahan data besar, perlu update seeder ini lagi

## Emergency Restore
Jika terjadi masalah:
1. Restore file `database/database.sqlite` dari backup
2. Atau jalankan: `php artisan db:seed --class=CurrentDataSeeder`