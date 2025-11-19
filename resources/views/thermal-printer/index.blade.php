@extends('layouts.app')

@section('title', 'Thermal Printer Settings')

@section('third_party_stylesheets')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Thermal Printer Settings</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-printer me-2"></i>
                            Thermal Printer Settings
                        </h4>
                        <div class="btn-group">
                            <a href="{{ route('thermal-printer.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add New Printer
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear me-1"></i>
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportSettings()">
                                            <i class="bi bi-download me-1"></i>
                                            Export Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                            <i class="bi bi-upload me-1"></i>
                                            Import Settings
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="testAllPrinters()">
                                            <i class="bi bi-lightning me-1"></i>
                                            Test All Printers
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Emergency Controls untuk fix masalah kertas roll -->
                        <div class="alert alert-warning mb-3">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Emergency Controls - Fix Infinite Paper Rolling
                            </h6>
                            <p class="mb-3">Jika kertas thermal terus bergulir tanpa henti, gunakan controls berikut:</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="emergency-printer-ip" 
                                               placeholder="Printer IP (e.g. 192.168.1.100)" value="192.168.1.100">
                                        <input type="number" class="form-control" id="emergency-printer-port" 
                                               placeholder="Port" value="9100" style="max-width: 80px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-danger" onclick="emergencyStopPrinter()">
                                            <i class="bi bi-stop-circle me-1"></i>
                                            Emergency Stop
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="fixPrinterSettings()">
                                            <i class="bi bi-gear me-1"></i>
                                            Fix Settings
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="testFixedPrint()">
                                            <i class="bi bi-printer me-1"></i>
                                            Test Fixed Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <small class="text-muted">
                                <strong>Tips:</strong> Emergency Stop untuk menghentikan printer sekarang. Fix Settings untuk mencegah masalah di masa depan.
                            </small>
                        </div>

                        @if ($printers->count() > 0)
                            <div class="row">
                                @foreach ($printers as $printer)
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card h-100 {{ $printer->is_default ? 'border-primary' : '' }}">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <i class="bi bi-printer me-1"></i>
                                                    {{ $printer->name }}
                                                    @if ($printer->is_default)
                                                        <span class="badge bg-primary ms-2">Default</span>
                                                    @endif
                                                    @if (!$printer->is_active)
                                                        <span class="badge bg-secondary ms-2">Inactive</span>
                                                    @endif
                                                </h6>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('thermal-printer.show', $printer) }}">
                                                                <i class="bi bi-eye me-1"></i>
                                                                View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('thermal-printer.edit', $printer) }}">
                                                                <i class="bi bi-pencil me-1"></i>
                                                                Edit
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        @if (!$printer->is_default)
                                                            <li>
                                                                <form action="{{ route('thermal-printer.set-default', $printer) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-star me-1"></i>
                                                                        Set as Default
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <button class="dropdown-item" onclick="testConnection({{ $printer->id }})">
                                                                <i class="bi bi-wifi me-1"></i>
                                                                Test Connection
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="printTest({{ $printer->id }})">
                                                                <i class="bi bi-printer me-1"></i>
                                                                Print Test
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('thermal-printer.destroy', $printer) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this printer setting?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bi bi-trash me-1"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted">Brand</small>
                                                        <div class="fw-bold">{{ $printer->brand ?: 'Generic' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Model</small>
                                                        <div class="fw-bold">{{ $printer->model ?: 'Unknown' }}</div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted">Connection</small>
                                                        <div>
                                                            <span class="badge bg-info">{{ ucfirst($printer->connection_type) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Paper Width</small>
                                                        <div class="fw-bold">{{ $printer->paper_width }}mm</div>
                                                    </div>
                                                </div>
                                                @if ($printer->connection_type === 'ethernet' || $printer->connection_type === 'wifi')
                                                    <div class="mb-3">
                                                        <small class="text-muted">IP Address</small>
                                                        <div class="fw-bold">{{ $printer->ip_address }}:{{ $printer->port }}</div>
                                                    </div>
                                                @endif
                                                @if ($printer->connection_type === 'serial')
                                                    <div class="mb-3">
                                                        <small class="text-muted">Serial Port</small>
                                                        <div class="fw-bold">{{ $printer->serial_port }} ({{ $printer->baud_rate }})</div>
                                                    </div>
                                                @endif
                                                @if ($printer->connection_type === 'bluetooth')
                                                    <div class="mb-3">
                                                        <small class="text-muted">Bluetooth Address</small>
                                                        <div class="fw-bold">{{ $printer->bluetooth_address }}</div>
                                                    </div>
                                                @endif
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">Print Settings</small>
                                                        <div class="small">
                                                            Speed: Level {{ $printer->print_speed }}<br>
                                                            Density: Level {{ $printer->print_density }}
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Features</small>
                                                        <div class="small">
                                                            @if ($printer->auto_cut)
                                                                <span class="badge bg-success me-1">Auto Cut</span>
                                                            @endif
                                                            @if ($printer->buzzer_enabled)
                                                                <span class="badge bg-warning me-1">Buzzer</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer" data-printer-id="{{ $printer->id }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Updated {{ $printer->updated_at->diffForHumans() }}
                                                    </small>
                                                    <div class="connection-status">
                                                        <span class="badge bg-secondary">Unknown</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-printer display-1 text-muted mb-3"></i>
                                <h4>No Thermal Printers Configured</h4>
                                <p class="text-muted">Get started by adding your first thermal printer configuration.</p>
                                <a href="{{ route('thermal-printer.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Add First Printer
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('thermal-printer.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Printer Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="settings_file" class="form-label">Settings File (JSON)</label>
                            <input type="file" class="form-control" id="settings_file" name="settings_file" accept=".json" required>
                            <div class="form-text">Select a JSON file exported from this system.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('third_party_scripts')
    <script src="{{ asset('js/app.js') }}"></script>
@endsection

@push('page_scripts')
<script>
    // Helper to safely read CSRF token meta tag
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Test printer connection
    function testConnection(printerId) {
        const statusElement = document.querySelector(`[data-printer-id="${printerId}"] .connection-status`);
        
        // Check if element exists
        if (!statusElement) {
            console.error('Status element not found for printer ID:', printerId);
            showToast('error', 'UI element not found');
            return;
        }
        
        statusElement.innerHTML = '<span class="badge bg-info">Testing...</span>';
        
        fetch(`/thermal-printer/${printerId}/test-connection`)
            .then(response => response.json())
            .then(data => {
                let badgeClass = 'bg-danger';
                let statusText = data.status;
                
                if (data.status === 'success') {
                    badgeClass = 'bg-success';
                    statusText = 'Connected';
                } else if (data.status === 'warning') {
                    badgeClass = 'bg-warning';
                    statusText = 'Warning';
                } else {
                    statusText = 'Failed';
                }
                
                statusElement.innerHTML = `<span class="badge ${badgeClass}">${statusText}</span>`;
                
                // Show toast notification
                showToast(data.status, data.message);
            })
            .catch(error => {
                console.error('Connection test error:', error);
                if (statusElement) {
                    statusElement.innerHTML = '<span class="badge bg-danger">Error</span>';
                }
                showToast('error', 'Connection test failed: ' + error.message);
            });
    }

    // Print test page
    function printTest(printerId) {
        fetch(`/thermal-printer/${printerId}/print-test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.status, data.message);
        })
        .catch(error => {
            showToast('error', 'Print test failed');
        });
    }

    // Test all printers
    function testAllPrinters() {
        const printerElements = document.querySelectorAll('[data-printer-id]');
        
        if (printerElements.length === 0) {
            console.log('No printers found to test');
            return;
        }
        
        printerElements.forEach((element, index) => {
            const printerId = element.getAttribute('data-printer-id');
            if (printerId) {
                // Stagger the requests to avoid overwhelming the server
                setTimeout(() => testConnection(printerId), index * 500);
            }
        });
    }

    // Export settings
    function exportSettings() {
        window.location.href = '{{ route("thermal-printer.export") }}';
    }

    // Show toast notification
    function showToast(type, message) {
        try {
            // Check if Bootstrap is available
            if (typeof bootstrap === 'undefined') {
                // Fallback to simple alert or console log
                console.log(`${type.toUpperCase()}: ${message}`);
                
                // Create simple notification div
                const notification = document.createElement('div');
                notification.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                `;
                
                document.body.appendChild(notification);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 5000);
                
                return;
            }
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            // Add to toast container (create if doesn't exist)
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            
            // Initialize and show toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
            
        } catch (error) {
            // Final fallback
            console.error('Toast notification error:', error);
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }

    // Emergency stop printer
    function emergencyStopPrinter(evt) {
        const ipEl = document.getElementById('emergency-printer-ip');
        const portEl = document.getElementById('emergency-printer-port');
        const ip = ipEl ? ipEl.value : '';
        const port = portEl ? portEl.value : '';
        
        if (!ip) {
            showToast('error', 'Please enter printer IP address');
            return;
        }
        
        let button = (evt && (evt.currentTarget || evt.target)) || (typeof event !== 'undefined' && (event.currentTarget || event.target)) || document.activeElement;
        if (button && button.closest) {
            button = button.closest('button') || button;
        }
        const originalContent = button && button.innerHTML ? button.innerHTML : '';
        if (button) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Stopping...';
            button.disabled = true;
        }
        
        fetch('{{ route("thermal-printer.emergency-stop") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                printer_ip: ip,
                port: parseInt(port)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
            
            if (data.success) {
                showToast('success', data.message);
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
            showToast('error', 'Emergency stop failed: ' + error.message);
        });
    }

    // Fix printer settings
    function fixPrinterSettings(evt) {
        let button = (evt && (evt.currentTarget || evt.target)) || (typeof event !== 'undefined' && (event.currentTarget || event.target)) || document.activeElement;
        if (button && button.closest) {
            button = button.closest('button') || button;
        }
        const originalContent = button && button.innerHTML ? button.innerHTML : '';
        if (button) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fixing...';
            button.disabled = true;
        }

        fetch('{{ route("thermal-printer.fix-settings") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => {
            if (response.ok) {
                showToast('success', 'Printer settings fixed! Page will reload...');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error('Fix settings failed');
            }
        })
        .catch(error => {
            if (button) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
            showToast('error', 'Fix settings failed: ' + error.message);
        });
    }

    // Test fixed print
    function testFixedPrint(evt) {
        const ipEl = document.getElementById('emergency-printer-ip');
        const portEl = document.getElementById('emergency-printer-port');
        const ip = ipEl ? ipEl.value : '';
        const port = portEl ? portEl.value : '';

        if (!ip) {
            showToast('error', 'Please enter printer IP address');
            return;
        }

        let button = (evt && (evt.currentTarget || evt.target)) || (typeof event !== 'undefined' && (event.currentTarget || event.target)) || document.activeElement;
        if (button && button.closest) {
            button = button.closest('button') || button;
        }
        const originalContent = button && button.innerHTML ? button.innerHTML : '';
        if (button) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing...';
            button.disabled = true;
        }

        fetch('{{ route("thermal-printer.index") }}/test-fixed-print', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                printer_ip: ip,
                port: parseInt(port)
            })
        })
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalContent;
            button.disabled = false;
            
            if (data.success) {
                showToast('success', data.message + ' (' + data.bytes_sent + ' bytes sent)');
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalContent;
            button.disabled = false;
            showToast('error', 'Test print failed: ' + error.message);
        });
    }

    // Auto-test connections on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Only auto-test if there are printers and we're not in an error state
        const printerCards = document.querySelectorAll('[data-printer-id]');
        const hasErrors = document.querySelector('.alert-danger');
        
        if (printerCards.length > 0 && !hasErrors) {
            console.log(`Found ${printerCards.length} printers, starting auto-test...`);
            // Test all connections after a short delay
            setTimeout(testAllPrinters, 2000);
        } else if (printerCards.length === 0) {
            console.log('No printers configured for testing');
        } else {
            console.log('Skipping auto-test due to page errors');
        }
    });
</script>
@endpush