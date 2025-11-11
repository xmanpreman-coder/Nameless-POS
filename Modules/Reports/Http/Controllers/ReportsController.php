<?php

namespace Modules\Reports\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Reports\DataTables\SalesReportDataTable;
use Modules\Reports\DataTables\PurchasesReportDataTable;
use Modules\Reports\DataTables\PaymentsReportDataTable;
use Modules\Reports\DataTables\SalesReturnReportDataTable;
use Modules\Reports\DataTables\PurchasesReturnReportDataTable;
use Modules\People\Entities\Customer;
use Modules\People\Entities\Supplier;

class ReportsController extends Controller
{

    public function profitLossReport() {
        abort_if(Gate::denies('access_reports'), 403);

        return view('reports::profit-loss.index');
    }

    public function paymentsReport(PaymentsReportDataTable $dataTable) {
        abort_if(Gate::denies('access_reports'), 403);

        return $dataTable->render('reports::payments.index');
    }

    public function salesReport(SalesReportDataTable $dataTable) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $customers = Customer::all();
        return $dataTable->render('reports::sales.index', compact('customers'));
    }
    
    private function exportSalesReportPDF(Request $request) {
        try {
            $sales = \Modules\Sale\Entities\Sale::whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date)
                ->when($request->customer_id, function ($query) use ($request) {
                    return $query->where('customer_id', $request->customer_id);
                })
                ->when($request->sale_status, function ($query) use ($request) {
                    return $query->where('status', $request->sale_status);
                })
                ->when($request->payment_status, function ($query) use ($request) {
                    return $query->where('payment_status', $request->payment_status);
                })
                ->orderBy('date', 'desc')->get();
            
            $pdf = \PDF::loadView('reports::sales.pdf', [
                'sales' => $sales,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            // Fallback: redirect ke print view untuk browser print (dalam window baru)
            // Tidak redirect halaman utama, tapi buka print view di window baru
            $printUrl = route('sales-report.print', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'customer_id' => $request->customer_id,
                'sale_status' => $request->sale_status,
                'payment_status' => $request->payment_status,
            ]);
            
            // Return JavaScript untuk buka print view di window baru
            return response('<script>window.open("' . $printUrl . '", "_blank"); window.close();</script>')
                ->header('Content-Type', 'text/html');
        }
    }
    
    public function salesReportPrint(Request $request) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $start_date = $request->get('start_date', session('start_date', today()->subDays(30)->format('Y-m-d')));
        $end_date = $request->get('end_date', session('end_date', today()->format('Y-m-d')));
        
        $sales = \Modules\Sale\Entities\Sale::whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when($request->customer_id, function ($query) use ($request) {
                return $query->where('customer_id', $request->customer_id);
            })
            ->when($request->sale_status, function ($query) use ($request) {
                return $query->where('status', $request->sale_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        return view('reports::sales.print', [
            'sales' => $sales,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }
    
    public function exportSalesReportCSV(Request $request) {
        $sales = \Modules\Sale\Entities\Sale::whereDate('date', '>=', $request->start_date)
            ->whereDate('date', '<=', $request->end_date)
            ->when($request->customer_id, function ($query) use ($request) {
                return $query->where('customer_id', $request->customer_id);
            })
            ->when($request->sale_status, function ($query) use ($request) {
                return $query->where('status', $request->sale_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        $filename = 'sales-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, ['Date', 'Reference', 'Customer', 'Status', 'Total', 'Paid', 'Due', 'Payment Status']);
            
            // Data
            foreach ($sales as $sale) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($sale->date)->format('d M Y'),
                    $sale->reference,
                    $sale->customer_name,
                    $sale->status,
                    number_format($sale->total_amount / 100, 2, '.', ''),
                    number_format($sale->paid_amount / 100, 2, '.', ''),
                    number_format($sale->due_amount / 100, 2, '.', ''),
                    $sale->payment_status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function purchasesReport(PurchasesReportDataTable $dataTable) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $suppliers = Supplier::all();
        return $dataTable->render('reports::purchases.index', compact('suppliers'));
    }
    
    private function exportPurchasesReportPDF(Request $request) {
        try {
            $purchases = \Modules\Purchase\Entities\Purchase::whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date)
                ->when($request->supplier_id, function ($query) use ($request) {
                    return $query->where('supplier_id', $request->supplier_id);
                })
                ->when($request->purchase_status, function ($query) use ($request) {
                    return $query->where('status', $request->purchase_status);
                })
                ->when($request->payment_status, function ($query) use ($request) {
                    return $query->where('payment_status', $request->payment_status);
                })
                ->orderBy('date', 'desc')->get();
            
            $pdf = \PDF::loadView('reports::purchases.pdf', [
                'purchases' => $purchases,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('purchases-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            $printUrl = route('purchases-report.print', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'supplier_id' => $request->supplier_id,
                'purchase_status' => $request->purchase_status,
                'payment_status' => $request->payment_status,
            ]);
            
            return response('<script>window.open("' . $printUrl . '", "_blank"); window.close();</script>')
                ->header('Content-Type', 'text/html');
        }
    }
    
    public function purchasesReportPrint(Request $request) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $start_date = $request->get('start_date', session('start_date', today()->subDays(30)->format('Y-m-d')));
        $end_date = $request->get('end_date', session('end_date', today()->format('Y-m-d')));
        
        $purchases = \Modules\Purchase\Entities\Purchase::whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->purchase_status, function ($query) use ($request) {
                return $query->where('status', $request->purchase_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        return view('reports::purchases.print', [
            'purchases' => $purchases,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }
    
    public function exportPurchasesReportCSV(Request $request) {
        $purchases = \Modules\Purchase\Entities\Purchase::whereDate('date', '>=', $request->start_date)
            ->whereDate('date', '<=', $request->end_date)
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->purchase_status, function ($query) use ($request) {
                return $query->where('status', $request->purchase_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        $filename = 'purchases-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($purchases) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Date', 'Reference', 'Supplier', 'Status', 'Total', 'Paid', 'Due', 'Payment Status']);
            
            foreach ($purchases as $purchase) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($purchase->date)->format('d M Y'),
                    $purchase->reference,
                    $purchase->supplier_name,
                    $purchase->status,
                    number_format($purchase->total_amount / 100, 2, '.', ''),
                    number_format($purchase->paid_amount / 100, 2, '.', ''),
                    number_format($purchase->due_amount / 100, 2, '.', ''),
                    $purchase->payment_status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function salesReturnReport(SalesReturnReportDataTable $dataTable) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $customers = Customer::all();
        return $dataTable->render('reports::sales-return.index', compact('customers'));
    }
    
    private function exportSalesReturnReportPDF(Request $request) {
        try {
            $sales_returns = \Modules\SalesReturn\Entities\SaleReturn::whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date)
                ->when($request->customer_id, function ($query) use ($request) {
                    return $query->where('customer_id', $request->customer_id);
                })
                ->when($request->sale_return_status, function ($query) use ($request) {
                    return $query->where('status', $request->sale_return_status);
                })
                ->when($request->payment_status, function ($query) use ($request) {
                    return $query->where('payment_status', $request->payment_status);
                })
                ->orderBy('date', 'desc')->get();
            
            $pdf = \PDF::loadView('reports::sales-return.pdf', [
                'sales_returns' => $sales_returns,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('sales-return-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('sales-return-report.print')
                ->with('sales_returns', $sales_returns ?? collect())
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date)
                ->with('error', 'PDF generation failed. Using browser print instead.');
        }
    }
    
    public function salesReturnReportPrint(Request $request) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $start_date = $request->get('start_date', session('start_date', today()->subDays(30)->format('Y-m-d')));
        $end_date = $request->get('end_date', session('end_date', today()->format('Y-m-d')));
        
        $sales_returns = \Modules\SalesReturn\Entities\SaleReturn::whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when($request->customer_id, function ($query) use ($request) {
                return $query->where('customer_id', $request->customer_id);
            })
            ->when($request->sale_return_status, function ($query) use ($request) {
                return $query->where('status', $request->sale_return_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        return view('reports::sales-return.print', [
            'sales_returns' => $sales_returns,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }
    
    public function exportSalesReturnReportCSV(Request $request) {
        $sales_returns = \Modules\SalesReturn\Entities\SaleReturn::whereDate('date', '>=', $request->start_date)
            ->whereDate('date', '<=', $request->end_date)
            ->when($request->customer_id, function ($query) use ($request) {
                return $query->where('customer_id', $request->customer_id);
            })
            ->when($request->sale_return_status, function ($query) use ($request) {
                return $query->where('status', $request->sale_return_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        $filename = 'sales-return-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($sales_returns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Date', 'Reference', 'Customer', 'Status', 'Total', 'Paid', 'Due', 'Payment Status']);
            
            foreach ($sales_returns as $sale_return) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($sale_return->date)->format('d M Y'),
                    $sale_return->reference,
                    $sale_return->customer_name,
                    $sale_return->status,
                    number_format($sale_return->total_amount / 100, 2, '.', ''),
                    number_format($sale_return->paid_amount / 100, 2, '.', ''),
                    number_format($sale_return->due_amount / 100, 2, '.', ''),
                    $sale_return->payment_status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function purchasesReturnReport(PurchasesReturnReportDataTable $dataTable) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $suppliers = Supplier::all();
        return $dataTable->render('reports::purchases-return.index', compact('suppliers'));
    }
    
    private function exportPurchasesReturnReportPDF(Request $request) {
        try {
            $purchase_returns = \Modules\PurchasesReturn\Entities\PurchaseReturn::whereDate('date', '>=', $request->start_date)
                ->whereDate('date', '<=', $request->end_date)
                ->when($request->supplier_id, function ($query) use ($request) {
                    return $query->where('supplier_id', $request->supplier_id);
                })
                ->when($request->purchase_return_status, function ($query) use ($request) {
                    return $query->where('status', $request->purchase_return_status);
                })
                ->when($request->payment_status, function ($query) use ($request) {
                    return $query->where('payment_status', $request->payment_status);
                })
                ->orderBy('date', 'desc')->get();
            
            $pdf = \PDF::loadView('reports::purchases-return.pdf', [
                'purchase_returns' => $purchase_returns,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])->setPaper('a4', 'landscape');
            
            return $pdf->download('purchases-return-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('purchases-return-report.print')
                ->with('purchase_returns', $purchase_returns ?? collect())
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date)
                ->with('error', 'PDF generation failed. Using browser print instead.');
        }
    }
    
    public function purchasesReturnReportPrint(Request $request) {
        abort_if(Gate::denies('access_reports'), 403);
        
        $start_date = $request->get('start_date', session('start_date', today()->subDays(30)->format('Y-m-d')));
        $end_date = $request->get('end_date', session('end_date', today()->format('Y-m-d')));
        
        $purchase_returns = \Modules\PurchasesReturn\Entities\PurchaseReturn::whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->purchase_return_status, function ($query) use ($request) {
                return $query->where('status', $request->purchase_return_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        return view('reports::purchases-return.print', [
            'purchase_returns' => $purchase_returns,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }
    
    public function exportPurchasesReturnReportCSV(Request $request) {
        $purchase_returns = \Modules\PurchasesReturn\Entities\PurchaseReturn::whereDate('date', '>=', $request->start_date)
            ->whereDate('date', '<=', $request->end_date)
            ->when($request->supplier_id, function ($query) use ($request) {
                return $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->purchase_return_status, function ($query) use ($request) {
                return $query->where('status', $request->purchase_return_status);
            })
            ->when($request->payment_status, function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->orderBy('date', 'desc')->get();
        
        $filename = 'purchases-return-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($purchase_returns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Date', 'Reference', 'Supplier', 'Status', 'Total', 'Paid', 'Due', 'Payment Status']);
            
            foreach ($purchase_returns as $purchase_return) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($purchase_return->date)->format('d M Y'),
                    $purchase_return->reference,
                    $purchase_return->supplier_name,
                    $purchase_return->status,
                    number_format($purchase_return->total_amount / 100, 2, '.', ''),
                    number_format($purchase_return->paid_amount / 100, 2, '.', ''),
                    number_format($purchase_return->due_amount / 100, 2, '.', ''),
                    $purchase_return->payment_status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
