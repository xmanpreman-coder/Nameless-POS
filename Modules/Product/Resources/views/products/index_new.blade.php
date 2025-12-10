@extends('layouts.app')

@section('title', 'Products')

@section('third_party_stylesheets')
    <link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
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
                        <!-- BUTTONS SECTION -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add Product
                            </a>
                            <a href="{{ route('products.import') }}?mode=add_new" class="btn btn-success">
                                <i class="bi bi-upload"></i> Add Products via CSV
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-download"></i> Download Template
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('products.download-header-template') }}" download="product-header-template.xlsx">
                                        <i class="bi bi-table text-warning"></i> Header Only
                                    </a>
                                    <a class="dropdown-item" href="{{ route('products.download-template-xlsx') }}" download="product-template.xlsx">
                                        <i class="bi bi-file-earmark-spreadsheet text-success"></i> With Samples
                                    </a>
                                </div>
                            </div>
                            <a href="{{ route('products.import') }}?mode=update_existing" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Update Products via CSV
                            </a>
                            <a href="{{ route('products.export-csv') }}" download="products-export-{{ date('Y-m-d') }}.csv" class="btn btn-secondary">
                                <i class="bi bi-file-earmark-text"></i> Export CSV
                            </a>
                        </div>

                        <!-- Filter Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label for="filter_category" class="form-label mb-1"><strong>Filter by Category</strong></label>
                                        <select id="filter_category" class="form-control">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter_brand" class="form-label mb-1"><strong>Filter by Brand</strong></label>
                                        <select id="filter_brand" class="form-control">
                                            <option value="">All Brands</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="btn_filter" class="btn btn-primary w-100">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="btn_reset" class="btn btn-secondary w-100">
                                            <i class="bi bi-x-circle"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="alert alert-info mb-3">
                            <strong>Quick Actions:</strong>
                            • <strong>Add Products via CSV</strong> - Create multiple products at once
                            • <strong>Download Template</strong> - Get Excel template with sample data
                            • <strong>Update via CSV</strong> - Bulk update existing products
                        </div>

                        <!-- DataTable -->
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
        window.printInProgress = window.printInProgress || false;
        
        // Fallback download handler (browser / Electron)
        function handleTauriDownload(url, filename) {
            window.location.href = url;
        }

        $(document).ready(function() {
            var table = window.LaravelDataTables['product-table'];
            
            // Override ajax.data to include filter parameters
            var originalAjax = table.settings()[0].ajax;
            if (typeof originalAjax === 'string') {
                table.settings()[0].ajax = {
                    url: originalAjax,
                    type: 'GET',
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.brand_id = $('#filter_brand').val();
                    }
                };
            } else if (typeof originalAjax === 'object') {
                var originalDataFn = originalAjax.data;
                originalAjax.data = function(d) {
                    if (originalDataFn) {
                        originalDataFn(d);
                    }
                    d.category_id = $('#filter_category').val();
                    d.brand_id = $('#filter_brand').val();
                };
            }

            // Filter button click - just reload with current filter values
            $('#btn_filter').click(function() {
                table.ajax.reload();
            });

            // Reset button click
            $('#btn_reset').click(function() {
                $('#filter_category').val('');
                $('#filter_brand').val('');
                table.ajax.reload();
            });

            // Auto-filter on dropdown change (optional - for better UX)
            $('#filter_category, #filter_brand').change(function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush