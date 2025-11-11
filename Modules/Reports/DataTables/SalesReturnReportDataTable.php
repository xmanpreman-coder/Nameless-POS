<?php

namespace Modules\Reports\DataTables;

use Modules\SalesReturn\Entities\SalesReturn;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesReturnReportDataTable extends DataTable
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
            ->editColumn('date', function($row) {
                return \Carbon\Carbon::parse($row->date)->format('d M Y');
            })
            ->editColumn('status', function($row) {
                return '<span class="badge badge-' . ($row->status == 'Completed' ? 'success' : 'warning') . '">' . $row->status . '</span>';
            })
            ->rawColumns(['status']);
    }

    public function query(SalesReturn $model)
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
            ->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('sales-return-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make('create')
                    ->text('<i class="bi bi-plus"></i> Add Sale Return')
                    ->action('function() { window.location.href = "' . route('sale-returns.create') . '"; }'),
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
            Column::make('date'),
        ];
    }

    protected function filename(): string
    {
        return 'SalesReturnReport_' . date('YmdHis');
    }
}