<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_sku')) {
                $table->string('product_sku')->nullable()->unique()->after('updated_at');
            }
            if (!Schema::hasColumn('products', 'product_gtin')) {
                $table->string('product_gtin')->nullable()->after('product_sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_sku')) {
                $table->dropColumn('product_sku');
            }
            if (Schema::hasColumn('products', 'product_gtin')) {
                $table->dropColumn('product_gtin');
            }
        });
    }
};
