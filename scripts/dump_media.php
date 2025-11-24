<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('media')->where('model_type', 'App\\Models\\User')->get();
if (count($rows) === 0) {
    echo "<no media rows for App\\Models\\User>\n";
} else {
    foreach ($rows as $r) {
        echo "id={$r->id} model_type={$r->model_type} model_id={$r->model_id} collection_name={$r->collection_name} file_name={$r->file_name} disk={$r->disk} mime_type={$r->mime_type} size={$r->size} created_at={$r->created_at}\n";
    }
}
