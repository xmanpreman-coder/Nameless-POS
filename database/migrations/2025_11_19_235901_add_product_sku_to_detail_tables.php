<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddProductSkuToDetailTables20251119 extends Migration
{
    public function up(): void
    {
        $detailTables = [
            'sale_details',
            'purchase_details',
            'quotation_details',
            'sale_return_details',
            'purchase_return_details'
        ];

        foreach ($detailTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'product_sku')) {
                        $t->string('product_sku')->nullable()->after('product_name');
                    }
                });
            }
        }

        // Copy existing values from product_code -> product_sku where present
        foreach ($detailTables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("UPDATE \"{$table}\" SET product_sku = product_code WHERE (product_sku IS NULL OR product_sku = '') AND (product_code IS NOT NULL AND product_code != '')");
                } catch (\Throwable $e) {
                    // ignore errors on older DBs
                }
            }
        }
    }

    public function down(): void
    {
        $detailTables = [
            'sale_details',
            'purchase_details',
            'quotation_details',
            'sale_return_details',
            'purchase_return_details'
        ];

        foreach ($detailTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (Schema::hasColumn($table, 'product_sku')) {
                        $t->dropColumn('product_sku');
                    }
                });
            }
        }
    }
}
