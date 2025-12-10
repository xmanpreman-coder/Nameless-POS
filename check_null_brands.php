<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $nullBrandCount = \Modules\Product\Entities\Product::whereNull('brand_id')->count();
    $totalProducts = \Modules\Product\Entities\Product::count();
    
    echo "Total Products: $totalProducts\n";
    echo "Products with NULL brand_id: $nullBrandCount\n";
    
    if ($nullBrandCount > 0) {
        echo "Issue confirmed: Old products have no brand assigned.\n";
    }

    $generalBrand = \Modules\Product\Entities\Brand::where('brand_code', 'BR_01')->first();
    if ($generalBrand) {
        echo "Default Brand 'General' found (ID: " . $generalBrand->id . ")\n";
    } else {
        echo "Default Brand 'General' NOT found!\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
