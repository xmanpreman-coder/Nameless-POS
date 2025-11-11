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
    //Profit Loss Report
    Route::get('/profit-loss-report', 'ReportsController@profitLossReport')
        ->name('profit-loss-report.index');
    Route::get('/profit-loss-report/print', 'ReportsController@profitLossReportPrint')
        ->name('profit-loss-report.print');
    Route::get('/profit-loss-report/export-csv', 'ReportsController@exportProfitLossReportCSV')
        ->name('profit-loss-report.export-csv');
    
    //Payments Report
    Route::get('/payments-report', 'ReportsController@paymentsReport')
        ->name('payments-report.index');
    Route::get('/payments-report/print', 'ReportsController@paymentsReportPrint')
        ->name('payments-report.print');
    Route::get('/payments-report/export-csv', 'ReportsController@exportPaymentsReportCSV')
        ->name('payments-report.export-csv');
    
    //Sales Report
    Route::get('/sales-report', 'ReportsController@salesReport')
        ->name('sales-report.index');
    Route::get('/sales-report/print', 'ReportsController@salesReportPrint')
        ->name('sales-report.print');
    Route::get('/sales-report/export-csv', 'ReportsController@exportSalesReportCSV')
        ->name('sales-report.export-csv');
    
    //Purchases Report
    Route::get('/purchases-report', 'ReportsController@purchasesReport')
        ->name('purchases-report.index');
    Route::get('/purchases-report/print', 'ReportsController@purchasesReportPrint')
        ->name('purchases-report.print');
    Route::get('/purchases-report/export-csv', 'ReportsController@exportPurchasesReportCSV')
        ->name('purchases-report.export-csv');
    
    //Sales Return Report
    Route::get('/sales-return-report', 'ReportsController@salesReturnReport')
        ->name('sales-return-report.index');
    Route::get('/sales-return-report/print', 'ReportsController@salesReturnReportPrint')
        ->name('sales-return-report.print');
    Route::get('/sales-return-report/export-csv', 'ReportsController@exportSalesReturnReportCSV')
        ->name('sales-return-report.export-csv');
    
    //Purchases Return Report
    Route::get('/purchases-return-report', 'ReportsController@purchasesReturnReport')
        ->name('purchases-return-report.index');
    Route::get('/purchases-return-report/print', 'ReportsController@purchasesReturnReportPrint')
        ->name('purchases-return-report.print');
    Route::get('/purchases-return-report/export-csv', 'ReportsController@exportPurchasesReturnReportCSV')
        ->name('purchases-return-report.export-csv');
});
