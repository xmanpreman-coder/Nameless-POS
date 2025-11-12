<?php

namespace Modules\Scanner\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Scanner\Entities\ScannerSetting;
use Modules\Product\Entities\Product;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner::index');
    }

    public function settings()
    {
        $settings = ScannerSetting::getSettings();
        return view('scanner::settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        // Pre-process boolean fields before validation
        $request->merge([
            'beep_sound' => $request->has('beep_sound'),
            'vibration' => $request->has('vibration'),
            'auto_focus' => $request->has('auto_focus'),
        ]);

        // Define validation rules
        $rules = [
            'scanner_type' => 'required|in:camera,usb,bluetooth,external',
        ];
        
        // Only require camera-specific fields if not external scanner
        if ($request->scanner_type !== 'external') {
            $rules += [
                'beep_sound' => 'boolean',
                'vibration' => 'boolean',
                'scan_mode' => 'required|in:auto,manual',
                'scan_timeout' => 'required|integer|min:5|max:120',
                'auto_focus' => 'boolean',
                'preferred_camera' => 'required|in:back,front',
            ];
        }
        
        $request->validate($rules);

        $settings = ScannerSetting::getSettings();
        
        // Prepare data for update
        $updateData = $request->only(['scanner_type']);
        
        if ($request->scanner_type !== 'external') {
            $updateData += $request->only([
                'beep_sound',
                'vibration',
                'scan_mode',
                'scan_timeout',
                'auto_focus',
                'preferred_camera'
            ]);
        }
        
        // Always update settings fields, even if empty
        $updateData['camera_settings'] = $request->camera_settings ?? [];
        $updateData['usb_settings'] = $request->usb_settings ?? [];
        $updateData['bluetooth_settings'] = $request->bluetooth_settings ?? [];
        $updateData['external_settings'] = $request->external_settings ?? [];

        $settings->update($updateData);

        return redirect()->back()->with('success', 'Scanner settings updated successfully!');
    }

    public function scan()
    {
        $settings = ScannerSetting::getSettings();
        return view('scanner::scan', compact('settings'));
    }

    public function searchProduct(Request $request)
    {
        $barcode = $request->barcode;
        
        // Search for product with barcode reconstruction support
        $searchResult = $this->searchProductWithDetails($barcode);
        
        if ($searchResult['product']) {
            $product = $searchResult['product'];
            
            return response()->json([
                'success' => true,
                'message' => $searchResult['reconstructed'] ? 
                    'Product found (barcode reconstructed: ' . $searchResult['actual_barcode'] . ')' : 
                    'Product found',
                'barcode' => $barcode,
                'actual_barcode' => $searchResult['actual_barcode'],
                'reconstructed' => $searchResult['reconstructed'],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'code' => $product->product_code,
                    'barcode' => $product->product_barcode_symbology,
                    'price' => $product->product_price,
                    'stock' => $product->product_quantity,
                    'image' => $product->product_image ? asset('storage/' . $product->product_image) : null
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found for barcode: ' . $barcode,
            'barcode' => $barcode,
            'actual_barcode' => $barcode,
            'reconstructed' => false
        ]);
    }

    /**
     * Search for product with barcode reconstruction support
     */
    private function searchProductWithDetails($barcode)
    {
        // First try exact match
        $product = Product::where('product_barcode_symbology', $barcode)
                         ->orWhere('product_code', $barcode)
                         ->orWhere('product_gtin', $barcode)
                         ->first();

        if ($product) {
            return [
                'product' => $product,
                'actual_barcode' => $barcode,
                'reconstructed' => false
            ];
        }

        // If not found and barcode looks like it might be missing first digit
        if ($this->mightBeMissingFirstDigit($barcode)) {
            $result = $this->searchWithPossibleMissingDigitDetails($barcode);
            if ($result) {
                return $result;
            }
        }

        return [
            'product' => null,
            'actual_barcode' => $barcode,
            'reconstructed' => false
        ];
    }

    /**
     * Check if barcode might be missing first digit
     */
    private function mightBeMissingFirstDigit($barcode)
    {
        // Check for common patterns where first digit might be missing
        // EAN-13 becomes 12 digits, EAN-8 becomes 7 digits, UPC-A becomes 11 digits
        $length = strlen($barcode);
        
        return in_array($length, [7, 11, 12]) && is_numeric($barcode);
    }

    /**
     * Search for product by trying common first digits with details
     */
    private function searchWithPossibleMissingDigitDetails($barcode)
    {
        // Common first digits for Indonesian products (8 is most common for EAN-13)
        $commonFirstDigits = ['8', '9', '0', '1', '2', '3', '4', '5', '6', '7'];
        
        foreach ($commonFirstDigits as $digit) {
            $fullBarcode = $digit . $barcode;
            
            $product = Product::where('product_barcode_symbology', $fullBarcode)
                             ->orWhere('product_code', $fullBarcode)
                             ->orWhere('product_gtin', $fullBarcode)
                             ->first();
            
            if ($product) {
                // Log this for debugging
                \Log::info("Scanner: Found product with reconstructed barcode", [
                    'original_scan' => $barcode,
                    'reconstructed' => $fullBarcode,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name
                ]);
                
                return [
                    'product' => $product,
                    'actual_barcode' => $fullBarcode,
                    'reconstructed' => true
                ];
            }
        }
        
        return null;
    }

    public function testCamera()
    {
        return view('scanner::test-camera');
    }

    /**
     * Handle external scanner input from mobile apps
     */
    public function receiveExternalScan(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'barcode' => 'required|string|max:255'
            ]);

            $barcode = $request->input('barcode');
            $source = $request->input('source', 'external_app');

            // Log the external scan for debugging
            \Log::info("External scanner input received", [
                'barcode' => $barcode,
                'source' => $source,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            // Handle test connection requests
            if ($barcode === 'TEST_EXTERNAL_CONNECTION' && $source === 'external_settings') {
                return response()->json([
                    'success' => true,
                    'message' => 'External scanner connection test successful',
                    'timestamp' => now()->toISOString()
                ]);
            }

            // Search for the product
            $searchResult = $this->searchProductWithDetails($barcode);

            if ($searchResult['product']) {
                $product = $searchResult['product'];
                
                // Return product data in format expected by external-scanner.js
                return response()->json([
                    'success' => true,
                    'message' => $searchResult['reconstructed'] ? 
                        'Product found (barcode reconstructed)' : 
                        'Product found',
                    'barcode' => $barcode,
                    'actual_barcode' => $searchResult['actual_barcode'],
                    'reconstructed' => $searchResult['reconstructed'],
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'barcode' => $product->product_barcode_symbology,
                        'price' => $product->product_price,
                        'stock' => $product->product_quantity,
                        'image' => $product->product_image ? asset('storage/' . $product->product_image) : null,
                        'category' => $product->category ? $product->category->category_name : null
                    ]
                ]);
            }

            // Product not found
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'barcode' => $barcode,
                'suggestions' => $this->getSearchSuggestions($barcode)
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error("External scanner error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Get search suggestions for failed barcode lookups
     */
    private function getSearchSuggestions($barcode)
    {
        $suggestions = [];

        // Try partial matches
        $partialMatches = Product::where('product_barcode_symbology', 'LIKE', "%{$barcode}%")
                                ->orWhere('product_code', 'LIKE', "%{$barcode}%")
                                ->orWhere('product_name', 'LIKE', "%{$barcode}%")
                                ->limit(5)
                                ->get(['id', 'product_name', 'product_code', 'product_barcode_symbology']);

        foreach ($partialMatches as $product) {
            $suggestions[] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'barcode' => $product->product_barcode_symbology,
                'match_type' => 'partial'
            ];
        }

        return $suggestions;
    }
}