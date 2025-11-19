<?php
// Set up Laravel
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete old migration records
DB::table('migrations')
    ->where('migration', 'like', '2025_11_09%')
    ->orWhere('migration', '=', '2025_11_19_000001_rename_product_code_to_sku')
    ->delete();

echo "Migration records cleaned. Remaining: " . DB::table('migrations')->count() . "\n";
