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

    //Generate PDF
    Route::get('/purchases/pdf/{id}', function ($id) {
        $purchase = \Modules\Purchase\Entities\Purchase::findOrFail($id);
        $supplier = \Modules\People\Entities\Supplier::findOrFail($purchase->supplier_id);

        try {
            $pdf = \PDF::loadView('purchase::print', [
                'purchase' => $purchase,
                'supplier' => $supplier,
            ])->setPaper('a4');

            return $pdf->stream('purchase-'. $purchase->reference .'.pdf');
        } catch (\Exception $e) {
            return redirect()->route('purchases.print', $id)->with('error', 'PDF generation failed. Using browser print instead.');
        }
    })->name('purchases.pdf');

    // Print view (browser print, no redirect)
    Route::get('/purchases/print/{id}', function ($id) {
        $purchase = \Modules\Purchase\Entities\Purchase::findOrFail($id);
        $supplier = \Modules\People\Entities\Supplier::findOrFail($purchase->supplier_id);
        return view('purchase::print-view', compact('purchase', 'supplier'));
    })->name('purchases.print');

    //Purchases
    Route::resource('purchases', 'PurchaseController');
    Route::get('/purchases/export-csv', 'PurchaseController@exportCsv')->name('purchases.export-csv');

    //Payments
    Route::get('/purchase-payments/{purchase_id}', 'PurchasePaymentsController@index')->name('purchase-payments.index');
    Route::get('/purchase-payments/{purchase_id}/create', 'PurchasePaymentsController@create')->name('purchase-payments.create');
    Route::post('/purchase-payments/store', 'PurchasePaymentsController@store')->name('purchase-payments.store');
    Route::get('/purchase-payments/{purchase_id}/edit/{purchasePayment}', 'PurchasePaymentsController@edit')->name('purchase-payments.edit');
    Route::patch('/purchase-payments/update/{purchasePayment}', 'PurchasePaymentsController@update')->name('purchase-payments.update');
    Route::delete('/purchase-payments/destroy/{purchasePayment}', 'PurchasePaymentsController@destroy')->name('purchase-payments.destroy');

});
