<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $setting = \Modules\Setting\Entities\Setting::first();
    if ($setting) {
        echo "✓ Settings found: " . $setting->key . "\n";
    } else {
        echo "✗ No settings in database\n";
    }
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
