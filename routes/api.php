<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Printer API Routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/system-printer-settings', [App\Http\Controllers\Api\PrinterController::class, 'getSystemSettings']);
    Route::get('/user-printer-preferences', [App\Http\Controllers\Api\PrinterController::class, 'getUserPreferences']);
    Route::post('/user-printer-preferences', [App\Http\Controllers\Api\PrinterController::class, 'saveUserPreferences']);
    Route::get('/printer-profiles', [App\Http\Controllers\Api\PrinterController::class, 'getPrinterProfiles']);
});
