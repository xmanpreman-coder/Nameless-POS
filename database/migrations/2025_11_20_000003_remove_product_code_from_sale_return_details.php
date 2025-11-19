<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_return_details_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sale_return_id');
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

        $rows = DB::select('SELECT * FROM sale_return_details');
        foreach ($rows as $r) {
            $sku = null;
            if (isset($r->product_sku) && $r->product_sku !== null && trim($r->product_sku) !== '') {
                $sku = $r->product_sku;
            } elseif (isset($r->product_code) && $r->product_code !== null && trim($r->product_code) !== '') {
                $sku = $r->product_code;
            }

            DB::table('sale_return_details_new')->insert([
                'id' => $r->id,
                'sale_return_id' => $r->sale_return_id,
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

        DB::statement('ALTER TABLE sale_return_details RENAME TO sale_return_details_old');
        DB::statement('ALTER TABLE sale_return_details_new RENAME TO sale_return_details');
    }

    public function down()
    {
        if (Schema::hasTable('sale_return_details_old')) {
            Schema::dropIfExists('sale_return_details');
            DB::statement('ALTER TABLE sale_return_details_old RENAME TO sale_return_details');
        }
    }
};
