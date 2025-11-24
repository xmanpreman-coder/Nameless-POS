<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$user = \App\Models\User::find(1);
$media = $user->getFirstMedia('avatars');
if ($media) {
    echo "Media ID: " . $media->id . "\n";
    echo "Filename: " . $media->file_name . "\n";
    echo "Original URL: " . $media->original_url . "\n";
    echo "getUrl(): " . $media->getUrl() . "\n";
} else {
    echo "No media found\n";
}
