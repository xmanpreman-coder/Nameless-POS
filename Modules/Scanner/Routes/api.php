<?php

use Illuminate\Http\Request;
use Modules\Scanner\Http\Controllers\ExternalScannerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// External Scanner API Routes (for Barcode to PC, etc.)
Route::prefix('scanner')->name('scanner.external.')->group(function() {
    // Main barcode receiving endpoint
    Route::post('/scan', [ExternalScannerController::class, 'receiveBarcode'])->name('receive');
    
    // WebSocket scanning
    Route::post('/websocket-scan', [ExternalScannerController::class, 'websocketScan'])->name('websocket');
    
    // Batch scanning
    Route::post('/batch-scan', [ExternalScannerController::class, 'receiveBatch'])->name('batch');
    
    // Configuration endpoint
    Route::get('/config', [ExternalScannerController::class, 'getConfiguration'])->name('config');
    
    // Status check
    Route::get('/status', [ExternalScannerController::class, 'getStatus'])->name('status');
    
    // Alternative endpoints for different scanner apps
    Route::post('/barcode', [ExternalScannerController::class, 'receiveBarcode'])->name('barcode');
    Route::post('/receive', [ExternalScannerController::class, 'receiveBarcode'])->name('receive-alt');
});