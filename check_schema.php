<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║              DATABASE SCHEMA CHECK                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$db = app('db');

// Get schema for main tables
$tables = ['categories', 'products', 'customers', 'suppliers', 'sales'];

foreach ($tables as $table) {
    echo "📋 $table columns:\n";
    try {
        $columns = $db->select("PRAGMA table_info($table)");
        foreach ($columns as $col) {
            echo "   • " . $col->name . " (" . $col->type . ")\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
