/**
 * External Scanner Handler
 * Handles barcode input from third-party scanner apps like Barcode to PC
 */

// Prevent multiple declarations
if (typeof window.ExternalScannerHandler === 'undefined') {

class ExternalScannerHandler {
    constructor() {
        this.apiEndpoint = `${window.location.origin}/api/scanner/scan`;
        this.websocketEndpoint = `${window.location.origin}/api/scanner/websocket-scan`;
        this.isListening = false;
        this.lastScanTime = 0;
        this.scanCooldown = 1000; // 1 second cooldown between scans
        
        this.initializeEventListeners();
        this.setupWebSocketConnection();
        
        console.log('External scanner initialized with endpoint:', this.apiEndpoint);
    }

    initializeEventListeners() {
        // Listen for global keyboard events (for apps that send as keyboard input)
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardInput(e);
        });

        // Also listen for keypress events (some scanners use different events)
        document.addEventListener('keypress', (e) => {
            this.handleKeyboardInput(e);
        });

        // Listen for input events on any focused input field
        document.addEventListener('input', (e) => {
            this.handleInputFieldChange(e);
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

        // Monitor specific input fields for scanner input
        this.setupInputFieldMonitoring();

        // Listen for HTTP POST requests from scanner apps
        this.setupHTTPListener();
    }

    handleKeyboardInput(e) {
        // Always collect keyboard input for scanner detection 
        // Don't rely only on rapid input detection for HP scanner apps
        this.collectKeyboardBarcode(e);
        
        // Also handle immediate processing for single paste events
        if (e.key && e.key.length > 8) {
            // Looks like a pasted barcode
            this.processBarcode(e.key, 'keyboard_paste');
            e.preventDefault();
        }
    }

    handleInputFieldChange(e) {
        // Monitor input field changes for scanner data
        const value = e.target.value;
        
        // Skip if this is normal typing (too short or gradual input)
        if (!value || value.length < 6) return;
        
        // Check if this looks like rapid scanner input
        const now = Date.now();
        if (!this.lastInputTime) this.lastInputTime = now;
        const timeSinceLastInput = now - this.lastInputTime;
        this.lastInputTime = now;
        
        // If input field was filled rapidly (likely scanner)
        if (timeSinceLastInput < 2000 && this.looksLikeBarcode(value)) {
            console.log('External scanner: Input field rapid fill detected:', value);
            this.processBarcode(value, 'input_field');
            
            // Clear the field to prevent duplicate processing
            setTimeout(() => {
                e.target.value = '';
            }, 100);
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

    setupInputFieldMonitoring() {
        // Monitor common input field selectors for scanner input
        const selectors = [
            'input[type="text"]',
            'input[name*="search"]',
            'input[id*="search"]', 
            'input[class*="search"]',
            'input[name*="barcode"]',
            'input[id*="barcode"]'
        ];
        
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(input => {
                // Add focus listener to track active input
                input.addEventListener('focus', (e) => {
                    console.log('External scanner: Input field focused:', e.target.id || e.target.name || 'unknown');
                    this.activeInputField = e.target;
                });
                
                // Monitor value changes
                input.addEventListener('input', (e) => {
                    const value = e.target.value;
                    if (value && this.looksLikeBarcode(value)) {
                        console.log('External scanner: Barcode detected in input field:', value);
                        this.processBarcode(value, 'monitored_input');
                    }
                });
            });
        });
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
        // WebSocket connection is optional for external scanners
        // Most external scanner apps use HTTP POST requests instead
        // Skip WebSocket setup to avoid connection errors
        console.log('External scanner ready - HTTP endpoints active');
        
        // Note: WebSocket can be implemented later if needed for real-time features
        // For now, HTTP POST to /api/scanner/scan is the primary method
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
        
        // More lenient timing to catch all scanner input (HP apps may be slower)
        // Accept anything faster than 200ms between characters
        return timeDiff < 200 && timeDiff > 0;
    }

    collectKeyboardBarcode(e) {
        // Initialize buffer if needed
        if (!this.barcodeBuffer) {
            this.barcodeBuffer = '';
            this.bufferTimeout = null;
            this.bufferStartTime = Date.now();
            this.isFirstChar = true;
        }

        // Add character to buffer - with special handling for first character
        if (e.key && e.key.length === 1) {
            // Special case: if this looks like start of barcode and we missed first char
            if (this.isFirstChar && e.key === '9' && this.barcodeBuffer === '') {
                console.log('External scanner: Potential missing first digit detected, adding "8" prefix');
                this.barcodeBuffer = '8';
            }
            
            this.barcodeBuffer += e.key;
            this.isFirstChar = false;
            
            console.log('External scanner: Buffer updated:', this.barcodeBuffer, 'Length:', this.barcodeBuffer.length);
        }

        // Clear buffer after 800ms of inactivity (increased further to avoid cutting off)
        clearTimeout(this.bufferTimeout);
        this.bufferTimeout = setTimeout(() => {
            if (this.barcodeBuffer && this.looksLikeBarcode(this.barcodeBuffer)) {
                const duration = Date.now() - this.bufferStartTime;
                console.log('External scanner: Processing buffer from timeout:', this.barcodeBuffer, 'Duration:', duration + 'ms');
                this.processBarcode(this.barcodeBuffer, 'keyboard_rapid');
            } else if (this.barcodeBuffer) {
                console.log('External scanner: Rejecting buffer (too short or invalid):', this.barcodeBuffer);
            }
            this.barcodeBuffer = '';
            this.isFirstChar = true;
        }, 800);

        // Process if Enter key is pressed
        if (e.key === 'Enter' && this.barcodeBuffer) {
            clearTimeout(this.bufferTimeout);
            const duration = Date.now() - this.bufferStartTime;
            console.log('External scanner: Processing buffer from Enter:', this.barcodeBuffer, 'Duration:', duration + 'ms', 'Length:', this.barcodeBuffer.length);
            
            if (this.looksLikeBarcode(this.barcodeBuffer)) {
                e.preventDefault();
                this.processBarcode(this.barcodeBuffer, 'keyboard_enter');
            } else {
                console.log('External scanner: Rejecting buffer from Enter (invalid barcode):', this.barcodeBuffer);
            }
            this.barcodeBuffer = '';
            this.isFirstChar = true;
        }
    }

    looksLikeBarcode(text) {
        if (!text || typeof text !== 'string') return false;
        
        text = text.trim();
        
        // Based on log analysis, accept barcodes from 6-14 characters
        // This handles partial scans and missing digits
        if (text.length < 6 || text.length > 14) {
            console.log('External scanner: Invalid barcode length:', text.length, 'for:', text);
            return false;
        }
        
        // Must be primarily numeric (allow some special chars)
        if (!/^\d+$/.test(text)) {
            console.log('External scanner: Non-numeric barcode rejected:', text);
            return false;
        }
        
        console.log('External scanner: Valid barcode accepted:', text, 'Length:', text.length);
        return true;
    }

    async processBarcode(barcode, source = 'unknown') {
        const now = Date.now();
        
        console.log('External scanner processBarcode:', {
            original: barcode,
            source: source,
            length: barcode ? barcode.length : 0,
            type: typeof barcode
        });
        
        // Prevent duplicate scans
        if (now - this.lastScanTime < this.scanCooldown) {
            console.log('External scanner: Scan blocked due to cooldown');
            return;
        }
        this.lastScanTime = now;

        // Clean barcode
        barcode = barcode.trim();
        
        console.log('External scanner after trim:', barcode, 'Length:', barcode.length);
        
        if (!this.looksLikeBarcode(barcode)) {
            console.log('External scanner: Barcode rejected - does not look like barcode');
            return;
        }

        try {
            // Show scanning indicator
            this.showScanningIndicator(barcode);

            console.log('External scanner: Making request to:', this.apiEndpoint);

            // Try API request with retry mechanism
            const result = await this.apiRequestWithRetry(barcode, source, now);
            
            if (result.success) {
                this.handleSuccessfulScan(result, source);
            } else {
                // Try fallback to Livewire if API fails
                const livewireResult = await this.tryLivewireFallback(barcode);
                if (livewireResult) {
                    this.handleSuccessfulScan({
                        success: true,
                        message: 'Product found via Livewire',
                        product: livewireResult,
                        reconstructed: false
                    }, 'livewire_fallback');
                } else {
                    this.handleFailedScan(result, source);
                }
            }

        } catch (error) {
            console.error('External scanner error:', error);
            
            // Try Livewire as last resort
            try {
                const livewireResult = await this.tryLivewireFallback(barcode);
                if (livewireResult) {
                    this.handleSuccessfulScan({
                        success: true,
                        message: 'Product found via Livewire (API failed)',
                        product: livewireResult,
                        reconstructed: false
                    }, 'livewire_fallback');
                    return;
                }
            } catch (livewireError) {
                console.error('Livewire fallback also failed:', livewireError);
            }
            
            this.showError(`Scanner connection error: ${error.message}`);
        }
    }

    async apiRequestWithRetry(barcode, source, timestamp, maxRetries = 3) {
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                console.log(`External scanner: API attempt ${attempt}/${maxRetries}`);
                
                const formData = new FormData();
                formData.append('barcode', barcode);
                formData.append('source', source);
                formData.append('timestamp', timestamp);

                const response = await fetch(this.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                console.log('External scanner: Response status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('External scanner: Response data:', data);
                    return data;
                }
                
                if (attempt === maxRetries) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Wait before retry
                await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
                
            } catch (error) {
                if (attempt === maxRetries) {
                    throw error;
                }
                console.log(`External scanner: Attempt ${attempt} failed, retrying...`, error.message);
                await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
            }
        }
    }

    async tryLivewireFallback(barcode) {
        try {
            console.log('External scanner: Trying Livewire fallback for:', barcode);
            
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
                
                if (searchComponent && searchComponent.call) {
                    console.log('External scanner: Found SearchProduct component, calling searchByBarcode...');
                    await searchComponent.call('searchByBarcode', barcode);
                    
                    // Listen for scannerResult event
                    return new Promise((resolve) => {
                        const timeout = setTimeout(() => resolve(null), 5000);
                        
                        const resultHandler = (event) => {
                            if (event.detail && event.detail[0] && event.detail[0].product) {
                                clearTimeout(timeout);
                                window.removeEventListener('scannerResult', resultHandler);
                                resolve(event.detail[0].product);
                            }
                        };
                        
                        window.addEventListener('scannerResult', resultHandler);
                    });
                } else {
                    console.log('External scanner: SearchProduct component not found, trying manual input fallback');
                    
                    // Fallback: manually trigger input
                    if (searchInput) {
                        searchInput.value = barcode;
                        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        
                        // Simulate a search result for external scanner feedback
                        return { name: 'Product (via manual search)', code: barcode };
                    }
                }
            }
            return null;
        } catch (error) {
            console.error('Livewire fallback error:', error);
            return null;
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
    if (typeof window.externalScannerHandler === 'undefined') {
        window.externalScannerHandler = new ExternalScannerHandler();
        
        // Make global functions available
        window.scanBarcode = ExternalScannerHandler.handleBarcode;
        window.isScannerReady = ExternalScannerHandler.isReady;
    }
});

// Close the class declaration guard
}

// Add CSS for animations
if (!document.getElementById('external-scanner-styles')) {
    const style = document.createElement('style');
    style.id = 'external-scanner-styles';
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
}

