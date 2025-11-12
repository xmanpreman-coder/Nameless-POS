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

// Printer Settings Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/printer-settings', [App\Http\Controllers\PrinterSettingController::class, 'index'])->name('printer-settings.index');
    Route::patch('/printer-settings', [App\Http\Controllers\PrinterSettingController::class, 'update'])->name('printer-settings.update');
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
});

