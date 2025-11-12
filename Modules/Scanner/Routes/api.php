<?php

use Illuminate\Http\Request;
use Modules\Scanner\Http\Controllers\ScannerController;

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
    Route::post('/scan', [ScannerController::class, 'receiveExternalScan'])->name('receive');
    
    // Alternative endpoints for different scanner apps
    Route::post('/barcode', [ScannerController::class, 'receiveExternalScan'])->name('barcode');
    Route::post('/receive', [ScannerController::class, 'receiveExternalScan'])->name('receive-alt');
});