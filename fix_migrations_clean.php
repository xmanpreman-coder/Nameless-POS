<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = app('db');

// Tables that actually exist
$existingTables = ['users', 'password_resets', 'failed_jobs', 'categories', 'products', 'media', 'scanner_settings', 'thermal_printer_settings'];

// Migrations for tables that exist
$validMigrations = [
    '2014_10_12_000000_create_users_table',
    '2014_10_12_100000_create_password_resets_table',
    '2019_08_19_000000_create_failed_jobs_table',
    '2021_07_14_145038_create_categories_table',
    '2021_07_14_145047_create_products_table',
    '2021_07_15_211319_create_media_table',
    '2024_01_01_000000_create_scanner_settings_table',
    '2025_01_01_000001_create_thermal_printer_settings_table',
    '2025_01_15_000000_add_external_settings_to_scanner_settings_table',
    '2025_11_09_000001_rename_product_code_to_sku_and_add_gtin',
];

// Clear migrations table and re-add only valid ones
$db->table('migrations')->delete();

foreach ($validMigrations as $migration) {
    $db->table('migrations')->insert([
        'migration' => $migration,
        'batch' => 1
    ]);
    echo "✅ Marked as run: $migration\n";
}

echo "\n✅ Migration table reset - only real migrations marked as run!\n";
