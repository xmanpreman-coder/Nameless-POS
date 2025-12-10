<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Modules\Setting\Entities\Setting;

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
    echo "✅ Settings created successfully!\n";
} else {
    echo "✅ Settings already exist.\n";
}
