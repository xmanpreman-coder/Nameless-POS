<?php
// Script to populate product_sku from product_code for SQLite DB
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Bootstrap the app
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    DB::statement("UPDATE products SET product_sku = product_code WHERE (product_sku IS NULL OR product_sku = '')");
    DB::commit();
    echo "product_sku population complete\n";
    $rows = DB::select("SELECT COUNT(*) as cnt FROM products WHERE product_sku IS NOT NULL AND product_sku != ''");
    echo "Products with product_sku: " . ($rows[0]->cnt ?? 0) . "\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
