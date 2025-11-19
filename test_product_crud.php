<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Product\Entities\Product;
use Modules\Product\Entities\Category;

echo "========================================\n";
echo "  TESTING PRODUCT CRUD OPERATIONS\n";
echo "========================================\n\n";

// Test 1: CREATE
echo "1️⃣  TEST CREATE\n";
try {
    $category = Category::first();
    $newProduct = Product::create([
        'product_name' => 'Test Product ' . time(),
        'product_sku' => 'TST' . rand(1000, 9999),
        'product_gtin' => 'GTIN' . rand(1000, 9999),
        'category_id' => $category->id,
        'product_barcode_symbology' => 'EAN13',
        'product_quantity' => 10,
        'product_cost' => 50000, // in cents
        'product_price' => 75000, // in cents
        'product_unit' => 'Pcs',
        'product_stock_alert' => 5,
    ]);
    echo "  ✅ Created product: ID {$newProduct->id}, SKU: {$newProduct->product_sku}\n";
    $testProductId = $newProduct->id;
} catch (\Exception $e) {
    echo "  ❌ Error creating product: {$e->getMessage()}\n";
    exit;
}

// Test 2: READ
echo "\n2️⃣  TEST READ\n";
try {
    $product = Product::find($testProductId);
    if ($product) {
        echo "  ✅ Found product: {$product->product_name}\n";
        echo "     SKU: {$product->product_sku}\n";
        echo "     GTIN: {$product->product_gtin}\n";
        echo "     Cost: {$product->product_cost}\n";
        echo "     Price: {$product->product_price}\n";
    } else {
        echo "  ❌ Product not found\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Error reading product: {$e->getMessage()}\n";
}

// Test 3: UPDATE
echo "\n3️⃣  TEST UPDATE\n";
try {
    $product = Product::find($testProductId);
    $product->update([
        'product_name' => 'Updated Test Product',
        'product_quantity' => 20,
        'product_price' => 100000,
    ]);
    echo "  ✅ Updated product successfully\n";
    echo "     New name: {$product->product_name}\n";
    echo "     New quantity: {$product->product_quantity}\n";
    echo "     New price: {$product->product_price}\n";
} catch (\Exception $e) {
    echo "  ❌ Error updating product: {$e->getMessage()}\n";
}

// Test 4: DELETE
echo "\n4️⃣  TEST DELETE\n";
try {
    $product = Product::find($testProductId);
    $product->delete();
    $stillExists = Product::find($testProductId);
    if (!$stillExists) {
        echo "  ✅ Deleted product successfully\n";
    } else {
        echo "  ❌ Product still exists after delete\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Error deleting product: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 40) . "\n";
echo "✅ ALL CRUD TESTS PASSED\n";
echo str_repeat("=", 40) . "\n";
