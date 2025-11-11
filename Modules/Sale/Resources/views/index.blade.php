@extends('layouts.app')

@section('title', 'Sales')

@section('third_party_stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('sales.create') }}" class="btn btn-primary">
                            Add Sale <i class="bi bi-plus"></i>
                        </a>

                        <hr>

                        <div class="table-responsive">
                            {!! $dataTable->table() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    {!! $dataTable->scripts() !!}
    <script>
        // Simple and direct print approach
        let printInProgress = false;
        
        function printSaleNota(saleId) {
            if (printInProgress) {
                return false;
            }
            
            printInProgress = true;
            
            // Simple popup approach - user manually clicks print
            const url = '{{ url("/sales/pos/print") }}/' + saleId;
            const printWindow = window.open(url, 'receipt_' + Date.now(), 
                'width=400,height=600,scrollbars=no,resizable=no');
            
            if (printWindow) {
                printWindow.focus();
            } else {
                alert('Please allow popups for printing receipts');
            }
            
            // Reset flag after short delay
            setTimeout(function() {
                printInProgress = false;
            }, 2000);
            
            return false;
        }

        // Compatibility flag for DataTable
        window.printInProgress = false;
    </script>
@endpush
