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
    Route::get('/quotations/pdf/{id}', function ($id) {
        $quotation = \Modules\Quotation\Entities\Quotation::findOrFail($id);
        $customer = \Modules\People\Entities\Customer::findOrFail($quotation->customer_id);

        try {
            $pdf = \PDF::loadView('quotation::print', [
                'quotation' => $quotation,
                'customer' => $customer,
            ])->setPaper('a4');

            return $pdf->stream('quotation-'. $quotation->reference .'.pdf');
        } catch (\Exception $e) {
            return redirect()->route('quotations.print', $id)->with('error', 'PDF generation failed. Using browser print instead.');
        }
    })->name('quotations.pdf');

    // Print view (browser print, no redirect)
    Route::get('/quotations/print/{id}', function ($id) {
        $quotation = \Modules\Quotation\Entities\Quotation::findOrFail($id);
        $customer = \Modules\People\Entities\Customer::findOrFail($quotation->customer_id);
        return view('quotation::print-view', compact('quotation', 'customer'));
    })->name('quotations.print');

    //Send Quotation Mail
    Route::get('/quotation/mail/{quotation}', 'SendQuotationEmailController')->name('quotation.email');

    //Sales Form Quotation
    Route::get('/quotation-sales/{quotation}', 'QuotationSalesController')->name('quotation-sales.create');

    //quotations
    Route::resource('quotations', 'QuotationController');
    Route::get('/quotations/export-csv', 'QuotationController@exportCsv')->name('quotations.export-csv');
});
