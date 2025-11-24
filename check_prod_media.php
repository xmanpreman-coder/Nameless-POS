<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$media = \DB::table('media')
    ->where('model_type','Modules\\Product\\Entities\\Product')
    ->limit(10)
    ->get(['id','model_id','file_name','collection_name']);

if ($media->count() > 0) {
    echo "Product media found: " . $media->count() . "\n";
    foreach ($media as $m) {
        echo "ID: {$m->id}, Product: {$m->model_id}, File: {$m->file_name}, Collection: {$m->collection_name}\n";
    }
} else {
    echo "No product media found\n";
}
