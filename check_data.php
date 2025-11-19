<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║          DATABASE INTEGRITY CHECK                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$db = app('db');

// Check data in main tables
$tables = [
    'users' => 'Users',
    'products' => 'Products',
    'sales' => 'Sales',
    'purchases' => 'Purchases',
    'categories' => 'Categories',
    'customers' => 'Customers',
    'suppliers' => 'Suppliers',
];

foreach ($tables as $table => $label) {
    try {
        $count = $db->table($table)->count();
        echo "📊 $label: $count records\n";
    } catch (\Exception $e) {
        echo "❌ $label: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Check if database file exists
$dbPath = database_path('database.sqlite');
if (file_exists($dbPath)) {
    $size = filesize($dbPath) / 1024; // KB
    echo "💾 Database file size: " . round($size, 2) . " KB\n";
    echo "📁 Database location: $dbPath\n";
} else {
    echo "❌ Database file not found!\n";
}

echo "\n";
