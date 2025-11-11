/**
 * Auto Printer Detection System
 * Detects available printers and auto-selects thermal/dot matrix printers
 */
class PrinterManager {
    constructor() {
        this.availablePrinters = [];
        this.defaultPrinter = null;
        this.userPreferences = null;
        this.systemSettings = null;
        this.init();
    }

    async init() {
        await this.loadSystemSettings();
        await this.loadUserPreferences();
        await this.detectPrinters();
        this.setupPrintHandlers();
    }

    async loadSystemSettings() {
        try {
            const response = await fetch('/api/system-printer-settings');
            this.systemSettings = await response.json();
        } catch (error) {
            console.log('Using default system settings');
            this.systemSettings = {
                receipt_paper_size: '80mm',
                auto_print_receipt: false,
                default_receipt_printer: null
            };
        }
    }

    async loadUserPreferences() {
        try {
            const response = await fetch('/api/user-printer-preferences');
            this.userPreferences = await response.json();
        } catch (error) {
            console.log('No user preferences found, using defaults');
            this.userPreferences = this.systemSettings;
        }
    }

    async detectPrinters() {
        // Try to detect printers using browser API
        if ('navigator' in window && 'printing' in navigator) {
            try {
                const printers = await navigator.printing.getPrinters();
                this.availablePrinters = printers;
            } catch (error) {
                console.log('Printer detection not supported, using fallback');
            }
        }

        // Fallback: Common thermal printer detection patterns
        this.detectThermalPrinters();
        this.selectBestPrinter();
    }

    detectThermalPrinters() {
        // Common thermal/POS printer names
        const thermalPatterns = [
            /epson.*tm/i,
            /pos.*80/i,
            /pos.*58/i,
            /thermal/i,
            /receipt/i,
            /star.*tsp/i,
            /citizen/i,
            /bixolon/i,
            /custom.*vkp/i
        ];

        // Simulate printer detection (in real scenario, this would query system)
        const commonPrinters = [
            'EPSON TM-T20II Receipt',
            'POS-80 Thermal Printer',
            'Star TSP143III',
            'Citizen CT-S310A',
            'Microsoft Print to PDF',
            'HP LaserJet Pro'
        ];

        this.availablePrinters = commonPrinters.map(name => ({
            name: name,
            isThermal: thermalPatterns.some(pattern => pattern.test(name)),
            isDotMatrix: /dot.*matrix|lq|fx/i.test(name),
            isPOS: /pos|receipt|thermal|tm-|tsp/i.test(name)
        }));
    }

    selectBestPrinter() {
        // Priority: User preference > Thermal/POS > Default system printer
        if (this.userPreferences?.receipt_printer_name) {
            this.defaultPrinter = this.availablePrinters.find(p => 
                p.name === this.userPreferences.receipt_printer_name
            );
        }

        if (!this.defaultPrinter) {
            // Auto-select thermal or POS printer
            this.defaultPrinter = this.availablePrinters.find(p => p.isPOS || p.isThermal);
        }

        if (!this.defaultPrinter && this.systemSettings?.default_receipt_printer) {
            this.defaultPrinter = this.availablePrinters.find(p => 
                p.name === this.systemSettings.default_receipt_printer
            );
        }

        // Fallback to first available printer
        if (!this.defaultPrinter && this.availablePrinters.length > 0) {
            this.defaultPrinter = this.availablePrinters[0];
        }

        console.log('Selected printer:', this.defaultPrinter);
    }

    setupPrintHandlers() {
        // Override global print function
        window.printWithOptimalSettings = (content, options = {}) => {
            this.printOptimal(content, options);
        };
    }

    async printOptimal(content, options = {}) {
        const settings = {
            printer: options.printer || this.defaultPrinter?.name,
            paperSize: options.paperSize || this.getUserPaperSize(),
            autoMargins: true,
            ...options
        };

        // Use different print strategies based on printer type
        if (this.defaultPrinter?.isThermal || this.defaultPrinter?.isPOS) {
            await this.printThermal(content, settings);
        } else {
            await this.printStandard(content, settings);
        }
    }

    async printThermal(content, settings) {
        // For thermal printers, use minimal margins and specific paper size
        const printWindow = window.open('', 'thermalPrint', 
            'width=400,height=600,scrollbars=no,resizable=no');
        
        if (printWindow) {
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body { 
                            font-family: 'Courier New', monospace; 
                            font-size: 12px; 
                            margin: 0; 
                            padding: 5px;
                            background: white;
                        }
                        @page { 
                            size: ${settings.paperSize} auto; 
                            margin: 0;
                        }
                        @media print {
                            body { margin: 0; padding: 0; }
                        }
                    </style>
                </head>
                <body>${content}</body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 1000);
            }, 500);
        }
    }

    async printStandard(content, settings) {
        // For regular printers, use standard print dialog
        const printWindow = window.open('', 'standardPrint', 
            'width=800,height=600,scrollbars=yes,resizable=yes');
        
        if (printWindow) {
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            font-size: 14px; 
                            margin: 20px;
                            background: white;
                        }
                        @page { 
                            size: ${settings.paperSize}; 
                            margin: 0.5in;
                        }
                    </style>
                </head>
                <body>${content}</body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 1000);
            }, 500);
        }
    }

    getUserPaperSize() {
        return this.userPreferences?.receipt_paper_size || 
               this.systemSettings?.receipt_paper_size || 
               '80mm';
    }

    async savePrinterPreference(printerName, settings) {
        try {
            await fetch('/api/user-printer-preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    receipt_printer_name: printerName,
                    ...settings
                })
            });
            
            // Reload preferences
            await this.loadUserPreferences();
        } catch (error) {
            console.error('Failed to save printer preference:', error);
        }
    }

    showPrinterSelector() {
        const modal = `
            <div id="printer-selector-modal" class="modal fade show" style="display: block;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Printer</h5>
                            <button type="button" class="close" onclick="document.getElementById('printer-selector-modal').remove()">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Available Printers:</label>
                                <select id="printer-select" class="form-control">
                                    ${this.availablePrinters.map(p => 
                                        `<option value="${p.name}" ${p.name === this.defaultPrinter?.name ? 'selected' : ''}>
                                            ${p.name} ${p.isPOS ? '(POS)' : ''} ${p.isThermal ? '(Thermal)' : ''}
                                        </option>`
                                    ).join('')}
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Paper Size:</label>
                                <select id="paper-size-select" class="form-control">
                                    <option value="58mm">58mm (Small Thermal)</option>
                                    <option value="80mm" selected>80mm (Standard Thermal)</option>
                                    <option value="letter">Letter</option>
                                    <option value="a4">A4</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" onclick="printerManager.saveAndPrint()">Save & Print</button>
                            <button class="btn btn-secondary" onclick="document.getElementById('printer-selector-modal').remove()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modal);
    }

    async saveAndPrint() {
        const printerName = document.getElementById('printer-select').value;
        const paperSize = document.getElementById('paper-size-select').value;
        
        await this.savePrinterPreference(printerName, {
            receipt_paper_size: paperSize,
            auto_print_receipt: this.userPreferences?.auto_print_receipt || false
        });
        
        document.getElementById('printer-selector-modal').remove();
        
        // Trigger the pending print
        if (window.pendingPrintContent) {
            this.printOptimal(window.pendingPrintContent);
            window.pendingPrintContent = null;
        }
    }
}

// Initialize printer manager when page loads
let printerManager;
document.addEventListener('DOMContentLoaded', function() {
    printerManager = new PrinterManager();
});