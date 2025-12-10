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
use Modules\Product\Entities\Brand;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    public function index(ProductDataTable $dataTable) {
        abort_if(Gate::denies('access_products'), 403);

        $categories = Category::all();
        $brands = Brand::all();

        return $dataTable->render('product::products.index_new', compact('categories', 'brands'));
    }

    /**
     * Display products with low stock (quantity <= stock_alert threshold)
     */
    public function stockAlert() {
        abort_if(Gate::denies('access_products'), 403);

        $low_stock_products = Product::with(['category', 'brand'])
            ->whereColumn('product_quantity', '<=', 'product_stock_alert')
            ->orderBy('product_quantity', 'asc')
            ->get();

        return view('product::products.stock-alert', compact('low_stock_products'));
    }

    public function exportCsv(Request $request) {
        abort_if(Gate::denies('access_products'), 403);
        
        $products = Product::with('category', 'brand')->get();
        
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
            fputcsv($file, ['Category', 'Brand', 'SKU', 'GTIN', 'Name', 'Cost', 'Price', 'Quantity', 'Unit']);
            
            // Data
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->category->category_name ?? '',
                    $product->brand->brand_name ?? '',
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
        $brands = Brand::all(); // Retrieve all brands
        
        return view('product::products.create', compact('units', 'categories', 'brands')); // Pass brands to the view
    }


    public function store(StoreProductRequest $request) {
        $product = Product::create($request->except(['document', 'images']));

        // Handle direct image upload (new method)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Add each uploaded file directly using its temporary path to avoid
                // re-reading the request input key (which can result in missing temp files).
                // Use the original client filename for storage clarity.
                try {
                    // Pass the UploadedFile instance directly to Spatie MediaLibrary.
                    // This avoids issues where temporary file paths may no longer exist
                    // when MediaLibrary attempts to read them.
                    $product->addMedia($image)
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                    Log::error('Product image upload failed: ' . $e->getMessage());
                }
            }
        }
        // Fallback: Handle dropzone uploads (old method)
        elseif ($request->has('document')) {
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
        $brands = Brand::all(); // Retrieve all brands
        
        return view('product::products.edit', compact('product', 'units', 'categories', 'brands')); // Pass brands to the view
    }


    public function update(UpdateProductRequest $request, Product $product) {
        $product->update($request->except(['document', 'images']));

        // Handle direct image upload (new method)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                try {
                    // Use UploadedFile instance directly to avoid missing temp file errors
                    $product->addMedia($image)
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                    Log::error('Product image upload failed (update): ' . $e->getMessage());
                }
            }
        }
        // Fallback: Handle dropzone uploads (old method)
        elseif ($request->has('document')) {
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

    /**
     * Delete media file from product
     */
    public function deleteMedia(Product $product, $mediaId) {
        abort_if(Gate::denies('edit_products'), 403);

        $media = $product->media()->where('id', $mediaId)->first();
        
        if ($media) {
            $media->delete();
            toast('Image Deleted!', 'success');
        }

        return back();
    }

    public function showImportForm() {
        abort_if(Gate::denies('edit_products'), 403);

        return view('product::products.import');
    }

    public function downloadTemplate()
    {
        abort_if(Gate::denies('access_products'), 403);

        // Check if enhanced template exists
        $enhancedTemplate = public_path('templates/product_import_template_enhanced.csv');
        
        if (file_exists($enhancedTemplate)) {
            return response()->download($enhancedTemplate, 'product_import_template_enhanced.csv');
        }

        // Generate template CSV content with proper encoding
        $filename = 'product_import_template.csv';
        
        // Create CSV content in memory
        $csvContent = "";
        
        // Add BOM for Excel compatibility
        $csvContent .= "\xEF\xBB\xBF";
        
        // Headers
        $headers = ['Category', 'Brand', 'SKU', 'GTIN', 'Name', 'Cost', 'Price', 'Quantity', 'Unit'];
        $csvContent .= implode(',', $headers) . "\r\n";
        
        // Sample data
        $sampleData = [
            ['Electronics', 'Samsung', 'PRD001', '1234567890123', 'Sample Product 1', '50000.00', '75000.00', '100', 'pcs'],
            ['Books', 'Gramedia', 'PRD002', '1234567890124', 'Sample Product 2', '25000.00', '35000.00', '50', 'pcs'],
        ];
        
        foreach ($sampleData as $row) {
            $csvContent .= implode(',', array_map(function($v) {
                return '"' . str_replace('"', '""', $v) . '"';
            }, $row)) . "\r\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($csvContent));
    }

    /**
     * Download Excel (.xlsx) template using maatwebsite/excel
     */
    public function downloadXlsxTemplate()
    {
        abort_if(Gate::denies('access_products'), 403);

        // Check if enhanced template exists
        $enhancedTemplate = public_path('templates/product_import_template_enhanced.xlsx');
        
        if (file_exists($enhancedTemplate)) {
            return response()->download($enhancedTemplate, 'product_import_template_enhanced.xlsx');
        }

        // Fallback to old template generation
        $filename = 'product_template.xlsx';

        $headers = [
            'SKU', 'GTIN', 'Name', 'Cost', 'Price', 'Quantity', 'Unit', 'Category', 'Stock_Alert', 'Note'
        ];

        $sampleData = [
            ['PRD001','1234567890123','Sample Product 1','50000.00','75000.00','100','pcs','Electronics','10','Sample note'],
            ['PRD002','1234567890124','Sample Product 2','25000.00','35000.00','50','pcs','Books','15','Another sample'],
        ];

        $export = new class($sampleData, $headers) implements FromArray, WithHeadings {
            private $data;
            private $headings;
            public function __construct($data, $headings) { $this->data = $data; $this->headings = $headings; }
            public function array(): array { return $this->data; }
            public function headings(): array { return $this->headings; }
        };

        return Excel::download($export, $filename);
    }

    /**
     * Download Excel template with only headers (no sample data)
     * Headers match exactly with database columns for bulk import
     */
    public function downloadHeaderOnlyTemplate()
    {
        abort_if(Gate::denies('access_products'), 403);

        $filename = 'product_header_template.xlsx';

        // Headers exactly matching database and DataTable columns
        $headers = [
            'Category',
            'Brand', 
            'SKU',
            'GTIN',
            'Name',
            'Cost',
            'Price',
            'Quantity',
            'Unit'
        ];

        // Empty data array - just headers
        $sampleData = [];

        $export = new class($sampleData, $headers) implements FromArray, WithHeadings {
            private $data;
            private $headings;
            public function __construct($data, $headings) { 
                $this->data = $data; 
                $this->headings = $headings; 
            }
            public function array(): array { return $this->data; }
            public function headings(): array { return $this->headings; }
        };

        return Excel::download($export, $filename);
    }

    public function importCsv(Request $request) {

        abort_if(Gate::denies('edit_products'), 403);

        Log::info('Import CSV request received.', ['mode' => $request->import_mode, 'file' => $request->file('csv_file')->getClientOriginalName()]);

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240', // Max 10MB
            'import_mode' => 'required|in:add_new,update_existing,both', // Allow add_new, update_existing, or both
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Read CSV file
        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Reset pointer and read first line to detect delimiter and BOM
            rewind($handle);
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                toast('CSV file is empty!', 'error');
                return redirect()->back();
            }

            // Remove UTF-8 BOM if present
            $firstLineClean = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            // Detect delimiter by counting occurrences (comma, semicolon, tab)
            $commaCount = substr_count($firstLineClean, ',');
            $semiCount = substr_count($firstLineClean, ';');
            $tabCount = substr_count($firstLineClean, "\t");

            if ($tabCount > $commaCount && $tabCount > $semiCount) {
                $delimiter = "\t";
            } elseif ($semiCount > $commaCount) {
                $delimiter = ';';
            } else {
                $delimiter = ',';
            }

            // Rewind and read header with detected delimiter
            rewind($handle);
            $header = fgetcsv($handle, 0, $delimiter);
            if ($header === false) {
                toast('CSV file is empty!', 'error');
                return redirect()->back();
            }
            
            // Normalize header (trim and lowercase)
            $header = array_map('trim', $header);
            $header = array_map('strtolower', $header);
            
            // Expected columns and common aliases
            $expectedColumns = ['sku', 'gtin', 'name', 'cost', 'price', 'quantity', 'unit', 'category', 'brand'];
            $aliases = [
                'sku' => ['sku', 'product_sku', 'product sku', 'product_code', 'product code', 'code', 'barcode'],
                'gtin' => ['gtin', 'product_gtin', 'product gtin', 'ean', 'upc'],
                'name' => ['name', 'product_name', 'product name'],
                'cost' => ['cost', 'product_cost', 'product cost'],
                'price' => ['price', 'product_price', 'product price'],
                'quantity' => ['quantity', 'qty', 'product_quantity', 'product quantity'],
                'unit' => ['unit', 'product_unit', 'product unit'],
                'category' => ['category', 'cat', 'category_name', 'category name'],
                'brand' => ['brand', 'brand_name', 'brand name'],
            ];

            $columnMap = [];

            // Map columns using aliases (case-insensitive)
            foreach ($aliases as $expected => $variants) {
                foreach ($header as $index => $col) {
                    $colNorm = strtolower(trim($col));
                    foreach ($variants as $v) {
                        if ($colNorm === strtolower($v)) {
                            $columnMap[$expected] = $index;
                            break 2;
                        }
                    }
                }
            }
            
            // Check if SKU or GTIN exists (required for update)
            if (!isset($columnMap['sku']) && !isset($columnMap['gtin'])) {
                toast('CSV must contain SKU or GTIN column for product identification!', 'error');
                return redirect()->back();
            }

            // Require Category column: categories must match existing registered categories
            if (!isset($columnMap['category'])) {
                toast('CSV must contain a "Category" column. Categories are required and must match registered categories.', 'error');
                return redirect()->back();
            }
            
            // Read data rows
            $rowNumber = 1;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
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
                    $product = Product::where('product_gtin', 'LIKE', $identifier)->first();
                }
                
                $importMode = $request->input('import_mode'); // Get import mode from request
                $isNewProduct = !$product;

                // Add logging for processing each product row
                Log::info("Row {$rowNumber}: Processing product with {$identifierType} '{$identifier}' in mode: {$importMode}");
                
                // If product not found in update_existing mode, add an error and skip
                if ($importMode === 'update_existing' && $isNewProduct) {
                    Log::info("Row {$rowNumber}: Product with {$identifierType} '{$identifier}' not found. Cannot add new products in this mode.");
                    $errors[] = "Row {$rowNumber}: Product with {$identifierType} '{$identifier}' not found. Cannot add new products in this mode.";
                    $errorCount++;
                    continue;
                }

                // If product not found but import mode is 'add_new' or 'both', create new product
                if ($isNewProduct && ($importMode === 'add_new' || $importMode === 'both')) {
                    // Initialize product with identifier and other defaults
                    $product = new Product();
                    if ($identifierType === 'sku') {
                        $product->product_sku = $identifier;
                    } else {
                        $product->product_gtin = $identifier;
                    }
                    // Set default values for required fields not in CSV
                    $product->product_barcode_symbology = 'C128'; // Default
                    $product->product_unit = 'pcs'; // Default
                    $product->product_quantity = 0; // Default
                    $product->product_cost = 0; // Default
                    $product->product_price = 0; // Default
                    $product->product_stock_alert = 10; // Default
                    $product->product_name = 'New Product ' . $identifier; // Placeholder name
                    Log::info("Row {$rowNumber}: Creating new product with {$identifierType} '{$identifier}'", ['product_data' => $product->toArray()]);
                }

                
                // Prepare product data
                $productData = [];
                
                // Process name (required for updates)
                if (isset($columnMap['name']) && isset($row[$columnMap['name']]) && !empty(trim($row[$columnMap['name']]))) {
                    $productData['product_name'] = trim($row[$columnMap['name']]);
                }
                
                // Process cost
                if (isset($columnMap['cost']) && isset($row[$columnMap['cost']]) && !empty(trim($row[$columnMap['cost']]))) {
                    $cost = str_replace(',', '', trim($row[$columnMap['cost']]));
                    if (is_numeric($cost)) {
                        $productData['product_cost'] = $cost * 100; // Convert to cents
                    }
                }
                // Add missing required fields for new products
                // These are not needed for updates unless specific columns are explicitly present in CSV
                
                // Process price
                if (isset($columnMap['price']) && isset($row[$columnMap['price']]) && !empty(trim($row[$columnMap['price']]))) {
                    $price = str_replace(',', '', trim($row[$columnMap['price']]));
                    if (is_numeric($price)) {
                        $productData['product_price'] = $price * 100; // Convert to cents
                    }
                }
                
                // Process quantity
                if (isset($columnMap['quantity']) && isset($row[$columnMap['quantity']]) && !empty(trim($row[$columnMap['quantity']]))) {
                    $quantity = str_replace(',', '', trim($row[$columnMap['quantity']]));
                    if (is_numeric($quantity)) {
                        $productData['product_quantity'] = (int)$quantity;
                    }
                }
                
                // Process unit
                if (isset($columnMap['unit']) && isset($row[$columnMap['unit']]) && !empty(trim($row[$columnMap['unit']]))) {
                    $productData['product_unit'] = trim($row[$columnMap['unit']]);
                }
                
                // Process category - must match an existing category. Try several matching strategies (id, code, exact name, partial name).
                if (isset($columnMap['category']) && isset($row[$columnMap['category']]) && !empty(trim($row[$columnMap['category']]))) {
                    $categoryInput = trim($row[$columnMap['category']]);
                    $category = null;

                    // If numeric, try by ID first
                    if (is_numeric($categoryInput)) {
                        $category = Category::find((int)$categoryInput);
                    }

                    // Try by category code
                    if (!$category) {
                        $category = Category::where('category_code', $categoryInput)->first();
                    }

                    // Try exact case-insensitive name match
                    if (!$category) {
                        $category = Category::whereRaw('LOWER(category_name) = ?', [strtolower($categoryInput)])->first();
                    }

                    // Try partial match (last resort)
                    if (!$category) {
                        $category = Category::whereRaw('LOWER(category_name) LIKE ?', ['%' . strtolower($categoryInput) . '%'])->first();
                    }

                    if (!$category) {
                        // Auto-create missing category (user requested auto-create)
                        $newCategoryData = ['category_name' => $categoryInput];
                        if (Schema::hasColumn('categories', 'category_code')) {
                            // Generate a reasonably unique code: CA_<timestamp><3rand>
                            $newCategoryData['category_code'] = 'CA_' . date('YmdHis') . strtoupper(Str::random(3));
                        }

                        try {
                            $category = Category::create($newCategoryData);
                            Log::info("Row {$rowNumber}: Created new category '{$category->category_name}' (id: {$category->id}).");
                        } catch (\Exception $e) {
                            $errors[] = "Row {$rowNumber}: Category '{$categoryInput}' could not be created. Product not processed.";
                            $errorCount++;
                            Log::error("Row {$rowNumber}: Failed to create category '{$categoryInput}' - " . $e->getMessage());
                            continue;
                        }
                    }

                    $productData['category_id'] = $category->id;
                } else {
                    $errors[] = "Row {$rowNumber}: Category is required. Product not processed.";
                    $errorCount++;
                    continue;
                }

                // Process brand (optional). If brand column exists and value provided, try match existing brand case-insensitive.
                if (isset($columnMap['brand']) && isset($row[$columnMap['brand']]) && !empty(trim($row[$columnMap['brand']]))) {
                    $brandName = trim($row[$columnMap['brand']]);
                    $brand = Brand::whereRaw('LOWER(brand_name) = ?', [strtolower($brandName)])->first();
                    if ($brand) {
                        $productData['brand_id'] = $brand->id;
                    } else {
                        // Brand is optional — do not create brand automatically. Leave brand_id null and log.
                        Log::info("Row {$rowNumber}: Brand '{$brandName}' not found; leaving brand empty for product.");
                    }
                }
                
                // Process GTIN
                if (isset($columnMap['gtin']) && isset($row[$columnMap['gtin']]) && !empty(trim($row[$columnMap['gtin']]))) {
                    $gtin = trim($row[$columnMap['gtin']]);
                    if ($product->product_gtin !== $gtin) {
                        $productData['product_gtin'] = $gtin;
                    }
                }
                
                // Process SKU
                if (isset($columnMap['sku']) && isset($row[$columnMap['sku']]) && !empty(trim($row[$columnMap['sku']]))) {
                    $sku = trim($row[$columnMap['sku']]);
                    if ($product->product_sku !== $sku) {
                        // Check if SKU is unique (only if changing SKU for existing product)
                        $existingProduct = Product::where('product_sku', $sku)->where('id', '!=', $product->id)->first();
                        
                        if (!$existingProduct) {
                            $productData['product_sku'] = $sku;
                        } else {
                            $errors[] = "Row {$rowNumber}: SKU '{$sku}' already exists for another product. Product not updated.";
                            $errorCount++;
                            continue;
                        }
                    }
                }
                
                // Apply data to product model
                foreach ($productData as $key => $value) {
                    $product->$key = $value;
                }

                // Defensive check: if DB requires category_id and we don't have it, skip before hitting DB constraint
                if (Schema::hasColumn('products', 'category_id')) {
                    $hasCategoryOnModel = isset($productData['category_id']) && !empty($productData['category_id']);
                    $existingCategoryOnModel = isset($product->category_id) && !empty($product->category_id);
                    if (!$hasCategoryOnModel && !$existingCategoryOnModel) {
                        $errors[] = "Row {$rowNumber}: Missing valid category_id before save. Product not processed.";
                        $errorCount++;
                        Log::info("Row {$rowNumber}: Missing category_id, skipping to avoid DB constraint violation.");
                        continue;
                    }
                }

                try {
                    if ($isNewProduct) {
                        $product->save(); // Save new product
                        Log::info("Row {$rowNumber}: Successfully created new product with ID: {$product->id}", ['product_data' => $product->toArray()]);
                    } else {
                        $product->update($productData); // Update existing product
                        Log::info("Row {$rowNumber}: Successfully updated product with ID: {$product->id}", ['product_data' => $productData]);
                    }
                    $successCount++;
                } catch (QueryException $qe) {
                    $msg = $qe->getMessage();
                    Log::error("Row {$rowNumber}: DB error processing product - " . $msg, ['product_data' => $product->toArray()]);
                    $errors[] = "Row {$rowNumber}: Database error processing product. Check category and required fields.";
                    $errorCount++;
                } catch (\Exception $e) {
                    Log::error("Row {$rowNumber}: Error processing product - " . $e->getMessage(), ['product_data' => $product->toArray()]);
                    $errors[] = "Row {$rowNumber}: Error processing product - " . $e->getMessage();
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
