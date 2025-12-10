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
        // Create brands table only if it does not exist. If it exists, ensure required
        // columns are present (safe for SQLite where drop/create may be problematic).
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('brand_code')->unique();
                $table->string('brand_name');
                $table->timestamps();
            });
        } else {
            // Ensure columns exist, add them if missing (SQLite supports adding columns).
            if (!Schema::hasColumn('brands', 'brand_code')) {
                Schema::table('brands', function (Blueprint $table) {
                    $table->string('brand_code')->unique()->after('id');
                });
            }
            if (!Schema::hasColumn('brands', 'brand_name')) {
                Schema::table('brands', function (Blueprint $table) {
                    $table->string('brand_name')->after('brand_code');
                });
            }
            if (!Schema::hasColumn('brands', 'created_at')) {
                Schema::table('brands', function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brands');
    }
};