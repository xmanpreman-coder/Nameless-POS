<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $brands = \Modules\Product\Entities\Brand::all();
    echo "Brands count: " . $brands->count() . "\n";
    foreach ($brands as $brand) {
        echo "- " . $brand->brand_name . " (" . $brand->brand_code . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
