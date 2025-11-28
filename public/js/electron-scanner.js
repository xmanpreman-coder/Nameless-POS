/**
 * Electron Scanner Handler
 * Bridges hardware scanner input between Electron main process and web content
 */

// Only load in Electron environment
if (typeof window.electronAPI !== 'undefined') {
    
    class ElectronScannerHandler {
        constructor() {
            this.barcodeBuffer = '';
            this.bufferTimeout = null;
            this.lastInputTime = 0;
            this.isProcessing = false;
            
            console.log('[ElectronScanner] Initializing Electron scanner handler...');
            this.initializeElectronBridge();
            this.setupGlobalHandler();
        }
        
        initializeElectronBridge() {
            // Listen for scanner input from main process
            if (window.electronAPI && window.electronAPI.onScannerInput) {
                window.electronAPI.onScannerInput((event, barcode) => {
                    console.log('[ElectronScanner] Received barcode from main process:', barcode);
                    this.processBarcode(barcode);
                });
            }
        }
        
        setupGlobalHandler() {
            // Global function for character-by-character input
            window.handleBarcodeCharInput = (char) => {
                const now = Date.now();
                
                // If too much time passed, start new barcode
                if (now - this.lastInputTime > 500) {
                    this.barcodeBuffer = '';
                }
                
                this.barcodeBuffer += char;
                this.lastInputTime = now;
                
                // Clear existing timeout
                if (this.bufferTimeout) {
                    clearTimeout(this.bufferTimeout);
                }
                
                // Set timeout to process buffer
                this.bufferTimeout = setTimeout(() => {
                    if (this.barcodeBuffer && this.barcodeBuffer.length >= 6) {
                        console.log('[ElectronScanner] Processing buffered barcode:', this.barcodeBuffer);
                        this.processBarcode(this.barcodeBuffer);
                        this.barcodeBuffer = '';
                    }
                }, 100);
            };
            
            // Enhanced keyboard listener for Electron
            document.addEventListener('keydown', (e) => {
                // Don't interfere if user is typing in an input field
                const activeElement = document.activeElement;
                if (activeElement && (
                    activeElement.tagName === 'INPUT' || 
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.isContentEditable
                )) {
                    return;
                }
                
                // Handle numeric input as potential scanner data
                if (e.key.match(/^[0-9]$/)) {
                    window.handleBarcodeCharInput(e.key);
                } else if (e.key === 'Enter' && this.barcodeBuffer) {
                    // Process on Enter
                    console.log('[ElectronScanner] Enter pressed, processing barcode:', this.barcodeBuffer);
                    this.processBarcode(this.barcodeBuffer);
                    this.barcodeBuffer = '';
                }
            });
            
            // Listen for paste events (some scanner apps use clipboard)
            document.addEventListener('paste', (e) => {
                const pastedText = e.clipboardData.getData('text');
                if (this.looksLikeBarcode(pastedText)) {
                    console.log('[ElectronScanner] Barcode pasted:', pastedText);
                    this.processBarcode(pastedText);
                    e.preventDefault();
                }
            });
            
            console.log('[ElectronScanner] Global handlers setup complete');
        }
        
        looksLikeBarcode(text) {
            if (!text || typeof text !== 'string') return false;
            text = text.trim();
            return text.length >= 6 && text.length <= 14 && /^[a-zA-Z0-9]+$/.test(text);
        }
        
        async processBarcode(barcode) {
            if (this.isProcessing) {
                console.log('[ElectronScanner] Already processing, skipping:', barcode);
                return;
            }
            
            barcode = barcode.trim();
            if (!this.looksLikeBarcode(barcode)) {
                console.log('[ElectronScanner] Invalid barcode format:', barcode);
                return;
            }
            
            this.isProcessing = true;
            
            try {
                console.log('[ElectronScanner] Processing barcode:', barcode);
                
                // Try to use existing scanner handlers first
                if (window.ExternalScannerHandler && typeof window.ExternalScannerHandler.prototype.processBarcode === 'function') {
                    // Use the existing external scanner handler
                    const handler = new window.ExternalScannerHandler();
                    await handler.processBarcode(barcode, 'electron_bridge');
                } else if (window.handleBarcodeInput && typeof window.handleBarcodeInput === 'function') {
                    // Fallback to global handler
                    window.handleBarcodeInput(barcode);
                } else {
                    // Direct Livewire integration
                    await this.directLivewireSearch(barcode);
                }
                
            } catch (error) {
                console.error('[ElectronScanner] Error processing barcode:', error);
            } finally {
                this.isProcessing = false;
            }
        }
        
        async directLivewireSearch(barcode) {
            try {
                // Find SearchProduct Livewire component
                const searchComponent = window.Livewire?.find(component => 
                    component.name === 'search-product' || 
                    component.el?.classList?.contains('search-product')
                );
                
                if (searchComponent) {
                    console.log('[ElectronScanner] Found SearchProduct component, calling searchByBarcode');
                    await searchComponent.call('searchByBarcode', barcode);
                } else {
                    // Fallback to manual input
                    const searchInput = document.querySelector('input[wire\\:model*="search"]');
                    if (searchInput) {
                        console.log('[ElectronScanner] Using manual search input fallback');
                        searchInput.value = barcode;
                        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        searchInput.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
                    }
                }
            } catch (error) {
                console.error('[ElectronScanner] Direct Livewire search failed:', error);
            }
        }
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.electronScannerHandler === 'undefined') {
            window.electronScannerHandler = new ElectronScannerHandler();
            console.log('[ElectronScanner] Electron scanner handler initialized');
        }
    });
    
    // Also initialize immediately if DOM is already ready
    if (document.readyState !== 'loading') {
        if (typeof window.electronScannerHandler === 'undefined') {
            window.electronScannerHandler = new ElectronScannerHandler();
            console.log('[ElectronScanner] Electron scanner handler initialized (immediate)');
        }
    }
    
} else {
    console.log('[ElectronScanner] Not in Electron environment, skipping initialization');
}