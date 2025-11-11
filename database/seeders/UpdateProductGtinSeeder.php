<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Entities\Product;

class UpdateProductGtinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Updating products with GTIN...');
        
        $products = Product::whereNull('product_gtin')->get();
        
        foreach ($products as $product) {
            // 50% chance to add GTIN
            if (rand(0, 1)) {
                // Generate EAN-13 format GTIN (13 digits)
                $gtin = str_pad(rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT);
                $product->update(['product_gtin' => $gtin]);
            }
        }
        
        $this->command->info('Updated ' . Product::whereNotNull('product_gtin')->count() . ' products with GTIN');
    }
}

