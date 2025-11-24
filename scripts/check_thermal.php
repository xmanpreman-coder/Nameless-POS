<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cnt = Illuminate\Support\Facades\DB::table('thermal_printer_settings')->count();
echo "thermal_printer_settings rows: " . $cnt . PHP_EOL;
$names = Illuminate\Support\Facades\DB::table('thermal_printer_settings')->pluck('name')->toArray();
echo "names: " . implode(', ', $names) . PHP_EOL;
