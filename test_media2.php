<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
echo "User: " . $user->name . "\n";
echo "getFirstMediaUrl: " . ($user->getFirstMediaUrl('avatars') ?? 'NULL') . "\n";
echo "getFirstMedia: " . ($user->getFirstMedia('avatars') ? 'EXISTS' : 'NULL') . "\n";
if ($user->getFirstMedia('avatars')) {
    echo "Media URL: " . $user->getFirstMedia('avatars')->original_url . "\n";
}
