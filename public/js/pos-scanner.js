/**
 * POS Scanner Integration
 * Initializes the correct scanner UI and behavior based on system settings.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ensure scanner elements and settings are present
    if (document.getElementById('product-search-input') && typeof window.scannerSettings !== 'undefined') {

        const scannerType = window.scannerSettings.scanner_type;
        const externalTypes = ['external', 'usb', 'bluetooth'];

        // --- Check scanner mode from settings ---
        if (externalTypes.includes(scannerType)) {
            initializeExternalScannerMode(scannerType);
        } else {
            initializeCameraScannerMode();
        }

    } else {
        console.warn('Scanner settings or search input not found. Defaulting to camera mode.');
        // Fallback to default camera mode if settings are missing
        if (document.getElementById('product-search-input')) {
            initializeCameraScannerMode();
        }
    }
});

/**
 * Configures the UI for external scanner usage.
 * Changes the main scanner button to focus the search input.
 */
function initializeExternalScannerMode(type) {
    console.log(`POS Scanner Mode: EXTERNAL (${type})`);

    const openScannerBtn = document.getElementById('open-scanner-btn');
    const searchInput = document.getElementById('product-search-input');

    if (openScannerBtn && searchInput) {
        // Change button icon and text
        openScannerBtn.innerHTML = '<i class="bi bi-keyboard"></i> Focus Input';
        
        // Change button functionality to focus the search input
        openScannerBtn.addEventListener('click', (event) => {
            event.preventDefault(); // Prevent any default action
            searchInput.focus();
        });
    }

    // Focus the main search bar initially
    if (searchInput) {
        searchInput.focus();
        searchInput.placeholder = 'Ready to scan...';
    }

    // Note: external-scanner.js is already initialized and handles global listening.
}

/**
 * Initializes the camera scanner functionality.
 * This creates an instance of the POSScanner class which handles the modal and QuaggaJS.
 */
function initializeCameraScannerMode() {
    console.log('POS Scanner Mode: CAMERA (default)');
    window.posScanner = new POSScanner();
}


/**
 * POSScanner Class
 * Manages the camera scanner modal, QuaggaJS, and related UI events.
 * This entire class is only used if the scanner_type is 'camera'.
 */
class POSScanner {
    constructor() {
        this.video = null;
        this.stream = null;
        this.isScanning = false;
        this.currentCamera = 'back';
        
        this.initializeElements();
        this.bindEvents();
    }

    initializeElements() {
        // Modal elements
        this.modal = document.getElementById('scannerModal');
        this.video = document.getElementById('modal-scanner-video');
        this.canvas = document.getElementById('modal-scanner-canvas');
        this.statusElement = document.getElementById('modal-scanner-status');
        
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
            openScannerBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.openScanner();
            });
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

        // Modal events
        if (this.modal) {
            $(this.modal).on('hidden.bs.modal', () => {
                this.stopScanning();
            });

            $(this.modal).on('shown.bs.modal', () => {
                setTimeout(() => {
                    if (this.manualInput) {
                        this.manualInput.focus();
                    }
                }, 300);
            });
        }

        // Listen for keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                this.openScanner();
            }
        });
    }

    openScanner() {
        if (this.modal) {
            $(this.modal).modal('show');
        }
    }

    async startScanning() {
        try {
            this.showStatus('Starting camera...', 'info');

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

            if (typeof Quagga !== 'undefined') {
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: this.video,
                        constraints: constraints.video
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader", "upc_e_reader", "codabar_reader"]
                    },
                    locate: true,
                    locator: { patchSize: "medium", halfSample: true },
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

                Quagga.onDetected((result) => {
                    const code = result.codeResult.code;
                    this.onBarcodeDetected(code);
                });
            } else {
                this.showStatus('Scanner library not loaded. Please refresh the page.', 'danger');
                return;
            }

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
        if (typeof Quagga !== 'undefined') Quagga.stop();
        if (this.video) this.video.srcObject = null;
        this.isScanning = false;
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
        if (!barcode || !this.isValidBarcode(barcode) || this.lastScannedCode === barcode) return;
        this.lastScannedCode = barcode;
        setTimeout(() => { this.lastScannedCode = null; }, 2000);

        if(window.scannerSettings && window.scannerSettings.beep_sound) ScannerUtils.playBeep();
        if(window.scannerSettings && window.scannerSettings.vibration) ScannerUtils.vibrate();
        
        this.searchBarcode(barcode);
        this.stopScanning();
    }

    searchManualBarcode() {
        const barcode = this.manualInput.value.trim();
        if (barcode) {
            this.searchBarcode(barcode);
            this.manualInput.value = '';
        }
    }

    async searchBarcode(barcode) {
        this.showStatus('Searching product...', 'info');
        try {
            // Wait a bit for Livewire to fully initialize if multiple instances detected
            await new Promise(resolve => setTimeout(resolve, 100));
            
            if (window.Livewire) {
                // Find SearchProduct component specifically
                let searchComponent = null;
                
                // Try to find SearchProduct component by looking for the search input
                const searchInput = document.getElementById('product-search-input');
                if (searchInput) {
                    const searchContainer = searchInput.closest('[wire\\:id]');
                    if (searchContainer) {
                        const componentId = searchContainer.getAttribute('wire:id');
                        searchComponent = window.Livewire.find(componentId);
                    }
                }
                
                // Fallback: iterate through all components to find SearchProduct
                if (!searchComponent && window.Livewire.components && window.Livewire.components.componentsById) {
                    for (const componentId in window.Livewire.components.componentsById) {
                        const comp = window.Livewire.find(componentId);
                        if (comp && comp.__name && comp.__name.includes('SearchProduct')) {
                            searchComponent = comp;
                            break;
                        }
                    }
                }
                
                if (searchComponent && typeof searchComponent.call === 'function') {
                    console.log('Found SearchProduct component, calling searchByBarcode...');
                    await searchComponent.call('searchByBarcode', barcode);
                    
                    // Close scanner modal after successful scan
                    setTimeout(() => {
                        if (this.modal) {
                            $(this.modal).modal('hide');
                        }
                    }, 1000);
                } else {
                    console.error('SearchProduct component not found or not callable.');
                    this.showStatus('Search component not found. Trying manual search...', 'warning');
                    
                    // Fallback: manually set search input value
                    if (searchInput) {
                        searchInput.value = barcode;
                        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        
                        // Close modal
                        setTimeout(() => {
                            if (this.modal) {
                                $(this.modal).modal('hide');
                            }
                        }, 500);
                    }
                }
            } else {
                console.error('Livewire is not available.');
                this.showStatus('Livewire not initialized.', 'danger');
            }
        } catch (error) {
            console.error('Error calling Livewire component:', error);
            this.showStatus('An error occurred during search: ' + error.message, 'danger');
        }
    }

    isValidBarcode(barcode) {
        if (!barcode || typeof barcode !== 'string') return false;
        barcode = barcode.trim();
        if (barcode.length < 4) return false;
        const patterns = [/^\d{8}$/, /^\d{12}$/, /^\d{13}$/, /^[0-9A-Za-z\-\._\+\*]+$/];
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
        const icons = { 'info': 'info-circle', 'success': 'check-circle', 'warning': 'exclamation-triangle', 'danger': 'x-circle' };
        this.statusElement.className = `alert alert-${type}`;
        this.statusElement.innerHTML = `<i class="bi bi-${icons[type] || 'info-circle'}"></i> ${message}`;
    }
}
