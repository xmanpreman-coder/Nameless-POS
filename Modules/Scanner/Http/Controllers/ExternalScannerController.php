<?php

namespace Modules\Scanner\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Product;
use Modules\Scanner\Entities\ScannerSetting;

class ExternalScannerController extends Controller
{
    /**
     * Handle barcode input from external scanners
     * Compatible with Barcode to PC and similar apps
     */
    public function receiveBarcode(Request $request)
    {
        $barcode = $request->input('barcode') ?: $request->input('text') ?: $request->input('data');
        
        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'No barcode data received'
            ], 400);
        }

        // Clean and validate barcode
        $barcode = trim($barcode);
        
        if (strlen($barcode) < 4) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid barcode format'
            ], 400);
        }

        // Search for product
        $searchResult = $this->searchProductWithDetails($barcode);

        if ($searchResult['product']) {
            $product = $searchResult['product'];
            
            // Log successful scan
            $this->logScan($barcode, $product->id, true);

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
                    'code' => $product->product_sku,
                    'barcode' => $product->product_barcode_symbology,
                    'price' => $product->product_price,
                    'stock' => $product->product_quantity,
                    'image' => $product->product_image ? asset('storage/' . $product->product_image) : null,
                    'category' => $product->category ? $product->category->category_name : null
                ],
                'timestamp' => now()->toISOString()
            ]);
        } else {
            // Log failed scan
            $this->logScan($barcode, null, false);

            return response()->json([
                'success' => false,
                'message' => 'Product not found for barcode: ' . $barcode,
                'barcode' => $barcode,
                'timestamp' => now()->toISOString()
            ], 404);
        }
    }

    /**
     * WebSocket endpoint for real-time scanning
     */
    public function websocketScan(Request $request)
    {
        $barcode = $request->input('barcode');
        
        if (!$barcode) {
            return response()->json(['error' => 'No barcode provided'], 400);
        }

        $product = $this->searchProduct($barcode);

        // Broadcast to all connected clients
        broadcast(new \Modules\Scanner\Events\BarcodeScanned($barcode, $product))->toOthers();

        return response()->json([
            'success' => $product ? true : false,
            'barcode' => $barcode,
            'product' => $product
        ]);
    }

    /**
     * Get scanner configuration for external apps
     */
    public function getConfiguration()
    {
        $settings = ScannerSetting::getSettings();
        
        return response()->json([
            'endpoints' => [
                'scan' => route('scanner.external.receive'),
                'websocket' => route('scanner.external.websocket'),
                'status' => route('scanner.external.status')
            ],
            'settings' => [
                'beep_sound' => $settings->beep_sound,
                'vibration' => $settings->vibration,
                'auto_add' => true,
                'scan_mode' => $settings->scan_mode
            ],
            'supported_formats' => [
                'barcode', 'text', 'data', 'qr_code'
            ],
            'api_version' => '1.0'
        ]);
    }

    /**
     * Get scanner status
     */
    public function getStatus()
    {
        return response()->json([
            'status' => 'active',
            'timestamp' => now()->toISOString(),
            'version' => '1.0'
        ]);
    }

    /**
     * Handle multiple barcode scan (batch processing)
     */
    public function receiveBatch(Request $request)
    {
        $barcodes = $request->input('barcodes', []);
        
        if (empty($barcodes)) {
            return response()->json([
                'success' => false,
                'message' => 'No barcodes provided'
            ], 400);
        }

        $results = [];
        
        foreach ($barcodes as $barcode) {
            $product = $this->searchProduct($barcode);
            $results[] = [
                'barcode' => $barcode,
                'success' => $product ? true : false,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'code' => $product->product_sku,
                    'price' => $product->product_price,
                    'stock' => $product->product_quantity
                ] : null
            ];
            
            $this->logScan($barcode, $product ? $product->id : null, $product ? true : false);
        }

        return response()->json([
            'success' => true,
            'total_scanned' => count($barcodes),
            'found' => count(array_filter($results, fn($r) => $r['success'])),
            'not_found' => count(array_filter($results, fn($r) => !$r['success'])),
            'results' => $results
        ]);
    }

    /**
     * Search for product by barcode with detailed information
     */
    private function searchProductWithDetails($barcode)
    {
        // First try exact match
        $product = Product::where('product_barcode_symbology', $barcode)
             ->orWhere('product_sku', $barcode)
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
     * Search for product by barcode (legacy method for backward compatibility)
     */
    private function searchProduct($barcode)
    {
        $result = $this->searchProductWithDetails($barcode);
        return $result['product'];
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
                             ->orWhere('product_sku', $fullBarcode)
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

    /**
     * Search for product by trying common first digits (legacy method)
     */
    private function searchWithPossibleMissingDigit($barcode)
    {
        $result = $this->searchWithPossibleMissingDigitDetails($barcode);
        return $result ? $result['product'] : null;
    }

    /**
     * Log scan activity
     */
    private function logScan($barcode, $productId, $success)
    {
        // Store in session for recent scans
        $recentScans = session('recent_scans', []);
        
        $scanData = [
            'barcode' => $barcode,
            'product_id' => $productId,
            'success' => $success,
            'timestamp' => now()->toISOString(),
            'source' => 'external_scanner'
        ];

        array_unshift($recentScans, $scanData);
        $recentScans = array_slice($recentScans, 0, 20); // Keep last 20 scans

        session(['recent_scans' => $recentScans]);
    }
}