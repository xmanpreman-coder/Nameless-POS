

<?php $__env->startSection('title', 'External Scanner Setup'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('scanner.index')); ?>">Scanner</a></li>
        <li class="breadcrumb-item active">External Scanner Setup</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> External Scanner App Setup</h5>
                </div>
                <div class="card-body">
                    <!-- App Integration Tabs -->
                    <ul class="nav nav-tabs" id="setupTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="barcode-to-pc-tab" data-toggle="tab" href="#barcode-to-pc" role="tab">
                                Barcode to PC
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="wifi-scanner-tab" data-toggle="tab" href="#wifi-scanner" role="tab">
                                WiFi Scanner
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bluetooth-scanner-tab" data-toggle="tab" href="#bluetooth-scanner" role="tab">
                                Bluetooth Scanner
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="api-integration-tab" data-toggle="tab" href="#api-integration" role="tab">
                                API Integration
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="setupTabContent">
                        <!-- Barcode to PC Setup -->
                        <div class="tab-pane fade show active" id="barcode-to-pc" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h6 class="text-primary"><i class="bi bi-phone"></i> Barcode to PC App Setup</h6>
                                    <p>Configure the "Barcode to PC" mobile app to send scans directly to your POS system.</p>
                                    
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Download App:</strong> Search "Barcode to PC" in Play Store or App Store
                                        <br>
                                        <a href="<?php echo e(route('scanner.barcode-to-pc-guide')); ?>" class="btn btn-info btn-sm mt-2">
                                            <i class="bi bi-book"></i> Detailed Setup Guide
                                        </a>
                                    </div>

                                    <h6>Configuration Steps:</h6>
                                    <ol>
                                        <li><strong>Install App:</strong> Download "Barcode to PC" on your mobile device</li>
                                        <li><strong>Connect to Same Network:</strong> Ensure mobile and PC are on same WiFi</li>
                                        <li><strong>Configure Server URL:</strong></li>
                                    </ol>

                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>App Configuration:</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Server URL:</strong></td>
                                                    <td>
                                                        <code id="server-url"><?php echo e(url('/api/scanner/scan')); ?></code>
                                                        <button class="btn btn-sm btn-outline-primary ml-2" onclick="copyToClipboard('server-url')">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Method:</strong></td>
                                                    <td><code>POST</code></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Content Type:</strong></td>
                                                    <td><code>application/json</code></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Payload Format:</strong></td>
                                                    <td><code>{"barcode": "${BARCODE}"}</code></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <h6>Alternative URLs:</h6>
                                        <ul>
                                            <li><code><?php echo e(url('/api/scanner/barcode')); ?></code> - Alternative endpoint</li>
                                            <li><code><?php echo e(url('/api/scanner/receive')); ?></code> - Generic receiver</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Quick Test</h6>
                                        </div>
                                        <div class="card-body">
                                            <p>Test the connection:</p>
                                            <div class="form-group">
                                                <input type="text" id="test-barcode" class="form-control" placeholder="Enter test barcode">
                                            </div>
                                            <button id="test-connection" class="btn btn-success btn-sm">
                                                <i class="bi bi-play"></i> Test Connection
                                            </button>
                                            <div id="test-result" class="mt-2"></div>
                                        </div>
                                    </div>

                                    <div class="card border-warning mt-3">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="scanner-status">
                                                <i class="bi bi-circle-fill text-success"></i> Ready to receive scans
                                            </div>
                                            <small class="text-muted">Last scan: <span id="last-scan">None</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WiFi Scanner Setup -->
                        <div class="tab-pane fade" id="wifi-scanner" role="tabpanel">
                            <h6 class="text-primary"><i class="bi bi-wifi"></i> WiFi Scanner Setup</h6>
                            <p>Configure any WiFi-enabled barcode scanner to send data to your POS system.</p>
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6>Network Configuration:</h6>
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong>Target IP:</strong></td>
                                            <td><code id="current-ip"><?php echo e(request()->getHost()); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Port:</strong></td>
                                            <td><code><?php echo e(request()->getPort() ?: '80'); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Protocol:</strong></td>
                                            <td><code>HTTP<?php echo e(request()->isSecure() ? 'S' : ''); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Endpoint:</strong></td>
                                            <td><code>/api/scanner/scan</code></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-lg-6">
                                    <h6>Payload Example:</h6>
                                    <pre class="bg-light p-3"><code>{
  "barcode": "1234567890123",
  "timestamp": "<?php echo e(now()->toISOString()); ?>",
  "device_id": "scanner_001"
}</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Bluetooth Scanner Setup -->
                        <div class="tab-pane fade" id="bluetooth-scanner" role="tabpanel">
                            <h6 class="text-primary"><i class="bi bi-bluetooth"></i> Bluetooth Scanner Setup</h6>
                            <p>Configure Bluetooth barcode scanners to work with the POS system.</p>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Most Bluetooth scanners work as keyboard input devices. No special configuration needed.
                            </div>

                            <h6>Setup Steps:</h6>
                            <ol>
                                <li><strong>Pair Scanner:</strong> Pair your Bluetooth scanner with this device</li>
                                <li><strong>Set Keyboard Mode:</strong> Configure scanner for keyboard emulation mode</li>
                                <li><strong>Test Input:</strong> Click in any search field and scan a barcode</li>
                                <li><strong>Auto-Detection:</strong> System will automatically detect barcode input</li>
                            </ol>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Supported Scanner Modes:</h6>
                                    <ul>
                                        <li><i class="bi bi-check text-success"></i> HID Keyboard Mode</li>
                                        <li><i class="bi bi-check text-success"></i> SPP (Serial Port Profile)</li>
                                        <li><i class="bi bi-check text-success"></i> Auto-Enter suffix</li>
                                        <li><i class="bi bi-check text-success"></i> Custom prefix/suffix</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- API Integration -->
                        <div class="tab-pane fade" id="api-integration" role="tabpanel">
                            <h6 class="text-primary"><i class="bi bi-code-slash"></i> API Integration</h6>
                            <p>Integrate with custom scanner applications using our API.</p>

                            <div class="row">
                                <div class="col-lg-6">
                                    <h6>Available Endpoints:</h6>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Method</th>
                                                <th>Endpoint</th>
                                                <th>Purpose</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge badge-success">POST</span></td>
                                                <td><code>/api/scanner/scan</code></td>
                                                <td>Single barcode scan</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-success">POST</span></td>
                                                <td><code>/api/scanner/batch-scan</code></td>
                                                <td>Multiple barcodes</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-info">GET</span></td>
                                                <td><code>/api/scanner/config</code></td>
                                                <td>Get configuration</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge badge-info">GET</span></td>
                                                <td><code>/api/scanner/status</code></td>
                                                <td>Check status</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-lg-6">
                                    <h6>Response Format:</h6>
<pre class="bg-light p-3"><code>{
  "success": true,
  "message": "Product found",
  "barcode": "1234567890",
  "product": {
    "id": 1,
    "name": "Product Name",
    "code": "PRD001",
    "price": 15000,
    "stock": 100
  },
  "timestamp": "2024-01-01T00:00:00Z"
}</code></pre>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6>JavaScript Integration:</h6>
<pre class="bg-dark text-white p-3"><code>// Send barcode via JavaScript
fetch('/api/scanner/scan', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        barcode: '1234567890123'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Product found:', data.product);
    } else {
        console.log('Product not found');
    }
});</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-center">
                        <a href="<?php echo e(route('scanner.index')); ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Scanner
                        </a>
                        <a href="<?php echo e(route('scanner.settings')); ?>" class="btn btn-primary">
                            <i class="bi bi-gear"></i> Scanner Settings
                        </a>
                        <button id="download-config" class="btn btn-info">
                            <i class="bi bi-download"></i> Download Config
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
<script src="<?php echo e(asset('js/external-scanner.js')); ?>"></script>
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        ScannerUtils.showNotification('URL copied to clipboard!', 'success');
    });
}

document.getElementById('test-connection').addEventListener('click', async function() {
    const barcode = document.getElementById('test-barcode').value.trim();
    const resultDiv = document.getElementById('test-result');
    
    if (!barcode) {
        resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a test barcode</div>';
        return;
    }
    
    try {
        const response = await fetch('/api/scanner/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ barcode: barcode })
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ Connection Success!</strong><br>
                    Product: ${data.product.name}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-warning">
                    <strong>⚠ Product Not Found</strong><br>
                    Barcode: ${barcode}
                </div>
            `;
        }
        
        document.getElementById('last-scan').textContent = new Date().toLocaleTimeString();
        
    } catch (error) {
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <strong>✗ Connection Failed</strong><br>
                Error: ${error.message}
            </div>
        `;
    }
});

document.getElementById('download-config').addEventListener('click', function() {
    const config = {
        endpoints: {
            scan: "<?php echo e(url('/api/scanner/scan')); ?>",
            batch: "<?php echo e(url('/api/scanner/batch-scan')); ?>",
            config: "<?php echo e(url('/api/scanner/config')); ?>",
            status: "<?php echo e(url('/api/scanner/status')); ?>"
        },
        server: {
            host: "<?php echo e(request()->getHost()); ?>",
            port: <?php echo e(request()->getPort() ?: 80); ?>,
            protocol: "<?php echo e(request()->isSecure() ? 'https' : 'http'); ?>"
        },
        payload_format: {
            single: '{"barcode": "${BARCODE}"}',
            batch: '{"barcodes": ["${BARCODE1}", "${BARCODE2}"]}'
        }
    };
    
    const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'nameless-pos-scanner-config.json';
    a.click();
    URL.revokeObjectURL(url);
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Scanner\Resources/views/external-setup.blade.php ENDPATH**/ ?>