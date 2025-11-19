<?php

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

Route::group(['middleware' => 'auth'], function () {

    //POS
    Route::get('/app/pos', 'PosController@index')->name('app.pos.index');
    Route::post('/app/pos', 'PosController@store')->name('app.pos.store');

    //Generate PDF
    Route::get('/sales/pdf/{id}', function ($id) {
        $sale = \Modules\Sale\Entities\Sale::findOrFail($id);
        $customer = \Modules\People\Entities\Customer::findOrFail($sale->customer_id);

        try {
        $pdf = \PDF::loadView('sale::print', [
            'sale' => $sale,
            'customer' => $customer,
        ])->setPaper('a4');

        return $pdf->stream('sale-'. $sale->reference .'.pdf');
        } catch (\Exception $e) {
            // Fallback: redirect ke print view jika PDF generation gagal
            return redirect()->route('sales.pos.print', $id)->with('error', 'PDF generation failed. Using browser print instead.');
        }
    })->name('sales.pdf');

    Route::get('/sales/pos/pdf/{id}', function ($id) {
        $sale = \Modules\Sale\Entities\Sale::findOrFail($id);

        try {
        $pdf = \PDF::loadView('sale::print-pos', [
            'sale' => $sale,
        ])->setPaper('a7')
            ->setOption('margin-top', 8)
            ->setOption('margin-bottom', 8)
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 5);

        return $pdf->stream('sale-'. $sale->reference .'.pdf');
        } catch (\Exception $e) {
            // Fallback: redirect ke print view jika PDF generation gagal
            return redirect()->route('sales.pos.print', $id)->with('error', 'PDF generation failed. Using browser print instead.');
        }
    })->name('sales.pos.pdf');

    // Print view (browser print, no redirect)
    Route::get('/sales/pos/print/{id}', function ($id) {
        $sale = \Modules\Sale\Entities\Sale::findOrFail($id);
        return view('sale::print-view', compact('sale'));
    })->name('sales.pos.print');

    // Thermal 80mm print view (optimized for thermal printers)
    Route::get('/sales/thermal/print/{id}', function ($id) {
        $sale = \Modules\Sale\Entities\Sale::findOrFail($id);
        $customer = null;
        if ($sale->customer_id) {
            $customer = \Modules\People\Entities\Customer::find($sale->customer_id);
        }
        return view('sale::print-thermal-80mm', compact('sale', 'customer'));
    })->name('sales.thermal.print');

    //Sales
    Route::resource('sales', 'SaleController');
    Route::get('/sales/export-csv', 'SaleController@exportCsv')->name('sales.export-csv');

    //Payments
    Route::get('/sale-payments/{sale_id}', 'SalePaymentsController@index')->name('sale-payments.index');
    Route::get('/sale-payments/{sale_id}/create', 'SalePaymentsController@create')->name('sale-payments.create');
    Route::post('/sale-payments/store', 'SalePaymentsController@store')->name('sale-payments.store');
    Route::get('/sale-payments/{sale_id}/edit/{salePayment}', 'SalePaymentsController@edit')->name('sale-payments.edit');
    Route::patch('/sale-payments/update/{salePayment}', 'SalePaymentsController@update')->name('sale-payments.update');
    Route::delete('/sale-payments/destroy/{salePayment}', 'SalePaymentsController@destroy')->name('sale-payments.destroy');
});
