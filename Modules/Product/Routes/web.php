<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'auth'], function () {
    //Product Stock Alert
    Route::get('/product-stock-alert', 'ProductController@stockAlert')->name('product-stock-alert.index');
    
    //Print Barcode
    Route::get('/products/print-barcode', 'BarcodeController@printBarcode')->name('barcode.print');
    Route::get('/products/print-barcode-view', 'BarcodeController@printBarcodeView')->name('products.print-barcode-view');
    Route::post('/products/barcodes/download-png-zip', 'BarcodeController@downloadPngZip')->name('products.barcodes.download_png_zip');
    
    //Product - Specific routes must be defined BEFORE resource route
    Route::get('/products/import', 'ProductController@showImportForm')->name('products.import');
    Route::post('/products/import-csv', 'ProductController@importCsv')->name('products.import-csv');
    Route::get('/products/export-csv', 'ProductController@exportCsv')->name('products.export-csv');
    Route::get('/products/download-template', 'ProductController@downloadTemplate')->name('products.download-template');
    Route::get('/products/download-template-xlsx', 'ProductController@downloadXlsxTemplate')->name('products.download-template-xlsx');
    Route::get('/products/download-header-template', 'ProductController@downloadHeaderOnlyTemplate')->name('products.download-header-template');
    Route::delete('/products/{product}/media/{media}', 'ProductController@deleteMedia')->name('products.media.delete');
    
    //Product Resource (must be after specific routes)
    Route::resource('products', 'ProductController');
    
    //Product Category
    Route::resource('product-categories', 'CategoriesController')->except('create', 'show');

    //Product Brand
    Route::resource('product-brands', 'BrandsController')->except('create', 'show');
});

