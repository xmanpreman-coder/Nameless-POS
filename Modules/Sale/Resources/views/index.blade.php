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
        // Iframe-based print approach - works in WebView/Tauri without popup blocker
        let printInProgress = false;
        
        function printSaleNota(saleId) {
            if (printInProgress) {
                return false;
            }
            
            printInProgress = true;
            
            const url = '{{ url("/sales/pos/print") }}/' + saleId;
            
            // Remove existing print iframe if any
            let existingFrame = document.getElementById('print-frame');
            if (existingFrame) {
                existingFrame.remove();
            }
            
            // Create hidden iframe for printing
            const iframe = document.createElement('iframe');
            iframe.id = 'print-frame';
            iframe.name = 'print-frame';
            iframe.style.cssText = 'position:fixed;left:-9999px;top:0;width:400px;height:600px;border:none;';
            document.body.appendChild(iframe);
            
            // Load print URL in iframe
            iframe.src = url;
            
            // Wait for iframe to load then print
            iframe.onload = function() {
                setTimeout(function() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch(e) {
                        // Fallback: open in new tab
                        window.open(url, '_blank');
                    }
                    
                    // Cleanup after print dialog closes
                    setTimeout(function() {
                        iframe.remove();
                        printInProgress = false;
                    }, 1000);
                }, 500);
            };
            
            // Error fallback
            iframe.onerror = function() {
                window.open(url, '_blank');
                printInProgress = false;
            };
            
            // Reset flag after timeout as backup
            setTimeout(function() {
                printInProgress = false;
            }, 10000);
            
            return false;
        }

        // Compatibility flag for DataTable
        window.printInProgress = false;
    </script>
@endpush
