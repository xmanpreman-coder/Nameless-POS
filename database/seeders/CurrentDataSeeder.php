<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\People\Entities\Customer;
use Modules\People\Entities\Supplier;
use Modules\Setting\Entities\Setting;
use Illuminate\Support\Facades\Hash;

class CurrentDataSeeder extends Seeder
{
    /**
     * Run the database seeds based on current real data.
     * This seeder preserves the actual data from the production database.
     */
    public function run()
    {
        // Users
        $users = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'email' => 'super.admin@test.com',
                'password' => Hash::make('12345678'),
                'is_active' => 1,
                'created_at' => '2025-11-11 15:32:25',
                'updated_at' => '2025-11-11 15:32:25',
            ],
            [
                'id' => 2,
                'name' => 'Test User',
                'email' => 'test.user@test.com',
                'password' => Hash::make('12345678'),
                'is_active' => 1,
                'created_at' => '2025-11-11 15:32:25',
                'updated_at' => '2025-11-11 15:32:25',
            ]
        ];

        foreach ($users as $userData) {
            $userDataWithoutId = $userData;
            unset($userDataWithoutId['id']);
            User::updateOrCreate(['email' => $userData['email']], $userDataWithoutId);
        }

        // Categories
        $categories = [
            ['id' => 1, 'category_code' => 'CAT-0001', 'category_name' => 'Minuman'],
            ['id' => 2, 'category_code' => 'CAT-0002', 'category_name' => 'Makanan'],
            ['id' => 3, 'category_code' => 'CAT-0003', 'category_name' => 'Rokok'],
            ['id' => 4, 'category_code' => 'CAT-0004', 'category_name' => 'Snack'],
            ['id' => 5, 'category_code' => 'CAT-0005', 'category_name' => 'Alat Tulis'],
            ['id' => 6, 'category_code' => 'CAT-0006', 'category_name' => 'Peralatan Mandi'],
            ['id' => 7, 'category_code' => 'CAT-0007', 'category_name' => 'Obat-obatan'],
            ['id' => 8, 'category_code' => 'CAT-0008', 'category_name' => 'Elektronik'],
        ];

        foreach ($categories as $categoryData) {
            $categoryDataWithoutId = $categoryData;
            unset($categoryDataWithoutId['id']);
            Category::updateOrCreate(['category_code' => $categoryData['category_code']], $categoryDataWithoutId);
        }

        // Sample Products (first 10 from database)
        $products = [
            ['id' => 1, 'product_name' => 'Aqua 600ml', 'product_sku' => 'PRD-0001', 'category_id' => 1, 'product_cost' => 3500, 'product_price' => 5000, 'product_quantity' => 50, 'product_unit' => 'pcs', 'product_stock_alert' => 10, 'product_order_tax' => 0, 'product_tax_type' => 1],
            ['id' => 2, 'product_name' => 'Indomie Ayam Bawang', 'product_sku' => 'PRD-0002', 'category_id' => 2, 'product_cost' => 2800, 'product_price' => 3500, 'product_quantity' => 100, 'product_unit' => 'pcs', 'product_stock_alert' => 20, 'product_order_tax' => 0, 'product_tax_type' => 1],
            ['id' => 3, 'product_name' => 'Gudang Garam Merah', 'product_sku' => 'PRD-0003', 'category_id' => 3, 'product_cost' => 22000, 'product_price' => 25000, 'product_quantity' => 30, 'product_unit' => 'pack', 'product_stock_alert' => 5, 'product_order_tax' => 0, 'product_tax_type' => 1],
            ['id' => 4, 'product_name' => 'Chitato Sapi Panggang', 'product_sku' => 'PRD-0004', 'category_id' => 4, 'product_cost' => 8500, 'product_price' => 10000, 'product_quantity' => 25, 'product_unit' => 'pcs', 'product_stock_alert' => 10, 'product_order_tax' => 0, 'product_tax_type' => 1],
            ['id' => 5, 'product_name' => 'Pulpen Standard', 'product_sku' => 'PRD-0005', 'category_id' => 5, 'product_cost' => 1500, 'product_price' => 2500, 'product_quantity' => 75, 'product_unit' => 'pcs', 'product_stock_alert' => 15, 'product_order_tax' => 0, 'product_tax_type' => 1],
        ];

        foreach ($products as $productData) {
            $productDataWithoutId = $productData;
            unset($productDataWithoutId['id']);
            Product::updateOrCreate(['product_sku' => $productData['product_sku']], $productDataWithoutId);
        }

        // Customers
        $customers = [
            ['id' => 1, 'customer_name' => 'Umum', 'customer_email' => 'umum@toko.com', 'customer_phone' => '-', 'city' => '-', 'country' => 'Indonesia', 'address' => '-'],
            ['id' => 2, 'customer_name' => 'Budi Santoso', 'customer_email' => 'budi@email.com', 'customer_phone' => '081234567890', 'city' => 'Jakarta', 'country' => 'Indonesia', 'address' => 'Jl. Sudirman No. 1'],
            ['id' => 3, 'customer_name' => 'Siti Rahayu', 'customer_email' => 'siti@email.com', 'customer_phone' => '082345678901', 'city' => 'Bandung', 'country' => 'Indonesia', 'address' => 'Jl. Braga No. 5'],
        ];

        foreach ($customers as $customerData) {
            $customerDataWithoutId = $customerData;
            unset($customerDataWithoutId['id']);
            Customer::updateOrCreate(['customer_name' => $customerData['customer_name']], $customerDataWithoutId);
        }

        // Settings
        Setting::updateOrCreate(['id' => 1], [
            'company_name' => 'Toko Kelontong ABC',
            'company_email' => 'info@tokoabc.com', 
            'company_phone' => '021-1234567',
            'site_logo' => null,
            'default_currency_id' => 1,
            'default_currency_position' => 'before',
            'notification_email' => 'info@tokoabc.com',
            'footer_text' => 'Terima kasih atas kunjungan Anda',
            'company_address' => 'Jl. Contoh No. 123, Jakarta',
            'login_logo' => null,
        ]);

        $this->command->info('CurrentDataSeeder completed successfully!');
    }
}