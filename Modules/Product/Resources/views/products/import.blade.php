@extends('layouts.app')

@section('title', 'Bulk Add/Import Products')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Bulk Add/Import Products</li>
    </ol>
@endsection

@push('page_scripts')
    <script>
        // Electron-compatible download handler
        function downloadTemplate(url, filename) {
            window.location.href = url;
        }
    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="card-title mb-0"><i class="bi bi-plus-square"></i> Bulk Add/Import Products</h4>
                                <small>Create multiple products at once using CSV/Excel files</small>
                            </div>
                            <div class="col-auto">
                                <span class="badge badge-light">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV/Excel Support
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Enhanced Instructions Card -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="alert alert-info">
                                    <h5><i class="bi bi-info-circle"></i> How to Bulk Create Products:</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>📝 Required Columns:</strong>
                                            <ul class="mb-2">
                                                <li><strong>SKU</strong> or <strong>GTIN</strong> - Product identification</li>
                                                <li><strong>Name</strong> - Product name</li>
                                            </ul>
                                            
                                            <strong>📊 Optional Columns:</strong>
                                            <ul class="mb-2">
                                                <li>Cost, Price, Quantity, Unit</li>
                                                <li>Category, Stock_Alert, Note</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>🔄 Import Modes:</strong>
                                            <ul class="mb-0">
                                                <li><span class="badge badge-success">Create New</span> - Creates new products only</li>
                                                <li><span class="badge badge-warning">Update Only</span> - Updates existing products</li>
                                                <li><span class="badge badge-info">Create & Update</span> - Most flexible option</li>
                                            </ul>
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    💰 <strong>Price format:</strong> 50000.00 for Rp 50,000<br>
                                                    🏷️ <strong>Categories:</strong> Auto-created if not exist<br>
                                                    📦 <strong>Stock Alert:</strong> Default is 10
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bi bi-download"></i> Get Started - Download Template</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-file-earmark-spreadsheet text-success" style="font-size: 2rem;"></i>
                                            <p class="mb-0 small text-muted">Ready-to-use templates with sample data</p>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('products.download-header-template') }}" class="btn btn-info" download>
                                                <i class="bi bi-table"></i> Header Only (.xlsx)
                                                <small class="d-block">Empty template - fill your data</small>
                                            </a>
                                            <a href="{{ route('products.download-template-xlsx') }}" class="btn btn-success" download>
                                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel Template (.xlsx)
                                                <small class="d-block">✨ Enhanced - 11 sample products included</small>
                                            </a>
                                            <a href="{{ route('products.download-template') }}" class="btn btn-outline-success" download>
                                                <i class="bi bi-file-earmark-text"></i> CSV Template
                                                <small class="d-block">Excel-compatible format</small>
                                            </a>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                💡 <strong>Tip:</strong> Edit the template with your products, then upload here!
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('products.import-csv') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="form-group">
                                <label for="import_mode"><i class="bi bi-gear"></i> Choose Action Mode</label>
                                <select class="form-control @error('import_mode') is-invalid @enderror" 
                                        id="import_mode" 
                                        name="import_mode" 
                                        required>
                                    <option value="add_new" {{ old('import_mode', request('mode', 'add_new')) === 'add_new' ? 'selected' : '' }}>
                                        Add New Products Only
                                    </option>
                                    <option value="update_existing" {{ old('import_mode', request('mode')) === 'update_existing' ? 'selected' : '' }}>
                                        Update Existing Products Only
                                    </option>
                                    <option value="both" {{ old('import_mode', request('mode')) === 'both' ? 'selected' : '' }}>
                                        Both (Add New & Update Existing)
                                    </option>
                                </select>
                                @error('import_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Choose how to handle products in your CSV file
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="csv_file">Select CSV File</label>
                                <input type="file" 
                                       class="form-control @error('csv_file') is-invalid @enderror" 
                                       id="csv_file" 
                                       name="csv_file" 
                                       accept=".csv,.txt"
                                       required>
                                @error('csv_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Maximum file size: 10MB. Supported formats: CSV, TXT
                                </small>
                            </div>

                            <div class="form-group mt-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-upload"></i> Upload and Import Products
                                    </button>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Products
                                    </a>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-info-circle"></i> Don't have a CSV file? Download our templates first:
                                    </small>
                                    <div class="btn-group">
                                        <a href="{{ route('products.download-template-xlsx') }}" class="btn btn-success">
                                            <i class="bi bi-file-earmark-spreadsheet"></i> Excel Template (.xlsx)
                                        </a>
                                        <a href="{{ route('products.download-template') }}" class="btn btn-secondary">
                                            <i class="bi bi-file-earmark-text"></i> CSV Template
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h5>CSV Format Example:</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-success">
                                        <tr>
                                            <th>SKU</th>
                                            <th>GTIN</th>
                                            <th>Name</th>
                                            <th>Cost</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Category</th>
                                            <th>Stock_Alert</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ELC001</td>
                                            <td>1234567890123</td>
                                            <td>iPhone 14 Pro</td>
                                            <td>12000000.00</td>
                                            <td>15000000.00</td>
                                            <td>50</td>
                                            <td>pcs</td>
                                            <td>Electronics</td>
                                            <td>5</td>
                                            <td>Flagship smartphone</td>
                                        </tr>
                                        <tr class="table-light">
                                            <td>FB001</td>
                                            <td>4234567890123</td>
                                            <td>Coffee Arabica 1kg</td>
                                            <td>80000.00</td>
                                            <td>120000.00</td>
                                            <td>200</td>
                                            <td>kg</td>
                                            <td>Food & Beverage</td>
                                            <td>50</td>
                                            <td>Premium coffee beans</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-secondary mt-3">
                                <small>
                                    <strong>Tips:</strong> Download template untuk mendapatkan 11 contoh produk dari 5 kategori berbeda. 
                                    Gunakan format Excel (.xlsx) untuk editing yang lebih mudah!
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

