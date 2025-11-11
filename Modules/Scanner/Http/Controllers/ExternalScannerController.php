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
        $product = $this->searchProduct($barcode);

        if ($product) {
            // Log successful scan
            $this->logScan($barcode, $product->id, true);

            return response()->json([
                'success' => true,
                'message' => 'Product found',
                'barcode' => $barcode,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'code' => $product->product_code,
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
                    'code' => $product->product_code,
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
     * Search for product by barcode
     */
    private function searchProduct($barcode)
    {
        return Product::where('product_barcode_symbology', $barcode)
                     ->orWhere('product_code', $barcode)
                     ->orWhere('product_gtin', $barcode)
                     ->first();
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