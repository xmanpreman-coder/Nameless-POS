<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cek columns di products table
echo "=== PRODUCTS TABLE COLUMNS ===\n";
$columns = \DB::getSchemaBuilder()->getColumnListing('products');
foreach ($columns as $col) {
    echo "  - {$col}\n";
}

// Cek sample products
echo "\n=== SAMPLE PRODUCTS ===\n";
$products = \Modules\Product\Entities\Product::limit(3)->get(['id', 'name', 'sku']);
foreach ($products as $p) {
    echo "ID {$p->id}: {$p->name} (SKU: {$p->sku})\n";
}

// Cek apakah ada media untuk products
echo "\n=== CHECKING MEDIA TABLE ===\n";
$all_media = \DB::table('media')->distinct('model_type')->get('model_type');
echo "Model types in media table:\n";
foreach ($all_media as $m) {
    $count = \DB::table('media')->where('model_type', $m->model_type)->count();
    echo "  {$m->model_type}: {$count} records\n";
}
