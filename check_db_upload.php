<?php
require_once 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== USER AVATAR STATUS ===\n";
$users = DB::table('users')->select('id', 'name', 'avatar')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Avatar: {$u->avatar}\n";
}

echo "\n=== CHECKING AVATAR FIELD TYPE ===\n";
$columns = DB::select('PRAGMA table_info(users)');
foreach ($columns as $col) {
    if ($col->name === 'avatar') {
        echo "Column 'avatar' type: {$col->type}\n";
    }
}

echo "\n=== MEDIA LIBRARY CHECK ===\n";
$media = DB::table('media')->select('id', 'model_type', 'model_id', 'collection_name', 'file_name')->get();
echo "Total media records: " . count($media) . "\n";
foreach ($media as $m) {
    echo "ID: {$m->id} | Model: {$m->model_type} | Model ID: {$m->model_id} | Collection: {$m->collection_name} | File: {$m->file_name}\n";
}

echo "\n=== CHECKING STORAGE SYMLINK ===\n";
$publicStoragePath = public_path('storage');
$isLink = is_link($publicStoragePath);
echo "public/storage is symlink: " . ($isLink ? "YES" : "NO") . "\n";
if ($isLink) {
    $target = readlink($publicStoragePath);
    echo "Points to: $target\n";
}

echo "\nDone!\n";
?>
