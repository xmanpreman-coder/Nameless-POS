<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Database Type: " . DB::connection()->getDriverName() . "\n";

$tables = ['brands', 'categories', 'products', 'customers', 'suppliers'];

foreach ($tables as $table) {
    echo "\nTable: $table\n";
    if (Schema::hasTable($table)) {
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            echo " - $column ($type)\n";
        }
    } else {
        echo " - TABLE NOT FOUND\n";
    }
}
