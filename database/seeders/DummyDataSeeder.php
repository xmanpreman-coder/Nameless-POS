<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Currency\Entities\Currency;
use Modules\Expense\Entities\Expense;
use Modules\Expense\Entities\ExpenseCategory;
use Modules\People\Entities\Customer;
use Modules\People\Entities\Supplier;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Entities\PurchaseDetail;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Quotation\Entities\Quotation;
use Modules\Quotation\Entities\QuotationDetails;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Modules\Setting\Entities\Unit;
use Spatie\Permission\Models\Role;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating dummy data...');

        // Get default currency
        $currency = Currency::first();
        if (!$currency) {
            $this->command->warn('No currency found. Please run CurrencyDatabaseSeeder first.');
            return;
        }

        // Create Categories
        $this->command->info('Creating categories...');
        $categories = [
            ['code' => 'CAT001', 'name' => 'Electronics'],
            ['code' => 'CAT002', 'name' => 'Clothing'],
            ['code' => 'CAT003', 'name' => 'Food & Beverages'],
            ['code' => 'CAT004', 'name' => 'Books'],
            ['code' => 'CAT005', 'name' => 'Furniture'],
            ['code' => 'CAT006', 'name' => 'Sports'],
            ['code' => 'CAT007', 'name' => 'Beauty'],
            ['code' => 'CAT008', 'name' => 'Toys'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = Category::firstOrCreate(
                ['category_code' => $cat['code']],
                ['category_name' => $cat['name']]
            );
            $categoryIds[] = $category->id;
        }

        // Create Units
        $this->command->info('Creating units...');
        $units = [
            ['name' => 'Pcs', 'short_name' => 'pcs'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Kg', 'short_name' => 'kg'],
            ['name' => 'Liter', 'short_name' => 'lt'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Set', 'short_name' => 'set'],
            ['name' => 'Dozen', 'short_name' => 'dz'],
        ];
        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                ['short_name' => $unit['short_name']]
            );
        }

        // Create Products
        $this->command->info('Creating products...');
        $products = [
            ['name' => 'Laptop Dell XPS 15', 'code' => 'PRD001', 'cost' => 12000000, 'price' => 15000000, 'quantity' => 50, 'unit' => 'Pcs'],
            ['name' => 'Smartphone Samsung S21', 'code' => 'PRD002', 'cost' => 8000000, 'price' => 10000000, 'quantity' => 100, 'unit' => 'Pcs'],
            ['name' => 'T-Shirt Cotton', 'code' => 'PRD003', 'cost' => 50000, 'price' => 75000, 'quantity' => 200, 'unit' => 'Pcs'],
            ['name' => 'Jeans Denim', 'code' => 'PRD004', 'cost' => 150000, 'price' => 200000, 'quantity' => 150, 'unit' => 'Pcs'],
            ['name' => 'Coca Cola 1.5L', 'code' => 'PRD005', 'cost' => 8000, 'price' => 12000, 'quantity' => 500, 'unit' => 'Bottle'],
            ['name' => 'Bread White', 'code' => 'PRD006', 'cost' => 15000, 'price' => 20000, 'quantity' => 300, 'unit' => 'Loaf'],
            ['name' => 'Programming Book', 'code' => 'PRD007', 'cost' => 100000, 'price' => 150000, 'quantity' => 80, 'unit' => 'Pcs'],
            ['name' => 'Office Chair', 'code' => 'PRD008', 'cost' => 500000, 'price' => 750000, 'quantity' => 30, 'unit' => 'Pcs'],
            ['name' => 'Football', 'code' => 'PRD009', 'cost' => 200000, 'price' => 300000, 'quantity' => 60, 'unit' => 'Pcs'],
            ['name' => 'Lipstick Red', 'code' => 'PRD010', 'cost' => 75000, 'price' => 100000, 'quantity' => 120, 'unit' => 'Pcs'],
            ['name' => 'Action Figure', 'code' => 'PRD011', 'cost' => 100000, 'price' => 150000, 'quantity' => 90, 'unit' => 'Pcs'],
            ['name' => 'Tablet iPad', 'code' => 'PRD012', 'cost' => 6000000, 'price' => 8000000, 'quantity' => 40, 'unit' => 'Pcs'],
            ['name' => 'Headphones Sony', 'code' => 'PRD013', 'cost' => 500000, 'price' => 700000, 'quantity' => 70, 'unit' => 'Pcs'],
            ['name' => 'Running Shoes', 'code' => 'PRD014', 'cost' => 400000, 'price' => 600000, 'quantity' => 50, 'unit' => 'Pcs'],
            ['name' => 'Coffee Beans 1kg', 'code' => 'PRD015', 'cost' => 80000, 'price' => 120000, 'quantity' => 200, 'unit' => 'Kg'],
        ];

        $productIds = [];
        foreach ($products as $index => $prod) {
            // Generate GTIN for some products (simulate real barcode numbers)
            $gtin = null;
            if (rand(0, 1)) { // 50% chance to have GTIN
                $gtin = str_pad(rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT); // EAN-13 format
            }
            
            $product = Product::firstOrCreate(
                ['product_sku' => $prod['code']],
                [
                    'category_id' => $categoryIds[$index % count($categoryIds)],
                    'product_name' => $prod['name'],
                    'product_sku' => $prod['code'],
                    'product_gtin' => $gtin,
                    'product_cost' => $prod['cost'],
                    'product_price' => $prod['price'],
                    'product_quantity' => $prod['quantity'],
                    'product_unit' => $prod['unit'],
                    'product_stock_alert' => 10,
                    'product_order_tax' => 0,
                    'product_tax_type' => 1,
                ]
            );
            $productIds[] = $product->id;
        }

        // Create Customers
        $this->command->info('Creating customers...');
        $customers = [
            ['customer_name' => 'John Doe', 'customer_email' => 'john@example.com', 'customer_phone' => '081234567890', 'city' => 'Jakarta', 'country' => 'Indonesia', 'address' => 'Jl. Sudirman No. 123'],
            ['customer_name' => 'Jane Smith', 'customer_email' => 'jane@example.com', 'customer_phone' => '081234567891', 'city' => 'Bandung', 'country' => 'Indonesia', 'address' => 'Jl. Dago No. 456'],
            ['customer_name' => 'Bob Johnson', 'customer_email' => 'bob@example.com', 'customer_phone' => '081234567892', 'city' => 'Surabaya', 'country' => 'Indonesia', 'address' => 'Jl. Pemuda No. 789'],
            ['customer_name' => 'Alice Williams', 'customer_email' => 'alice@example.com', 'customer_phone' => '081234567893', 'city' => 'Yogyakarta', 'country' => 'Indonesia', 'address' => 'Jl. Malioboro No. 321'],
            ['customer_name' => 'Charlie Brown', 'customer_email' => 'charlie@example.com', 'customer_phone' => '081234567894', 'city' => 'Medan', 'country' => 'Indonesia', 'address' => 'Jl. Gatot Subroto No. 654'],
            ['customer_name' => 'Diana Prince', 'customer_email' => 'diana@example.com', 'customer_phone' => '081234567895', 'city' => 'Bali', 'country' => 'Indonesia', 'address' => 'Jl. Legian No. 987'],
            ['customer_name' => 'Edward Norton', 'customer_email' => 'edward@example.com', 'customer_phone' => '081234567896', 'city' => 'Semarang', 'country' => 'Indonesia', 'address' => 'Jl. Pandanaran No. 147'],
            ['customer_name' => 'Fiona Apple', 'customer_email' => 'fiona@example.com', 'customer_phone' => '081234567897', 'city' => 'Makassar', 'country' => 'Indonesia', 'address' => 'Jl. Ahmad Yani No. 258'],
        ];

        $customerIds = [];
        foreach ($customers as $cust) {
            $customer = Customer::firstOrCreate(
                ['customer_email' => $cust['customer_email']],
                $cust
            );
            $customerIds[] = $customer->id;
        }

        // Create Suppliers
        $this->command->info('Creating suppliers...');
        $suppliers = [
            ['supplier_name' => 'PT Supplier Elektronik', 'supplier_email' => 'supplier1@example.com', 'supplier_phone' => '02112345678', 'city' => 'Jakarta', 'country' => 'Indonesia', 'address' => 'Jl. Thamrin No. 100'],
            ['supplier_name' => 'CV Supplier Pakaian', 'supplier_email' => 'supplier2@example.com', 'supplier_phone' => '02212345678', 'city' => 'Bandung', 'country' => 'Indonesia', 'address' => 'Jl. Asia Afrika No. 200'],
            ['supplier_name' => 'PT Supplier Makanan', 'supplier_email' => 'supplier3@example.com', 'supplier_phone' => '03112345678', 'city' => 'Surabaya', 'country' => 'Indonesia', 'address' => 'Jl. Diponegoro No. 300'],
            ['supplier_name' => 'UD Supplier Buku', 'supplier_email' => 'supplier4@example.com', 'supplier_phone' => '027412345678', 'city' => 'Yogyakarta', 'country' => 'Indonesia', 'address' => 'Jl. Solo No. 400'],
            ['supplier_name' => 'PT Supplier Furniture', 'supplier_email' => 'supplier5@example.com', 'supplier_phone' => '06112345678', 'city' => 'Medan', 'country' => 'Indonesia', 'address' => 'Jl. Sisingamangaraja No. 500'],
        ];

        $supplierIds = [];
        foreach ($suppliers as $sup) {
            $supplier = Supplier::firstOrCreate(
                ['supplier_email' => $sup['supplier_email']],
                $sup
            );
            $supplierIds[] = $supplier->id;
        }

        // Create Expense Categories
        $this->command->info('Creating expense categories...');
        $expenseCategories = [
            ['category_name' => 'Office Supplies', 'category_description' => 'Office equipment and supplies'],
            ['category_name' => 'Utilities', 'category_description' => 'Electricity, water, internet bills'],
            ['category_name' => 'Transportation', 'category_description' => 'Fuel, parking, toll fees'],
            ['category_name' => 'Marketing', 'category_description' => 'Advertising and promotional expenses'],
            ['category_name' => 'Maintenance', 'category_description' => 'Equipment and facility maintenance'],
        ];

        $expenseCategoryIds = [];
        foreach ($expenseCategories as $ec) {
            $expenseCategory = ExpenseCategory::firstOrCreate(
                ['category_name' => $ec['category_name']],
                $ec
            );
            $expenseCategoryIds[] = $expenseCategory->id;
        }

        // Create Expenses
        $this->command->info('Creating expenses...');
        for ($i = 0; $i < 30; $i++) {
            Expense::create([
                'category_id' => $expenseCategoryIds[array_rand($expenseCategoryIds)],
                'date' => now()->subDays(rand(1, 90)),
                'reference' => 'EXP-' . strtoupper(Str::random(8)),
                'details' => 'Expense details ' . ($i + 1),
                'amount' => rand(50000, 500000) * 100, // in cents
            ]);
        }

        // Create Purchases
        $this->command->info('Creating purchases...');
        $purchaseIds = [];
        for ($i = 0; $i < 20; $i++) {
            $supplier = Supplier::find($supplierIds[array_rand($supplierIds)]);
            $purchaseDate = now()->subDays(rand(1, 60));
            
            // Select random products for purchase
            $numProducts = rand(2, 5);
            $selectedIndices = array_rand($productIds, min($numProducts, count($productIds)));
            if (!is_array($selectedIndices)) {
                $selectedIndices = [$selectedIndices];
            }
            $subtotal = 0;
            $purchaseDetails = [];

            foreach ($selectedIndices as $prodIndex) {
                $product = Product::find($productIds[$prodIndex]);
                $quantity = rand(5, 50);
                $unitPrice = $product->product_cost * 100; // in cents
                $discount = rand(0, 5000);
                $tax = (int)($unitPrice * 0.1); // 10% tax
                $lineTotal = ($unitPrice * $quantity) - $discount + $tax;
                
                $purchaseDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'sub_total' => $lineTotal,
                ];
                
                $subtotal += $lineTotal;
            }

            $discountAmount = rand(0, (int)($subtotal * 0.1));
            $taxAmount = (int)(($subtotal - $discountAmount) * 0.1);
            $shippingAmount = rand(0, 50000) * 100;
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
            $paidAmount = rand((int)($totalAmount * 0.5), $totalAmount);
            $dueAmount = $totalAmount - $paidAmount;

            $purchase = Purchase::create([
                'date' => $purchaseDate,
                'reference' => 'PR-' . strtoupper(Str::random(8)),
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->supplier_name,
                'tax_percentage' => 10,
                'tax_amount' => $taxAmount,
                'discount_percentage' => (int)(($discountAmount / $subtotal) * 100),
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => rand(0, 1) ? 'Completed' : 'Pending',
                'payment_status' => $dueAmount == 0 ? 'Paid' : ($paidAmount > 0 ? 'Partial' : 'Unpaid'),
                'payment_method' => ['Cash', 'Bank Transfer', 'Credit Card'][array_rand([0, 1, 2])],
                'note' => 'Purchase note ' . ($i + 1),
            ]);

            $purchaseIds[] = $purchase->id;

            // Create purchase details
            foreach ($purchaseDetails as $detail) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $detail['product']->id,
                    'product_name' => $detail['product']->product_name,
                    'product_sku' => $detail['product']->product_sku ?? '',
                    'quantity' => $detail['quantity'],
                    'price' => $detail['unit_price'],
                    'unit_price' => $detail['unit_price'],
                    'sub_total' => $detail['sub_total'],
                    'product_discount_amount' => $detail['discount'],
                    'product_discount_type' => 'fixed',
                    'product_tax_amount' => $detail['tax'],
                ]);

                // Update product quantity
                $detail['product']->increment('product_quantity', $detail['quantity']);
            }

            // Create purchase payment if paid
            if ($paidAmount > 0) {
                PurchasePayment::create([
                    'date' => $purchaseDate,
                    'reference' => 'PP-' . strtoupper(Str::random(8)),
                    'purchase_id' => $purchase->id,
                    'amount' => $paidAmount,
                    'payment_method' => $purchase->payment_method,
                ]);
            }
        }

        // Create Sales
        $this->command->info('Creating sales...');
        for ($i = 0; $i < 30; $i++) {
            $customer = Customer::find($customerIds[array_rand($customerIds)]);
            $saleDate = now()->subDays(rand(1, 30));
            
            // Select random products for sale
            $numProducts = rand(1, 4);
            $selectedIndices = array_rand($productIds, min($numProducts, count($productIds)));
            if (!is_array($selectedIndices)) {
                $selectedIndices = [$selectedIndices];
            }
            $subtotal = 0;
            $saleDetails = [];

            foreach ($selectedIndices as $prodIndex) {
                $product = Product::find($productIds[$prodIndex]);
                if ($product->product_quantity <= 0) continue;
                
                $quantity = rand(1, min(10, $product->product_quantity));
                $unitPrice = $product->product_price * 100; // in cents
                $discount = rand(0, 10000);
                $tax = (int)($unitPrice * 0.1); // 10% tax
                $lineTotal = ($unitPrice * $quantity) - $discount + $tax;
                
                $saleDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'sub_total' => $lineTotal,
                ];
                
                $subtotal += $lineTotal;
            }

            if (empty($saleDetails)) continue;

            $discountAmount = rand(0, (int)($subtotal * 0.15));
            $taxAmount = (int)(($subtotal - $discountAmount) * 0.1);
            $shippingAmount = rand(0, 30000) * 100;
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
            $paidAmount = rand((int)($totalAmount * 0.6), $totalAmount);
            $dueAmount = $totalAmount - $paidAmount;

            $sale = Sale::create([
                'date' => $saleDate,
                'reference' => 'SL-' . strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'tax_percentage' => 10,
                'tax_amount' => $taxAmount,
                'discount_percentage' => (int)(($discountAmount / $subtotal) * 100),
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => 'Completed',
                'payment_status' => $dueAmount == 0 ? 'Paid' : ($paidAmount > 0 ? 'Partial' : 'Unpaid'),
                'payment_method' => ['Cash', 'Bank Transfer', 'Credit Card'][array_rand([0, 1, 2])],
                'note' => 'Sale note ' . ($i + 1),
            ]);

            // Create sale details
            foreach ($saleDetails as $detail) {
                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'product_id' => $detail['product']->id,
                    'product_name' => $detail['product']->product_name,
                    'product_sku' => $detail['product']->product_sku ?? '',
                    'quantity' => $detail['quantity'],
                    'price' => $detail['unit_price'],
                    'unit_price' => $detail['unit_price'],
                    'sub_total' => $detail['sub_total'],
                    'product_discount_amount' => $detail['discount'],
                    'product_discount_type' => 'fixed',
                    'product_tax_amount' => $detail['tax'],
                ]);

                // Update product quantity
                $detail['product']->decrement('product_quantity', $detail['quantity']);
            }

            // Create sale payment if paid
            if ($paidAmount > 0) {
                SalePayment::create([
                    'date' => $saleDate,
                    'reference' => 'SP-' . strtoupper(Str::random(8)),
                    'sale_id' => $sale->id,
                    'amount' => $paidAmount,
                    'payment_method' => $sale->payment_method,
                ]);
            }
        }

        // Create Quotations
        $this->command->info('Creating quotations...');
        for ($i = 0; $i < 10; $i++) {
            $customer = Customer::find($customerIds[array_rand($customerIds)]);
            $quotationDate = now()->subDays(rand(1, 15));
            
            // Select random products
            $numProducts = rand(2, 4);
            $selectedIndices = array_rand($productIds, min($numProducts, count($productIds)));
            if (!is_array($selectedIndices)) {
                $selectedIndices = [$selectedIndices];
            }
            $subtotal = 0;
            $quotationDetails = [];

            foreach ($selectedIndices as $prodIndex) {
                $product = Product::find($productIds[$prodIndex]);
                $quantity = rand(1, 20);
                $unitPrice = $product->product_price * 100;
                $discount = rand(0, 5000);
                $tax = (int)($unitPrice * 0.1);
                $lineTotal = ($unitPrice * $quantity) - $discount + $tax;
                
                $quotationDetails[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'sub_total' => $lineTotal,
                ];
                
                $subtotal += $lineTotal;
            }

            $discountAmount = rand(0, (int)($subtotal * 0.1));
            $taxAmount = (int)(($subtotal - $discountAmount) * 0.1);
            $shippingAmount = rand(0, 20000) * 100;
            $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;

            $quotation = Quotation::create([
                'date' => $quotationDate,
                'reference' => 'QT-' . strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'tax_percentage' => 10,
                'tax_amount' => $taxAmount,
                'discount_percentage' => (int)(($discountAmount / $subtotal) * 100),
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'status' => ['Sent', 'Pending', 'Approved'][array_rand([0, 1, 2])],
                'note' => 'Quotation note ' . ($i + 1),
            ]);

            // Create quotation details
            foreach ($quotationDetails as $detail) {
                QuotationDetails::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $detail['product']->id,
                    'product_name' => $detail['product']->product_name,
                    'product_sku' => $detail['product']->product_sku ?? '',
                    'quantity' => $detail['quantity'],
                    'price' => $detail['unit_price'],
                    'unit_price' => $detail['unit_price'],
                    'sub_total' => $detail['sub_total'],
                    'product_discount_amount' => $detail['discount'],
                    'product_discount_type' => 'fixed',
                    'product_tax_amount' => $detail['tax'],
                ]);
            }
        }

        $this->command->info('Dummy data created successfully!');
        $this->command->info('Summary:');
        $this->command->info('- Categories: ' . Category::count());
        $this->command->info('- Products: ' . Product::count());
        $this->command->info('- Customers: ' . Customer::count());
        $this->command->info('- Suppliers: ' . Supplier::count());
        $this->command->info('- Expenses: ' . Expense::count());
        $this->command->info('- Purchases: ' . Purchase::count());
        $this->command->info('- Sales: ' . Sale::count());
        $this->command->info('- Quotations: ' . Quotation::count());
    }
}

