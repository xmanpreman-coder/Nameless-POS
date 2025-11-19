<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo "  DATABASE MIGRATION AUDIT & FIX\n";
echo "========================================\n\n";

// Check products table structure
echo "1️⃣  CHECKING PRODUCTS TABLE\n";
$columns = DB::select("PRAGMA table_info(products)");
$columnNames = array_column((array)$columns, 'name');

echo "Current columns:\n";
foreach ($columns as $col) {
    echo "  - {$col->name} ({$col->type})\n";
}

// Check what's missing
$hasProductCode = in_array('product_code', $columnNames);
$hasProductSku = in_array('product_sku', $columnNames);
$hasProductGtin = in_array('product_gtin', $columnNames);

echo "\nColumn status:\n";
echo "  product_code: " . ($hasProductCode ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "  product_sku: " . ($hasProductSku ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "  product_gtin: " . ($hasProductGtin ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Now apply the migration manually if needed
if ($hasProductCode && !$hasProductSku) {
    echo "\n2️⃣  APPLYING MIGRATION: ADD product_sku and product_gtin\n";
    
    try {
        // Add product_sku column
        if (!Schema::hasColumn('products', 'product_sku')) {
            Schema::table('products', function ($table) {
                $table->string('product_sku')->nullable()->after('product_name');
            });
            echo "  ✅ Added product_sku column\n";
        }
        
        // Add product_gtin column
        if (!Schema::hasColumn('products', 'product_gtin')) {
            Schema::table('products', function ($table) {
                $table->string('product_gtin')->nullable()->after('product_sku');
            });
            echo "  ✅ Added product_gtin column\n";
        }
        
        // Copy data from product_code to product_sku
        DB::statement('UPDATE products SET product_sku = product_code WHERE product_sku IS NULL');
        echo "  ✅ Copied product_code → product_sku\n";
        
        // Verify
        $columns = DB::select("PRAGMA table_info(products)");
        $columnNames = array_column((array)$columns, 'name');
        $hasProductSku = in_array('product_sku', $columnNames);
        $hasProductGtin = in_array('product_gtin', $columnNames);
        
        echo "\n3️⃣  VERIFICATION\n";
        echo "  product_sku: " . ($hasProductSku ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        echo "  product_gtin: " . ($hasProductGtin ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        
        // Check data
        $productCount = DB::table('products')->count();
        $productsWithSku = DB::table('products')->whereNotNull('product_sku')->count();
        echo "  Total products: {$productCount}\n";
        echo "  Products with SKU: {$productsWithSku}\n";
        
        if ($productCount == $productsWithSku) {
            echo "\n✅ ALL MIGRATIONS APPLIED SUCCESSFULLY!\n";
        } else {
            echo "\n⚠️ Some products may be missing SKU values\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: {$e->getMessage()}\n";
    }
} else if ($hasProductSku) {
    echo "\n✅ DATABASE ALREADY UP TO DATE\n";
} else {
    echo "\n❌ UNEXPECTED STATE - product_code missing\n";
}

echo "\n" . str_repeat("=", 40) . "\n";
