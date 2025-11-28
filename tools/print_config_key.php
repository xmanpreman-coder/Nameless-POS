<?php
$root = __DIR__ . '/../';
chdir($root);
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "CONFIG_APP_KEY=" . $app->make('config')->get('app.key') . PHP_EOL; 
echo "APP_ENV=" . $app->make('config')->get('app.env') . PHP_EOL; 
echo "APP_DEBUG=" . ($app->make('config')->get('app.debug') ? 'true' : 'false') . PHP_EOL; 
