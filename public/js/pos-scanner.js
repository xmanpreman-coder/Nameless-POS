/**
 * POS Scanner Integration
 * Integrates barcode scanner functionality with Livewire components
 */

class POSScanner {
    constructor() {
        this.video = null;
        this.stream = null;
        this.isScanning = false;
        this.currentCamera = 'back';
        this.recentScans = JSON.parse(localStorage.getItem('recent_scans') || '[]');
        this.maxRecentScans = 10;
        
        this.initializeElements();
        this.bindEvents();
        this.updateRecentScansDisplay();
    }

    initializeElements() {
        // Modal elements
        this.modal = document.getElementById('scannerModal');
        this.video = document.getElementById('modal-scanner-video');
        this.canvas = document.getElementById('modal-scanner-canvas');
        this.statusElement = document.getElementById('modal-scanner-status');
        this.resultElement = document.getElementById('modal-scanner-result');
        
        // Control buttons
        this.startButton = document.getElementById('modal-start-camera');
        this.stopButton = document.getElementById('modal-stop-camera');
        this.switchButton = document.getElementById('modal-switch-camera');
        this.searchButton = document.getElementById('modal-search-manual');
        
        // Input elements
        this.manualInput = document.getElementById('modal-manual-barcode');
        this.searchInput = document.getElementById('product-search-input');
    }

    bindEvents() {
        // Scanner trigger buttons
        const openScannerBtn = document.getElementById('open-scanner-btn');
        if (openScannerBtn) {
            openScannerBtn.addEventListener('click', () => this.openScanner());
        }

        const quickScanBtn = document.getElementById('quick-scan-btn');
        if (quickScanBtn) {
            quickScanBtn.addEventListener('click', () => this.toggleQuickScan());
        }

        // Modal control buttons
        if (this.startButton) {
            this.startButton.addEventListener('click', () => this.startScanning());
        }

        if (this.stopButton) {
            this.stopButton.addEventListener('click', () => this.stopScanning());
        }

        if (this.switchButton) {
            this.switchButton.addEventListener('click', () => this.switchCamera());
        }

        if (this.searchButton) {
            this.searchButton.addEventListener('click', () => this.searchManualBarcode());
        }

        // Manual input events
        if (this.manualInput) {
            this.manualInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.searchManualBarcode();
                }
            });

            // Auto-search for barcode input (USB/Bluetooth scanners)
            this.manualInput.addEventListener('input', (e) => {
                const value = e.target.value.trim();
                if (value.length >= 8 && this.isValidBarcode(value)) {
                    // Auto-search after short delay
                    clearTimeout(this.autoSearchTimeout);
                    this.autoSearchTimeout = setTimeout(() => {
                        this.searchManualBarcode();
                    }, 500);
                }
            });
        }

        // Modal events
        if (this.modal) {
            $(this.modal).on('hidden.bs.modal', () => {
                this.stopScanning();
                this.clearResult();
            });

            $(this.modal).on('shown.bs.modal', () => {
                // Focus on manual input when modal opens
                setTimeout(() => {
                    if (this.manualInput) {
                        this.manualInput.focus();
                    }
                }, 300);
            });
        }

        // Listen for keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl + Shift + S to open scanner
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                this.openScanner();
            }
        });

        // Listen for USB/Bluetooth scanner input on search field
        if (this.searchInput) {
            this.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const value = e.target.value.trim();
                    if (this.isValidBarcode(value)) {
                        this.searchBarcode(value);
                        e.preventDefault();
                    }
                }
            });
        }
    }

    openScanner() {
        if (this.modal) {
            $(this.modal).modal('show');
        }
    }

    async startScanning() {
        try {
            this.showStatus('Starting camera...', 'info');

            // Check camera permission first
            const permission = await this.checkCameraPermission();
            if (permission === 'denied') {
                this.showStatus('Camera permission denied. Please allow camera access.', 'danger');
                return;
            }

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
            if (typeof Quagga !== 'undefined') {
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
                            "upc_reader",
                            "upc_e_reader",
                            "codabar_reader"
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
            } else {
                this.showStatus('Scanner library not loaded. Please refresh the page.', 'danger');
                return;
            }

            // Update UI
            this.startButton.style.display = 'none';
            this.stopButton.style.display = 'inline-block';
            this.switchButton.style.display = 'inline-block';

        } catch (error) {
            console.error('Camera access error:', error);
            this.showStatus('Camera access denied or not available. Please check permissions.', 'danger');
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

        if (this.video) {
            this.video.srcObject = null;
        }
        
        this.isScanning = false;

        // Update UI
        if (this.startButton) this.startButton.style.display = 'inline-block';
        if (this.stopButton) this.stopButton.style.display = 'none';
        if (this.switchButton) this.switchButton.style.display = 'none';
        
        this.showStatus('Camera stopped', 'info');
    }

    async switchCamera() {
        this.currentCamera = this.currentCamera === 'back' ? 'front' : 'back';
        if (this.isScanning) {
            this.stopScanning();
            await new Promise(resolve => setTimeout(resolve, 500));
            this.startScanning();
        }
    }

    onBarcodeDetected(barcode) {
        if (!barcode || !this.isValidBarcode(barcode)) return;

        // Prevent duplicate rapid scans
        if (this.lastScannedCode === barcode) {
            return;
        }
        this.lastScannedCode = barcode;

        // Clear the duplicate prevention after 2 seconds
        setTimeout(() => {
            this.lastScannedCode = null;
        }, 2000);

        ScannerUtils.playBeep();
        ScannerUtils.vibrate();
        
        this.addToRecentScans(barcode);
        this.searchBarcode(barcode);
        
        // Auto-stop scanning after successful detection
        this.stopScanning();
    }

    searchManualBarcode() {
        const barcode = this.manualInput.value.trim();
        if (barcode) {
            this.addToRecentScans(barcode);
            this.searchBarcode(barcode);
            this.manualInput.value = '';
        }
    }

    async searchBarcode(barcode) {
        try {
            this.showStatus('Searching product...', 'info');

            // Call Livewire method to search by barcode
            if (window.Livewire) {
                const component = window.Livewire.find(
                    document.querySelector('[wire\\:id]').getAttribute('wire:id')
                );
                
                if (component) {
                    await component.call('searchByBarcode', barcode);
                    
                    // Listen for the result
                    window.addEventListener('scannerResult', (event) => {
                        const result = event.detail[0];
                        this.handleSearchResult(result, barcode);
                    }, { once: true });
                }
            } else {
                // Fallback to direct API call
                const response = await fetch('/scanner/search-product', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ barcode: barcode })
                });

                const data = await response.json();
                this.handleSearchResult(data, barcode);
            }

        } catch (error) {
            console.error('Search error:', error);
            this.showStatus('Error searching for product', 'danger');
        }
    }

    handleSearchResult(result, barcode) {
        if (result.success) {
            this.showResult(result.product);
            this.showStatus('Product found and added!', 'success');
            ScannerUtils.showNotification('Product added: ' + result.product.name, 'success');
            
            // Close modal after successful scan
            setTimeout(() => {
                if (this.modal) {
                    $(this.modal).modal('hide');
                }
            }, 1500);
        } else {
            this.showStatus(result.message, 'warning');
            ScannerUtils.showNotification(result.message, 'warning');
        }
    }

    showResult(product) {
        if (!this.resultElement) return;

        const content = document.getElementById('modal-result-content');
        if (content && product) {
            content.innerHTML = `
                <div class="d-flex align-items-center">
                    ${product.image ? `<img src="${product.image}" class="img-thumbnail mr-3" style="width: 60px; height: 60px;">` : ''}
                    <div>
                        <strong>${product.name}</strong><br>
                        <small>Code: ${product.code}</small><br>
                        <small>Price: Rp ${new Intl.NumberFormat('id-ID').format(product.price)}</small>
                    </div>
                </div>
            `;
        }
        
        this.resultElement.style.display = 'block';
    }

    clearResult() {
        if (this.resultElement) {
            this.resultElement.style.display = 'none';
        }
    }

    addToRecentScans(barcode) {
        // Remove if exists
        this.recentScans = this.recentScans.filter(scan => scan !== barcode);
        
        // Add to beginning
        this.recentScans.unshift(barcode);
        
        // Limit size
        if (this.recentScans.length > this.maxRecentScans) {
            this.recentScans = this.recentScans.slice(0, this.maxRecentScans);
        }
        
        // Save to localStorage
        localStorage.setItem('recent_scans', JSON.stringify(this.recentScans));
        
        this.updateRecentScansDisplay();
    }

    updateRecentScansDisplay() {
        const container = document.getElementById('modal-recent-scans');
        if (!container) return;

        if (this.recentScans.length === 0) {
            container.innerHTML = '<small class="text-muted">No recent scans</small>';
            return;
        }

        let html = '';
        this.recentScans.forEach((barcode, index) => {
            html += `
                <div class="d-flex justify-content-between align-items-center py-1 ${index < this.recentScans.length - 1 ? 'border-bottom' : ''}">
                    <small><code>${barcode}</code></small>
                    <button class="btn btn-sm btn-outline-primary btn-xs" onclick="posScanner.searchBarcode('${barcode}')">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    isValidBarcode(barcode) {
        if (!barcode || typeof barcode !== 'string') return false;
        
        barcode = barcode.trim();
        
        // Must be at least 4 characters
        if (barcode.length < 4) return false;

        // Check for common barcode patterns
        const patterns = [
            /^\d{8}$/, // EAN-8
            /^\d{12}$/, // UPC-A
            /^\d{13}$/, // EAN-13
            /^[0-9A-Za-z\-\.\_\+\*]+$/ // Code 128, Code 39, etc.
        ];

        return patterns.some(pattern => pattern.test(barcode));
    }

    async checkCameraPermission() {
        try {
            const result = await navigator.permissions.query({ name: 'camera' });
            return result.state;
        } catch (error) {
            return 'unknown';
        }
    }

    showStatus(message, type = 'info') {
        if (!this.statusElement) return;
        
        const icons = {
            'info': 'info-circle',
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'danger': 'x-circle'
        };

        this.statusElement.className = `alert alert-${type}`;
        this.statusElement.innerHTML = `<i class="bi bi-${icons[type] || 'info-circle'}"></i> ${message}`;
    }

    toggleQuickScan() {
        // Quick scan functionality - open camera directly in small overlay
        // This would be a future enhancement
        ScannerUtils.showNotification('Quick scan feature coming soon!', 'info');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page with the scanner elements
    if (document.getElementById('product-search-input')) {
        window.posScanner = new POSScanner();
    }
});