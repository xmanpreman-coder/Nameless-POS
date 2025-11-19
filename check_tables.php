<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$db = app('db');

// Get all existing tables
$tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
echo "=== Existing Tables ===\n";
foreach ($tables as $t) {
    echo "- " . $t->name . "\n";
}
echo "\n";

// Get all migrations marked as run
$migrations = $db->table('migrations')->get();
echo "=== Migrations Marked as Run ===\n";
foreach ($migrations as $m) {
    echo "- " . $m->migration . "\n";
}
