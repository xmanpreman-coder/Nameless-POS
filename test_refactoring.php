<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Product\Entities\Product;
use Modules\Sale\Entities\SaleDetails;
use Modules\Purchase\Entities\PurchaseDetail;

echo "\n" . str_repeat("=", 80) . "\n";
echo "🧪 TEST APLIKASI SETELAH REFACTORING\n";
echo str_repeat("=", 80) . "\n\n";

// Test 1: Cek Product
echo "1️⃣  TEST PRODUCT MODEL\n";
echo str_repeat("-", 80) . "\n";
try {
    $product = Product::first();
    if ($product) {
        echo "✅ Product berhasil diload\n";
        echo "   ID: {$product->id}\n";
        echo "   Name: {$product->product_name}\n";
        echo "   SKU: {$product->product_sku}\n";
        echo "   GTIN: {$product->product_gtin}\n";
        echo "   Code (lama): {$product->product_code}\n";
    } else {
        echo "⚠️  Tidak ada product di database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 2: Cek SaleDetails
echo "\n2️⃣  TEST SALE_DETAILS MODEL\n";
echo str_repeat("-", 80) . "\n";
try {
    $saleDetail = SaleDetails::first();
    if ($saleDetail) {
        echo "✅ SaleDetail berhasil diload\n";
        echo "   Product Name: {$saleDetail->product_name}\n";
        echo "   Product SKU: {$saleDetail->product_sku}\n";
        // Check if old column exists
        if (isset($saleDetail->product_code)) {
            echo "   Product Code (lama): {$saleDetail->product_code}\n";
        }
    } else {
        echo "⚠️  Tidak ada sale detail di database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 3: Cek PurchaseDetail
echo "\n3️⃣  TEST PURCHASE_DETAILS MODEL\n";
echo str_repeat("-", 80) . "\n";
try {
    $purchaseDetail = PurchaseDetail::first();
    if ($purchaseDetail) {
        echo "✅ PurchaseDetail berhasil diload\n";
        echo "   Product Name: {$purchaseDetail->product_name}\n";
        echo "   Product SKU: {$purchaseDetail->product_sku}\n";
    } else {
        echo "⚠️  Tidak ada purchase detail di database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 4: Cek Search Functionality
echo "\n4️⃣  TEST SEARCH FUNCTIONALITY\n";
echo str_repeat("-", 80) . "\n";
try {
    // Search by product_sku
    $results = Product::where('product_sku', 'like', '%PRD%')->limit(3)->get();
    echo "✅ Search by product_sku: " . $results->count() . " hasil\n";
    
    // Search by product_gtin
    $results_gtin = Product::where('product_gtin', '!=', null)->limit(3)->get();
    echo "✅ Search by product_gtin: " . $results_gtin->count() . " hasil\n";
    
    // Search by product_name
    $results_name = Product::where('product_name', 'like', '%Dell%')->limit(3)->get();
    echo "✅ Search by product_name: " . $results_name->count() . " hasil\n";
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 5: Database Structure
echo "\n5️⃣  TEST DATABASE STRUCTURE\n";
echo str_repeat("-", 80) . "\n";
try {
    $columns = \DB::select("PRAGMA table_info(products)");
    $colNames = array_column((array)$columns, 'name');
    
    $checks = [
        'product_sku' => in_array('product_sku', $colNames),
        'product_gtin' => in_array('product_gtin', $colNames),
        'product_code' => in_array('product_code', $colNames),
    ];
    
    foreach ($checks as $col => $exists) {
        echo ($exists ? "✅" : "❌") . " Kolom '{$col}': " . ($exists ? "ADA" : "TIDAK ADA") . "\n";
    }
    
    // Check sale_details
    $sale_cols = \DB::select("PRAGMA table_info(sale_details)");
    $sale_colNames = array_column((array)$sale_cols, 'name');
    echo "\n  sale_details:\n";
    echo (in_array('product_sku', $sale_colNames) ? "    ✅" : "    ❌") . " product_sku\n";
    echo (in_array('product_code', $sale_colNames) ? "    ⚠️ " : "    ✅") . " product_code\n";
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ TEST SELESAI\n";
echo str_repeat("=", 80) . "\n\n";
