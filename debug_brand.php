<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Product\Entities\Brand;
use Illuminate\Support\Facades\DB;

try {
    echo "Attempting to create Brand...\n";
    DB::enableQueryLog();
    $brand = Brand::create([
        'brand_code' => 'TESTCODE',
        'brand_name' => 'Test Brand',
        'brand_description' => 'Desc',
        'brand_image' => null 
    ]);
    echo "Success! ID: " . $brand->id . "\n";
} catch (\Exception $e) {
    echo "Fail: " . $e->getMessage() . "\n";
    // Check if table has brand_code
    $cols = DB::select('PRAGMA table_info(brands)');
    echo "Columns in DB:\n";
    foreach($cols as $c) {
        echo $c->name . "\n";
    }
}
