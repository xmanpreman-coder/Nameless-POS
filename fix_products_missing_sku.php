<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;

echo "Checking products without SKU...\n\n";

// Find products without SKU
$productsWithoutSku = Product::whereNull('product_sku')->get();

echo "Products without SKU: " . count($productsWithoutSku) . "\n\n";

foreach ($productsWithoutSku as $product) {
    echo "ID: {$product->id}, Name: {$product->product_name}, Code: {$product->product_code}\n";
    
    // Generate SKU from ID if product_code is also empty
    if (empty($product->product_code)) {
        $sku = 'PRD' . str_pad($product->id, 4, '0', STR_PAD_LEFT);
        $product->update(['product_sku' => $sku, 'product_code' => $sku]);
        echo "  ✅ Generated SKU: {$sku}\n";
    } else {
        // Use product_code
        $product->update(['product_sku' => $product->product_code]);
        echo "  ✅ Set SKU from product_code: {$product->product_code}\n";
    }
}

echo "\n✅ All products now have SKU\n";

// Final verification
$productsWithoutSku = Product::whereNull('product_sku')->count();
echo "Products still without SKU: {$productsWithoutSku}\n";

if ($productsWithoutSku == 0) {
    echo "\n✅ DATABASE FIXED!\n";
}
