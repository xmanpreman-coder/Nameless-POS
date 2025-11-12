@extends('layouts.app')

@section('title', 'Scanner Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-qrcode"></i> Scanner Settings - HTTP Request Configuration
                    </h4>
                    <p class="text-muted mb-0">Configure your barcode scanner app to connect to this POS system</p>
                </div>
                <div class="card-body">
                    
                    <!-- Quick Setup Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-mobile-alt"></i> Quick Setup for Scanner App</h5>
                                <p class="mb-2">Copy these settings to your barcode scanner mobile app:</p>
                                
                                <div class="form-group">
                                    <label class="font-weight-bold">Server URL:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="server-url" 
                                               value="{{ request()->getSchemeAndHttpHost() }}" readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('server-url')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">API Endpoint:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="api-endpoint" 
                                               value="{{ url('/api/scanner/scan') }}" readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('api-endpoint')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">HTTP Method:</label>
                                    <input type="text" class="form-control" value="POST" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h5><i class="fas fa-cog"></i> Connection Status</h5>
                                <div id="connection-status">
                                    <p><i class="fas fa-spinner fa-spin"></i> Checking connection...</p>
                                </div>
                                
                                <button class="btn btn-success btn-sm" onclick="testConnection()">
                                    <i class="fas fa-wifi"></i> Test Connection
                                </button>
                            </div>

                            <!-- QR Code for Easy Setup -->
                            <div class="text-center">
                                <h6>Scan QR Code for Quick Setup:</h6>
                                <div id="qr-code"></div>
                                <small class="text-muted">Scan with your scanner app for automatic configuration</small>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Configuration -->
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab">
                                        Basic Setup
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="advanced-tab" data-toggle="tab" href="#advanced" role="tab">
                                        Advanced Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="testing-tab" data-toggle="tab" href="#testing" role="tab">
                                        Test & Debug
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="apps-tab" data-toggle="tab" href="#apps" role="tab">
                                        Supported Apps
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="configTabContent">
                                <!-- Basic Setup Tab -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="mt-3">
                                        <h5>Step-by-Step Setup:</h5>
                                        <ol>
                                            <li>
                                                <strong>Download Scanner App</strong>
                                                <p>Install "Barcode to PC" or similar app on your mobile device</p>
                                            </li>
                                            <li>
                                                <strong>Configure Server Settings</strong>
                                                <div class="ml-3 mt-2">
                                                    <table class="table table-sm table-bordered">
                                                        <tr>
                                                            <td><strong>Server URL:</strong></td>
                                                            <td>
                                                                <code id="display-server-url">{{ request()->getSchemeAndHttpHost() }}</code>
                                                                <button class="btn btn-sm btn-link p-0 ml-1" onclick="copyToClipboard('display-server-url')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Port:</strong></td>
                                                            <td><code>{{ request()->getPort() ?: (request()->isSecure() ? '443' : '80') }}</code></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Endpoint Path:</strong></td>
                                                            <td><code>/api/scanner/scan</code></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Parameter Name:</strong></td>
                                                            <td><code>barcode</code></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </li>
                                            <li>
                                                <strong>Test Connection</strong>
                                                <p>Use the "Test Connection" button above to verify setup</p>
                                            </li>
                                        </ol>
                                    </div>
                                </div>

                                <!-- Advanced Settings Tab -->
                                <div class="tab-pane fade" id="advanced" role="tabpanel">
                                    <div class="mt-3">
                                        <h5>Advanced Configuration:</h5>
                                        
                                        <div class="form-group">
                                            <label><strong>Custom Headers (if required):</strong></label>
                                            <textarea class="form-control" rows="3" readonly>Content-Type: application/x-www-form-urlencoded
Accept: application/json
User-Agent: BarcodeScanner/1.0</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Request Body Format:</strong></label>
                                            <pre class="bg-light p-2"><code>barcode=YOUR_SCANNED_CODE
source=mobile_app
timestamp=CURRENT_TIMESTAMP</code></pre>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Expected Response Format:</strong></label>
                                            <pre class="bg-light p-2"><code>{
  "success": true,
  "message": "Product found",
  "barcode": "998127912363",
  "actual_barcode": "8998127912363",
  "reconstructed": true,
  "product": {
    "id": 16,
    "name": "dunhill",
    "code": "DUN001",
    "price": 25000
  }
}</code></pre>
                                        </div>

                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle"></i> Network Configuration:</h6>
                                            <p>Ensure both devices are on the same network:</p>
                                            <ul class="mb-0">
                                                <li>Mobile device and POS computer must be connected to same WiFi</li>
                                                <li>Firewall should allow incoming connections on port {{ request()->getPort() ?: '80' }}</li>
                                                <li>If using HTTPS, ensure SSL certificate is valid</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Testing Tab -->
                                <div class="tab-pane fade" id="testing" role="tabpanel">
                                    <div class="mt-3">
                                        <h5>Test Scanner Configuration:</h5>
                                        
                                        <div class="form-group">
                                            <label for="test-barcode">Test Barcode:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="test-barcode" 
                                                       placeholder="Enter barcode to test" value="998127912363">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" onclick="testBarcode()">
                                                        <i class="fas fa-play"></i> Test
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="test-results" class="mt-3">
                                            <!-- Test results will appear here -->
                                        </div>

                                        <h6 class="mt-4">Debug Information:</h6>
                                        <div class="bg-dark text-light p-3 rounded" style="height: 300px; overflow-y: auto;" id="debug-console">
                                            <div class="text-success">[INFO] Scanner settings page loaded</div>
                                            <div class="text-info">[INFO] Server URL: {{ request()->getSchemeAndHttpHost() }}</div>
                                            <div class="text-info">[INFO] API Endpoint: {{ route('scanner.external.receive') }}</div>
                                        </div>
                                        
                                        <button class="btn btn-secondary btn-sm mt-2" onclick="clearDebugConsole()">
                                            <i class="fas fa-trash"></i> Clear Console
                                        </button>
                                    </div>
                                </div>

                                <!-- Supported Apps Tab -->
                                <div class="tab-pane fade" id="apps" role="tabpanel">
                                    <div class="mt-3">
                                        <h5>Supported Scanner Apps:</h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6><i class="fab fa-android"></i> Barcode to PC (Recommended)</h6>
                                                        <p class="small text-muted">Best compatibility with this POS system</p>
                                                        <p><strong>Settings:</strong></p>
                                                        <ul class="small">
                                                            <li>Server: <code>{{ request()->getSchemeAndHttpHost() }}</code></li>
                                                            <li>Port: <code>{{ request()->getPort() ?: (request()->isSecure() ? '443' : '80') }}</code></li>
                                                            <li>Path: <code>/api/scanner/scan</code></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6><i class="fas fa-qrcode"></i> QR & Barcode Scanner</h6>
                                                        <p class="small text-muted">Alternative scanner app</p>
                                                        <p><strong>Settings:</strong></p>
                                                        <ul class="small">
                                                            <li>HTTP POST method</li>
                                                            <li>Parameter name: <code>barcode</code></li>
                                                            <li>Full URL: <code>{{ route('scanner.external.receive') }}</code></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-lightbulb"></i> Tips for Better Scanning:</h6>
                                            <ul class="mb-0">
                                                <li>Ensure good lighting when scanning</li>
                                                <li>Hold phone steady and focus on barcode</li>
                                                <li>Clean phone camera lens for better reading</li>
                                                <li>Keep mobile and POS on same WiFi network</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR code for easy setup
    generateQRCode();
    
    // Test initial connection
    testConnection();
    
    // Add debug info
    addDebugInfo('Page loaded successfully');
});

function generateQRCode() {
    const setupData = {
        serverUrl: '{{ request()->getSchemeAndHttpHost() }}',
        endpoint: '{{ url('/api/scanner/scan') }}',
        method: 'POST',
        parameter: 'barcode'
    };
    
    QRCode.toCanvas(document.getElementById('qr-code'), JSON.stringify(setupData), {
        width: 200,
        margin: 2
    }, function (error) {
        if (error) {
            console.error('QR Code generation failed:', error);
            document.getElementById('qr-code').innerHTML = '<p class="text-danger">QR Code generation failed</p>';
        }
    });
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent || element.value;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        const button = element.nextElementSibling?.querySelector('button') || 
                      element.parentElement?.querySelector('button');
        if (button) {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 1000);
        }
        
        addDebugInfo(`Copied to clipboard: ${text}`);
    }).catch(function(error) {
        console.error('Copy failed:', error);
        addDebugInfo(`Copy failed: ${error.message}`, 'error');
    });
}

function testConnection() {
    const statusDiv = document.getElementById('connection-status');
    statusDiv.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Testing connection...</p>';
    
    fetch('/api/scanner/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ barcode: 'TEST_CONNECTION' })
    })
    .then(response => {
        if (response.ok) {
            statusDiv.innerHTML = `
                <p><i class="fas fa-check-circle text-success"></i> Connection successful!</p>
                <small class="text-muted">API endpoint is responding correctly</small>
            `;
            addDebugInfo('Connection test successful', 'success');
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `
            <p><i class="fas fa-times-circle text-danger"></i> Connection failed</p>
            <small class="text-danger">Error: ${error.message}</small>
        `;
        addDebugInfo(`Connection test failed: ${error.message}`, 'error');
    });
}

function testBarcode() {
    const barcode = document.getElementById('test-barcode').value;
    const resultsDiv = document.getElementById('test-results');
    
    if (!barcode) {
        resultsDiv.innerHTML = '<div class="alert alert-warning">Please enter a barcode to test</div>';
        return;
    }
    
    resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Testing barcode...</div>';
    addDebugInfo(`Testing barcode: ${barcode}`);
    
    fetch('/api/scanner/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ barcode: barcode })
    })
    .then(response => response.json())
    .then(data => {
        let alertClass = data.success ? 'alert-success' : 'alert-warning';
        let icon = data.success ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        resultsDiv.innerHTML = `
            <div class="alert ${alertClass}">
                <h6><i class="fas ${icon}"></i> Test Result:</h6>
                <p><strong>Message:</strong> ${data.message}</p>
                ${data.success ? `
                    <p><strong>Product:</strong> ${data.product?.name || 'N/A'}</p>
                    <p><strong>Actual Barcode:</strong> ${data.actual_barcode}</p>
                    <p><strong>Reconstructed:</strong> ${data.reconstructed ? 'Yes' : 'No'}</p>
                ` : ''}
                <pre class="mt-2 bg-light p-2"><code>${JSON.stringify(data, null, 2)}</code></pre>
            </div>
        `;
        
        addDebugInfo(`Test result: ${data.success ? 'SUCCESS' : 'FAILED'} - ${data.message}`, data.success ? 'success' : 'warning');
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-times-circle"></i> Test Failed:</h6>
                <p>Error: ${error.message}</p>
            </div>
        `;
        addDebugInfo(`Test error: ${error.message}`, 'error');
    });
}

function addDebugInfo(message, type = 'info') {
    const console = document.getElementById('debug-console');
    const timestamp = new Date().toLocaleTimeString();
    const colors = {
        info: 'text-info',
        success: 'text-success', 
        warning: 'text-warning',
        error: 'text-danger'
    };
    
    const div = document.createElement('div');
    div.className = colors[type] || 'text-info';
    div.textContent = `[${timestamp}] ${message}`;
    
    console.appendChild(div);
    console.scrollTop = console.scrollHeight;
}

function clearDebugConsole() {
    document.getElementById('debug-console').innerHTML = '';
    addDebugInfo('Console cleared');
}
</script>
@endsection