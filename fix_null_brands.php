<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $generalBrand = \Modules\Product\Entities\Brand::where('brand_code', 'BR_01')->first();
    
    if (!$generalBrand) {
        echo "Error: Default brand not found.\n";
        exit;
    }

    $affected = \Modules\Product\Entities\Product::whereNull('brand_id')
        ->update(['brand_id' => $generalBrand->id]);
        
    echo "Updated $affected products to use Brand: " . $generalBrand->brand_name . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
