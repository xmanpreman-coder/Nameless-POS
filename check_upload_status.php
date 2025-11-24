<?php
// Load Laravel
require_once 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USER AVATAR STATUS ===\n";
$users = \App\Models\User::select('id', 'name', 'avatar')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Avatar: {$u->avatar}\n";
}

echo "\n=== STORAGE LINK CHECK ===\n";
$path = public_path('storage');
echo "Storage link exists: " . (is_link($path) ? "YES" : "NO") . "\n";
echo "Storage path: $path\n";

echo "\n=== AVATARS DIRECTORY ===\n";
$avatarDir = storage_path('app/public/avatars');
echo "Avatars dir: $avatarDir\n";
echo "Exists: " . (is_dir($avatarDir) ? "YES" : "NO") . "\n";

if (is_dir($avatarDir)) {
    $files = array_diff(scandir($avatarDir), ['.', '..']);
    echo "Files found: " . count($files) . "\n";
    foreach ($files as $f) {
        $size = filesize("$avatarDir/$f");
        echo "  - $f (" . round($size / 1024, 2) . " KB)\n";
    }
} else {
    echo "Directory does not exist!\n";
}

echo "\n=== PRODUCT IMAGES (Media Library) ===\n";
$products = \Modules\Product\Entities\Product::all();
foreach ($products as $p) {
    $mediaCount = $p->media()->count();
    echo "ID: {$p->id} | Name: {$p->product_name} | Images: $mediaCount\n";
    if ($mediaCount > 0) {
        foreach ($p->media as $m) {
            echo "  - {$m->name} ({$m->size} bytes)\n";
        }
    }
}

echo "\n=== MEDIA TABLE INFO ===\n";
$mediaTable = \Illuminate\Support\Facades\DB::table('media')->select('collection_name', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))->groupBy('collection_name')->get();
foreach ($mediaTable as $row) {
    echo "Collection: {$row->collection_name} | Count: {$row->count}\n";
}

echo "\n=== STORAGE DIRECTORIES ===\n";
$storageDir = storage_path('app/public');
if (is_dir($storageDir)) {
    $dirs = array_diff(scandir($storageDir), ['.', '..']);
    echo "Directories in storage/app/public:\n";
    foreach ($dirs as $d) {
        $fullPath = "$storageDir/$d";
        if (is_dir($fullPath)) {
            $fileCount = count(array_diff(scandir($fullPath), ['.', '..']));
            echo "  - $d/ (" . $fileCount . " files)\n";
        }
    }
}

echo "\nDone!\n";
