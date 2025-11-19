<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration recreates the `sale_details` table without the `product_code` column (SQLite-safe).
     * IMPORTANT: Do a DB backup before running.
     *
     * @return void
     */
    public function up()
    {
        // Create new table with desired schema
        Schema::create('sale_details_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('unit_price');
            $table->integer('sub_total');
            $table->integer('product_discount_amount')->default(0);
            $table->string('product_discount_type')->default('fixed');
            $table->integer('product_tax_amount')->default(0);
            $table->timestamps();
        });

        // Copy data: prefer existing product_sku; if empty, copy from product_code
        $rows = DB::select('SELECT * FROM sale_details');
        foreach ($rows as $r) {
            $sku = null;
            if (isset($r->product_sku) && $r->product_sku !== null && trim($r->product_sku) !== '') {
                $sku = $r->product_sku;
            } elseif (isset($r->product_code) && $r->product_code !== null && trim($r->product_code) !== '') {
                $sku = $r->product_code;
            }

            DB::table('sale_details_new')->insert([
                'id' => $r->id,
                'sale_id' => $r->sale_id,
                'product_id' => $r->product_id,
                'product_name' => $r->product_name,
                'product_sku' => $sku,
                'quantity' => $r->quantity,
                'price' => $r->price,
                'unit_price' => $r->unit_price,
                'sub_total' => $r->sub_total,
                'product_discount_amount' => $r->product_discount_amount,
                'product_discount_type' => $r->product_discount_type,
                'product_tax_amount' => $r->product_tax_amount,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
        }

        // Rename tables: keep old as backup
        DB::statement('ALTER TABLE sale_details RENAME TO sale_details_old');
        DB::statement('ALTER TABLE sale_details_new RENAME TO sale_details');
    }

    /**
     * Reverse the migrations.
     * This will attempt to restore the old table name if present.
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('sale_details_old')) {
            // Drop current and restore old
            Schema::dropIfExists('sale_details');
            DB::statement('ALTER TABLE sale_details_old RENAME TO sale_details');
        }
    }
};
