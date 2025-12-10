@extends('layouts.app')

@section('title', 'Products')

@section('third_party_stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Products</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add Product
                            </a>
                            
                            <!-- Bulk Update Products -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-pencil"></i> Update Multiple Products
                                </button>
                                <div class="dropdown-menu">
                                    <h6 class="dropdown-header">Bulk Update Products</h6>
                                    <a class="dropdown-item" href="{{ route('products.import') }}?mode=update_existing">
                                        <i class="bi bi-pencil text-warning"></i> Update Existing Only
                                        <small class="d-block text-muted">Update existing products via CSV</small>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('products.import') }}">
                                        <i class="bi bi-gear"></i> Advanced Import Settings
                                    </a>
                                </div>
                            </div>

                            <!-- Quick Template Downloads -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-download"></i> Download Templates
                                </button>
                                <div class="dropdown-menu">
                                    <h6 class="dropdown-header">Import Templates</h6>
                                    <a class="dropdown-item" href="{{ route('products.download-header-template') }}" download="product-header-template.xlsx">
                                        <i class="bi bi-table text-warning"></i> Header Only (.xlsx)
                                        <small class="d-block text-muted">Empty template with column headers</small>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('products.download-template-xlsx') }}" download="product-template.xlsx">
                                        <i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel Template (.xlsx)
                                        <small class="d-block text-muted">Enhanced with 11 sample products</small>
                                    </a>
                                    <a class="dropdown-item" href="{{ route('products.download-template') }}" download="product-template.csv">
                                        <i class="bi bi-file-earmark-text text-secondary"></i> CSV Template
                                        <small class="d-block text-muted">Excel-compatible format</small>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <div class="dropdown-item-text">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Templates include examples from 5 categories: Electronics, Fashion, Books, Food & Beverage, Office Supplies
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Export Options -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-box-arrow-up"></i> Export
                                </button>
                                <div class="dropdown-menu">
                                    <h6 class="dropdown-header">Export Current Data</h6>
                                    <a class="dropdown-item" href="{{ route('products.export-csv') }}" download="products-export.csv">
                                        <i class="bi bi-file-earmark-text"></i> Export as CSV
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="alert('Excel export coming soon!')">
                                        <i class="bi bi-file-earmark-spreadsheet"></i> Export as Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Info Card -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-light border-left border-info">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-1"><i class="bi bi-lightbulb text-warning"></i> Quick Start Guide</h6>
                                            <small class="text-muted">
                                                • <strong>Add Single Product:</strong> Click "Add Product" button<br>
                                                • <strong>Update Existing Products:</strong> Use "Update Multiple Products" → "Update Existing Only"<br>
                                                • <strong>Need Template?</strong> Download our Excel template with 11 sample products
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-md-right">
                                            <a href="{{ route('products.download-template-xlsx') }}" download="product-template.xlsx" class="btn btn-sm btn-info">
                                                <i class="bi bi-download"></i> Get Template
                                            </a>
                                            <a href="{{ route('products.import') }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-upload"></i> Import Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
        // Initialize print protection flag
        window.printInProgress = window.printInProgress || false;
        
        // Fallback download handler (browser / Electron)
        function handleTauriDownload(url, filename) {
            window.location.href = url;
        }
    </script>
@endpush
