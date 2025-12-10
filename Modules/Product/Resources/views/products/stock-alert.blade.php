@extends('layouts.app')

@section('title', 'Product Stock Alert')

@section('third_party_stylesheets')
    <link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active">Stock Alert</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Alert Banner -->
                @if($low_stock_products->count() > 0)
                <div class="alert alert-warning mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>{{ $low_stock_products->count() }} produk</strong> memiliki stok rendah dan perlu segera ditambah!
                </div>
                @else
                <div class="alert alert-success mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Semua produk memiliki stok yang cukup.
                </div>
                @endif

                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Produk dengan Stok Rendah</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="stockAlertTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>SKU</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Brand</th>
                                        <th class="text-center">Stok Saat Ini</th>
                                        <th class="text-center">Batas Alert</th>
                                        <th class="text-center">Kekurangan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($low_stock_products as $index => $product)
                                    <tr class="{{ $product->product_quantity == 0 ? 'table-danger' : 'table-warning' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><code>{{ $product->product_sku ?? '-' }}</code></td>
                                        <td>
                                            <a href="{{ route('products.show', $product->id) }}">
                                                {{ $product->product_name }}
                                            </a>
                                        </td>
                                        <td>{{ $product->category->category_name ?? '-' }}</td>
                                        <td>{{ $product->brand->brand_name ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($product->product_quantity == 0)
                                                <span class="badge bg-danger">HABIS</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ $product->product_quantity }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $product->product_stock_alert }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">
                                                {{ max(0, $product->product_stock_alert - $product->product_quantity) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="Edit Stok">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('adjustments.create') }}?product_id={{ $product->id }}" class="btn btn-sm btn-success" title="Adjustment Stok">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-success">
                                            <i class="bi bi-check-circle"></i> Tidak ada produk dengan stok rendah
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        @if($low_stock_products->count() > 0)
        $('#stockAlertTable').DataTable({
            order: [[5, 'asc']], // Sort by stock quantity ascending
            pageLength: 25,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ produk",
                infoEmpty: "Tidak ada data",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "»",
                    previous: "«"
                }
            }
        });
        @endif
    });
</script>
@endpush
