/**
 * Thermal Printer ESC Commands for Eppos EP220II
 * Based on 80MM THERMAL RECEIPT PRINTER PROGRAMMER MANUAL
 */

class ThermalPrinterCommands {
    
    constructor() {
        // ESC commands dari manual
        this.ESC = '\x1B'; // 27
        this.GS = '\x1D';  // 29
        this.LF = '\x0A';  // 10 - Line feed
        this.CR = '\x0D';  // 13 - Carriage return
        this.FF = '\x0C';  // 12 - Form feed
        this.CUT = '\x1B\x69'; // ESC i - Partial cut
    }

    // Initialize printer dengan pengaturan optimal untuk 80mm
    initializePrinter() {
        let commands = '';
        
        // ESC @ - Initialize printer (halaman 9 manual)
        commands += this.ESC + '@';
        
        // ESC 2 - Select default line spacing (3.75mm)
        commands += this.ESC + '2';
        
        // ESC M - Select character font A (12x24) untuk ketajaman
        commands += this.ESC + 'M' + '\x00';
        
        // ESC a - Set justification to left
        commands += this.ESC + 'a' + '\x00';
        
        // GS L - Set left margin to 0
        commands += this.GS + 'L' + '\x00' + '\x00';
        
        return commands;
    }

    // Set ukuran font optimal untuk thermal
    setOptimalFont() {
        let commands = '';
        
        // ESC ! - Select print mode untuk font A dengan normal size
        // Bit 0 = 0 (Font A), Bit 3-7 = 0 (normal size)
        commands += this.ESC + '!' + '\x00';
        
        // ESC SP - Set character spacing minimal
        commands += this.ESC + ' ' + '\x00';
        
        return commands;
    }

    // Set line spacing minimal untuk menghemat kertas
    setMinimalLineSpacing() {
        // ESC 3 n - Set line spacing ke minimum (n=20)
        return this.ESC + '3' + '\x14'; // 20 in decimal
    }

    // Cut paper command
    cutPaper() {
        // ESC i - Partial cut (halaman 14 manual)
        return this.ESC + 'i';
    }

    // Feed minimal paper sebelum cut
    feedAndCut() {
        let commands = '';
        
        // Feed 3 lines sebelum cut
        commands += this.LF + this.LF + this.LF;
        
        // Cut paper
        commands += this.cutPaper();
        
        return commands;
    }

    // Set print density (untuk mencegah print terlalu tebal/tipis)
    setPrintDensity(level = 2) {
        // Level 1-5, default 2 untuk thermal
        if (level < 1) level = 1;
        if (level > 5) level = 5;
        
        // Custom command untuk density (vendor specific)
        return '\x1D\x7C\x00' + String.fromCharCode(level);
    }

    // Generate complete thermal print command
    generateThermalCommands(content) {
        let commands = '';
        
        // 1. Initialize
        commands += this.initializePrinter();
        
        // 2. Set optimal settings
        commands += this.setOptimalFont();
        commands += this.setMinimalLineSpacing();
        commands += this.setPrintDensity(2);
        
        // 3. Content (akan diproses oleh browser)
        // Commands di atas akan dijalankan oleh printer driver
        
        // 4. Feed and cut
        commands += this.feedAndCut();
        
        return commands;
    }
}

// Fungsi untuk inject thermal commands ke print job
function injectThermalCommands() {
    const thermalPrinter = new ThermalPrinterCommands();
    const commands = thermalPrinter.generateThermalCommands();
    
    // Inject commands sebagai hidden div yang akan dibaca driver
    const commandDiv = document.createElement('div');
    commandDiv.style.display = 'none';
    commandDiv.className = 'thermal-commands';
    commandDiv.setAttribute('data-thermal-init', btoa(thermalPrinter.initializePrinter()));
    commandDiv.setAttribute('data-thermal-end', btoa(thermalPrinter.feedAndCut()));
    
    document.body.appendChild(commandDiv);
    
    return commands;
}

// Auto inject saat halaman thermal print load
if (window.location.href.includes('/thermal/print/')) {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Injecting thermal printer commands...');
        injectThermalCommands();
    });
}

// Export untuk digunakan di file lain
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThermalPrinterCommands;
}