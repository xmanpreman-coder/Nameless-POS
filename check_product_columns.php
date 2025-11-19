<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;

// Check product table columns
$columns = DB::select("PRAGMA table_info(products)");

echo "=== PRODUCT TABLE COLUMNS ===\n";
foreach ($columns as $col) {
    echo "- {$col->name}\n";
}

// Check if product_sku exists
$hasSku = collect($columns)->pluck('name')->contains('product_sku');
echo "\nHas 'product_sku': " . ($hasSku ? 'YES' : 'NO') . "\n";

// Check first product
$product = Product::first();
if ($product) {
    echo "\n=== FIRST PRODUCT ===\n";
    echo "ID: {$product->id}\n";
    echo "Name: {$product->product_name}\n";
    echo "Available attributes:\n";
    foreach ($product->getAttributes() as $key => $value) {
        echo "  - $key: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "\n";
    }
}

echo "\n✅ Diagnostic complete\n";
