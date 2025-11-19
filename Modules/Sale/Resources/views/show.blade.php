@extends('layouts.app')

@section('title', 'Sales Details')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
        <li class="breadcrumb-item active">Details</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center">
                        <div>
                            Reference: <strong>{{ $sale->reference }}</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mfs-auto mfe-1 d-print-none" onclick="openPrintWindow('sales', {{ $sale->id }})">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <div class="dropdown d-inline">
                            <button type="button" class="btn btn-sm btn-success dropdown-toggle mfe-1 d-print-none" data-bs-toggle="dropdown">
                                <i class="bi bi-receipt"></i> Thermal Print
                            </button>
                            <ul class="dropdown-menu" style="min-width: 280px;">
                                <li>
                                    <a class="dropdown-item bg-success bg-opacity-10 fw-bold" href="#" onclick="printWithThermalService({{ $sale->id }}); return false;">
                                        <i class="bi bi-printer me-1" style="color: #28a745;"></i> ✓ Use Default Printer
                                        <br><small class="text-muted d-block ms-4">Recommended - Direct to thermal printer</small>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-muted" href="#" onclick="showBrowserPrintWarning(); return false;" title="Not recommended - May print 2 pages">
                                        <i class="bi bi-exclamation-triangle me-1" style="color: #ffc107;"></i> Browser Print (80mm)
                                        <br><small class="text-muted d-block ms-4">⚠️ Not recommended (may print 2 pages)</small>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('thermal-printer.index') }}">
                                        <i class="bi bi-gear me-1"></i> Printer Settings
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <a target="_blank" class="btn btn-sm btn-info mfe-1 d-print-none" href="{{ route('sales.pdf', $sale->id) }}">
                            <i class="bi bi-save"></i> Save PDF
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">Company Info:</h5>
                                <div><strong>{{ settings()->company_name }}</strong></div>
                                <div>{{ settings()->company_address }}</div>
                                <div>Email: {{ settings()->company_email }}</div>
                                <div>Phone: {{ settings()->company_phone }}</div>
                            </div>

                            <div class="col-sm-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">Customer Info:</h5>
                                @if($customer)
                                    <div><strong>{{ $customer->customer_name }}</strong></div>
                                    <div>{{ $customer->address }}</div>
                                    <div>Email: {{ $customer->customer_email }}</div>
                                    <div>Phone: {{ $customer->customer_phone }}</div>
                                @else
                                    <div><em class="text-muted">Walk-in Customer (No details recorded)</em></div>
                                @endif
                            </div>

                            <div class="col-sm-4 mb-3 mb-md-0">
                                <h5 class="mb-2 border-bottom pb-2">Invoice Info:</h5>
                                <div>Invoice: <strong>INV/{{ $sale->reference }}</strong></div>
                                <div>Date: {{ \Carbon\Carbon::parse($sale->date)->format('d M, Y') }}</div>
                                <div>
                                    Status: <strong>{{ $sale->status }}</strong>
                                </div>
                                <div>
                                    Payment Status: <strong>{{ $sale->payment_status }}</strong>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive-sm">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="align-middle">Product</th>
                                    <th class="align-middle">Net Unit Price</th>
                                    <th class="align-middle">Quantity</th>
                                    <th class="align-middle">Discount</th>
                                    <th class="align-middle">Tax</th>
                                    <th class="align-middle">Sub Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sale->saleDetails as $item)
                                    <tr>
                                        <td class="align-middle">
                                            {{ $item->product_name }} <br>
                                            <span class="badge badge-success">
                                                {{ $item->product_sku }}
                                            </span>
                                        </td>

                                        <td class="align-middle">{{ format_currency($item->unit_price) }}</td>

                                        <td class="align-middle">
                                            {{ $item->quantity }}
                                        </td>

                                        <td class="align-middle">
                                            {{ format_currency($item->product_discount_amount) }}
                                        </td>

                                        <td class="align-middle">
                                            {{ format_currency($item->product_tax_amount) }}
                                        </td>

                                        <td class="align-middle">
                                            {{ format_currency($item->sub_total) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-sm-5 ml-md-auto">
                                <table class="table">
                                    <tbody>
                                    <tr>
                                        <td class="left"><strong>Discount ({{ $sale->discount_percentage }}%)</strong></td>
                                        <td class="right">{{ format_currency($sale->discount_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Tax ({{ $sale->tax_percentage }}%)</strong></td>
                                        <td class="right">{{ format_currency($sale->tax_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Shipping</strong></td>
                                        <td class="right">{{ format_currency($sale->shipping_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="left"><strong>Grand Total</strong></td>
                                        <td class="right"><strong>{{ format_currency($sale->total_amount) }}</strong></td>
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

@push('page_scripts')
<script>
    function openPrintWindow(type, id) {
        if (arguments.length === 1) {
            // Backward compatibility: jika hanya 1 parameter, anggap sebagai saleId
            id = type;
            type = 'sales';
        }
        
        const url = type === 'sales' 
            ? '{{ url("/sales/pos/print") }}/' + id
            : type === 'purchases'
            ? '{{ url("/purchases/print") }}/' + id
            : '{{ url("/quotations/print") }}/' + id;
        
        const printWindow = window.open(url, '_blank', 'width=500,height=700');
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 500);
        };
    }

    function openThermalPrintWindow(saleId) {
        const url = '{{ url("/sales/thermal/print") }}/' + saleId;
        // Open thermal preview in new window but do NOT auto-trigger browser print.
        // The thermal template provides its own "Thermal Print" button which calls
        // ESC/POS optimizations and `window.print()` if the user wants to use
        // the browser print dialog. Avoiding auto-print prevents duplicate
        // print dialogs and allows the user to select correct paper size.
        window.open(url, '_blank', 'width=400,height=600,scrollbars=yes');
    }

    function showBrowserPrintWarning() {
        // Create a Bootstrap alert/modal
        const warningHtml = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert" id="browser-print-warning" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-weight: bold; margin-bottom: 8px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Browser Print Not Recommended
                </div>
                <div style="font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
                    <p>The "Browser Print" option may print receipts on <strong>2 pages</strong> due to browser print dialog limitations.</p>
                    <p><strong>✓ Recommended:</strong> Use "<strong>Use Default Printer</strong>" option instead for direct printing to your thermal printer.</p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-warning me-2" onclick="document.getElementById('browser-print-warning').remove(); openThermalPrintWindow({{ $sale->id }});">
                    <i class="bi bi-exclamation-triangle me-1"></i> Continue Anyway
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('browser-print-warning').remove();">
                    Cancel
                </button>
            </div>
        `;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = warningHtml;
        document.body.appendChild(tempDiv.firstElementChild);
    }

    function printWithThermalService(saleId) {
        // Show loading indicator
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Printing...';
        button.disabled = true;

        // Send print request to thermal service
        // Use web route (CSRF-protected) for authenticated web calls
        fetch('{{ route("sales.thermal.print") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                sale_id: saleId
            })
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            button.innerHTML = originalContent;
            button.disabled = false;

            if (data.success) {
                showNotification('success', 'Receipt printed successfully to ' + (data.printer_name || 'thermal printer'));
            } else {
                showNotification('error', 'Print failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Restore button
            button.innerHTML = originalContent;
            button.disabled = false;
            
            console.error('Print error:', error);
            showNotification('error', 'Print failed: Network error');
        });
    }

    function showNotification(type, message) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        // Add to page
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }
        container.appendChild(toast);

        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove after hide
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
</script>
@endpush

