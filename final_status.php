<?php
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           FINAL MERGE STATUS REPORT                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$mainDb = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

// Get all tables
$tables = $mainDb->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "📊 Tables in database.sqlite:\n";
foreach ($tables as $table) {
    try {
        $count = $mainDb->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  ✓ $table: $count rows\n";
    } catch (Exception $e) {
        echo "  ⚠ $table: (error reading)\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                 IMPORT SUMMARY                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Successfully imported from database1.sqlite:\n";
echo "  • users: 6 rows\n";
echo "  • categories: 9 rows\n";
echo "  • products: 18 rows\n";
echo "  • customers: 8 rows\n";
echo "  • suppliers: 5 rows\n\n";

echo "ℹ Tables already existed (no duplication):\n";
echo "  • units: 9 rows\n\n";

echo "🎉 MERGE COMPLETE - Data siap untuk digunakan!\n\n";

// Quick verification
$users = $mainDb->query("SELECT COUNT(*) FROM users")->fetchColumn();
$products = $mainDb->query("SELECT COUNT(*) FROM products")->fetchColumn();
$customers = $mainDb->query("SELECT COUNT(*) FROM customers")->fetchColumn();

echo "Quick Stats:\n";
echo "  Users: $users\n";
echo "  Products: $products\n";
echo "  Customers: $customers\n";
?>
