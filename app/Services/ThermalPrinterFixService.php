<?php

namespace App\Services;

use App\Models\ThermalPrinterSetting;

class ThermalPrinterFixService
{
    /**
     * Fix infinite paper rolling issue dengan ESC commands yang tepat
     */
    public static function generateProperESCCommands($printerSetting = null)
    {
        $commands = [];
        
        // 1. Initialize printer dengan reset
        $commands[] = "\x1B\x40"; // ESC @ - Initialize printer
        
        // 2. Set line spacing yang tepat untuk menghemat kertas
        $commands[] = "\x1B\x33\x14"; // ESC 3 20 - Set line spacing to 20/180 inch (minimal)
        
        // 3. Set font yang compact
        $commands[] = "\x1B\x4D\x01"; // ESC M 1 - Select Font B (smaller)
        $commands[] = "\x1B\x21\x00"; // ESC ! 0 - Normal print mode
        
        // 4. Set character spacing minimal
        $commands[] = "\x1B\x20\x00"; // ESC SP 0 - No extra character spacing
        
        // 5. Set print area untuk 80mm paper (prevent overflow)
        $commands[] = "\x1D\x4C\x00\x00"; // GS L 0 0 - Set left margin to 0
        $commands[] = "\x1D\x57\x00\x02"; // GS W 512 - Set print area width for 80mm
        
        return implode('', $commands);
    }
    
    /**
     * Generate proper cut commands untuk stop paper rolling
     */
    public static function generateStopRollingCommands()
    {
        $commands = [];
        
        // 1. Feed minimal lines sebelum cut
        $commands[] = "\x0A"; // LF - 1 line feed only
        
        // 2. Partial cut untuk stop rolling
        $commands[] = "\x1B\x69"; // ESC i - Partial cut
        
        // 3. Alternative: Full cut jika partial tidak work
        // $commands[] = "\x1B\x6D"; // ESC m - Full cut
        
        return implode('', $commands);
    }
    
    /**
     * Emergency stop command untuk stop paper rolling
     */
    public static function emergencyStopCommand()
    {
        $commands = [];
        
        // 1. Cancel any pending operations
        $commands[] = "\x18"; // CAN - Cancel command
        
        // 2. Reset printer
        $commands[] = "\x1B\x40"; // ESC @ - Initialize
        
        // 3. Immediate cut
        $commands[] = "\x1B\x69"; // ESC i - Partial cut
        
        return implode('', $commands);
    }
    
    /**
     * Generate receipt dengan proper formatting untuk avoid infinite rolling
     */
    public static function generateOptimizedReceipt($saleData)
    {
        $content = [];
        
        // 1. Initialize dengan proper settings
        $content[] = self::generateProperESCCommands();
        
        // 2. Header dengan alignment
        $content[] = "\x1B\x61\x01"; // ESC a 1 - Center alignment
        $content[] = settings()->company_name . "\n";
        $content[] = "\x1B\x61\x00"; // ESC a 0 - Left alignment
        $content[] = settings()->company_address . "\n";
        
        // 3. Single separator line (tidak berlebihan)
        $content[] = str_repeat('-', 42) . "\n"; // 42 chars untuk 80mm
        
        // 4. Transaction info (compact format)
        $content[] = "Receipt: " . $saleData->reference . "\n";
        $content[] = "Date: " . $saleData->date . "\n";
        $content[] = str_repeat('-', 42) . "\n";
        
        // 5. Items (format compact)
        foreach ($saleData->saleDetails as $item) {
            $content[] = substr($item->product_name, 0, 35) . "\n"; // Limit nama produk
            $content[] = sprintf("%d x %s = %s\n", 
                $item->quantity, 
                format_currency($item->unit_price), 
                format_currency($item->sub_total)
            );
        }
        
        // 6. Total section (compact)
        $content[] = str_repeat('-', 42) . "\n";
        $content[] = sprintf("TOTAL: %s\n", format_currency($saleData->total_amount));
        $content[] = sprintf("PAID: %s\n", format_currency($saleData->paid_amount));
        
        if ($saleData->paid_amount > $saleData->total_amount) {
            $content[] = sprintf("CHANGE: %s\n", format_currency($saleData->paid_amount - $saleData->total_amount));
        }
        
        // 7. Footer minimal
        $content[] = "\x1B\x61\x01"; // Center
        $content[] = "Thank You!\n";
        $content[] = "\x1B\x61\x00"; // Left
        
        // 8. IMPORTANT: Proper ending untuk stop paper rolling
        $content[] = self::generateStopRollingCommands();
        
        return implode('', $content);
    }
    
    /**
     * Test print untuk debug infinite rolling issue
     */
    public static function generateTestPrint()
    {
        $content = [];
        
        // Initialize
        $content[] = self::generateProperESCCommands();
        
        // Simple test content
        $content[] = "\x1B\x61\x01"; // Center
        $content[] = "** TEST PRINT **\n";
        $content[] = "\x1B\x61\x00"; // Left
        $content[] = "This is a test receipt\n";
        $content[] = "Time: " . date('Y-m-d H:i:s') . "\n";
        $content[] = str_repeat('-', 20) . "\n";
        $content[] = "If you see this text,\n";
        $content[] = "the printer is working.\n";
        $content[] = str_repeat('-', 20) . "\n";
        
        // Stop rolling dengan minimal feed
        $content[] = "\x0A"; // 1 line feed only
        $content[] = "\x1B\x69"; // Partial cut
        
        return implode('', $content);
    }
    
    /**
     * Update printer settings dengan commands yang benar
     */
    public static function updatePrinterSettings()
    {
        // Update existing printer settings
        $printerSetting = \App\Models\PrinterSetting::getInstance();
        
        // Set thermal commands yang benar
        $printerSetting->update([
            'thermal_printer_commands' => json_encode([
                'init' => base64_encode(self::generateProperESCCommands()),
                'cut' => base64_encode(self::generateStopRollingCommands()),
                'emergency_stop' => base64_encode(self::emergencyStopCommand())
            ]),
            'receipt_paper_size' => '80mm'
        ]);
        
        return $printerSetting;
    }
    
    /**
     * Send emergency stop ke printer
     */
    public static function sendEmergencyStop($printerIP = null, $port = 9100)
    {
        try {
            // Default ke printer network jika tersedia
            $ip = $printerIP ?: '192.168.1.100'; // Sesuaikan dengan IP printer Anda
            
            $socket = fsockopen($ip, $port, $errno, $errstr, 5);
            
            if ($socket) {
                fwrite($socket, self::emergencyStopCommand());
                fclose($socket);
                return ['success' => true, 'message' => 'Emergency stop sent'];
            } else {
                return ['success' => false, 'message' => "Connection failed: $errstr"];
            }
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}