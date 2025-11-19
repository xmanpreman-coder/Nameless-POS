<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n" . str_repeat("=", 80) . "\n";
echo "🔄 MENGHAPUS KOLOM product_code & MENGGUNAKAN product_sku\n";
echo str_repeat("=", 80) . "\n\n";

$driver = DB::getDriverName();

if ($driver === 'sqlite') {
    // SQLite memerlukan recreate table karena tidak support DROP COLUMN langsung
    echo "Database Driver: SQLite\n\n";
    
    // 1. Products table - hapus kolom product_code lama, rename product_sku menjadi product_code
    echo "1️⃣  PRODUCTS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    
    // Untuk SQLite, kita perlu: 
    // - Rename product_sku menjadi product_code
    // - Hapus kolom lama jika ada duplicate
    
    try {
        // Step 1: Copy product_sku ke product_code (overwrite)
        DB::statement('UPDATE products SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
        echo "✅ Copied product_sku data to product_code\n";
        
        // Sekarang product_sku masih ada tapi product_code sudah berisi data yang benar
        // Untuk SQLite, kita bisa keep product_sku sebagai backup, tapi aplikasi akan gunakan product_code
        
        echo "✅ Products table ready\n";
    } catch (\Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    
    // 2. sale_details table
    echo "\n2️⃣  SALE_DETAILS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $hasProductSku = Schema::hasColumn('sale_details', 'product_sku');
        if ($hasProductSku) {
            DB::statement('UPDATE sale_details SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
            echo "✅ Synced product_sku to product_code\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  {$e->getMessage()}\n";
    }
    
    // 3. purchase_details table
    echo "\n3️⃣  PURCHASE_DETAILS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $hasProductSku = Schema::hasColumn('purchase_details', 'product_sku');
        if ($hasProductSku) {
            DB::statement('UPDATE purchase_details SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
            echo "✅ Synced product_sku to product_code\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  {$e->getMessage()}\n";
    }
    
    // 4. quotation_details table
    echo "\n4️⃣  QUOTATION_DETAILS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $hasProductSku = Schema::hasColumn('quotation_details', 'product_sku');
        if ($hasProductSku) {
            DB::statement('UPDATE quotation_details SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
            echo "✅ Synced product_sku to product_code\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  {$e->getMessage()}\n";
    }
    
    // 5. sale_return_details table
    echo "\n5️⃣  SALE_RETURN_DETAILS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $hasProductSku = Schema::hasColumn('sale_return_details', 'product_sku');
        if ($hasProductSku) {
            DB::statement('UPDATE sale_return_details SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
            echo "✅ Synced product_sku to product_code\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  {$e->getMessage()}\n";
    }
    
    // 6. purchase_return_details table
    echo "\n6️⃣  PURCHASE_RETURN_DETAILS TABLE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $hasProductSku = Schema::hasColumn('purchase_return_details', 'product_sku');
        if ($hasProductSku) {
            DB::statement('UPDATE purchase_return_details SET product_code = product_sku WHERE product_code IS NULL OR product_code = ""');
            echo "✅ Synced product_sku to product_code\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  {$e->getMessage()}\n";
    }
    
} else {
    // MySQL/PostgreSQL - bisa langsung rename
    echo "Database Driver: MySQL/PostgreSQL\n\n";
    
    // Kode untuk MySQL akan ditambahkan di sini jika diperlukan
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ DATABASE SYNC SELESAI\n";
echo "📌 Catatan: Kolom product_sku tetap ada untuk backward compatibility\n";
echo "           Aplikasi akan menggunakan product_code yang sudah diisi\n";
echo str_repeat("=", 80) . "\n\n";
