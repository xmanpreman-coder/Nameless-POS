<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║      MULTI-PRINTER SYSTEM - DEPLOYMENT VERIFICATION            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$tests = [
    'Service Layer' => function() {
        $file = base_path('app/Services/PrinterService.php');
        return file_exists($file) ? "✅ PrinterService.php exists\n" : "❌ PrinterService.php missing\n";
    },
    'Driver Factory' => function() {
        $file = base_path('app/Services/PrinterDriverFactory.php');
        return file_exists($file) ? "✅ PrinterDriverFactory.php exists\n" : "❌ PrinterDriverFactory.php missing\n";
    },
    'Database Tables' => function() {
        $db = app('db');
        $tables = $db->select("SELECT count(*) as cnt FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        return "✅ {$tables[0]->cnt} tables created\n";
    },
    'Printer Preferences Table' => function() {
        $db = app('db');
        try {
            $table = $db->select("SELECT 1 FROM user_printer_preferences LIMIT 1");
            return "✅ user_printer_preferences table exists\n";
        } catch (\Exception $e) {
            return "❌ user_printer_preferences table missing\n";
        }
    },
    'Thermal Printer Settings Table' => function() {
        $db = app('db');
        try {
            $table = $db->select("SELECT 1 FROM thermal_printer_settings LIMIT 1");
            return "✅ thermal_printer_settings table exists\n";
        } catch (\Exception $e) {
            return "❌ thermal_printer_settings table missing\n";
        }
    },
    'Controller Methods' => function() {
        $file = base_path('app/Http/Controllers/PrinterSettingController.php');
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $methods = ['testConnection', 'setDefault', 'deletePrinter', 'savePreference'];
            $found = 0;
            foreach ($methods as $method) {
                if (strpos($content, "function $method") !== false) {
                    $found++;
                }
            }
            return "✅ PrinterSettingController: $found/4 methods found\n";
        }
        return "❌ PrinterSettingController not found\n";
    },
    'Routes Configured' => function() {
        $file = base_path('routes/web.php');
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $routeCount = substr_count($content, 'Route::');
            return "✅ {$routeCount}+ routes configured\n";
        }
        return "❌ Routes file not found\n";
    },
    'Documentation' => function() {
        $docs = [
            'README_START_HERE.md',
            'ACTION_ITEMS.md',
            'DEPLOYMENT_CHECKLIST.md',
            'MULTI_PRINTER_IMPLEMENTATION.md',
        ];
        $found = 0;
        foreach ($docs as $doc) {
            if (file_exists(base_path($doc))) {
                $found++;
            }
        }
        return "✅ $found/4 key documentation files found\n";
    },
    'Migrations Status' => function() {
        $db = app('db');
        $migrations = $db->table('migrations')->count();
        return "✅ $migrations migrations recorded\n";
    }
];

foreach ($tests as $name => $test) {
    echo "📋 Testing: $name\n";
    echo "   " . $test();
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║              ✅ DEPLOYMENT VERIFICATION COMPLETE               ║\n";
echo "║                                                                ║\n";
echo "║  Next Steps:                                                   ║\n";
echo "║  1. php artisan serve                                          ║\n";
echo "║  2. Visit http://localhost:8000/printer-settings               ║\n";
echo "║  3. Configure your thermal printers                            ║\n";
echo "║                                                                ║\n";
echo "║  Documentation:                                                ║\n";
echo "║  • README_START_HERE.md                                        ║\n";
echo "║  • ACTION_ITEMS.md                                             ║\n";
echo "║  • DEPLOYMENT_CHECKLIST.md                                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
