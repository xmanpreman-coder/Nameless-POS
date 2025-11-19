<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "  CHECKING MIGRATION STATUS\n";
echo "========================================\n\n";

// Get list of migrations in database
$migrationsRan = DB::table('migrations')->orderBy('batch')->get();

echo "Migrations that have RUN:\n";
foreach ($migrationsRan as $migration) {
    echo "  - {$migration->migration} (Batch: {$migration->batch})\n";
}

echo "\n\nProduct-related migrations:\n";
$productMigrations = DB::table('migrations')
    ->where('migration', 'like', '%product%')
    ->orWhere('migration', 'like', '%Product%')
    ->get();

foreach ($productMigrations as $m) {
    echo "  ✅ {$m->migration}\n";
}

// Check if product_sku column exists in migrations table log
echo "\n\nLooking for: 'rename_product_code_to_sku_and_add_gtin'\n";
$hasSkuMigration = DB::table('migrations')
    ->where('migration', 'like', '%rename_product_code_to_sku%')
    ->exists();

if ($hasSkuMigration) {
    echo "  ✅ Migration HAS been recorded in database\n";
} else {
    echo "  ❌ Migration NOT recorded - needs to be marked as run\n";
}

echo "\n" . str_repeat("=", 40) . "\n";

// Mark the migration as run if not already
if (!$hasSkuMigration) {
    echo "\nMarking SKU migration as RAN...\n";
    DB::table('migrations')->insert([
        'migration' => '2025_11_09_000001_rename_product_code_to_sku_and_add_gtin',
        'batch' => DB::table('migrations')->max('batch') + 1,
    ]);
    echo "✅ Migration marked as run\n";
}
