<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

try {
    $productCount = \Modules\Product\Entities\Product::count();
    echo "Products: " . $productCount . "\n";

    $categoryCount = \Modules\Product\Entities\Category::count();
    echo "Categories: " . $categoryCount . "\n";

    $brandCount = \Modules\Product\Entities\Brand::count();
    echo "Brands: " . $brandCount . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
