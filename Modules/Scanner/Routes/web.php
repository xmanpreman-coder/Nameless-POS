<?php

use Modules\Scanner\Http\Controllers\ScannerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function() {
    Route::prefix('scanner')->name('scanner.')->group(function() {
        Route::get('/', [ScannerController::class, 'index'])->name('index');
        Route::get('/scan', [ScannerController::class, 'scan'])->name('scan');
        Route::get('/settings', [ScannerController::class, 'settings'])->name('settings');
        Route::post('/settings', [ScannerController::class, 'updateSettings'])->name('settings.update');
        Route::get('/test-camera', [ScannerController::class, 'testCamera'])->name('test-camera');
        Route::get('/external-setup', function() {
            return view('scanner::external-setup');
        })->name('external-setup');
        Route::get('/barcode-to-pc-guide', function() {
            return view('scanner::barcode-to-pc-guide');
        })->name('barcode-to-pc-guide');
        Route::post('/search-product', [ScannerController::class, 'searchProduct'])->name('search-product');
    });
});

// External Scanner Routes (without auth for mobile apps)
Route::post('scanner/external/receive', [ScannerController::class, 'receiveExternalScan'])->name('scanner.external.mobile');
