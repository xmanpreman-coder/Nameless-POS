@extends('layouts.app')

@section('title', 'Scanner Settings')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('scanner.index') }}">Scanner</a></li>
        <li class="breadcrumb-item active">Settings</li>
    </ol>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @include('utils.alerts')
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Scanner Settings</h5>
                </div>
                <div class="card-body">
                    @php
                        $settings = \Modules\Scanner\Entities\ScannerSetting::getSettings();
                    @endphp
                    
                    <form action="{{ route('scanner.settings.update') }}" method="POST" id="scannerSettingsForm">
                        @csrf
                        <input type="hidden" name="beep_sound" value="0">
                        <input type="hidden" name="vibration" value="0">
                        <input type="hidden" name="auto_focus" value="0">
                        
                        <!-- Scanner Type Selection -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="scanner_type">Scanner Type <span class="text-danger">*</span></label>
                                    <select name="scanner_type" id="scanner_type" class="form-control" required>
                                        <option {{ $settings->scanner_type == 'camera' ? 'selected' : '' }} value="camera">
                                            Camera Scanner (Laptop/Phone)
                                        </option>
                                        <option {{ $settings->scanner_type == 'usb' ? 'selected' : '' }} value="usb">
                                            USB Barcode Scanner
                                        </option>
                                        <option {{ $settings->scanner_type == 'bluetooth' ? 'selected' : '' }} value="bluetooth">
                                            Bluetooth Scanner
                                        </option>
                                        <option {{ $settings->scanner_type == 'external' ? 'selected' : '' }} value="external">
                                            External Scanner Setup (Mobile App)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="scan_mode">Scan Mode <span class="text-danger">*</span></label>
                                    <select name="scan_mode" id="scan_mode" class="form-control" required>
                                        <option {{ $settings->scan_mode == 'auto' ? 'selected' : '' }} value="auto">
                                            Automatic Scan
                                        </option>
                                        <option {{ $settings->scan_mode == 'manual' ? 'selected' : '' }} value="manual">
                                            Manual Capture
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Settings -->
                        <div id="camera-settings" class="scanner-settings" style="{{ $settings->scanner_type !== 'camera' ? 'display: none;' : '' }}">
                            <h6 class="text-primary mt-4"><i class="bi bi-camera"></i> Camera Settings</h6>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="preferred_camera">Preferred Camera</label>
                                        <select name="preferred_camera" id="preferred_camera" class="form-control">
                                            <option {{ $settings->preferred_camera == 'back' ? 'selected' : '' }} value="back">
                                                Back Camera (Recommended)
                                            </option>
                                            <option {{ $settings->preferred_camera == 'front' ? 'selected' : '' }} value="front">
                                                Front Camera
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="scan_timeout">Scan Timeout (seconds)</label>
                                        <input type="number" name="scan_timeout" id="scan_timeout" 
                                               class="form-control" value="{{ $settings->scan_timeout }}" 
                                               min="5" max="120">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="auto_focus" id="auto_focus" 
                                               class="form-check-input" {{ $settings->auto_focus ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_focus">
                                            Auto Focus (Recommended)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Camera Tips:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Use back camera for better barcode scanning quality</li>
                                    <li>Ensure good lighting for optimal scanning</li>
                                    <li>Keep device steady when scanning</li>
                                </ul>
                            </div>
                        </div>

                        <!-- USB Scanner Settings -->
                        <div id="usb-settings" class="scanner-settings" style="{{ $settings->scanner_type !== 'usb' ? 'display: none;' : '' }}">
                            <h6 class="text-primary mt-4"><i class="bi bi-usb"></i> USB Scanner Settings</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Universal USB Scanner Support:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Auto-Detection:</strong> Supports all USB scanners as keyboard input</li>
                                    <li><strong>Compatible:</strong> CashCow, Honeywell, Symbol, Zebra, Datalogic, dll.</li>
                                    <li><strong>No Configuration:</strong> Plug & play - tidak perlu setting manual</li>
                                    <li><strong>Real-time:</strong> Deteksi otomatis saat scanner dihubungkan</li>
                                </ul>
                            </div>
                            
                            <!-- Auto-Detection Status -->
                            <div class="card border-success">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Auto-Detection Status</h6>
                                </div>
                                <div class="card-body">
                                    <div id="usb-auto-status" class="alert alert-secondary">
                                        <i class="bi bi-gear"></i> Initializing universal scanner detection...
                                    </div>
                                    <div id="detected-scanners" style="display: none;"></div>
                                </div>
                            </div>
                            
                            <!-- Universal Test -->
                            <div class="card border-primary mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-speedometer2"></i> Universal Scanner Test</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">Test any USB barcode scanner (semua merk/jenis):</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-lg" 
                                               id="universal-scanner-test" 
                                               placeholder="Scan barcode dengan scanner USB apapun..." 
                                               autocomplete="off"
                                               style="font-family: 'Courier New', monospace;">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="clearUniversalTest()">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-success">
                                        <i class="bi bi-check-circle"></i> Semua scanner USB akan langsung terdeteksi tanpa konfigurasi
                                    </small>
                                    
                                    <div id="universal-test-log" class="mt-3" style="background: #f8f9fa; border-radius: 5px; padding: 10px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; display: none;">
                                        <strong>Detection Log:</strong><br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bluetooth Scanner Settings -->
                        <div id="bluetooth-settings" class="scanner-settings" style="{{ $settings->scanner_type !== 'bluetooth' ? 'display: none;' : '' }}">
                            <h6 class="text-primary mt-4"><i class="bi bi-bluetooth"></i> Bluetooth Scanner Settings</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Bluetooth Scanner Setup:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Pair your Bluetooth scanner with this device</li>
                                    <li>Ensure scanner is in keyboard emulation mode</li>
                                    <li>Scanner should appear as connected input device</li>
                                    <li>Test connection using the scanner test below</li>
                                </ol>
                            </div>
                        </div>

                        <!-- External Scanner Settings -->
                        <div id="external-settings" class="scanner-settings" style="{{ $settings->scanner_type !== 'external' ? 'display: none;' : '' }}">
                            <h6 class="text-primary mt-4"><i class="bi bi-phone"></i> External Scanner Setup (Mobile App)</h6>
                            <div class="alert alert-success">
                                <i class="bi bi-info-circle"></i>
                                <strong>External Mobile Scanner Setup:</strong>
                                <p class="mb-2">Configure your mobile barcode scanner app to connect to this POS system via HTTP requests.</p>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bi bi-gear"></i> Scanner App Configuration</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Server URL:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="external-server-url" 
                                                           value="{{ url('/api/scanner/scan') }}" readonly>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('external-server-url')">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">HTTP Method:</label>
                                                <input type="text" class="form-control" value="POST" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Content Type:</label>
                                                <input type="text" class="form-control" value="application/json" readonly>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Payload Format:</label>
                                                <input type="text" class="form-control" value='{"barcode": "${BARCODE}"}' readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bi bi-check-circle"></i> Quick Setup</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div id="external-qr-code" class="mb-3"></div>
                                            <small class="text-muted">Scan QR code with your scanner app for automatic setup</small>
                                            
                                            <div class="mt-3">
                                                <button class="btn btn-success btn-sm" onclick="testExternalConnection()">
                                                    <i class="bi bi-wifi"></i> Test Connection
                                                </button>
                                            </div>
                                            
                                            <div id="external-connection-status" class="mt-3">
                                                <!-- Connection status will appear here -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('scanner.settings') }}" class="btn btn-info btn-block">
                                            <i class="bi bi-gear"></i> Advanced External Setup
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Recommended Apps:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Barcode to PC</strong> - Best compatibility with this system</li>
                                    <li><strong>QR & Barcode Scanner</strong> - Alternative with HTTP support</li>
                                    <li>Any barcode app that supports HTTP POST requests</li>
                                </ul>
                            </div>
                        </div>

                        <!-- General Settings -->
                        <h6 class="text-primary mt-4"><i class="bi bi-gear"></i> General Settings</h6>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="beep_sound" id="beep_sound" 
                                           class="form-check-input" {{ $settings->beep_sound ? 'checked' : '' }}>
                                    <label class="form-check-label" for="beep_sound">
                                        Beep Sound on Successful Scan
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="vibration" id="vibration" 
                                           class="form-check-input" {{ $settings->vibration ? 'checked' : '' }}>
                                    <label class="form-check-label" for="vibration">
                                        Vibration Feedback (Mobile)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Save Settings
                                </button>
                                <a href="{{ route('scanner.test-camera') }}" class="btn btn-info ml-2">
                                    <i class="bi bi-camera"></i> Test Camera
                                </a>
                            </div>
                            <a href="{{ route('scanner.scan') }}" class="btn btn-success">
                                <i class="bi bi-upc-scan"></i> Start Scanning
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<!-- Include QR Code Library for external scanner - using local alternative -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scannerType = document.getElementById('scanner_type');
    const cameraSettings = document.getElementById('camera-settings');
    const usbSettings = document.getElementById('usb-settings');
    const bluetoothSettings = document.getElementById('bluetooth-settings');
    const externalSettings = document.getElementById('external-settings');

    function toggleScannerSettings() {
        // Hide all settings
        cameraSettings.style.display = 'none';
        usbSettings.style.display = 'none';
        bluetoothSettings.style.display = 'none';
        externalSettings.style.display = 'none';

        // Show relevant settings
        const selectedType = scannerType.value;
        if (selectedType === 'camera') {
            cameraSettings.style.display = 'block';
        } else if (selectedType === 'usb') {
            usbSettings.style.display = 'block';
        } else if (selectedType === 'bluetooth') {
            bluetoothSettings.style.display = 'block';
        } else if (selectedType === 'external') {
            externalSettings.style.display = 'block';
            generateExternalQRCode();
        }
    }

    scannerType.addEventListener('change', toggleScannerSettings);
    
    // Initialize external settings if already selected
    if (scannerType.value === 'external') {
        generateExternalQRCode();
    }
});

function generateExternalQRCode() {
    const setupData = {
        name: "Nameless POS External Scanner",
        serverUrl: '{{ url('/api/scanner/scan') }}',
        method: 'POST',
        contentType: 'application/json',
        payloadFormat: '{"barcode": "${BARCODE}"}',
        type: 'external_scanner_setup'
    };
    
    const qrContainer = document.getElementById('external-qr-code');
    if (!qrContainer) return;
    
    qrContainer.innerHTML = ''; // Clear existing QR code
    
    try {
        // Check if qrcode library is loaded (qrcode-generator)
        if (typeof qrcode === 'undefined') {
            qrContainer.innerHTML = `
                <div class="text-center">
                    <p class="text-warning small">QR Code library not available</p>
                    <p class="small">Use manual configuration below</p>
                </div>
            `;
            return;
        }
        
        // Generate QR code using qrcode-generator library
        const qr = qrcode(0, 'M');
        qr.addData(JSON.stringify(setupData));
        qr.make();
        
        // Create QR code image and add to container
        qrContainer.innerHTML = qr.createImgTag(4, 8);
        
        console.log('QR Code generated successfully for external scanner');
    } catch (error) {
        console.error('QR Code generation failed:', error);
        qrContainer.innerHTML = `
            <div class="text-center">
                <p class="text-danger small">QR Code generation failed</p>
                <p class="small">Please use manual setup</p>
            </div>
        `;
    }
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value || element.textContent;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        const button = element.parentElement.querySelector('button');
        if (button) {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check text-success"></i>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 1500);
        }
        
        // Show toast notification
        showToast('Copied to clipboard!', 'success');
    }).catch(function(error) {
        console.error('Copy failed:', error);
        showToast('Copy failed: ' + error.message, 'error');
    });
}

function testExternalConnection() {
    const statusDiv = document.getElementById('external-connection-status');
    statusDiv.innerHTML = '<div class="alert alert-info alert-sm"><i class="bi bi-hourglass-split"></i> Testing connection...</div>';
    
    fetch('/api/scanner/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ barcode: 'TEST_EXTERNAL_CONNECTION' })
    })
    .then(response => {
        if (response.ok) {
            statusDiv.innerHTML = `
                <div class="alert alert-success alert-sm">
                    <i class="bi bi-check-circle"></i> Connection successful!
                    <br><small>External scanner endpoint is working</small>
                </div>
            `;
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `
            <div class="alert alert-danger alert-sm">
                <i class="bi bi-x-circle"></i> Connection failed
                <br><small>Error: ${error.message}</small>
            </div>
        `;
    });
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'} me-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush