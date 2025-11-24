<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PROFILE AVATAR ===\n";
$user = \App\Models\User::find(1);
echo "User: {$user->name}\n";
echo "  users.avatar column: " . ($user->avatar ?? 'NULL') . "\n";
$media = $user->getFirstMedia('avatars');
if ($media) {
    echo "  media table record:\n";
    echo "    - ID: {$media->id}\n";
    echo "    - file_name: {$media->file_name}\n";
    echo "    - disk: {$media->disk}\n";
    echo "    - original_url: {$media->original_url}\n";
} else {
    echo "  No media found\n";
}

echo "\n=== PRODUCT IMAGES ===\n";
$products = \Modules\Product\Entities\Product::limit(5)->get();
foreach ($products as $product) {
    echo "Product ID {$product->id}: {$product->product_name}\n";
    $media_items = $product->getMedia('images');
    if ($media_items->count() > 0) {
        foreach ($media_items as $m) {
            echo "  - file_name: {$m->file_name}\n";
            echo "    disk: {$m->disk}\n";
            echo "    url: {$m->getUrl()}\n";
        }
    } else {
        echo "  No images\n";
    }
}
