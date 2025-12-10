<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $settings = \Modules\Setting\Entities\Setting::first();
    if ($settings) {
        $settings->site_logo = null;
        $settings->login_logo = null;
        $settings->save();
        echo "Settings updated: Logos reset to NULL.\n";
    } else {
        echo "No settings found.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
