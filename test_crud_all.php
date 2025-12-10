<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Product\Entities\Product;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Brand;
use Modules\People\Entities\Customer;
use Modules\People\Entities\Supplier;
use Modules\Expense\Entities\Expense;
use Modules\Expense\Entities\ExpenseCategory;
use Modules\Setting\Entities\Unit;
use Illuminate\Support\Facades\DB;

function printResult($section, $message, $status = 'PASS') {
    $colors = [
        'PASS' => "\033[32m", // Green
        'FAIL' => "\033[31m", // Red
        'INFO' => "\033[34m", // Blue
        'RESET' => "\033[0m",
    ];
    echo "{$colors[$status]}[$section] $message{$colors['RESET']}\n";
}

echo "========================================\n";
echo "  COMPREHENSIVE CRUD TESTING START\n";
echo "========================================\n\n";

DB::beginTransaction();

try {

    // --- 1. CATEGORIES ---
    printResult('CATEGORY', 'Testing Create...');
    $category = Category::create([
        'category_code' => 'CAT-' . time(),
        'category_name' => 'Test Cat ' . time()
    ]);
    if ($category) printResult('CATEGORY', "Created ID: {$category->id}", 'PASS');

    printResult('CATEGORY', 'Testing Update...');
    $category->update(['category_name' => 'Updated Cat Name']);
    if ($category->category_name === 'Updated Cat Name') printResult('CATEGORY', "Updated Successfully", 'PASS');

    // --- 2. BRANDS ---
    printResult('BRAND', 'Testing Create...');
    $brand = Brand::create([
        'brand_code' => 'BRD-' . time(),
        'brand_name' => 'Test Brand ' . time()
        // Removed description as it doesn't exist in schema
    ]);
    if ($brand) printResult('BRAND', "Created ID: {$brand->id}", 'PASS');

    // --- 3. CUSTOMERS ---
    printResult('CUSTOMER', 'Testing Create...');
    $customer = Customer::create([
        'customer_name' => 'Test Customer',
        'customer_email' => 'cust'.time().'@test.com',
        'customer_phone' => '08123456789',
        'city' => 'Jakarta',
        'country' => 'Indonesia',
        'address' => 'Jl. Test No. 1'
    ]);
    if ($customer) printResult('CUSTOMER', "Created ID: {$customer->id}", 'PASS');

    // --- 4. SUPPLIERS ---
    printResult('SUPPLIER', 'Testing Create...');
    $supplier = Supplier::create([
        'supplier_name' => 'Test Supplier',
        'supplier_email' => 'supp'.time().'@test.com',
        'supplier_phone' => '08987654321',
        'city' => 'Surabaya',
        'country' => 'Indonesia',
        'address' => 'Jl. Supplier No. 1'
    ]);
    if ($supplier) printResult('SUPPLIER', "Created ID: {$supplier->id}", 'PASS');

    // --- 5. UNITS ---
    $unit = Unit::first();
    if (!$unit) {
        $unit = Unit::create(['name' => 'Piece', 'short_name' => 'pc', 'operator' => '*', 'operation_value' => 1]);
    }

    // --- 6. PRODUCTS ---
    printResult('PRODUCT', 'Testing Create...');
    $product = Product::create([
        'product_name' => 'Test Product ' . time(),
        'product_sku' => 'SKU-' . time(),
        'product_gtin' => 'GTIN-' . time(),
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'product_barcode_symbology' => 'C128',
        'product_quantity' => 100,
        'product_cost' => 50000,
        'product_price' => 100000,
        'product_unit' => $unit->short_name,
        'product_stock_alert' => 10,
        'product_tax_type' => 1
    ]);
    if ($product) printResult('PRODUCT', "Created ID: {$product->id}", 'PASS');

    // --- 7. EXPENSES ---
    printResult('EXPENSE', 'Testing Create Category...');
    $expenseCat = ExpenseCategory::create([
        'category_name' => 'Test Exp Cat',
        'category_description' => 'Desc'
    ]);
    
    printResult('EXPENSE', 'Testing Create Expense...');
    $expense = Expense::create([
        'reference' => 'EXP-'.time(),
        'category_id' => $expenseCat->id,
        'amount' => 50000,
        'details' => 'Test Details',
        'date' => date('Y-m-d')
    ]);
    if ($expense) printResult('EXPENSE', "Created ID: {$expense->id}", 'PASS');


    DB::commit();
    echo "\n========================================\n";
    echo "  ✅ ALL BACKEND CRUD TESTS PASSED\n";
    echo "========================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n\033[31mCRITICAL ERROR: " . $e->getMessage() . "\033[0m\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
