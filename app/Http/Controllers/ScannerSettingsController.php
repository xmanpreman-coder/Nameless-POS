<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScannerSettingsController extends Controller
{
    /**
     * Display scanner settings page
     */
    public function index()
    {
        // Get current server info for display
        $serverInfo = [
            'url' => request()->getSchemeAndHttpHost(),
            'port' => request()->getPort() ?: (request()->isSecure() ? '443' : '80'),
            'is_secure' => request()->isSecure(),
            'api_endpoint' => route('scanner.external.receive'),
            'local_ip' => $this->getLocalIPAddress(),
        ];

        // Log access to settings page
        Log::info('Scanner settings page accessed', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'server_info' => $serverInfo
        ]);

        return view('scanner-settings.index', compact('serverInfo'));
    }

    /**
     * Test scanner connection
     */
    public function testConnection(Request $request)
    {
        $barcode = $request->input('barcode', 'TEST_CONNECTION');
        $source = $request->input('source', 'settings_test');

        try {
            // Simulate the same process as external scanner
            $controller = new \Modules\Scanner\Http\Controllers\ExternalScannerController();
            $testRequest = new Request();
            $testRequest->merge([
                'barcode' => $barcode,
                'source' => $source
            ]);

            $response = $controller->receiveBarcode($testRequest);
            $responseData = json_decode($response->getContent(), true);

            // Log test result
            Log::info('Scanner connection test performed', [
                'user_id' => auth()->id(),
                'test_barcode' => $barcode,
                'result' => $responseData['success'] ? 'success' : 'failed',
                'message' => $responseData['message'] ?? 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connection test completed',
                'test_result' => $responseData,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner connection test failed', [
                'user_id' => auth()->id(),
                'test_barcode' => $barcode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Get network configuration info
     */
    public function getNetworkInfo()
    {
        $networkInfo = [
            'server_url' => request()->getSchemeAndHttpHost(),
            'server_port' => request()->getPort() ?: (request()->isSecure() ? '443' : '80'),
            'local_ip' => $this->getLocalIPAddress(),
            'is_https' => request()->isSecure(),
            'api_endpoints' => [
                'scan' => route('scanner.external.receive'),
                'search' => route('scanner.search-product'),
                'settings' => route('scanner.settings')
            ],
            'supported_methods' => ['POST'],
            'supported_formats' => ['application/x-www-form-urlencoded', 'application/json'],
            'required_parameters' => ['barcode'],
            'optional_parameters' => ['source', 'timestamp']
        ];

        return response()->json($networkInfo);
    }

    /**
     * Generate QR code configuration data
     */
    public function getQRConfig()
    {
        $config = [
            'type' => 'scanner_config',
            'version' => '1.0',
            'server' => [
                'url' => request()->getSchemeAndHttpHost(),
                'port' => request()->getPort() ?: (request()->isSecure() ? '443' : '80'),
                'secure' => request()->isSecure()
            ],
            'endpoints' => [
                'scan' => '/api/scanner/scan',
                'search' => '/scanner/search-product'
            ],
            'settings' => [
                'method' => 'POST',
                'parameter_name' => 'barcode',
                'content_type' => 'application/x-www-form-urlencoded',
                'timeout' => 10000,
                'retry_count' => 3
            ],
            'features' => [
                'barcode_reconstruction' => true,
                'missing_digit_recovery' => true,
                'multiple_formats_support' => true,
                'real_time_feedback' => true
            ],
            'generated_at' => now()->toISOString(),
            'generated_by' => 'POS Scanner Settings'
        ];

        return response()->json($config);
    }

    /**
     * Get local IP address for network configuration
     */
    private function getLocalIPAddress()
    {
        // Try to get the server's local IP address
        $localIP = null;

        try {
            // Method 1: Check $_SERVER variables
            $possibleKeys = [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
                'SERVER_ADDR'
            ];

            foreach ($possibleKeys as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (explode(',', $_SERVER[$key]) as $ip) {
                        $ip = trim($ip);
                        if (filter_var($ip, FILTER_VALIDATE_IP, 
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            $localIP = $ip;
                            break 2;
                        }
                    }
                }
            }

            // Method 2: Use hostname if no IP found
            if (!$localIP) {
                $localIP = gethostbyname(gethostname());
            }

            // Method 3: Fallback to localhost
            if (!$localIP || $localIP === gethostname()) {
                $localIP = '127.0.0.1';
            }

        } catch (\Exception $e) {
            Log::warning('Could not determine local IP address', ['error' => $e->getMessage()]);
            $localIP = '127.0.0.1';
        }

        return $localIP;
    }

    /**
     * Export scanner configuration as downloadable file
     */
    public function exportConfig()
    {
        $config = [
            'scanner_config' => [
                'server_url' => request()->getSchemeAndHttpHost(),
                'api_endpoint' => route('scanner.external.receive'),
                'method' => 'POST',
                'parameter_name' => 'barcode',
                'content_type' => 'application/x-www-form-urlencoded'
            ],
            'network_info' => [
                'port' => request()->getPort() ?: (request()->isSecure() ? '443' : '80'),
                'secure' => request()->isSecure(),
                'local_ip' => $this->getLocalIPAddress()
            ],
            'features' => [
                'barcode_reconstruction' => true,
                'missing_digit_recovery' => true,
                'retry_mechanism' => true,
                'fallback_support' => true
            ],
            'instructions' => [
                'step_1' => 'Install barcode scanner app on mobile device',
                'step_2' => 'Configure HTTP POST settings with provided URL',
                'step_3' => 'Set parameter name to "barcode"',
                'step_4' => 'Test connection using provided endpoint',
                'step_5' => 'Start scanning barcodes'
            ],
            'exported_at' => now()->toISOString(),
            'exported_by' => auth()->user()->name
        ];

        $filename = 'scanner_config_' . date('Y-m-d_H-i-s') . '.json';

        Log::info('Scanner configuration exported', [
            'user_id' => auth()->id(),
            'filename' => $filename,
            'config' => $config
        ]);

        return response()->json($config)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }
}