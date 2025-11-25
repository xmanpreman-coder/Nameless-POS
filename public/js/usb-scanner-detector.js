/**
 * USB Scanner Auto-Detection for Nameless POS
 * Specifically optimized for CashCow HC T62 and similar desktop scanners
 */

class USBScannerDetector {
    constructor() {
        this.isActive = false;
        this.inputBuffer = '';
        this.inputTimer = null;
        this.lastInputTime = 0;
        this.scannerDevices = [];
        
        // Scanner characteristics
        this.scannerPatterns = {
            minLength: 6,
            maxLength: 50,
            maxCharInterval: 100, // ms between characters for scanner input
            bufferTimeout: 500 // ms to wait before processing buffer
        };
        
        // Universal scanner detection - tidak perlu vendor ID khusus
        // Deteksi semua USB device yang bertingkah seperti keyboard (HID)
        this.universalDetection = true;
        
        // Pattern characteristics untuk semua jenis scanner
        this.scannerCharacteristics = {
            rapidInputThreshold: 50, // ms - scanner biasanya input < 50ms per karakter
            minimumBarcodeLength: 4,
            maximumBarcodeLength: 100,
            enterKeyEnding: true, // kebanyakan scanner akhiri dengan Enter
            consecutiveCharPattern: true // input berurutan tanpa jeda
        };
        
        this.init();
    }
    
    async init() {
        console.log('USB Scanner Detector: Initializing...');
        
        // Set up keyboard monitoring
        this.setupKeyboardMonitoring();
        
        // Try to detect USB devices
        await this.detectUSBScanners();
        
        // Set up USB device event listeners
        this.setupUSBEventListeners();
        
        console.log('USB Scanner Detector: Ready');
    }
    
    setupKeyboardMonitoring() {
        // Global keydown listener for scanner input detection
        document.addEventListener('keydown', (e) => {
            this.handleKeyInput(e);
        }, true);
        
        // Monitor input fields for rapid filling (scanner characteristic)
        document.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' && e.target.type === 'text') {
                this.handleInputFieldChange(e);
            }
        }, true);
    }
    
    handleKeyInput(e) {
        const now = Date.now();
        const timeDiff = now - this.lastInputTime;
        
        // Universal scanner detection berdasarkan pattern, bukan vendor
        // Semua scanner USB akan terdeteksi dengan method ini
        
        // Deteksi rapid input (karakteristik utama semua USB scanner)
        if (timeDiff < this.scannerCharacteristics.rapidInputThreshold && this.inputBuffer.length > 0) {
            console.log(`Universal Scanner: Rapid input detected (${timeDiff}ms) - likely any USB scanner`);
            this.logToUI(`Rapid input: ${timeDiff}ms (Scanner detected)`);
        }
        
        this.lastInputTime = now;
        
        // Build input buffer untuk semua karakter yang valid
        if (e.key.length === 1 && /[0-9A-Za-z\-\.\+\*\#\$\%\@\!]/.test(e.key)) {
            this.inputBuffer += e.key;
            
            // Log real-time untuk debugging
            if (this.inputBuffer.length <= 3) {
                this.logToUI(`Building buffer: "${this.inputBuffer}"`);
            }
            
            // Set timeout to process buffer
            clearTimeout(this.inputTimer);
            this.inputTimer = setTimeout(() => {
                this.processInputBuffer();
            }, this.scannerPatterns.bufferTimeout);
        }
        
        // Process pada Enter key (hampir semua scanner menggunakan ini)
        if (e.key === 'Enter' && this.inputBuffer.length >= this.scannerCharacteristics.minimumBarcodeLength) {
            clearTimeout(this.inputTimer);
            this.processInputBuffer(true);
        }
        
        // Clear buffer jika terlalu lama atau Escape
        if (e.key === 'Escape' || timeDiff > 2000) {
            this.clearInputBuffer();
        }
    }
    
    handleInputFieldChange(e) {
        const value = e.target.value;
        const now = Date.now();
        
        // Check if input field was filled rapidly (scanner behavior)
        if (value.length >= this.scannerPatterns.minLength) {
            const timeSinceLastKey = now - this.lastInputTime;
            
            // If field filled quickly after last key, likely scanner
            if (timeSinceLastKey < 1000 && this.looksLikeBarcode(value)) {
                console.log('USB Scanner: Rapid field fill detected:', value);
                this.processScannedBarcode(value, 'usb_field_fill');
            }
        }
    }
    
    processInputBuffer(isEnterTriggered = false) {
        if (this.inputBuffer.length >= this.scannerPatterns.minLength) {
            const barcode = this.inputBuffer.trim();
            
            if (this.looksLikeBarcode(barcode)) {
                console.log(`USB Scanner: Barcode detected via ${isEnterTriggered ? 'Enter key' : 'timeout'}:`, barcode);
                this.processScannedBarcode(barcode, isEnterTriggered ? 'usb_enter' : 'usb_buffer');
            }
        }
        
        this.clearInputBuffer();
    }
    
    clearInputBuffer() {
        this.inputBuffer = '';
        clearTimeout(this.inputTimer);
    }
    
    looksLikeBarcode(text) {
        // Check if text matches barcode patterns
        const patterns = [
            /^\d{8}$/, // EAN-8
            /^\d{12}$/, // UPC-A  
            /^\d{13}$/, // EAN-13
            /^[0-9A-Za-z\-\.]{6,50}$/ // Code 128, Code 39, etc.
        ];
        
        return patterns.some(pattern => pattern.test(text));
    }
    
    async processScannedBarcode(barcode, source) {
        console.log(`USB Scanner: Processing barcode: ${barcode} (source: ${source})`);
        
        // Play beep sound
        this.playBeep();
        
        // Show notification
        this.showNotification(`Scanner detected: ${barcode}`, 'success');
        
        // Send to the existing external scanner handler if available
        if (window.externalScannerHandler) {
            try {
                await window.externalScannerHandler.processBarcode(barcode, source);
            } catch (error) {
                console.error('USB Scanner: Error processing barcode:', error);
                this.showNotification('Error processing scanned barcode', 'error');
            }
        } else {
            // Fallback: trigger product search directly
            this.triggerProductSearch(barcode);
        }
    }
    
    triggerProductSearch(barcode) {
        // Try to find and fill search input
        const searchInput = document.querySelector('#product-search-input, input[name*="search"], input[placeholder*="search"]');
        
        if (searchInput) {
            searchInput.value = barcode;
            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            searchInput.dispatchEvent(new Event('change', { bubbles: true }));
            console.log('USB Scanner: Triggered search for:', barcode);
        } else {
            console.warn('USB Scanner: No search input found');
        }
    }
    
    async detectUSBScanners() {
        console.log('USB Scanner: Universal detection mode - checking for any HID input devices');
        
        // Update status di UI
        this.updateDetectionStatus('checking', 'Scanning for USB input devices...');
        
        // Dalam mode universal, kita fokus pada pattern detection dari keyboard input
        // Karena semua USB scanner bekerja sebagai keyboard emulation
        
        try {
            if (navigator.usb) {
                const devices = await navigator.usb.getDevices();
                console.log(`USB Scanner: Found ${devices.length} total USB devices connected`);
                
                // Dalam mode universal, semua HID device berpotensi jadi scanner
                const hidDevices = devices.filter(device => {
                    // Deteksi HID (Human Interface Device) class
                    return device.configuration && 
                           device.configuration.interfaces && 
                           device.configuration.interfaces.some(iface => 
                               iface.classCode === 3 // HID class
                           );
                });
                
                if (hidDevices.length > 0) {
                    console.log(`USB Scanner: Found ${hidDevices.length} HID device(s) - potential scanners`);
                    this.updateDetectionStatus('ready', `Universal scanner detection active - ${hidDevices.length} HID devices found`);
                    
                    hidDevices.forEach((device, index) => {
                        console.log(`HID Device ${index + 1}: ${device.productName || 'Unknown'} (ID: ${device.vendorId}:${device.productId})`);
                    });
                } else {
                    this.updateDetectionStatus('ready', 'Universal scanner detection active - ready for any USB scanner');
                }
            } else {
                // Tanpa Web USB API, tetap gunakan keyboard pattern detection
                this.updateDetectionStatus('ready', 'Universal scanner detection active (keyboard input mode)');
                console.log('USB Scanner: Using keyboard input pattern detection for universal compatibility');
            }
            
            // Test pattern detection capability
            this.logToUI('Universal scanner detector initialized');
            this.logToUI('Compatible: All USB scanners that work as keyboard input');
            this.logToUI('Ready to detect: CashCow, Honeywell, Symbol, Zebra, Datalogic, Generic, dll.');
            
        } catch (error) {
            console.error('USB Scanner: Error in universal detection:', error);
            this.updateDetectionStatus('ready', 'Universal scanner detection active (fallback mode)');
        }
    }
    
    setupUSBEventListeners() {
        if (navigator.usb) {
            navigator.usb.addEventListener('connect', (event) => {
                console.log('USB Scanner: Device connected:', event.device);
                this.detectUSBScanners();
            });
            
            navigator.usb.addEventListener('disconnect', (event) => {
                console.log('USB Scanner: Device disconnected:', event.device);
                this.detectUSBScanners();
            });
        }
    }
    
    playBeep() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();
            
            oscillator.connect(gain);
            gain.connect(audioContext.destination);
            
            oscillator.frequency.value = 1000; // Higher pitch for USB scanner
            oscillator.type = 'sine';
            gain.gain.value = 0.1;
            
            oscillator.start();
            setTimeout(() => oscillator.stop(), 150);
        } catch (error) {
            console.warn('USB Scanner: Audio not available');
        }
    }
    
    showNotification(message, type = 'info') {
        if (window.ScannerUtils && typeof window.ScannerUtils.showNotification === 'function') {
            window.ScannerUtils.showNotification(message, type);
        } else {
            // Fallback notification
            console.log(`USB Scanner ${type.toUpperCase()}: ${message}`);
        }
    }
    
    // Helper functions untuk UI updates
    updateDetectionStatus(status, message) {
        const statusDiv = document.getElementById('usb-auto-status');
        if (statusDiv) {
            let alertClass = 'alert-secondary';
            let icon = 'bi-gear';
            
            if (status === 'ready') {
                alertClass = 'alert-success';
                icon = 'bi-check-circle';
            } else if (status === 'checking') {
                alertClass = 'alert-warning';
                icon = 'bi-search';
            } else if (status === 'error') {
                alertClass = 'alert-danger';
                icon = 'bi-x-circle';
            }
            
            statusDiv.className = `alert ${alertClass}`;
            statusDiv.innerHTML = `<i class="bi ${icon}"></i> ${message}`;
        }
    }
    
    logToUI(message) {
        const logDiv = document.getElementById('universal-test-log');
        if (logDiv) {
            logDiv.style.display = 'block';
            const timestamp = new Date().toLocaleTimeString();
            logDiv.innerHTML += `[${timestamp}] ${message}<br>`;
            
            // Auto scroll to bottom
            logDiv.scrollTop = logDiv.scrollHeight;
            
            // Keep only last 20 lines
            const lines = logDiv.innerHTML.split('<br>');
            if (lines.length > 20) {
                logDiv.innerHTML = lines.slice(-20).join('<br>');
            }
        }
    }
    
    clearUniversalTest() {
        const testInput = document.getElementById('universal-scanner-test');
        const logDiv = document.getElementById('universal-test-log');
        
        if (testInput) testInput.value = '';
        if (logDiv) {
            logDiv.innerHTML = '<strong>Detection Log:</strong><br>';
            logDiv.style.display = 'none';
        }
        
        this.clearInputBuffer();
        console.log('Universal Scanner Test: Cleared');
    }
    
    // Manual refresh function untuk UI button
    async refreshDetection() {
        this.logToUI('Manual refresh triggered');
        await this.detectUSBScanners();
    }
}

// Global functions untuk UI interaction
function clearUniversalTest() {
    if (window.usbScannerDetector) {
        window.usbScannerDetector.clearUniversalTest();
    }
}

function detectUSBScanners() {
    if (window.usbScannerDetector) {
        window.usbScannerDetector.refreshDetection();
    }
}

// Initialize USB Scanner Detector when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.usbScannerDetector = new USBScannerDetector();
});

// Make available globally
window.USBScannerDetector = USBScannerDetector;