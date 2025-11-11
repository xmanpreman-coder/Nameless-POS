@extends('layouts.app')

@section('title', 'Import Products via CSV')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Update via CSV</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Update Products via CSV</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Instructions:</h5>
                            <ul class="mb-0">
                                <li>CSV file must contain <strong>SKU</strong> or <strong>GTIN</strong> column for product identification</li>
                                <li>Supported columns: <strong>SKU</strong>, <strong>GTIN</strong>, <strong>Name</strong>, <strong>Cost</strong>, <strong>Price</strong>, <strong>Quantity</strong>, <strong>Unit</strong>, <strong>Category</strong></li>
                                <li>Only products that exist in the database will be updated</li>
                                <li>Empty cells will be ignored (product will keep existing values)</li>
                                <li>Cost and Price should be in decimal format (e.g., 10000.50)</li>
                                <li>You can download a sample CSV by exporting products first</li>
                            </ul>
                        </div>

                        <form action="{{ route('products.import-csv') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload and Update Products
                                </button>
                                <a href="{{ route('products.download-template') }}" class="btn btn-info">
                                    <i class="bi bi-download"></i> Download CSV Template
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Products
                                </a>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h5>CSV Format Example:</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>GTIN</th>
                                            <th>Name</th>
                                            <th>Cost</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>SKU001</td>
                                            <td>1234567890123</td>
                                            <td>Product Name</td>
                                            <td>10000.00</td>
                                            <td>15000.00</td>
                                            <td>100</td>
                                            <td>pcs</td>
                                            <td>Electronics</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

