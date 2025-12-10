<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Modules\Setting\Entities\Setting;

try {
    // Check if settings already exist
    if (Setting::count() === 0) {
        Setting::create([
            'company_name' => 'Nameless.POS',
            'company_email' => 'company@test.com',
            'company_phone' => '012345678901',
            'notification_email' => 'notification@test.com',
            'default_currency_id' => 1,
            'default_currency_position' => 'prefix',
            'footer_text' => 'Nameless.POS © 2021 || Developed by <strong><a target="_blank" href="https://fahimanzam.me">Fahim Anzam</a></strong>',
            'company_address' => 'Tangail, Bangladesh'
        ]);
        echo "[OK] Settings created successfully\n";
    } else {
        echo "[INFO] Settings already exist (" . Setting::count() . " records)\n";
    }
} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
