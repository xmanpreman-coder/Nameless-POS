<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = app('db');

// List of existing tables that should be marked as migrated
$existingMigrations = [
    '2021_07_15_211319_create_media_table',
    '2021_07_16_010005_create_uploads_table',
    '2021_07_16_220524_create_permission_tables',
    '2021_07_22_003941_create_adjustments_table',
    '2021_07_22_004043_create_adjusted_products_table',
    '2021_07_28_192608_create_expense_categories_table',
    '2021_07_28_192616_create_expenses_table',
    '2021_07_29_165419_create_customers_table',
    '2021_07_29_165440_create_suppliers_table',
    '2021_07_31_015923_create_currencies_table',
    '2021_07_31_140531_create_settings_table',
    '2021_07_31_201003_create_sales_table',
    '2021_07_31_212446_create_sale_details_table',
    '2021_08_07_192203_create_sale_payments_table',
    '2021_08_08_021108_create_purchases_table',
    '2021_08_08_021131_create_purchase_payments_table',
    '2021_08_08_021713_create_purchase_details_table',
    '2021_08_08_175345_create_sale_returns_table',
    '2021_08_08_175358_create_sale_return_details_table',
    '2021_08_08_175406_create_sale_return_payments_table',
    '2021_08_08_222603_create_purchase_returns_table',
    '2021_08_08_222612_create_purchase_return_details_table',
    '2021_08_08_222646_create_purchase_return_payments_table',
    '2021_08_16_015031_create_quotations_table',
    '2021_08_16_155013_create_quotation_details_table',
    '2023_07_01_184221_create_units_table',
];

foreach ($existingMigrations as $migration) {
    $db->table('migrations')->updateOrInsert(
        ['migration' => $migration],
        ['batch' => 1]
    );
    echo "✅ Marked: $migration\n";
}

echo "\n✅ All existing migrations marked as run!\n";
