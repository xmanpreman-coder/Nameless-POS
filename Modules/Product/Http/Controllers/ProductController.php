<?php

namespace Modules\Product\Http\Controllers;

use Modules\Product\DataTables\ProductDataTable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Upload\Entities\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Product\Entities\Category;

class ProductController extends Controller
{

    public function index(ProductDataTable $dataTable) {
        abort_if(Gate::denies('access_products'), 403);

        return $dataTable->render('product::products.index');
    }

    public function exportCsv(Request $request) {
        abort_if(Gate::denies('access_products'), 403);
        
        $products = Product::with('category')->get();
        
        $filename = 'products-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, ['Category', 'SKU', 'GTIN', 'Name', 'Cost', 'Price', 'Quantity', 'Unit']);
            
            // Data
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->category->category_name ?? '',
                    $product->product_sku ?? '',
                    $product->product_gtin ?? '',
                    $product->product_name,
                    number_format($product->product_cost / 100, 2, '.', ''),
                    number_format($product->product_price / 100, 2, '.', ''),
                    $product->product_quantity,
                    $product->product_unit,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }


    public function create() {
        abort_if(Gate::denies('create_products'), 403);

        $units = \Modules\Setting\Entities\Unit::all();
        $categories = Category::all();
        
        return view('product::products.create', compact('units', 'categories'));
    }


    public function store(StoreProductRequest $request) {
        $product = Product::create($request->except('document'));

        if ($request->has('document')) {
            foreach ($request->input('document', []) as $file) {
                $product->addMedia(Storage::path('temp/dropzone/' . $file))->toMediaCollection('images');
            }
        }

        toast('Product Created!', 'success');

        return redirect()->route('products.index');
    }


    public function show(Product $product) {
        abort_if(Gate::denies('show_products'), 403);

        return view('product::products.show', compact('product'));
    }


    public function edit(Product $product) {
        abort_if(Gate::denies('edit_products'), 403);

        $units = \Modules\Setting\Entities\Unit::all();
        $categories = Category::all();
        
        return view('product::products.edit', compact('product', 'units', 'categories'));
    }


    public function update(UpdateProductRequest $request, Product $product) {
        $product->update($request->except('document'));

        if ($request->has('document')) {
            if (count($product->getMedia('images')) > 0) {
                foreach ($product->getMedia('images') as $media) {
                    if (!in_array($media->file_name, $request->input('document', []))) {
                        $media->delete();
                    }
                }
            }

            $media = $product->getMedia('images')->pluck('file_name')->toArray();

            foreach ($request->input('document', []) as $file) {
                if (count($media) === 0 || !in_array($file, $media)) {
                    $product->addMedia(Storage::path('temp/dropzone/' . $file))->toMediaCollection('images');
                }
            }
        }

        toast('Product Updated!', 'info');

        return redirect()->route('products.index');
    }


    public function destroy(Product $product) {
        abort_if(Gate::denies('delete_products'), 403);

        $product->delete();

        toast('Product Deleted!', 'warning');

        return redirect()->route('products.index');
    }

    public function showImportForm() {
        abort_if(Gate::denies('edit_products'), 403);

        return view('product::products.import');
    }

    public function downloadTemplate()
    {
        abort_if(Gate::denies('access_products'), 403);

        $filename = 'product_template.csv';
        
        // Headers sesuai dengan format yang diperlukan sistem
        $headers = [
            'SKU',
            'GTIN',
            'Name',
            'Cost',
            'Price', 
            'Quantity',
            'Unit',
            'Category'
        ];

        // Sample data yang user-friendly
        $sampleData = [
            [
                'PRD001',
                '1234567890123',
                'Sample Product 1',
                '50000.00',
                '75000.00',
                '100',
                'pcs',
                'Electronics'
            ],
            [
                'PRD002',
                '1234567890124', 
                'Sample Product 2',
                '25000.00',
                '35000.00',
                '50',
                'pcs',
                'Books'
            ]
        ];

        // Create CSV content with UTF-8 BOM for Excel compatibility
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csvContent .= implode(',', $headers) . "\r\n";
        
        foreach ($sampleData as $row) {
            $csvContent .= implode(',', $row) . "\r\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    public function importCsv(Request $request) {
        abort_if(Gate::denies('edit_products'), 403);

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Read CSV file
        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Skip BOM if exists
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            
            // Read header
            $header = fgetcsv($handle);
            if ($header === false) {
                toast('CSV file is empty!', 'error');
                return redirect()->back();
            }
            
            // Normalize header (trim and lowercase)
            $header = array_map('trim', $header);
            $header = array_map('strtolower', $header);
            
            // Expected columns
            $expectedColumns = ['sku', 'gtin', 'name', 'cost', 'price', 'quantity', 'unit', 'category'];
            $columnMap = [];
            
            // Map columns
            foreach ($expectedColumns as $expected) {
                foreach ($header as $index => $col) {
                    if (strtolower(trim($col)) === $expected || 
                        strtolower(trim($col)) === str_replace('_', ' ', $expected)) {
                        $columnMap[$expected] = $index;
                        break;
                    }
                }
            }
            
            // Check if SKU or GTIN exists (required for update)
            if (!isset($columnMap['sku']) && !isset($columnMap['gtin'])) {
                toast('CSV must contain SKU or GTIN column for product identification!', 'error');
                return redirect()->back();
            }
            
            // Read data rows
            $rowNumber = 1;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Get identifier (SKU or GTIN)
                $identifier = null;
                $identifierType = null;
                
                if (isset($columnMap['sku']) && isset($row[$columnMap['sku']]) && !empty(trim($row[$columnMap['sku']]))) {
                    $identifier = trim($row[$columnMap['sku']]);
                    $identifierType = 'sku';
                } elseif (isset($columnMap['gtin']) && isset($row[$columnMap['gtin']]) && !empty(trim($row[$columnMap['gtin']]))) {
                    $identifier = trim($row[$columnMap['gtin']]);
                    $identifierType = 'gtin';
                } else {
                    $errors[] = "Row {$rowNumber}: SKU or GTIN is required";
                    $errorCount++;
                    continue;
                }
                
                // Find product by SKU or GTIN
                $product = null;
                if ($identifierType === 'sku') {
                    $product = Product::where('product_sku', $identifier)->first();
                } else {
                    $product = Product::where('product_gtin', $identifier)->first();
                }
                
                if (!$product) {
                    $errors[] = "Row {$rowNumber}: Product with {$identifierType} '{$identifier}' not found";
                    $errorCount++;
                    continue;
                }
                
                // Prepare update data
                $updateData = [];
                
                // Update name if provided
                if (isset($columnMap['name']) && isset($row[$columnMap['name']]) && !empty(trim($row[$columnMap['name']]))) {
                    $updateData['product_name'] = trim($row[$columnMap['name']]);
                }
                
                // Update cost if provided
                if (isset($columnMap['cost']) && isset($row[$columnMap['cost']]) && !empty(trim($row[$columnMap['cost']]))) {
                    $cost = str_replace(',', '', trim($row[$columnMap['cost']]));
                    if (is_numeric($cost)) {
                        $updateData['product_cost'] = $cost * 100; // Convert to cents
                    }
                }
                
                // Update price if provided
                if (isset($columnMap['price']) && isset($row[$columnMap['price']]) && !empty(trim($row[$columnMap['price']]))) {
                    $price = str_replace(',', '', trim($row[$columnMap['price']]));
                    if (is_numeric($price)) {
                        $updateData['product_price'] = $price * 100; // Convert to cents
                    }
                }
                
                // Update quantity if provided
                if (isset($columnMap['quantity']) && isset($row[$columnMap['quantity']]) && !empty(trim($row[$columnMap['quantity']]))) {
                    $quantity = str_replace(',', '', trim($row[$columnMap['quantity']]));
                    if (is_numeric($quantity)) {
                        $updateData['product_quantity'] = (int)$quantity;
                    }
                }
                
                // Update unit if provided
                if (isset($columnMap['unit']) && isset($row[$columnMap['unit']]) && !empty(trim($row[$columnMap['unit']]))) {
                    $updateData['product_unit'] = trim($row[$columnMap['unit']]);
                }
                
                // Update category if provided
                if (isset($columnMap['category']) && isset($row[$columnMap['category']]) && !empty(trim($row[$columnMap['category']]))) {
                    $categoryName = trim($row[$columnMap['category']]);
                    $category = Category::where('category_name', $categoryName)->first();
                    if ($category) {
                        $updateData['category_id'] = $category->id;
                    }
                }
                
                // Update GTIN if provided and different
                if (isset($columnMap['gtin']) && isset($row[$columnMap['gtin']]) && !empty(trim($row[$columnMap['gtin']]))) {
                    $gtin = trim($row[$columnMap['gtin']]);
                    if ($product->product_gtin !== $gtin) {
                        $updateData['product_gtin'] = $gtin;
                    }
                }
                
                // Update SKU if provided and different
                if (isset($columnMap['sku']) && isset($row[$columnMap['sku']]) && !empty(trim($row[$columnMap['sku']]))) {
                    $sku = trim($row[$columnMap['sku']]);
                    if ($product->product_sku !== $sku) {
                        // Check if new SKU is unique
                        $existingProduct = Product::where('product_sku', $sku)->where('id', '!=', $product->id)->first();
                        if (!$existingProduct) {
                            $updateData['product_sku'] = $sku;
                        }
                    }
                }
                
                // Update product if there's data to update
                if (!empty($updateData)) {
                    try {
                        $product->update($updateData);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Row {$rowNumber}: Error updating product - " . $e->getMessage();
                        $errorCount++;
                    }
                } else {
                    $errors[] = "Row {$rowNumber}: No valid data to update";
                    $errorCount++;
                }
            }
            
            fclose($handle);
        }
        
        // Show results
        if ($successCount > 0) {
            toast("Successfully updated {$successCount} product(s)!", 'success');
        }
        
        if ($errorCount > 0) {
            $errorMessage = "Failed to update {$errorCount} product(s). ";
            if (count($errors) > 0) {
                $errorMessage .= "Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= " and " . (count($errors) - 5) . " more...";
                }
            }
            toast($errorMessage, 'error');
        }
        
        return redirect()->route('products.index');
    }
}
