@extends('layouts.app')

@section('title', 'Barcode to PC Setup Guide')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('scanner.index') }}">Scanner</a></li>
        <li class="breadcrumb-item active">Barcode to PC Guide</li>
    </ol>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> Barcode to PC - Setup Guide</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Step-by-Step Guide -->
                        <div class="col-lg-8">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Barcode to PC</strong> adalah aplikasi mobile yang memungkinkan Anda menggunakan smartphone sebagai scanner barcode untuk PC/laptop Anda.
                            </div>

                            <!-- Step 1 -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">📱 Langkah 1: Download & Install App</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Android (Play Store):</h6>
                                            <p>Cari: <strong>"Barcode to PC: WiFi scanner"</strong></p>
                                            <p>Developer: <em>Filippo Tortomasi</em></p>
                                            <a href="https://play.google.com/store/apps/details?id=com.barcodetopc" target="_blank" class="btn btn-success btn-sm">
                                                <i class="bi bi-download"></i> Download Android
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>iOS (App Store):</h6>
                                            <p>Cari: <strong>"Barcode to PC: WiFi scanner"</strong></p>
                                            <p>Developer: <em>Filippo Tortomasi</em></p>
                                            <a href="https://apps.apple.com/app/barcode-to-pc-wifi-scanner/id1180168368" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="bi bi-download"></i> Download iOS
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">🌐 Langkah 2: Pastikan Koneksi WiFi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Penting:</strong> Smartphone dan PC/laptop harus terhubung ke WiFi yang sama!
                                    </div>
                                    <ul>
                                        <li>Pastikan HP dan PC terhubung ke WiFi yang sama</li>
                                        <li>Jika menggunakan hotspot HP, nyalakan hotspot dan sambungkan PC ke hotspot tersebut</li>
                                        <li>Pastikan firewall tidak memblokir koneksi</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">⚙️ Langkah 3: Konfigurasi App</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Buka app "Barcode to PC" dan ikuti langkah berikut:</h6>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Method 1: HTTP Request</h6>
                                            <ol>
                                                <li>Tap <strong>"Add server"</strong></li>
                                                <li>Pilih <strong>"HTTP Request"</strong></li>
                                                <li>Masukkan detail berikut:</li>
                                            </ol>
                                            
                                            <table class="table table-sm table-bordered">
                                                <tr>
                                                    <td><strong>Name:</strong></td>
                                                    <td>Nameless POS</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>URL:</strong></td>
                                                    <td><code id="http-url">{{ url('/api/scanner/scan') }}</code></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Method:</strong></td>
                                                    <td>POST</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Content Type:</strong></td>
                                                    <td>application/json</td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6 class="text-success">Method 2: WebSocket (Recommended)</h6>
                                            <ol>
                                                <li>Tap <strong>"Add server"</strong></li>
                                                <li>Pilih <strong>"WebSocket"</strong></li>
                                                <li>Masukkan detail berikut:</li>
                                            </ol>
                                            
                                            <table class="table table-sm table-bordered">
                                                <tr>
                                                    <td><strong>Name:</strong></td>
                                                    <td>Nameless POS WS</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Server Address:</strong></td>
                                                    <td><code id="ws-url">{{ request()->getHost() }}</code></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Port:</strong></td>
                                                    <td>{{ request()->getPort() ?: '80' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <h6 class="text-info">Request Body Template (untuk HTTP):</h6>
                                        <pre class="bg-light p-3"><code>{"barcode": "BARCODE_VALUE"}</code></pre>
                                        <small class="text-muted">Template ini akan otomatis mengganti BARCODE_VALUE dengan barcode yang di-scan</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4 -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">✅ Langkah 4: Test Koneksi</h6>
                                </div>
                                <div class="card-body">
                                    <ol>
                                        <li>Tap server yang sudah Anda buat</li>
                                        <li>Tap tombol <strong>"Test connection"</strong></li>
                                        <li>Jika berhasil, akan muncul checkmark hijau</li>
                                        <li>Jika gagal, periksa URL dan koneksi WiFi</li>
                                    </ol>

                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle"></i>
                                        <strong>Berhasil!</strong> Anda sekarang bisa mulai scan barcode dari HP dan akan langsung muncul di sistem POS.
                                    </div>
                                </div>
                            </div>

                            <!-- Usage -->
                            <div class="card mb-3">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0">🎯 Cara Menggunakan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Scan dari HP:</h6>
                                            <ol>
                                                <li>Buka halaman <strong>Sales/Purchase/POS</strong> di PC</li>
                                                <li>Buka app "Barcode to PC" di HP</li>
                                                <li>Tap server yang sudah dikonfigurasi</li>
                                                <li>Arahkan kamera HP ke barcode</li>
                                                <li>Barcode otomatis terkirim ke PC</li>
                                                <li>Produk langsung ditambahkan ke cart</li>
                                            </ol>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Tips Penggunaan:</h6>
                                            <ul>
                                                <li><i class="bi bi-lightbulb text-warning"></i> Pastikan pencahayaan cukup</li>
                                                <li><i class="bi bi-lightbulb text-warning"></i> Jaga jarak 10-30cm dari barcode</li>
                                                <li><i class="bi bi-lightbulb text-warning"></i> Tahan HP dengan stabil</li>
                                                <li><i class="bi bi-lightbulb text-warning"></i> Bersihkan lensa kamera HP</li>
                                                <li><i class="bi bi-lightbulb text-warning"></i> Gunakan mode auto-focus</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Info -->
                        <div class="col-lg-4">
                            <!-- Quick Copy -->
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">📋 Quick Copy</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Server URL:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="copy-url" value="{{ url('/api/scanner/scan') }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary" onclick="copyToClipboard('copy-url')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Server IP:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="copy-ip" value="{{ request()->getHost() }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary" onclick="copyToClipboard('copy-ip')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Port:</label>
                                        <input type="text" class="form-control form-control-sm" value="{{ request()->getPort() ?: '80' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Connection Test -->
                            <div class="card border-success mt-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">🧪 Test Connection</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <input type="text" id="test-barcode-guide" class="form-control form-control-sm" placeholder="Test barcode">
                                    </div>
                                    <button id="test-connection-guide" class="btn btn-success btn-sm btn-block">
                                        <i class="bi bi-play"></i> Test
                                    </button>
                                    <div id="test-result-guide" class="mt-2"></div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="card border-info mt-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">📊 Status</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small><strong>Server Status:</strong></small>
                                        <div id="server-status">
                                            <i class="bi bi-circle-fill text-success"></i> Online
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <small><strong>Last Scan:</strong></small>
                                        <div id="last-scan-guide">
                                            <span class="text-muted">None</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code -->
                            <div class="card border-dark mt-3">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0">📱 QR Setup</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div id="qr-code" style="margin: 10px auto;"></div>
                                    <small class="text-muted">Scan QR code ini dengan app "Barcode to PC" untuk auto-setup</small>
                                </div>
                            </div>

                            <!-- Help -->
                            <div class="card border-warning mt-3">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">💡 Troubleshooting</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Jika tidak bisa connect:</h6>
                                    <ul class="small">
                                        <li>Periksa koneksi WiFi</li>
                                        <li>Matikan firewall sementara</li>
                                        <li>Coba gunakan IP address langsung</li>
                                        <li>Restart router WiFi</li>
                                        <li>Gunakan hotspot HP</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-center">
                        <a href="{{ route('scanner.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Scanner
                        </a>
                        <a href="{{ route('scanner.external-setup') }}" class="btn btn-info">
                            <i class="bi bi-gear"></i> Other Setup Options
                        </a>
                        <button id="download-guide" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download PDF Guide
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<!-- QR Code Generator -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>

<script>
// Generate QR Code for easy setup
document.addEventListener('DOMContentLoaded', function() {
    const serverConfig = {
        name: "Nameless POS",
        url: "{{ url('/api/scanner/scan') }}",
        method: "POST",
        contentType: "application/json",
        template: '{"barcode": "{{ barcode }}"}'
    };

    const qr = qrcode(0, 'M');
    qr.addData(JSON.stringify(serverConfig));
    qr.make();
    document.getElementById('qr-code').innerHTML = qr.createImgTag(4, 8);
});

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    
    navigator.clipboard.writeText(element.value).then(() => {
        ScannerUtils.showNotification('Copied to clipboard!', 'success');
    });
}

document.getElementById('test-connection-guide').addEventListener('click', async function() {
    const barcode = document.getElementById('test-barcode-guide').value.trim();
    const resultDiv = document.getElementById('test-result-guide');
    
    if (!barcode) {
        resultDiv.innerHTML = '<small class="text-warning">Enter a test barcode</small>';
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
            resultDiv.innerHTML = '<small class="text-success">✓ Connection OK!</small>';
        } else {
            resultDiv.innerHTML = '<small class="text-warning">⚠ Product not found</small>';
        }
        
        document.getElementById('last-scan-guide').innerHTML = new Date().toLocaleTimeString();
        
    } catch (error) {
        resultDiv.innerHTML = '<small class="text-danger">✗ Connection failed</small>';
    }
});

document.getElementById('download-guide').addEventListener('click', function() {
    window.print();
});
</script>
@endpush