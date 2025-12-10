<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = ['brands', 'categories', 'products', 'customers', 'suppliers', 'expenses'];

foreach ($tables as $table) {
    echo "\n=== $table ===\n";
    if (Schema::hasTable($table)) {
        $columns = Schema::getColumnListing($table);
        echo implode(', ', $columns) . "\n";
    }
}
