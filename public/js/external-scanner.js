/**
 * External Scanner Handler
 * Handles barcode input from third-party scanner apps like Barcode to PC
 */

class ExternalScannerHandler {
    constructor() {
        this.apiEndpoint = '/api/scanner/scan';
        this.websocketEndpoint = '/api/scanner/websocket-scan';
        this.isListening = false;
        this.lastScanTime = 0;
        this.scanCooldown = 1000; // 1 second cooldown between scans
        
        this.initializeEventListeners();
        this.setupWebSocketConnection();
    }

    initializeEventListeners() {
        // Listen for global keyboard events (for apps that send as keyboard input)
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardInput(e);
        });

        // Listen for paste events (some scanner apps use clipboard)
        document.addEventListener('paste', (e) => {
            this.handlePasteInput(e);
        });

        // Listen for custom barcode events
        window.addEventListener('externalBarcodeScan', (e) => {
            this.handleExternalScan(e.detail);
        });

        // Setup global function for external apps to call
        window.handleBarcodeInput = (barcode) => {
            this.processBarcode(barcode, 'external_function');
        };

        // Listen for HTTP POST requests from scanner apps
        this.setupHTTPListener();
    }

    handleKeyboardInput(e) {
        // Detect rapid keyboard input that looks like a barcode scanner
        if (this.isRapidInput(e)) {
            this.collectKeyboardBarcode(e);
        }
    }

    handlePasteInput(e) {
        const clipboardData = e.clipboardData || window.clipboardData;
        const pastedData = clipboardData.getData('text');
        
        if (this.looksLikeBarcode(pastedData)) {
            e.preventDefault();
            this.processBarcode(pastedData, 'clipboard');
        }
    }

    handleExternalScan(data) {
        if (data && data.barcode) {
            this.processBarcode(data.barcode, 'external_event');
        }
    }

    setupHTTPListener() {
        // Create endpoint for scanner apps to send POST requests
        // This would typically be handled by the Laravel API routes we created
        
        // Setup CORS and accept barcode data
        if (window.location.protocol === 'https:') {
            this.setupSecureListener();
        }
    }

    setupWebSocketConnection() {
        // Setup WebSocket connection for real-time scanning
        if ('WebSocket' in window) {
            try {
                const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
                const wsUrl = `${wsProtocol}//${window.location.host}/scanner-ws`;
                
                this.websocket = new WebSocket(wsUrl);
                
                this.websocket.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    if (data.type === 'barcode_scan' && data.barcode) {
                        this.processBarcode(data.barcode, 'websocket');
                    }
                };

                this.websocket.onopen = () => {
                    console.log('Scanner WebSocket connected');
                };

                this.websocket.onerror = (error) => {
                    console.warn('Scanner WebSocket error:', error);
                };
            } catch (error) {
                console.warn('WebSocket not available for scanner');
            }
        }
    }

    setupSecureListener() {
        // For HTTPS sites, setup service worker to handle external requests
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/js/scanner-sw.js')
                .then(registration => {
                    console.log('Scanner service worker registered');
                })
                .catch(error => {
                    console.warn('Scanner service worker registration failed:', error);
                });
        }
    }

    isRapidInput(e) {
        const now = Date.now();
        const timeDiff = now - this.lastKeyTime;
        this.lastKeyTime = now;
        
        // Barcode scanners typically input very quickly (< 50ms between characters)
        return timeDiff < 50 && timeDiff > 0;
    }

    collectKeyboardBarcode(e) {
        if (!this.barcodeBuffer) {
            this.barcodeBuffer = '';
            this.bufferTimeout = null;
        }

        // Add character to buffer
        if (e.key && e.key.length === 1) {
            this.barcodeBuffer += e.key;
        }

        // Clear buffer after 200ms of inactivity
        clearTimeout(this.bufferTimeout);
        this.bufferTimeout = setTimeout(() => {
            if (this.barcodeBuffer && this.looksLikeBarcode(this.barcodeBuffer)) {
                this.processBarcode(this.barcodeBuffer, 'keyboard_rapid');
            }
            this.barcodeBuffer = '';
        }, 200);

        // Process if Enter key is pressed
        if (e.key === 'Enter' && this.barcodeBuffer) {
            clearTimeout(this.bufferTimeout);
            if (this.looksLikeBarcode(this.barcodeBuffer)) {
                e.preventDefault();
                this.processBarcode(this.barcodeBuffer, 'keyboard_enter');
            }
            this.barcodeBuffer = '';
        }
    }

    looksLikeBarcode(text) {
        if (!text || typeof text !== 'string') return false;
        
        text = text.trim();
        
        // Must be at least 4 characters
        if (text.length < 4) return false;
        
        // Check common barcode patterns
        const patterns = [
            /^\d{8}$/, // EAN-8
            /^\d{12}$/, // UPC-A
            /^\d{13}$/, // EAN-13
            /^[0-9A-Za-z\-\.\_\+\*\$\%\#\@\!]{4,}$/ // General alphanumeric
        ];

        return patterns.some(pattern => pattern.test(text));
    }

    async processBarcode(barcode, source = 'unknown') {
        const now = Date.now();
        
        // Prevent duplicate scans
        if (now - this.lastScanTime < this.scanCooldown) {
            return;
        }
        this.lastScanTime = now;

        // Clean barcode
        barcode = barcode.trim();
        
        if (!this.looksLikeBarcode(barcode)) {
            return;
        }

        try {
            // Show scanning indicator
            this.showScanningIndicator(barcode);

            // Send to backend
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ 
                    barcode: barcode,
                    source: source,
                    timestamp: now
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.handleSuccessfulScan(data, source);
            } else {
                this.handleFailedScan(data, source);
            }

        } catch (error) {
            console.error('External scanner error:', error);
            this.showError('Scanner connection error');
        }
    }

    handleSuccessfulScan(data, source) {
        const product = data.product;
        
        // Play feedback
        ScannerUtils.playBeep();
        ScannerUtils.vibrate();
        
        // Show notification
        ScannerUtils.showNotification(`Product scanned: ${product.name}`, 'success');
        
        // Add to Livewire component if available
        if (window.Livewire) {
            this.addToLivewireComponent(data);
        }
        
        // Update UI
        this.updateSearchField(product.code);
        this.showProductPreview(product);
        
        // Log scan
        console.log('External scan successful:', {
            barcode: data.barcode,
            product: product.name,
            source: source
        });

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('externalScanSuccess', {
            detail: { data, source }
        }));
    }

    handleFailedScan(data, source) {
        ScannerUtils.showNotification(data.message || 'Product not found', 'warning');
        
        console.log('External scan failed:', {
            barcode: data.barcode,
            source: source
        });

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('externalScanFailed', {
            detail: { data, source }
        }));
    }

    addToLivewireComponent(data) {
        try {
            // Find the search product component
            const searchComponent = document.querySelector('[wire\\:id]');
            if (searchComponent) {
                const componentId = searchComponent.getAttribute('wire:id');
                const component = window.Livewire.find(componentId);
                
                if (component) {
                    component.call('searchByBarcode', data.barcode);
                }
            }
        } catch (error) {
            console.warn('Could not add to Livewire component:', error);
        }
    }

    updateSearchField(productCode) {
        const searchInput = document.getElementById('product-search-input');
        if (searchInput) {
            searchInput.value = productCode;
            searchInput.focus();
        }
    }

    showProductPreview(product) {
        // Create temporary preview notification
        const preview = document.createElement('div');
        preview.className = 'scanner-product-preview';
        preview.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: white;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            max-width: 300px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        preview.innerHTML = `
            <div class="d-flex align-items-center">
                ${product.image ? `<img src="${product.image}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 4px;">` : ''}
                <div>
                    <strong style="color: #28a745;">${product.name}</strong><br>
                    <small>Code: ${product.code}</small><br>
                    <small>Price: Rp ${new Intl.NumberFormat('id-ID').format(product.price)}</small>
                </div>
            </div>
        `;
        
        document.body.appendChild(preview);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (preview.parentNode) {
                preview.remove();
            }
        }, 3000);
    }

    showScanningIndicator(barcode) {
        // Show temporary scanning indicator
        ScannerUtils.showNotification(`Scanning: ${barcode}`, 'info');
    }

    showError(message) {
        ScannerUtils.showNotification(message, 'error');
    }

    // Public methods for external scanner apps
    static handleBarcode(barcode) {
        if (window.externalScannerHandler) {
            window.externalScannerHandler.processBarcode(barcode, 'external_api');
        }
    }

    static isReady() {
        return window.externalScannerHandler && true;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.externalScannerHandler = new ExternalScannerHandler();
    
    // Make global functions available
    window.scanBarcode = ExternalScannerHandler.handleBarcode;
    window.isScannerReady = ExternalScannerHandler.isReady;
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .scanner-product-preview {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
`;
document.head.appendChild(style);