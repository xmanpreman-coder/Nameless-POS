<?php
// Adds product_sku and product_gtin columns (SQLite safe) and creates unique index for product_sku
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // Add columns if not exists (SQLite doesn't support IF NOT EXISTS for ADD COLUMN)
    $columns = DB::select("PRAGMA table_info(products)");
    $hasSku = false;
    $hasGtin = false;
    foreach ($columns as $col) {
        if (isset($col->name) && $col->name == 'product_sku') $hasSku = true;
        if (isset($col->name) && $col->name == 'product_gtin') $hasGtin = true;
    }

    if (!$hasSku) {
        DB::statement("ALTER TABLE products ADD COLUMN product_sku TEXT NULL;");
        echo "Added column product_sku\n";
    } else {
        echo "Column product_sku already exists\n";
    }

    if (!$hasGtin) {
        DB::statement("ALTER TABLE products ADD COLUMN product_gtin TEXT NULL;");
        echo "Added column product_gtin\n";
    } else {
        echo "Column product_gtin already exists\n";
    }

    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Now populate product_sku from product_code
try {
    DB::statement("UPDATE products SET product_sku = product_code WHERE product_sku IS NULL OR product_sku = ''");
    echo "Populated product_sku from product_code\n";
} catch (\Throwable $e) {
    echo "Warning while populating SKU: " . $e->getMessage() . "\n";
}

// Create unique index if possible
try {
    DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS idx_products_product_sku ON products(product_sku);");
    echo "Created unique index on product_sku (if no duplicates)\n";
} catch (\Throwable $e) {
    echo "Could not create unique index: " . $e->getMessage() . "\n";
}

// Report sample
$cnt = DB::select("SELECT COUNT(*) as cnt FROM products WHERE product_sku IS NOT NULL AND product_sku != ''");
echo "Products with product_sku: " . ($cnt[0]->cnt ?? 0) . "\n";
