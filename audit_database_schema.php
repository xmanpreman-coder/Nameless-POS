<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo "  DATABASE SCHEMA AUDIT - ALL TABLES\n";
echo "========================================\n\n";

// Get all tables
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");

$issues = [];
$summary = [];

foreach ($tables as $tableObj) {
    $table = $tableObj->name;
    
    // Skip system tables
    if (str_starts_with($table, 'sqlite_')) continue;
    
    $columns = DB::select("PRAGMA table_info({$table})");
    $columnNames = array_column((array)$columns, 'name');
    
    $summary[$table] = [
        'columns' => count($columns),
        'data' => DB::table($table)->count(),
        'status' => '✅'
    ];
    
    // Check for common issues
    
    // 1. Check products table
    if ($table === 'products') {
        $hasProductSku = in_array('product_sku', $columnNames);
        $hasProductCode = in_array('product_code', $columnNames);
        $hasProductGtin = in_array('product_gtin', $columnNames);
        
        if (!$hasProductSku) {
            $issues[$table][] = "❌ Missing column: product_sku";
            $summary[$table]['status'] = '⚠️ MISSING SKU';
        }
        if (!$hasProductGtin) {
            $issues[$table][] = "❌ Missing column: product_gtin";
            $summary[$table]['status'] = '⚠️ MISSING GTIN';
        }
        if ($hasProductCode && $hasProductSku) {
            // Check if all SKUs are filled
            $withoutSku = DB::table('products')->whereNull('product_sku')->count();
            if ($withoutSku > 0) {
                $issues[$table][] = "⚠️ {$withoutSku} products without SKU";
                $summary[$table]['status'] = '⚠️ MISSING DATA';
            }
        }
    }
    
    // 2. Check sales table
    if ($table === 'sales') {
        if (!in_array('reference', $columnNames)) {
            $issues[$table][] = "❌ Missing column: reference";
            $summary[$table]['status'] = '❌ CRITICAL';
        }
    }
    
    // 3. Check purchases table
    if ($table === 'purchases') {
        if (!in_array('reference', $columnNames)) {
            $issues[$table][] = "❌ Missing column: reference";
            $summary[$table]['status'] = '❌ CRITICAL';
        }
    }
    
    // 4. Check users table
    if ($table === 'users') {
        if (!in_array('email', $columnNames)) {
            $issues[$table][] = "❌ Missing column: email";
            $summary[$table]['status'] = '❌ CRITICAL';
        }
    }
    
    // 5. Check thermal_printer_settings
    if ($table === 'thermal_printer_settings') {
        if (!in_array('connection_type', $columnNames)) {
            $issues[$table][] = "❌ Missing column: connection_type";
            $summary[$table]['status'] = '❌ CRITICAL';
        }
    }
}

// Display summary
echo "📊 DATABASE SUMMARY\n";
echo str_repeat("-", 60) . "\n";
echo sprintf("%-30s | %8s | %8s | %s\n", "Table", "Columns", "Records", "Status");
echo str_repeat("-", 60) . "\n";

foreach ($summary as $table => $info) {
    echo sprintf("%-30s | %8d | %8d | %s\n", 
        substr($table, 0, 28), 
        $info['columns'], 
        $info['data'],
        $info['status']
    );
}

echo str_repeat("-", 60) . "\n";

// Display issues
if (!empty($issues)) {
    echo "\n⚠️ ISSUES FOUND:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($issues as $table => $tableIssues) {
        echo "\n{$table}:\n";
        foreach ($tableIssues as $issue) {
            echo "  {$issue}\n";
        }
    }
} else {
    echo "\n✅ NO ISSUES FOUND - All tables have required columns\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Test CRUD operations for each module
echo "\n🧪 TESTING CRUD OPERATIONS\n";
echo str_repeat("=", 60) . "\n";

$testResults = [];

// Test Product CRUD
echo "\n1️⃣  PRODUCTS MODULE\n";
try {
    $product = DB::table('products')->first();
    if ($product) {
        echo "  ✅ READ: Product found (ID: {$product->id}, SKU: {$product->product_sku})\n";
        $testResults['products_read'] = true;
    } else {
        echo "  ⚠️ READ: No products in database\n";
        $testResults['products_read'] = false;
    }
} catch (\Exception $e) {
    echo "  ❌ READ: {$e->getMessage()}\n";
    $testResults['products_read'] = false;
}

// Test Sales CRUD
echo "\n2️⃣  SALES MODULE\n";
try {
    $sale = DB::table('sales')->first();
    if ($sale) {
        echo "  ✅ READ: Sale found (ID: {$sale->id}, Reference: {$sale->reference})\n";
        $testResults['sales_read'] = true;
    } else {
        echo "  ⚠️ READ: No sales in database\n";
        $testResults['sales_read'] = false;
    }
} catch (\Exception $e) {
    echo "  ❌ READ: {$e->getMessage()}\n";
    $testResults['sales_read'] = false;
}

// Test Purchases CRUD
echo "\n3️⃣  PURCHASES MODULE\n";
try {
    $purchase = DB::table('purchases')->first();
    if ($purchase) {
        echo "  ✅ READ: Purchase found (ID: {$purchase->id}, Reference: {$purchase->reference})\n";
        $testResults['purchases_read'] = true;
    } else {
        echo "  ⚠️ READ: No purchases in database\n";
        $testResults['purchases_read'] = false;
    }
} catch (\Exception $e) {
    echo "  ❌ READ: {$e->getMessage()}\n";
    $testResults['purchases_read'] = false;
}

// Test Users
echo "\n4️⃣  USERS\n";
try {
    $user = DB::table('users')->first();
    if ($user) {
        echo "  ✅ READ: User found (ID: {$user->id}, Email: {$user->email})\n";
        $testResults['users_read'] = true;
    } else {
        echo "  ⚠️ READ: No users in database\n";
        $testResults['users_read'] = false;
    }
} catch (\Exception $e) {
    echo "  ❌ READ: {$e->getMessage()}\n";
    $testResults['users_read'] = false;
}

// Final status
echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ DATABASE AUDIT COMPLETE\n";
echo str_repeat("=", 60) . "\n";
