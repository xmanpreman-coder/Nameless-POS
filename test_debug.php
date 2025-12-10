<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Product\Entities\Category;
use Modules\Product\Entities\Brand;
use Modules\People\Entities\Customer;

echo "STARTING TEST...\n";

try {
    // CATEGORY
    echo "Creating Category...\n";
    $cat = Category::create([
        'category_code' => 'CAT'.time(),
        'category_name' => 'Test Cat'
    ]);
    echo "Category Created ID: " . $cat->id . "\n";

    // BRAND
    echo "Creating Brand...\n";
    $brand = Brand::create([
        'brand_code' => 'BRD'.time(), // Added code
        'brand_name' => 'Test Brand',
        'brand_description' => 'Desc'
    ]);
    echo "Brand Created ID: " . $brand->id . "\n";

     // CUSTOMER
     echo "Creating Customer...\n";
     $customer = Customer::create([
         'customer_name' => 'Test Customer',
         'customer_email' => 'cust'.time().'@test.com',
         'customer_phone' => '08123',
         'city' => 'Jkt',
         'country' => 'Id',
         'address' => 'Addr'
     ]);
     echo "Customer Created ID: " . $customer->id . "\n";
     
     echo "ALL PASSED!\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
