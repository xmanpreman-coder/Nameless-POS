<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $settings = \Modules\Setting\Entities\Setting::first();
    if ($settings) {
        echo "Site Logo: " . $settings->site_logo . "\n";
        echo "Login Logo: " . $settings->login_logo . "\n";
        
        $siteLogoPath = storage_path('app/public/' . $settings->site_logo);
        echo "Site Logo Path: " . $siteLogoPath . "\n";
        echo "Exists: " . (file_exists($siteLogoPath) ? 'YES' : 'NO') . "\n";
    } else {
        echo "No settings found.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
