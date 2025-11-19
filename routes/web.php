<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

// Printer Settings Routes (Multi-Printer Management)
Route::middleware(['auth'])->group(function () {
    Route::get('/printer-settings', [App\Http\Controllers\PrinterSettingController::class, 'index'])->name('printer-settings.index');
    Route::patch('/printer-settings', [App\Http\Controllers\PrinterSettingController::class, 'update'])->name('printer-settings.update');
    
    // Multi-Printer CRUD Routes
    Route::get('/printer-settings/create', [App\Http\Controllers\PrinterSettingController::class, 'create'])->name('printer-settings.create');
    Route::post('/printer-settings', [App\Http\Controllers\PrinterSettingController::class, 'store'])->name('printer-settings.store');
    Route::get('/printer-settings/{thermalPrinterSetting}/test', [App\Http\Controllers\PrinterSettingController::class, 'testConnection'])->name('printer-settings.test');
    Route::post('/printer-settings/{thermalPrinterSetting}/default', [App\Http\Controllers\PrinterSettingController::class, 'setDefault'])->name('printer-settings.default');
    Route::delete('/printer-settings/{thermalPrinterSetting}', [App\Http\Controllers\PrinterSettingController::class, 'deletePrinter'])->name('printer-settings.destroy');
    Route::post('/printer-preferences', [App\Http\Controllers\PrinterSettingController::class, 'savePreference'])->name('printer-preferences.save');
});

// User Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\UserProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
});

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', 'HomeController@index')
        ->name('home');

    Route::get('/sales-purchases/chart-data', 'HomeController@salesPurchasesChart')
        ->name('sales-purchases.chart');

    Route::get('/current-month/chart-data', 'HomeController@currentMonthChart')
        ->name('current-month.chart');

    Route::get('/payment-flow/chart-data', 'HomeController@paymentChart')
        ->name('payment-flow.chart');

    // Scanner Settings Routes
    Route::get('/scanner-settings', 'ScannerSettingsController@index')->name('scanner-settings.index');
    Route::get('/scanner-settings', 'ScannerSettingsController@index')->name('scanner.settings');
    Route::post('/scanner-settings/test', 'ScannerSettingsController@testConnection')->name('scanner.settings.test');
    Route::get('/scanner-settings/network-info', 'ScannerSettingsController@getNetworkInfo')->name('scanner.settings.network');
    Route::get('/scanner-settings/qr-config', 'ScannerSettingsController@getQRConfig')->name('scanner.settings.qr');
    Route::get('/scanner-settings/export', 'ScannerSettingsController@exportConfig')->name('scanner.settings.export');

    // Thermal Printer Settings Routes
    Route::prefix('thermal-printer')->name('thermal-printer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ThermalPrinterController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ThermalPrinterController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ThermalPrinterController::class, 'store'])->name('store');
        Route::get('/{thermalPrinter}', [\App\Http\Controllers\ThermalPrinterController::class, 'show'])->name('show');
        Route::get('/{thermalPrinter}/edit', [\App\Http\Controllers\ThermalPrinterController::class, 'edit'])->name('edit');
        Route::put('/{thermalPrinter}', [\App\Http\Controllers\ThermalPrinterController::class, 'update'])->name('update');
        Route::delete('/{thermalPrinter}', [\App\Http\Controllers\ThermalPrinterController::class, 'destroy'])->name('destroy');
        
        // Additional actions
        Route::post('/{thermalPrinter}/set-default', [\App\Http\Controllers\ThermalPrinterController::class, 'setDefault'])->name('set-default');
        Route::get('/{thermalPrinter}/test-connection', [\App\Http\Controllers\ThermalPrinterController::class, 'testConnection'])->name('test-connection');
        Route::post('/{thermalPrinter}/print-test', [\App\Http\Controllers\ThermalPrinterController::class, 'printTest'])->name('print-test');
        Route::get('/preset/load', [\App\Http\Controllers\ThermalPrinterController::class, 'loadPreset'])->name('load-preset');
        Route::get('/export/settings', [\App\Http\Controllers\ThermalPrinterController::class, 'exportSettings'])->name('export');
        Route::post('/import/settings', [\App\Http\Controllers\ThermalPrinterController::class, 'importSettings'])->name('import');
        
        // Emergency fix routes
        Route::post('/emergency-stop', [\App\Http\Controllers\ThermalPrinterController::class, 'emergencyStop'])->name('emergency-stop');
        Route::post('/fix-settings', [\App\Http\Controllers\ThermalPrinterController::class, 'fixSettings'])->name('fix-settings');
        Route::post('/test-fixed-print', [\App\Http\Controllers\ThermalPrinterController::class, 'testFixedPrint'])->name('test-fixed-print');
    });

    // Web endpoint for printing sales via thermal service (uses web auth + CSRF)
    Route::post('/sales/thermal/print', [App\Http\Controllers\Api\ThermalPrintController::class, 'printSale'])
        ->name('sales.thermal.print');
});

