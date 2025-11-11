<div>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form wire:submit="generateReport">
                        <div class="form-row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input wire:model="start_date" type="date" class="form-control" name="start_date">
                                    @error('start_date')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>End Date <span class="text-danger">*</span></label>
                                    <input wire:model="end_date" type="date" class="form-control" name="end_date">
                                    @error('end_date')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select wire:model="customer_id" class="form-control" name="customer_id">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select wire:model="sale_status" class="form-control" name="sale_status">
                                        <option value="">Select Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Shipped">Shipped</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select wire:model="payment_status" class="form-control" name="payment_status">
                                        <option value="">Select Payment Status</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Unpaid">Unpaid</option>
                                        <option value="Partial">Partial</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <span wire:target="generateReport" wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <i wire:target="generateReport" wire:loading.remove class="bi bi-shuffle"></i>
                                Filter Report
                            </button>
                            <button type="button" class="btn btn-success ml-2" onclick="printReport()">
                                <i class="bi bi-printer-fill"></i> Print
                            </button>
                            <button type="button" class="btn btn-danger ml-2" onclick="downloadPDF()">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                            </button>
                            <button type="button" class="btn btn-info ml-2" onclick="downloadExcel()">
                                <i class="bi bi-file-earmark-spreadsheet-fill"></i> Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-striped text-center mb-0">
                        <div wire:loading.flex class="col-12 position-absolute justify-content-center align-items-center" style="top:0;right:0;left:0;bottom:0;background-color: rgba(255,255,255,0.5);z-index: 99;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</td>
                                <td>{{ $sale->reference }}</td>
                                <td>{{ $sale->customer_name }}</td>
                                <td>
                                    @if ($sale->status == 'Pending')
                                        <span class="badge badge-info">
                                    {{ $sale->status }}
                                </span>
                                    @elseif ($sale->status == 'Shipped')
                                        <span class="badge badge-primary">
                                    {{ $sale->status }}
                                </span>
                                    @else
                                        <span class="badge badge-success">
                                    {{ $sale->status }}
                                </span>
                                    @endif
                                </td>
                                <td>{{ format_currency($sale->total_amount) }}</td>
                                <td>{{ format_currency($sale->paid_amount) }}</td>
                                <td>{{ format_currency($sale->due_amount) }}</td>
                                <td>
                                    @if ($sale->payment_status == 'Partial')
                                        <span class="badge badge-warning">
                                    {{ $sale->payment_status }}
                                </span>
                                    @elseif ($sale->payment_status == 'Paid')
                                        <span class="badge badge-success">
                                    {{ $sale->payment_status }}
                                </span>
                                    @else
                                        <span class="badge badge-danger">
                                    {{ $sale->payment_status }}
                                </span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <span class="text-danger">No Sales Data Available!</span>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div @class(['mt-3' => $sales->hasPages()])>
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .card, .form-group, .btn, nav, .breadcrumb, header, footer, aside {
                display: none !important;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table th, .table td {
                border: 1px solid #000;
                padding: 8px;
            }
            .table th {
                background-color: #f2f2f2;
            }
        }
    </style>
    <script>
        // Initialize print protection flag
        window.printInProgress = window.printInProgress || false;
        
        function printReport() {
            if (window.printInProgress) {
                return;
            }
            
            window.printInProgress = true;
            
            // Gunakan print langsung dari halaman current untuk konsistensi
            setTimeout(function() {
                // Hide semua elemen yang tidak perlu untuk print
                document.querySelectorAll('.card-body form, .btn, nav, .breadcrumb, header, footer, aside, .c-sidebar, .c-header').forEach(function(el) {
                    if (el) el.style.display = 'none';
                });
                
                // Print halaman
                window.print();
                
                // Restore semua elemen setelah print
                setTimeout(function() {
                    document.querySelectorAll('.card-body form, .btn, nav, .breadcrumb, header, footer, aside, .c-sidebar, .c-header').forEach(function(el) {
                        if (el) el.style.display = '';
                    });
                    window.printInProgress = false;
                }, 1000);
            }, 100);
        }
        
        function downloadPDF() {
            // Build URL dengan filter parameters untuk PDF download
            var params = new URLSearchParams();
            params.append('start_date', '{{ $this->start_date }}');
            params.append('end_date', '{{ $this->end_date }}');
            @if($this->customer_id)
                params.append('customer_id', '{{ $this->customer_id }}');
            @endif
            @if($this->sale_status)
                params.append('sale_status', '{{ $this->sale_status }}');
            @endif
            @if($this->payment_status)
                params.append('payment_status', '{{ $this->payment_status }}');
            @endif
            params.append('export', 'pdf');
            
            // Buka di window baru untuk download PDF, tidak redirect halaman utama
            var pdfWindow = window.open('{{ route("sales-report.index") }}?' + params.toString(), '_blank');
            
            // Jika PDF gagal dan redirect ke print view, window baru sudah terbuka
            // Tidak perlu handle lebih lanjut karena browser akan handle download
        }
        
        function downloadExcel() {
            // Build URL dengan filter parameters untuk CSV export
            var params = new URLSearchParams();
            params.append('start_date', '{{ $this->start_date }}');
            params.append('end_date', '{{ $this->end_date }}');
            @if($this->customer_id)
                params.append('customer_id', '{{ $this->customer_id }}');
            @endif
            @if($this->sale_status)
                params.append('sale_status', '{{ $this->sale_status }}');
            @endif
            @if($this->payment_status)
                params.append('payment_status', '{{ $this->payment_status }}');
            @endif
            
            window.location.href = '{{ route("sales-report.export-csv") }}?' + params.toString();
        }
    </script>
</div>
