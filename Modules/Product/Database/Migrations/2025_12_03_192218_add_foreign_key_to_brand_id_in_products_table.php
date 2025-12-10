<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only add the column if it doesn't already exist (prevents duplicate column errors
        // when migrations run multiple times on SQLite or when partial runs occurred).
        if (!Schema::hasColumn('products', 'brand_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('category_id');
                $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
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
        // SQLite does not support dropping foreign keys easily.
        // We will leave this empty to avoid errors.
        // To properly reverse this, the table would need to be rebuilt.
    }
};