<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cek media table untuk products
echo "=== MEDIA TABLE - PRODUCTS ===\n";
$media = \DB::table('media')
    ->where('model_type', 'Modules\Product\Entities\Product')
    ->limit(5)
    ->get(['id', 'file_name', 'collection_name', 'model_id']);
    
if ($media->count() > 0) {
    foreach ($media as $m) {
        echo "Product ID {$m->model_id}: {$m->file_name} (collection: {$m->collection_name})\n";
    }
} else {
    echo "No product media found in media table\n";
}

// Cek products table sendiri
echo "\n=== PRODUCTS TABLE (Image Column) ===\n";
$products = \DB::table('products')->select('id', 'name', 'image')->limit(5)->get();
foreach ($products as $p) {
    if ($p->image) {
        echo "ID {$p->id}: {$p->name} -> {$p->image}\n";
    }
}

// Cek apakah ada folder gambar produk
echo "\n=== STORAGE FOLDERS ===\n";
$folders = glob("D:\\project warnet\\Nameless\\storage\\app\\public\\*", GLOB_ONLYDIR);
foreach ($folders as $f) {
    $name = basename($f);
    $count = count(glob("$f/*"));
    echo "  {$name}: {$count} files\n";
}
