<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n" . str_repeat("=", 80) . "\n";
echo "🔍 ANALISIS STRUKTUR DATABASE & REFERENSI product_code\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Cek struktur tabel products
echo "1️⃣  TABEL PRODUCTS - STRUKTUR KOLOM\n";
echo str_repeat("-", 80) . "\n";

$columns = DB::select("PRAGMA table_info(products)");
$columnNames = array_column((array)$columns, 'name');

echo "Kolom yang ada:\n";
foreach ($columns as $col) {
    echo "  - {$col->name} ({$col->type})\n";
}

$hasProductCode = in_array('product_code', $columnNames);
$hasProductSku = in_array('product_sku', $columnNames);
$hasProductGtin = in_array('product_gtin', $columnNames);
$hasProductSkuDuplicate = count(array_filter(array_map(function($c) { 
    return stripos($c->name, 'product_sku') !== false ? $c->name : null; 
}, (array)$columns))) > 1;

echo "\n✅ Status Kolom:\n";
echo "  - product_code: " . ($hasProductCode ? "✅ ADA" : "❌ TIDAK ADA") . "\n";
echo "  - product_sku: " . ($hasProductSku ? "✅ ADA" : "❌ TIDAK ADA") . "\n";
echo "  - product_gtin: " . ($hasProductGtin ? "✅ ADA" : "❌ TIDAK ADA") . "\n";

// 2. Cek data di tabel products
echo "\n\n2️⃣  DATA TABEL PRODUCTS\n";
echo str_repeat("-", 80) . "\n";

$products = DB::table('products')->limit(3)->get();
echo "Sample 3 produk pertama:\n";
foreach ($products as $product) {
    echo "\n  ID: {$product->id}\n";
    echo "  Name: {$product->product_name}\n";
    if ($hasProductCode) echo "  product_code: {$product->product_code}\n";
    if ($hasProductSku) echo "  product_sku: {$product->product_sku}\n";
    if ($hasProductGtin) echo "  product_gtin: {$product->product_gtin}\n";
}

// 3. Cek semua tabel yang referensi products
echo "\n\n3️⃣  TABEL-TABEL YANG MEREFERENSI PRODUCTS\n";
echo str_repeat("-", 80) . "\n";

$allTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$tablesWithProductCode = [];

foreach ($allTables as $table) {
    $tableName = $table->name;
    if (strpos($tableName, 'sqlite_') === 0) continue;
    
    try {
        $cols = DB::select("PRAGMA table_info({$tableName})");
        $colNames = array_column((array)$cols, 'name');
        
        if (in_array('product_code', $colNames)) {
            $tablesWithProductCode[] = $tableName;
            echo "  ✅ {$tableName} - punya kolom 'product_code'\n";
        }
    } catch (\Exception $e) {
        // Skip
    }
}

// 4. Cek penggunaan dalam code
echo "\n\n4️⃣  REFERENSI DALAM KODE APLIKASI\n";
echo str_repeat("-", 80) . "\n";

$patterns = [
    'product_code' => ['app/', 'Modules/'],
];

// Cek file-file penting
$importantFiles = [
    'Modules/Product/Entities/Product.php',
    'Modules/Product/Http/Controllers/ProductController.php',
    'Modules/Product/DataTables/ProductDataTable.php',
    'app/Livewire/SearchProduct.php',
    'app/Livewire/Pos/Checkout.php',
    'app/Livewire/ProductCart.php',
    'Modules/Sale/Database/Migrations/2021_08_08_021408_create_sales_details_table.php',
    'Modules/Purchase/Database/Migrations/2021_08_08_021713_create_purchase_details_table.php',
];

echo "File-file yang perlu dicek:\n";
foreach ($importantFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'product_code') !== false) {
            echo "  ⚠️  {$file}\n";
        }
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ ANALISIS SELESAI\n";
echo str_repeat("=", 80) . "\n\n";
