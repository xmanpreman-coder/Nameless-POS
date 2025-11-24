<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PROFILE AVATAR ===\n";
$user = \App\Models\User::find(1);
if ($user->getFirstMedia('avatars')) {
    echo "✓ Profile avatar exists\n";
    echo "  URL: " . $user->getFirstMediaUrl('avatars') . "\n";
} else {
    echo "✗ Profile avatar NOT found\n";
}

echo "\n=== PRODUCT IMAGES ===\n";
$products = \Modules\Product\Entities\Product::limit(3)->get();
foreach ($products as $product) {
    echo "\nProduct: {$product->name}\n";
    $media = $product->getMedia('products');
    if ($media->count() > 0) {
        foreach ($media as $m) {
            echo "  ✓ Image: " . $m->getUrl() . "\n";
        }
    } else {
        echo "  ✗ No images\n";
    }
}
