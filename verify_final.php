<?php
$db = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          FINAL DATABASE STATUS VERIFICATION               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$tables = [
    'users' => 'Users (Admin, Manager, Cashier)',
    'products' => 'Products',
    'categories' => 'Product Categories',
    'customers' => 'Customers',
    'suppliers' => 'Suppliers',
    'sales' => 'Sales Transactions',
    'sale_details' => 'Sales Details',
    'purchases' => 'Purchase Orders',
    'purchase_details' => 'Purchase Details',
    'quotations' => 'Quotations',
    'expenses' => 'Expenses',
    'roles' => 'Roles (Admin, Manager, etc)',
    'permissions' => 'Permissions',
    'units' => 'Units (pcs, box, kg, dll)',
];

foreach ($tables as $table => $label) {
    $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $bar = str_repeat('█', min(floor($count / 5), 20));
    printf("✓ %-30s: %4d rows %s\n", $label, $count, $bar);
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                   CREDENTIALS                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "Email: super.admin@test.com\n";
echo "Password: 12345678\n";

echo "\n✅ Database siap digunakan!\n";
?>
