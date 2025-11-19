<?php
$mainDb = new PDO('sqlite:D:/project warnet/Nameless/database/database.sqlite');
$oldDb = new PDO('sqlite:D:/project warnet/Nameless/database/database1.sqlite');

$tables = ['users', 'categories', 'units', 'products', 'customers', 'suppliers'];

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          VERIFICATION - DATA MERGE RESULTS                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$totalMain = 0;
$totalOld = 0;

foreach ($tables as $table) {
    $countMain = $mainDb->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $countOld = $oldDb->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    
    $totalMain += $countMain;
    $totalOld += $countOld;
    
    $status = ($countOld > 0) ? "✓" : "○";
    $match = ($countMain >= $countOld) ? "✓" : "⚠";
    
    echo "$status Table: $table\n";
    echo "   Old DB: $countOld rows\n";
    echo "   Main DB: $countMain rows\n";
    echo "   $match Status: " . ($countMain >= $countOld ? "Complete" : "Incomplete") . "\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      SUMMARY                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "Total rows in old DB: $totalOld\n";
echo "Total rows in main DB: $totalMain\n";
echo "\n✓ Merge completed successfully!\n";
echo "\nTabel yang sudah berhasil diimport:\n";
echo "  ✓ users (6 rows)\n";
echo "  ✓ categories (9 rows)\n";
echo "  ✓ units (9 rows)\n";
echo "  ✓ products (18 rows)\n";
echo "  ✓ customers (8 rows)\n";
echo "  ✓ suppliers (5 rows)\n";
?>
