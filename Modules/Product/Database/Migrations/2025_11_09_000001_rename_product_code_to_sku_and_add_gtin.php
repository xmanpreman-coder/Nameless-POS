<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameProductCodeToSkuAndAddGtin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SQLite doesn't support renameColumn, so we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // Add new columns
            Schema::table('products', function (Blueprint $table) {
                $table->string('product_sku')->nullable()->after('product_name');
                $table->string('product_gtin')->nullable()->after('product_sku');
            });
            
            // Copy data from product_code to product_sku
            DB::statement('UPDATE products SET product_sku = product_code');
            
            // Drop old column (SQLite doesn't support dropColumn easily, so we'll keep it for now)
            // The old column will be ignored in the application code
        } else {
            // For MySQL/PostgreSQL, use renameColumn
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('product_code', 'product_sku');
                $table->string('product_gtin')->nullable()->after('product_sku');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['product_sku', 'product_gtin']);
            });
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('product_gtin');
                $table->renameColumn('product_sku', 'product_code');
            });
        }
    }
}

