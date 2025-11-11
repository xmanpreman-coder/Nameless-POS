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
                                    <label>Supplier</label>
                                    <select wire:model="supplier_id" class="form-control" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select wire:model="purchase_return_status" class="form-control" name="purchase_return_status">
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
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($purchase_returns as $purchase_return)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($purchase_return->date)->format('d M, Y') }}</td>
                                <td>{{ $purchase_return->reference }}</td>
                                <td>{{ $purchase_return->supplier_name }}</td>
                                <td>
                                    @if ($purchase_return->status == 'Pending')
                                        <span class="badge badge-info">
                                            {{ $purchase_return->status }}
                                        </span>
                                            @elseif ($purchase_return->status == 'Shipped')
                                                <span class="badge badge-primary">
                                            {{ $purchase_return->status }}
                                        </span>
                                            @else
                                                <span class="badge badge-success">
                                            {{ $purchase_return->status }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ format_currency($purchase_return->total_amount) }}</td>
                                <td>{{ format_currency($purchase_return->paid_amount) }}</td>
                                <td>{{ format_currency($purchase_return->due_amount) }}</td>
                                <td>
                                    @if ($purchase_return->payment_status == 'Partial')
                                        <span class="badge badge-warning">
                                    {{ $purchase_return->payment_status }}
                                </span>
                                    @elseif ($purchase_return->payment_status == 'Paid')
                                        <span class="badge badge-success">
                                    {{ $purchase_return->payment_status }}
                                </span>
                                    @else
                                        <span class="badge badge-danger">
                                    {{ $purchase_return->payment_status }}
                                </span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <span class="text-danger">No Purchase Return Data Available!</span>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div @class(['mt-3' => $purchase_returns->hasPages()])>
                        {{ $purchase_returns->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function printReport() {
        var params = new URLSearchParams();
        params.append('start_date', '{{ $this->start_date }}');
        params.append('end_date', '{{ $this->end_date }}');
        @if($this->supplier_id)
            params.append('supplier_id', '{{ $this->supplier_id }}');
        @endif
        @if($this->purchase_return_status)
            params.append('purchase_return_status', '{{ $this->purchase_return_status }}');
        @endif
        @if($this->payment_status)
            params.append('payment_status', '{{ $this->payment_status }}');
        @endif
        
        // Use consistent print logic
        if (window.printInProgress) {
            return;
        }
        
        window.printInProgress = true;
        
        setTimeout(function() {
            document.querySelectorAll('.card-body form, .btn, nav, .breadcrumb, header, footer, aside, .c-sidebar, .c-header').forEach(function(el) {
                if (el) el.style.display = 'none';
            });
            
            window.print();
            
            setTimeout(function() {
                document.querySelectorAll('.card-body form, .btn, nav, .breadcrumb, header, footer, aside, .c-sidebar, .c-header').forEach(function(el) {
                    if (el) el.style.display = '';
                });
                window.printInProgress = false;
            }, 1000);
        }, 100);
    }
    
    function downloadExcel() {
        var params = new URLSearchParams();
        params.append('start_date', '{{ $this->start_date }}');
        params.append('end_date', '{{ $this->end_date }}');
        @if($this->supplier_id)
            params.append('supplier_id', '{{ $this->supplier_id }}');
        @endif
        @if($this->purchase_return_status)
            params.append('purchase_return_status', '{{ $this->purchase_return_status }}');
        @endif
        @if($this->payment_status)
            params.append('payment_status', '{{ $this->payment_status }}');
        @endif
        
        window.location.href = '{{ route("purchases-return-report.export-csv") }}?' + params.toString();
    }
</script>
