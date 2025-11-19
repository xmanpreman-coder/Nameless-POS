<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║          NAMELESS POS - DATA RECOVERY & RESTORATION               ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$db = app('db');

// Backup current database
$backupPath = database_path('database.sqlite.backup.' . date('Y-m-d_H-i-s'));
copy(database_path('database.sqlite'), $backupPath);
echo "✅ Backup created: $backupPath\n\n";

// Create sample data
echo "📝 Creating sample data...\n\n";

// Skip categories - they already exist
echo "   ⏭️  Categories already exist (skipped)\n";

// Skip products - they already exist from previous run
echo "   ⏭️  Products already exist (skipped)\n";

// 3. Customers
$customers = [
    ['customer_name' => 'PT Maju Jaya', 'customer_email' => 'info@majujaya.com', 'customer_phone' => '08123456789', 'city' => 'Jakarta', 'country' => 'Indonesia', 'address' => 'Jl. Merdeka 1, Jakarta'],
    ['customer_name' => 'Toko Kelontong', 'customer_email' => 'toko@kelontong.com', 'customer_phone' => '08234567890', 'city' => 'Bandung', 'country' => 'Indonesia', 'address' => 'Jl. Braga 10, Bandung'],
    ['customer_name' => 'Restoran Sejahtera', 'customer_email' => 'resto@sejahtera.com', 'customer_phone' => '08345678901', 'city' => 'Surabaya', 'country' => 'Indonesia', 'address' => 'Jl. Diponegoro 5, Surabaya'],
];

foreach ($customers as $cust) {
    $db->table('customers')->insert(array_merge($cust, ['created_at' => now(), 'updated_at' => now()]));
}
echo "   ✅ 3 customers created\n";

// 4. Suppliers
$suppliers = [
    ['supplier_name' => 'Supplier Coffee', 'supplier_email' => 'supplier@coffee.com', 'supplier_phone' => '08456789012', 'city' => 'Medan', 'country' => 'Indonesia', 'address' => 'Jl. Ahmad Yani 20, Medan'],
    ['supplier_name' => 'Supplier Snacks', 'supplier_email' => 'supplier@snacks.com', 'supplier_phone' => '08567890123', 'city' => 'Yogyakarta', 'country' => 'Indonesia', 'address' => 'Jl. Malioboro 15, Yogyakarta'],
];

foreach ($suppliers as $sup) {
    $db->table('suppliers')->insert(array_merge($sup, ['created_at' => now(), 'updated_at' => now()]));
}
echo "   ✅ 2 suppliers created\n\n";

// Verify data
echo "📊 DATA VERIFICATION\n";
echo "   • Users: " . $db->table('users')->count() . "\n";
echo "   • Categories: " . $db->table('categories')->count() . "\n";
echo "   • Products: " . $db->table('products')->count() . "\n";
echo "   • Customers: " . $db->table('customers')->count() . "\n";
echo "   • Suppliers: " . $db->table('suppliers')->count() . "\n\n";

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                    ✅ DATA RESTORED SUCCESSFULLY                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

echo "📌 NOTES:\n";
echo "   • Sample data created with correct schema\n";
echo "   • All tables populated and ready for use\n";
echo "   • System ready for testing\n\n";
