<?php

namespace App\Services;

use App\Models\ThermalPrinterSetting;
use Modules\Sale\Entities\Sale;
use Modules\People\Entities\Customer;

class ThermalPrinterService
{
    private $printer;
    
    public function __construct(ThermalPrinterSetting $printer = null)
    {
        $this->printer = $printer ?: ThermalPrinterSetting::getDefault();
    }

    /**
     * Print sale receipt
     */
    public function printSaleReceipt(Sale $sale, $printerId = null)
    {
        if ($printerId) {
            $this->printer = ThermalPrinterSetting::findOrFail($printerId);
        }

        $content = $this->generateSaleReceiptContent($sale);
        
        // Check receipt_copies setting and print N times
        $copies = $this->getReceiptCopies();
        $result = null;
        
        for ($i = 0; $i < $copies; $i++) {
            $result = $this->print($content);
            if (!$result['success'] && $i === 0) {
                // If first copy fails, stop and return error
                return $result;
            }
            // Small delay between copies to avoid printer buffer overflow
            if ($i < $copies - 1) {
                usleep(200000); // 200ms delay
            }
        }
        
        // Append copy count info to final result
        if ($result && $copies > 1) {
            $result['message'] .= " ($copies copies)";
        }
        
        return $result;
    }
    
    /**
     * Get receipt copies from config or PrinterSetting model
     */
    private function getReceiptCopies()
    {
        try {
            // Try to get from PrinterSetting model (general app setting)
            $setting = \App\Models\PrinterSetting::first();
            if ($setting && $setting->receipt_copies) {
                return max(1, (int) $setting->receipt_copies);
            }
        } catch (\Exception $e) {
            // Model not found or table doesn't exist
        }
        
        // Fallback to config or default to 1
        return max(1, config('printer.receipt_copies', 1));
    }

    /**
     * Generate sale receipt content with ESC commands
     */
    private function generateSaleReceiptContent(Sale $sale)
    {
        $content = [];
        $customer = Customer::find($sale->customer_id);
        
        // Initialize printer dengan ESC commands berdasarkan manual
        $content[] = $this->printer->generateInitCommand();
        
        // Company header dengan center alignment
        $content[] = $this->escCommand('a', 1); // Center alignment
        $content[] = $this->escCommand('!', 8); // Emphasized mode
        $content[] = settings()->company_name . "\n";
        $content[] = $this->escCommand('!', 0); // Normal mode
        $content[] = settings()->company_address . "\n";
        if (settings()->company_phone) {
            $content[] = "Tel: " . settings()->company_phone . "\n";
        }
        if (settings()->company_email) {
            $content[] = settings()->company_email . "\n";
        }
        
        // Separator line
        $content[] = $this->generateSeparatorLine();
        
        // Receipt title
        $content[] = $this->escCommand('a', 1); // Center
        $content[] = $this->escCommand('!', 8); // Emphasized
        $content[] = "SALES RECEIPT\n";
        $content[] = $this->escCommand('!', 0); // Normal
        $content[] = $this->escCommand('a', 0); // Left align
        
        // Transaction info
        $content[] = $this->generateSeparatorLine();
        $content[] = sprintf("Receipt#: %s\n", $sale->reference);
        $content[] = sprintf("Date: %s\n", \Carbon\Carbon::parse($sale->date)->format('d/m/y H:i'));
        $content[] = sprintf("Customer: %s\n", $customer->customer_name ?? 'Walk-in');
        $content[] = sprintf("Cashier: %s\n", $sale->user->name ?? 'System');
        $content[] = $this->generateSeparatorLine();
        
        // Items header
        $content[] = $this->formatReceiptLine("Item", "Qty", "Amount", true);
        $content[] = $this->generateSeparatorLine('-');
        
        // Items list
        foreach ($sale->saleDetails as $item) {
            $content[] = $item->product_name . "\n";
            $content[] = $this->formatReceiptLine(
                format_currency($item->unit_price), 
                $item->quantity,
                format_currency($item->sub_total)
            );
        }
        
        $content[] = $this->generateSeparatorLine();
        
        // Totals
        $subtotal = $sale->total_amount + $sale->discount_amount - $sale->tax_amount - $sale->shipping_amount;
        $content[] = $this->formatReceiptLine("Subtotal:", "", format_currency($subtotal));
        
        if ($sale->discount_amount > 0) {
            $content[] = $this->formatReceiptLine(
                "Discount (" . $sale->discount_percentage . "%):", 
                "", 
                "-" . format_currency($sale->discount_amount)
            );
        }
        
        if ($sale->tax_amount > 0) {
            $content[] = $this->formatReceiptLine(
                "Tax (" . $sale->tax_percentage . "%):", 
                "", 
                format_currency($sale->tax_amount)
            );
        }
        
        if ($sale->shipping_amount > 0) {
            $content[] = $this->formatReceiptLine("Shipping:", "", format_currency($sale->shipping_amount));
        }
        
        // Grand total dengan emphasized
        $content[] = $this->generateSeparatorLine();
        $content[] = $this->escCommand('!', 8); // Emphasized
        $content[] = $this->formatReceiptLine("TOTAL:", "", format_currency($sale->total_amount));
        $content[] = $this->escCommand('!', 0); // Normal
        
        // Payment info
        $content[] = $this->generateSeparatorLine();
        $content[] = $this->formatReceiptLine(
            "Payment (" . $sale->payment_method . "):", 
            "", 
            format_currency($sale->paid_amount)
        );
        
        if ($sale->paid_amount > $sale->total_amount) {
            $content[] = $this->formatReceiptLine(
                "Change:", 
                "", 
                format_currency($sale->paid_amount - $sale->total_amount)
            );
        }
        
        // Barcode (jika printer support)
        if ($this->printer->print_barcode && $this->printerSupportsBarcode()) {
            $content[] = "\n";
            $content[] = $this->escCommand('a', 1); // Center
            $content[] = $this->generateBarcode($sale->reference);
            $content[] = $this->escCommand('a', 0); // Left
        }
        
        // Footer
        $content[] = "\n";
        $content[] = $this->escCommand('a', 1); // Center
        if ($this->printer->footer_text) {
            $content[] = $this->printer->footer_text . "\n";
        } else {
            $content[] = "Thank you for your business!\n";
            $content[] = settings()->company_name . "\n";
        }
        $content[] = date('Y') . "\n";
        $content[] = $this->escCommand('a', 0); // Left
        
        // Cut paper
        $content[] = $this->printer->generateCutCommand();
        
        return implode('', $content);
    }

    /**
     * Generate ESC command
     */
    private function escCommand($command, $parameter = null)
    {
        switch ($command) {
            case 'a': // Justification
                return "\x1B\x61" . chr($parameter);
            case '!': // Print mode
                return "\x1B\x21" . chr($parameter);
            case '2': // Default line spacing
                return "\x1B\x32";
            case '3': // Set line spacing
                return "\x1B\x33" . chr($parameter);
            case 'SP': // Character spacing
                return "\x1B\x20" . chr($parameter);
            case '-': // Underline
                return "\x1B\x2D" . chr($parameter);
            case 'E': // Emphasized
                return "\x1B\x45" . chr($parameter);
            case 'M': // Font selection
                return "\x1B\x4D" . chr($parameter);
            case 'i': // Partial cut
                return "\x1B\x69";
            case 'm': // Full cut
                return "\x1B\x6D";
            case '@': // Initialize
                return "\x1B\x40";
            default:
                return '';
        }
    }

    /**
     * Generate separator line based on paper width
     */
    private function generateSeparatorLine($char = '=')
    {
        $width = $this->getLineWidth();
        return str_repeat($char, $width) . "\n";
    }

    /**
     * Get line width based on paper width and font
     */
    private function getLineWidth()
    {
        $paperWidth = (int) $this->printer->paper_width;
        $fontSize = $this->printer->font_size;
        
        // Approximate character counts for different paper widths
        $charCounts = [
            '58' => ['small' => 42, 'normal' => 32, 'large' => 24],
            '80' => ['small' => 56, 'normal' => 42, 'large' => 32],
            '112' => ['small' => 76, 'normal' => 56, 'large' => 42]
        ];
        
        return $charCounts[$paperWidth][$fontSize] ?? 42;
    }

    /**
     * Format receipt line dengan kolom
     */
    private function formatReceiptLine($col1, $col2 = '', $col3 = '', $bold = false)
    {
        $width = $this->getLineWidth();
        
        if ($col2 === '' && $col3 === '') {
            // Single column
            return ($bold ? $this->escCommand('!', 8) : '') . $col1 . ($bold ? $this->escCommand('!', 0) : '') . "\n";
        } elseif ($col2 === '') {
            // Two columns (left and right)
            $col1Len = mb_strlen($col1);
            $col3Len = mb_strlen($col3);
            $spaces = $width - $col1Len - $col3Len;
            $spaces = max(1, $spaces); // At least 1 space
            
            return ($bold ? $this->escCommand('!', 8) : '') . 
                   $col1 . str_repeat(' ', $spaces) . $col3 . 
                   ($bold ? $this->escCommand('!', 0) : '') . "\n";
        } else {
            // Three columns
            $col1Width = intval($width * 0.5);
            $col2Width = intval($width * 0.15);
            $col3Width = intval($width * 0.35);
            
            $line = ($bold ? $this->escCommand('!', 8) : '') .
                    str_pad(mb_substr($col1, 0, $col1Width), $col1Width) .
                    str_pad(mb_substr($col2, 0, $col2Width), $col2Width, ' ', STR_PAD_LEFT) .
                    str_pad(mb_substr($col3, 0, $col3Width), $col3Width, ' ', STR_PAD_LEFT) .
                    ($bold ? $this->escCommand('!', 0) : '') . "\n";
            
            return $line;
        }
    }

    /**
     * Generate barcode (CODE128)
     */
    private function generateBarcode($data)
    {
        if (!$this->printerSupportsBarcode()) {
            return '';
        }
        
        $commands = [];
        
        // Set barcode height (GS h)
        $commands[] = "\x1D\x68" . chr(80); // 80 dots height
        
        // Set HRI position (GS H) - below barcode
        $commands[] = "\x1D\x48" . chr(2);
        
        // Print CODE128 barcode (GS k)
        $commands[] = "\x1D\x6B\x49" . chr(strlen($data)) . $data;
        
        return implode('', $commands);
    }

    /**
     * Check if printer supports barcode
     */
    private function printerSupportsBarcode()
    {
        if (!$this->printer->capabilities) {
            return true; // Assume yes if not specified
        }
        
        $capabilities = is_array($this->printer->capabilities) 
            ? $this->printer->capabilities 
            : json_decode($this->printer->capabilities, true);
            
        return isset($capabilities['barcode']) && !empty($capabilities['barcode']);
    }

    /**
     * Print content to printer
     */
    private function print($content)
    {
        try {
            switch ($this->printer->connection_type) {
                case 'ethernet':
                case 'wifi':
                    return $this->printToNetwork($content);
                    
                case 'usb':
                    return $this->printToUSB($content);
                    
                case 'serial':
                    return $this->printToSerial($content);
                    
                case 'bluetooth':
                    return $this->printToBluetooth($content);
                    
                default:
                    throw new \Exception('Unsupported connection type: ' . $this->printer->connection_type);
            }
        } catch (\Exception $e) {
            \Log::error('Thermal printer error: ' . $e->getMessage(), [
                'printer' => $this->printer->name,
                'connection' => $this->printer->connection_type
            ]);
            
            return [
                'success' => false,
                'message' => 'Print failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Print to network printer
     */
    private function printToNetwork($content)
    {
        // Prefer using PrinterManager (mike42/escpos-php) if available
        try {
            if (class_exists(\App\Services\PrinterManager::class)) {
                \App\Services\PrinterManager::printRaw($content, [
                    'type' => 'network',
                    'host' => $this->printer->ip_address,
                    'port' => $this->printer->port ?? 9100
                ]);

                return [
                    'success' => true,
                    'message' => 'Print successful (via PrinterManager)',
                    'method' => 'printer_manager'
                ];
            }
        } catch (\Exception $e) {
            // fallback to raw socket if PrinterManager failed
            \Log::warning('PrinterManager failed, falling back to raw socket: ' . $e->getMessage());
        }

        $socket = @fsockopen($this->printer->ip_address, $this->printer->port, $errno, $errstr, 10);
        
        if (!$socket) {
            throw new \Exception("Network connection failed: $errstr ($errno)");
        }
        
        $bytes = fwrite($socket, $content);
        fclose($socket);
        
        return [
            'success' => true,
            'message' => 'Print successful',
            'bytes' => $bytes,
            'method' => 'network'
        ];
    }

    /**
     * Print to USB printer
     */
    private function printToUSB($content)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            if (!config('printer.allow_system_commands', true) || !function_exists('exec')) {
                throw new \Exception('Cannot print: system command execution disabled on this host');
            }

            // Windows - write temp file then call print command
            $tempFile = tempnam(sys_get_temp_dir(), 'thermal_receipt_');
            file_put_contents($tempFile, $content);

            $printerName = $this->printer->name ?? '';
            if ($printerName === '') {
                unlink($tempFile);
                throw new \Exception('Printer name not configured for Windows printing');
            }

            $command = "print /d:\"" . $printerName . "\" \"$tempFile\"";
            exec($command . ' 2>&1', $output, $returnVar);

            unlink($tempFile);

            if ($returnVar === 0) {
                return [
                    'success' => true,
                    'message' => 'Print successful',
                    'method' => 'windows_print',
                    'output' => $output
                ];
            } else {
                // Try using PrinterManager as fallback
                try {
                    if (class_exists(\App\Services\PrinterManager::class)) {
                        \App\Services\PrinterManager::printRaw($content, [
                            'type' => 'windows',
                            'printerName' => $this->printer->name
                        ]);

                        return [
                            'success' => true,
                            'message' => 'Print successful (via PrinterManager)',
                            'method' => 'printer_manager'
                        ];
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Windows print command failed: ' . implode("; ", $output) . ' | PrinterManager: ' . $e->getMessage());
                }
                throw new \Exception('Windows print command failed: ' . implode("; ", $output));
            }
        } else {
            // Linux/Unix - direct to device (configurable)
            $device = config('printer.usb_device_path', '/dev/usb/lp0');

            if (!file_exists($device)) {
                throw new \Exception('USB device not found: ' . $device);
            }

            if (!is_writable($device)) {
                throw new \Exception('USB device exists but is not writable: ' . $device);
            }

            // Try PrinterManager (file connector) first if available
            try {
                if (class_exists(\App\Services\PrinterManager::class)) {
                    \App\Services\PrinterManager::printRaw($content, [
                        'type' => 'file',
                        'path' => $device
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Print successful (via PrinterManager)',
                        'method' => 'printer_manager'
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('PrinterManager file print failed: ' . $e->getMessage());
            }

            $bytes = file_put_contents($device, $content);

            return [
                'success' => true,
                'message' => 'Print successful',
                'bytes' => $bytes,
                'method' => 'device_file'
            ];
        }
    }

    /**
     * Print to serial printer
     */
    private function printToSerial($content)
    {
        if (!$this->printer->serial_port) {
            throw new \Exception('Serial port not configured');
        }
        
        $handle = fopen($this->printer->serial_port, 'r+b');
        
        if (!$handle) {
            throw new \Exception('Cannot open serial port: ' . $this->printer->serial_port);
        }
        
        $bytes = fwrite($handle, $content);
        fclose($handle);
        
        return [
            'success' => true,
            'message' => 'Print successful',
            'bytes' => $bytes,
            'method' => 'serial'
        ];
    }

    /**
     * Print to bluetooth printer
     */
    private function printToBluetooth($content)
    {
        // Bluetooth implementation depends on system
        // This is a placeholder for bluetooth printing
        throw new \Exception('Bluetooth printing not implemented yet');
    }

    /**
     * Open cash drawer
     */
    public function openCashDrawer()
    {
        $command = $this->printer->generateCashDrawerCommand();
        
        if (!$command) {
            return ['success' => false, 'message' => 'Cash drawer not configured'];
        }
        
        return $this->print($command);
    }

    /**
     * Print test page
     */
    public function printTestPage()
    {
        $content = $this->generateTestContent();
        return $this->print($content);
    }

    /**
     * Generate test content
     */
    private function generateTestContent()
    {
        $content = [];
        
        // Initialize
        $content[] = $this->printer->generateInitCommand();
        
        // Header
        $content[] = $this->escCommand('a', 1); // Center
        $content[] = $this->escCommand('!', 8); // Emphasized
        $content[] = "THERMAL PRINTER TEST\n";
        $content[] = $this->escCommand('!', 0);
        $content[] = $this->generateSeparatorLine();
        $content[] = $this->escCommand('a', 0); // Left
        
        // Printer info
        $content[] = "Printer: " . $this->printer->name . "\n";
        $content[] = "Brand: " . ($this->printer->brand ?: 'Generic') . "\n";
        $content[] = "Model: " . ($this->printer->model ?: 'Unknown') . "\n";
        $content[] = "Paper: " . $this->printer->paper_width . "mm\n";
        $content[] = "Connection: " . ucfirst($this->printer->connection_type) . "\n";
        $content[] = $this->generateSeparatorLine();
        
        // Font test
        $content[] = "Font Test:\n";
        $content[] = "Normal: ABCDEFGHIJKLMNOP\n";
        $content[] = "Numbers: 0123456789\n";
        $content[] = "Symbols: !@#$%^&*()_+-=\n";
        $content[] = $this->generateSeparatorLine();
        
        // Width test
        $content[] = "Width Test (" . $this->printer->paper_width . "mm):\n";
        $content[] = $this->generateSeparatorLine('1');
        $content[] = $this->generateSeparatorLine();
        
        // Settings
        $content[] = "Settings:\n";
        $content[] = "Speed: Level " . $this->printer->print_speed . "\n";
        $content[] = "Density: Level " . $this->printer->print_density . "\n";
        $content[] = "Auto Cut: " . ($this->printer->auto_cut ? 'ON' : 'OFF') . "\n";
        $content[] = "Buzzer: " . ($this->printer->buzzer_enabled ? 'ON' : 'OFF') . "\n";
        $content[] = $this->generateSeparatorLine();
        
        // Footer
        $content[] = $this->escCommand('a', 1); // Center
        $content[] = "Test completed successfully!\n";
        $content[] = "Time: " . date('Y-m-d H:i:s') . "\n";
        
        // Cut
        $content[] = $this->printer->generateCutCommand();
        
        return implode('', $content);
    }

    /**
     * Set printer
     */
    public function setPrinter(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
        return $this;
    }

    /**
     * Get current printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }
}