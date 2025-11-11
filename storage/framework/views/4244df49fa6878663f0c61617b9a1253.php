

<?php $__env->startSection('title', 'Barcode Scanner'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('scanner.index')); ?>">Scanner</a></li>
        <li class="breadcrumb-item active">Scan</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Scanner Section -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Barcode Scanner</h5>
                    <div>
                        <button id="toggle-camera" class="btn btn-light btn-sm">
                            <i class="bi bi-camera"></i> Start Camera
                        </button>
                        <button id="switch-camera" class="btn btn-light btn-sm" style="display: none;">
                            <i class="bi bi-arrow-repeat"></i> Switch Camera
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Camera Scanner -->
                    <div id="camera-scanner" style="<?php echo e($settings->scanner_type === 'camera' ? '' : 'display: none;'); ?>">
                        <div class="text-center mb-3">
                            <div id="scanner-container" style="position: relative; max-width: 500px; margin: 0 auto;">
                                <video id="scanner-video" style="width: 100%; height: 300px; background: #000; border-radius: 8px;"></video>
                                <div id="scanner-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border: 2px solid #ff0000; width: 200px; height: 100px; border-radius: 8px;"></div>
                            </div>
                            <canvas id="scanner-canvas" style="display: none;"></canvas>
                        </div>
                        
                        <div class="text-center">
                            <div id="scanner-status" class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Click "Start Camera" to begin scanning
                            </div>
                        </div>
                    </div>

                    <!-- Manual Input -->
                    <div id="manual-input" class="mt-4">
                        <div class="form-group">
                            <label for="manual-barcode">Manual Barcode Entry</label>
                            <div class="input-group">
                                <input type="text" id="manual-barcode" class="form-control" placeholder="Enter or scan barcode here...">
                                <div class="input-group-append">
                                    <button id="search-manual" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Settings Quick Access -->
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Scanner Type: <strong><?php echo e(ucfirst($settings->scanner_type)); ?></strong> | 
                            <a href="<?php echo e(route('scanner.settings')); ?>">Change Settings</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Product Result</h5>
                </div>
                <div class="card-body">
                    <div id="product-result" class="text-center">
                        <i class="bi bi-upc-scan" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">Scan a barcode to see product details</p>
                    </div>

                    <div id="product-details" style="display: none;">
                        <div class="text-center mb-3">
                            <img id="product-image" src="" alt="Product" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        </div>
                        
                        <h6 id="product-name" class="font-weight-bold"></h6>
                        
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Code:</strong></td>
                                <td id="product-code"></td>
                            </tr>
                            <tr>
                                <td><strong>Barcode:</strong></td>
                                <td id="product-barcode"></td>
                            </tr>
                            <tr>
                                <td><strong>Price:</strong></td>
                                <td id="product-price" class="text-success font-weight-bold"></td>
                            </tr>
                            <tr>
                                <td><strong>Stock:</strong></td>
                                <td id="product-stock"></td>
                            </tr>
                        </table>

                        <div class="d-flex justify-content-between">
                            <button id="add-to-cart" class="btn btn-success btn-sm">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <button id="scan-again" class="btn btn-primary btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Scan Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Scans</h6>
                </div>
                <div class="card-body p-2">
                    <div id="recent-scans">
                        <small class="text-muted">No recent scans</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
<!-- QuaggaJS Library for Barcode Scanning -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

<script>
class BarcodeScanner {
    constructor() {
        this.video = document.getElementById('scanner-video');
        this.canvas = document.getElementById('scanner-canvas');
        this.isScanning = false;
        this.stream = null;
        this.currentCamera = '<?php echo e($settings->preferred_camera); ?>';
        this.recentScans = [];
        this.maxRecentScans = 5;

        this.initializeScanner();
        this.bindEvents();
    }

    initializeScanner() {
        // Check if camera is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.showStatus('Camera not supported on this device', 'danger');
            return;
        }
    }

    bindEvents() {
        document.getElementById('toggle-camera').addEventListener('click', () => {
            if (this.isScanning) {
                this.stopScanning();
            } else {
                this.startScanning();
            }
        });

        document.getElementById('switch-camera').addEventListener('click', () => {
            this.switchCamera();
        });

        document.getElementById('search-manual').addEventListener('click', () => {
            const barcode = document.getElementById('manual-barcode').value.trim();
            if (barcode) {
                this.searchProduct(barcode);
            }
        });

        document.getElementById('manual-barcode').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const barcode = e.target.value.trim();
                if (barcode) {
                    this.searchProduct(barcode);
                }
            }
        });

        document.getElementById('scan-again').addEventListener('click', () => {
            this.hideProductDetails();
            if (!this.isScanning) {
                this.startScanning();
            }
        });
    }

    async startScanning() {
        try {
            this.showStatus('Starting camera...', 'info');
            
            const constraints = {
                video: {
                    facingMode: this.currentCamera === 'back' ? 'environment' : 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            };

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            
            await this.video.play();
            this.isScanning = true;

            // Initialize QuaggaJS
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: this.video,
                    constraints: constraints.video
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader", 
                        "ean_8_reader",
                        "code_39_reader",
                        "code_39_vin_reader",
                        "codabar_reader",
                        "upc_reader",
                        "upc_e_reader"
                    ]
                },
                locate: true,
                locator: {
                    patchSize: "medium",
                    halfSample: true
                },
                numOfWorkers: 2,
                frequency: 10
            }, (err) => {
                if (err) {
                    console.error('QuaggaJS initialization error:', err);
                    this.showStatus('Failed to initialize scanner', 'danger');
                    return;
                }
                Quagga.start();
                this.showStatus('Camera ready - Point at a barcode', 'success');
            });

            // Listen for successful scans
            Quagga.onDetected((result) => {
                const code = result.codeResult.code;
                this.onBarcodeDetected(code);
            });

            // Update UI
            document.getElementById('toggle-camera').innerHTML = '<i class="bi bi-camera-fill"></i> Stop Camera';
            document.getElementById('switch-camera').style.display = 'inline-block';

        } catch (error) {
            console.error('Camera access error:', error);
            this.showStatus('Camera access denied or not available', 'danger');
        }
    }

    stopScanning() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        if (typeof Quagga !== 'undefined') {
            Quagga.stop();
        }

        this.video.srcObject = null;
        this.isScanning = false;

        // Update UI
        document.getElementById('toggle-camera').innerHTML = '<i class="bi bi-camera"></i> Start Camera';
        document.getElementById('switch-camera').style.display = 'none';
        this.showStatus('Camera stopped', 'info');
    }

    async switchCamera() {
        this.currentCamera = this.currentCamera === 'back' ? 'front' : 'back';
        if (this.isScanning) {
            this.stopScanning();
            await new Promise(resolve => setTimeout(resolve, 500)); // Small delay
            this.startScanning();
        }
    }

    onBarcodeDetected(barcode) {
        // Prevent multiple rapid scans of the same code
        if (this.recentScans.includes(barcode)) {
            return;
        }

        this.playBeep();
        this.vibrate();
        
        this.showStatus(`Barcode detected: ${barcode}`, 'success');
        this.searchProduct(barcode);
        this.addToRecentScans(barcode);
    }

    async searchProduct(barcode) {
        try {
            this.showStatus('Searching product...', 'info');
            
            const response = await fetch('<?php echo e(route("scanner.search-product")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ barcode: barcode })
            });

            const data = await response.json();

            if (data.success) {
                this.showProductDetails(data.product);
                this.showStatus('Product found!', 'success');
            } else {
                this.showStatus(data.message, 'warning');
            }

        } catch (error) {
            console.error('Search error:', error);
            this.showStatus('Error searching for product', 'danger');
        }
    }

    showProductDetails(product) {
        document.getElementById('product-result').style.display = 'none';
        document.getElementById('product-details').style.display = 'block';

        document.getElementById('product-name').textContent = product.name;
        document.getElementById('product-code').textContent = product.code;
        document.getElementById('product-barcode').textContent = product.barcode || 'N/A';
        document.getElementById('product-price').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(product.price);
        document.getElementById('product-stock').textContent = product.stock;

        const productImage = document.getElementById('product-image');
        if (product.image) {
            productImage.src = product.image;
            productImage.style.display = 'block';
        } else {
            productImage.src = '<?php echo e(asset("images/fallback_product_image.png")); ?>';
            productImage.style.display = 'block';
        }

        // Clear manual input
        document.getElementById('manual-barcode').value = '';

        // Auto-stop scanning after successful detection
        if (this.isScanning && '<?php echo e($settings->scan_mode); ?>' === 'auto') {
            this.stopScanning();
        }
    }

    hideProductDetails() {
        document.getElementById('product-result').style.display = 'block';
        document.getElementById('product-details').style.display = 'none';
    }

    addToRecentScans(barcode) {
        if (!this.recentScans.includes(barcode)) {
            this.recentScans.unshift(barcode);
            if (this.recentScans.length > this.maxRecentScans) {
                this.recentScans.pop();
            }
            this.updateRecentScansDisplay();
        }
    }

    updateRecentScansDisplay() {
        const container = document.getElementById('recent-scans');
        if (this.recentScans.length === 0) {
            container.innerHTML = '<small class="text-muted">No recent scans</small>';
            return;
        }

        let html = '';
        this.recentScans.forEach((barcode, index) => {
            html += `
                <div class="d-flex justify-content-between align-items-center py-1 ${index < this.recentScans.length - 1 ? 'border-bottom' : ''}">
                    <small><code>${barcode}</code></small>
                    <button class="btn btn-sm btn-outline-primary btn-xs" onclick="scanner.searchProduct('${barcode}')">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    showStatus(message, type = 'info') {
        const statusElement = document.getElementById('scanner-status');
        statusElement.className = `alert alert-${type}`;
        statusElement.innerHTML = `<i class="bi bi-${this.getStatusIcon(type)}"></i> ${message}`;
    }

    getStatusIcon(type) {
        const icons = {
            'info': 'info-circle',
            'success': 'check-circle',
            'warning': 'exclamation-triangle', 
            'danger': 'x-circle'
        };
        return icons[type] || 'info-circle';
    }

    playBeep() {
        <?php if($settings->beep_sound): ?>
        // Create beep sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();
        
        oscillator.connect(gain);
        gain.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'square';
        gain.gain.value = 0.1;
        
        oscillator.start();
        setTimeout(() => oscillator.stop(), 200);
        <?php endif; ?>
    }

    vibrate() {
        <?php if($settings->vibration): ?>
        if ('vibrate' in navigator) {
            navigator.vibrate(200);
        }
        <?php endif; ?>
    }
}

// Initialize scanner when page loads
let scanner;
document.addEventListener('DOMContentLoaded', function() {
    scanner = new BarcodeScanner();
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Scanner\Resources/views/scan.blade.php ENDPATH**/ ?>