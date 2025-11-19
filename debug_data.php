<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Product\Entities\Product;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Entities\PurchaseDetail;

echo "\n" . str_repeat("=", 80) . "\n";
echo "🔍 DEBUG DATA DI DATABASE\n";
echo str_repeat("=", 80) . "\n\n";

// Check data counts
echo "1️⃣  DATA COUNTS\n";
echo str_repeat("-", 80) . "\n";
echo "  Products: " . Product::count() . "\n";
echo "  Sales: " . Sale::count() . "\n";
echo "  SaleDetails: " . SaleDetails::count() . "\n";
echo "  Purchases: " . Purchase::count() . "\n";
echo "  PurchaseDetails: " . PurchaseDetail::count() . "\n";

// Check first sale
echo "\n2️⃣  FIRST SALE\n";
echo str_repeat("-", 80) . "\n";
$sale = Sale::with('details')->first();
if ($sale) {
    echo "  Sale ID: {$sale->id}\n";
    echo "  Reference: {$sale->reference}\n";
    echo "  Details count: " . $sale->details()->count() . "\n";
    
    $detail = $sale->details()->first();
    if ($detail) {
        echo "\n  First Detail:\n";
        echo "    Product Name: {$detail->product_name}\n";
        echo "    Product SKU: {$detail->product_sku}\n";
    }
} else {
    echo "  No sale found\n";
}

// Check first purchase
echo "\n3️⃣  FIRST PURCHASE\n";
echo str_repeat("-", 80) . "\n";
$purchase = Purchase::with('details')->first();
if ($purchase) {
    echo "  Purchase ID: {$purchase->id}\n";
    echo "  Reference: {$purchase->reference}\n";
    echo "  Details count: " . $purchase->details()->count() . "\n";
    
    $detail = $purchase->details()->first();
    if ($detail) {
        echo "\n  First Detail:\n";
        echo "    Product Name: {$detail->product_name}\n";
        echo "    Product SKU: {$detail->product_sku}\n";
    }
} else {
    echo "  No purchase found\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
