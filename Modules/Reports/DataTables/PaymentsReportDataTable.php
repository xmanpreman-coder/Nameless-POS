<?php

namespace Modules\Reports\DataTables;

use Modules\Sale\Entities\SalePayment;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentsReportDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('customer_name', function($row) {
                return $row->sale->customer_name ?? 'Walk-in Customer';
            })
            ->editColumn('reference', function($row) {
                return $row->sale->reference ?? '-';
            })
            ->editColumn('amount', function($row) {
                return format_currency($row->amount);
            })
            ->editColumn('date', function($row) {
                return \Carbon\Carbon::parse($row->date)->format('d M Y');
            })
            ->editColumn('payment_method', function($row) {
                return ucfirst($row->payment_method);
            });
    }

    public function query(SalePayment $model)
    {
        return $model->newQuery()
            ->with(['sale'])
            ->when(request('start_date'), function($query) {
                return $query->whereDate('date', '>=', request('start_date'));
            })
            ->when(request('end_date'), function($query) {
                return $query->whereDate('date', '<=', request('end_date'));
            })
            ->when(request('payment_method'), function($query) {
                return $query->where('payment_method', request('payment_method'));
            })
            ->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('payments-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
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
            Column::make('reference')->title('Sale Reference'),
            Column::make('customer_name')->title('Customer'),
            Column::make('payment_method'),
            Column::make('amount'),
            Column::make('date'),
        ];
    }

    protected function filename(): string
    {
        return 'PaymentsReport_' . date('YmdHis');
    }
}