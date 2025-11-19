<?php

// Safe script to recreate `products` table in SQLite with final schema
// - Creates `products_new` with `product_sku` and `product_gtin`
// - Copies data preferring existing `product_sku` (if present) otherwise `product_code`
// - Swaps tables using SQLite-safe PRAGMA foreign_keys=OFF approach
// - Populates `product_sku` on detail tables if missing

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Starting recreate_products_table_NEW.php\n";

$dbFile = __DIR__ . '/../database/database.sqlite';
if (!file_exists($dbFile)) {
    echo "ERROR: database file not found at database/database.sqlite\n";
    exit(1);
}

$backup = __DIR__ . '/../database/database.sqlite.recreate_products_backup_' . date('Ymd_His') . '.bak';
if (!copy($dbFile, $backup)) {
    echo "ERROR: failed to create DB backup at {$backup}\n";
    exit(1);
}
echo "Backup created: {$backup}\n";

DB::statement('PRAGMA foreign_keys = OFF');

// Create products_new
if (Schema::hasTable('products_new')) {
    Schema::dropIfExists('products_new');
}

Schema::create('products_new', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->unsignedBigInteger('category_id');
    $table->string('product_name');
    $table->string('product_sku')->nullable();
    $table->string('product_gtin')->nullable();
    $table->string('product_barcode_symbology')->nullable();
    $table->integer('product_quantity');
    $table->integer('product_cost');
    $table->integer('product_price');
    $table->string('product_unit')->nullable();
    $table->integer('product_stock_alert');
    $table->integer('product_order_tax')->nullable();
    $table->tinyInteger('product_tax_type')->nullable();
    $table->text('product_note')->nullable();
    $table->timestamps();
});

echo "Created table products_new\n";

// Copy data from existing `products` into products_new
$rows = DB::table('products')->get();
$count = 0;
foreach ($rows as $r) {
    // Determine final SKU: prefer existing product_sku column if populated, otherwise use product_code
    $sku = null;
    if (isset($r->product_sku) && $r->product_sku !== null && trim($r->product_sku) !== '') {
        $sku = $r->product_sku;
    } elseif (isset($r->product_code) && $r->product_code !== null && trim($r->product_code) !== '') {
        $sku = $r->product_code;
    }

    DB::table('products_new')->insert([
        'id' => $r->id,
        'category_id' => $r->category_id,
        'product_name' => $r->product_name,
        'product_sku' => $sku,
        'product_gtin' => $r->product_gtin ?? null,
        'product_barcode_symbology' => $r->product_barcode_symbology ?? null,
        'product_quantity' => $r->product_quantity,
        'product_cost' => $r->product_cost,
        'product_price' => $r->product_price,
        'product_unit' => $r->product_unit ?? null,
        'product_stock_alert' => $r->product_stock_alert,
        'product_order_tax' => $r->product_order_tax ?? null,
        'product_tax_type' => $r->product_tax_type ?? null,
        'product_note' => $r->product_note ?? null,
        'created_at' => $r->created_at ?? null,
        'updated_at' => $r->updated_at ?? null,
    ]);
    $count++;
}

echo "Copied {$count} rows to products_new\n";

// Swap tables safely
try {
    DB::statement('ALTER TABLE products RENAME TO products_old');
    DB::statement('ALTER TABLE products_new RENAME TO products');
    echo "Renamed tables: products -> products_old, products_new -> products\n";
} catch (\Throwable $e) {
    echo "ERROR during rename: " . $e->getMessage() . "\n";
    DB::statement('PRAGMA foreign_keys = ON');
    exit(1);
}

// Populate product_sku on detail tables if needed
$detailTables = [
    'sale_details',
    'purchase_details',
    'quotation_details',
    'sale_return_details',
    'purchase_return_details'
];

foreach ($detailTables as $table) {
    if (!Schema::hasTable($table)) continue;

    // Add product_sku column if missing
    if (!Schema::hasColumn($table, 'product_sku')) {
        Schema::table($table, function (Blueprint $t) use ($table) {
            $t->string('product_sku')->nullable()->after('product_name');
        });
        echo "Added column product_sku to {$table}\n";
    }

    // Copy from product_code -> product_sku where appropriate
    try {
        DB::statement("UPDATE \"{$table}\" SET product_sku = product_code WHERE (product_sku IS NULL OR product_sku = '') AND (product_code IS NOT NULL AND product_code != '')");
        echo "Synced product_code -> product_sku for {$table}\n";
    } catch (\Throwable $e) {
        echo "Warning: failed to sync table {$table}: " . $e->getMessage() . "\n";
    }
}

// Try to create unique index on products.product_sku (if no duplicates)
try {
    DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS products_product_sku_unique ON products(product_sku)');
    echo "Attempted to create unique index on products.product_sku\n";
} catch (\Throwable $e) {
    echo "Warning: could not create unique index on product_sku: " . $e->getMessage() . "\n";
}

// Drop old products table backup (leave it for now but inform user)
echo "Products old table renamed to products_old (left in DB as backup).\n";

DB::statement('PRAGMA foreign_keys = ON');

echo "Done. Please verify database/database.sqlite and run your app to confirm.\n";

return 0;
