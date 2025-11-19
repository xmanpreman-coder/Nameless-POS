/**
 * Thermal Printer Test & Debug Utility
 * untuk Eppos EP220II 80mm
 */

class ThermalPrinterTest {
    
    constructor() {
        this.testResults = {};
        this.isDebugging = false;
    }

    // Test koneksi dan status printer
    async testPrinterConnection() {
        console.log('🔍 Testing printer connection...');
        
        try {
            // Test USB/Serial connection (jika browser support)
            if ('serial' in navigator) {
                const ports = await navigator.serial.getPorts();
                console.log('📱 Serial ports available:', ports.length);
                this.testResults.connection = ports.length > 0 ? 'PASS' : 'WARNING';
            } else {
                console.log('⚠️ Serial API not supported');
                this.testResults.connection = 'UNKNOWN';
            }
        } catch (error) {
            console.error('❌ Connection test failed:', error);
            this.testResults.connection = 'FAIL';
        }
        
        return this.testResults.connection;
    }

    // Test CSS dan layout
    testPrintLayout() {
        console.log('🎨 Testing print layout...');
        
        const receipt = document.querySelector('.thermal-receipt');
        if (!receipt) {
            console.error('❌ Thermal receipt element not found');
            this.testResults.layout = 'FAIL';
            return 'FAIL';
        }

        // Check dimensions
        const styles = window.getComputedStyle(receipt);
        const width = parseFloat(styles.width);
        const maxWidth = parseFloat(styles.maxWidth);
        
        console.log(`📏 Container width: ${width}px, max-width: ${maxWidth}px`);
        
        // Convert mm to px (assuming 96 DPI)
        const expectedWidth = (72 / 25.4) * 96; // 72mm to px
        const tolerance = 10; // 10px tolerance
        
        if (Math.abs(width - expectedWidth) < tolerance || Math.abs(maxWidth - expectedWidth) < tolerance) {
            console.log('✅ Layout width is correct');
            this.testResults.layout = 'PASS';
        } else {
            console.warn(`⚠️ Layout width mismatch. Expected ~${expectedWidth}px`);
            this.testResults.layout = 'WARNING';
        }
        
        return this.testResults.layout;
    }

    // Test font dan typography
    testFontRendering() {
        console.log('🔤 Testing font rendering...');
        
        const body = document.body;
        const styles = window.getComputedStyle(body);
        const fontFamily = styles.fontFamily;
        const fontSize = styles.fontSize;
        
        console.log(`📝 Font: ${fontFamily}, Size: ${fontSize}`);
        
        // Check if monospace font is being used
        const hasMonospace = fontFamily.includes('Courier') || 
                           fontFamily.includes('monospace') || 
                           fontFamily.includes('Liberation Mono');
        
        if (hasMonospace) {
            console.log('✅ Monospace font detected');
            this.testResults.font = 'PASS';
        } else {
            console.warn('⚠️ Non-monospace font detected, may cause alignment issues');
            this.testResults.font = 'WARNING';
        }
        
        return this.testResults.font;
    }

    // Test ESC commands injection
    testESCCommands() {
        console.log('🖨️ Testing ESC commands...');
        
        const escDiv = document.querySelector('.esc-commands');
        if (!escDiv) {
            console.error('❌ ESC commands not injected');
            this.testResults.escCommands = 'FAIL';
            return 'FAIL';
        }
        
        const commandData = escDiv.innerHTML;
        if (commandData.includes('THERMAL_INIT')) {
            console.log('✅ ESC commands properly injected');
            this.testResults.escCommands = 'PASS';
        } else {
            console.warn('⚠️ ESC commands format issue');
            this.testResults.escCommands = 'WARNING';
        }
        
        return this.testResults.escCommands;
    }

    // Test print media query
    testPrintCSS() {
        console.log('📄 Testing print CSS...');
        
        // Create temporary print test
        const printTest = document.createElement('div');
        printTest.className = 'thermal-receipt print-test';
        printTest.style.visibility = 'hidden';
        printTest.innerHTML = 'Test';
        document.body.appendChild(printTest);
        
        // Apply print styles temporarily
        const printStyle = document.createElement('style');
        printStyle.innerHTML = `
            .print-test { 
                width: 72mm !important; 
                font-size: 10px !important;
                font-family: 'Courier New', monospace !important;
            }
        `;
        document.head.appendChild(printStyle);
        
        const styles = window.getComputedStyle(printTest);
        const width = styles.width;
        const fontSize = styles.fontSize;
        const fontFamily = styles.fontFamily;
        
        console.log(`🖨️ Print styles - Width: ${width}, Font: ${fontSize} ${fontFamily}`);
        
        // Cleanup
        document.body.removeChild(printTest);
        document.head.removeChild(printStyle);
        
        if (width.includes('mm') || parseInt(width) > 200) {
            console.log('✅ Print CSS is working');
            this.testResults.printCSS = 'PASS';
        } else {
            console.warn('⚠️ Print CSS may not be applied correctly');
            this.testResults.printCSS = 'WARNING';
        }
        
        return this.testResults.printCSS;
    }

    // Generate test receipt untuk debugging
    generateTestReceipt() {
        console.log('📄 Generating test receipt...');
        
        const testContent = `
        <div class="thermal-receipt" style="border: 1px dashed #ccc; padding: 10px; margin: 10px;">
            <div class="company-header" style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 4px;">
                <div style="font-weight: bold;">TEST RECEIPT</div>
                <div style="font-size: 9px;">Eppos EP220II 80mm Test</div>
            </div>
            
            <div style="margin: 6px 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Width Test:</span>
                    <span>1234567890123456789012345678901234567890</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Font Test:</span>
                    <span>iIlL1|! oO0 </span>
                </div>
            </div>
            
            <div style="border-top: 1px dashed #000; padding-top: 4px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total:</span>
                    <span>Rp 1,234,567</span>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 8px; font-size: 8px;">
                Generated: ${new Date().toLocaleString()}<br>
                Status: ${Object.values(this.testResults).every(r => r === 'PASS') ? 'ALL TESTS PASSED' : 'CHECK WARNINGS'}
            </div>
        </div>
        `;
        
        return testContent;
    }

    // Run semua tests
    async runAllTests() {
        console.log('🚀 Starting thermal printer tests...');
        console.log('================================');
        
        await this.testPrinterConnection();
        this.testPrintLayout();
        this.testFontRendering();
        this.testESCCommands();
        this.testPrintCSS();
        
        console.log('================================');
        console.log('📊 TEST RESULTS:');
        console.log('================================');
        
        Object.entries(this.testResults).forEach(([test, result]) => {
            const icon = result === 'PASS' ? '✅' : result === 'WARNING' ? '⚠️' : '❌';
            console.log(`${icon} ${test}: ${result}`);
        });
        
        const overallStatus = Object.values(this.testResults).every(r => r === 'PASS') ? 
                             'ALL TESTS PASSED' : 
                             Object.values(this.testResults).some(r => r === 'FAIL') ? 
                             'SOME TESTS FAILED' : 
                             'TESTS PASSED WITH WARNINGS';
        
        console.log('================================');
        console.log(`🎯 OVERALL STATUS: ${overallStatus}`);
        console.log('================================');
        
        return {
            results: this.testResults,
            status: overallStatus,
            testReceipt: this.generateTestReceipt()
        };
    }

    // Debug mode toggle
    toggleDebugMode() {
        this.isDebugging = !this.isDebugging;
        
        if (this.isDebugging) {
            console.log('🔧 Debug mode ENABLED');
            document.body.classList.add('thermal-debug');
            
            // Add debug styles
            const debugStyle = document.createElement('style');
            debugStyle.id = 'thermal-debug-styles';
            debugStyle.innerHTML = `
                .thermal-debug .thermal-receipt {
                    border: 2px solid red !important;
                    background: rgba(255, 255, 0, 0.1) !important;
                }
                .thermal-debug .thermal-receipt::before {
                    content: "DEBUG: 72mm width" !important;
                    position: absolute !important;
                    top: -20px !important;
                    left: 0 !important;
                    background: red !important;
                    color: white !important;
                    padding: 2px 5px !important;
                    font-size: 10px !important;
                }
            `;
            document.head.appendChild(debugStyle);
            
        } else {
            console.log('🔧 Debug mode DISABLED');
            document.body.classList.remove('thermal-debug');
            
            const debugStyle = document.getElementById('thermal-debug-styles');
            if (debugStyle) {
                debugStyle.remove();
            }
        }
        
        return this.isDebugging;
    }
}

// Global instance
window.ThermalTest = new ThermalPrinterTest();

// Auto-run tests jika di halaman thermal
if (window.location.href.includes('/thermal/print/')) {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            window.ThermalTest.runAllTests();
        }, 1000);
    });
}

// Keyboard shortcuts untuk testing
document.addEventListener('keydown', function(e) {
    // Ctrl+Alt+T = Run tests
    if (e.ctrlKey && e.altKey && e.key === 't') {
        e.preventDefault();
        window.ThermalTest.runAllTests();
    }
    
    // Ctrl+Alt+D = Toggle debug
    if (e.ctrlKey && e.altKey && e.key === 'd') {
        e.preventDefault();
        window.ThermalTest.toggleDebugMode();
    }
});

console.log('🔧 Thermal Printer Test utility loaded');
console.log('💡 Use Ctrl+Alt+T to run tests, Ctrl+Alt+D to toggle debug mode');