<?php

namespace Modules\Reports\DataTables;

use Modules\Sale\Entities\Sale;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Html\Editor\Editor;

class SalesReportDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('customer_name', function($row) {
                return $row->customer_name ?? 'Walk-in Customer';
            })
            ->editColumn('total_amount', function($row) {
                return format_currency($row->total_amount);
            })
            ->editColumn('paid_amount', function($row) {
                return format_currency($row->paid_amount);
            })
            ->editColumn('due_amount', function($row) {
                return format_currency($row->due_amount);
            })
            ->editColumn('date', function($row) {
                return \Carbon\Carbon::parse($row->date)->format('d M Y');
            })
            ->editColumn('status', function($row) {
                return view('sale::partials.status', ['data' => $row]);
            })
            ->editColumn('payment_status', function($row) {
                return view('sale::partials.payment-status', ['data' => $row]);
            })
            ->rawColumns(['status', 'payment_status']);
    }

    public function query(Sale $model)
    {
        return $model->newQuery()
            ->when(request('start_date'), function($query) {
                return $query->whereDate('date', '>=', request('start_date'));
            })
            ->when(request('end_date'), function($query) {
                return $query->whereDate('date', '<=', request('end_date'));
            })
            ->when(request('customer_id'), function($query) {
                return $query->where('customer_id', request('customer_id'));
            })
            ->when(request('sale_status'), function($query) {
                return $query->where('status', request('sale_status'));
            })
            ->when(request('payment_status'), function($query) {
                return $query->where('payment_status', request('payment_status'));
            })
            ->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('sales-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make('create')
                    ->text('<i class="bi bi-plus"></i> Add Sale')
                    ->action('function() { window.location.href = "' . route('sales.create') . '"; }'),
                Button::make('excel')
                    ->text('<i class="bi bi-file-earmark-excel"></i> Excel')
                    ->extend('excel')
                    ->className('btn btn-success')
                    ->exportOptions([
                        'columns' => ':visible'
                    ]),
                Button::make('print')
                    ->text('<i class="bi bi-printer-fill"></i> Print')
                    ->action('function() {
                        if (!window.printInProgress) {
                            window.printInProgress = true;
                            setTimeout(function() {
                                window.print();
                                setTimeout(function() {
                                    window.printInProgress = false;
                                }, 1000);
                            }, 100);
                        }
                        return false;
                    }'),
                Button::make('reset')
                    ->text('<i class="bi bi-x-circle"></i> Reset'),
                Button::make('reload')
                    ->text('<i class="bi bi-arrow-repeat"></i> Reload')
            );
    }

    protected function getColumns()
    {
        return [
            Column::make('reference'),
            Column::make('customer_name')->title('Customer'),
            Column::make('status'),
            Column::make('total_amount'),
            Column::make('paid_amount'),
            Column::make('due_amount'),
            Column::make('payment_status'),
            Column::make('date'),
        ];
    }

    protected function filename(): string
    {
        return 'SalesReport_' . date('YmdHis');
    }
}