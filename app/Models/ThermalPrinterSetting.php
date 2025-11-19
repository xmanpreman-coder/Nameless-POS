<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThermalPrinterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'brand', 'model', 'connection_type', 'ip_address', 'port',
        'bluetooth_address', 'serial_port', 'baud_rate', 'paper_width',
        'paper_length', 'paper_type', 'print_speed', 'print_density',
        'character_set', 'font_size', 'auto_cut', 'buzzer_enabled',
        'esc_commands', 'init_command', 'cut_command', 'cash_drawer_command',
        'margin_left', 'margin_right', 'margin_top', 'margin_bottom',
        'line_spacing', 'char_spacing', 'print_logo', 'header_text',
        'footer_text', 'print_barcode', 'barcode_position', 'is_active',
        'is_default', 'capabilities', 'notes'
    ];

    protected $casts = [
        'esc_commands' => 'array',
        'capabilities' => 'array',
        'auto_cut' => 'boolean',
        'buzzer_enabled' => 'boolean',
        'print_logo' => 'boolean',
        'print_barcode' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // Preset configurations untuk berbagai brand printer
    public static function getPresets()
    {
        return [
            'eppos_ep220ii' => [
                'name' => 'Eppos EP220II',
                'brand' => 'Eppos',
                'model' => 'EP220II',
                'paper_width' => '80',
                'init_command' => '\x1B\x40\x1B\x32\x1B\x4D\x00',
                'cut_command' => '\x1B\x69',
                'capabilities' => [
                    'auto_cut' => true,
                    'cash_drawer' => true,
                    'barcode' => ['CODE39', 'CODE128', 'QR'],
                    'fonts' => ['A', 'B'],
                    'charsets' => ['PC437', 'PC850']
                ]
            ],
            'xprinter_xp80c' => [
                'name' => 'Xprinter XP-80C',
                'brand' => 'Xprinter',
                'model' => 'XP-80C',
                'paper_width' => '80',
                'init_command' => '\x1B\x40\x1B\x32',
                'cut_command' => '\x1B\x6D',
                'capabilities' => [
                    'auto_cut' => true,
                    'cash_drawer' => true,
                    'barcode' => ['CODE39', 'CODE128', 'QR', 'UPC'],
                    'fonts' => ['A', 'B'],
                    'charsets' => ['PC437', 'PC850', 'PC852']
                ]
            ],
            'epson_tm_t20' => [
                'name' => 'Epson TM-T20',
                'brand' => 'Epson',
                'model' => 'TM-T20',
                'paper_width' => '80',
                'init_command' => '\x1B\x40',
                'cut_command' => '\x1B\x69',
                'cash_drawer_command' => '\x1B\x70\x00\x19\x19',
                'capabilities' => [
                    'auto_cut' => true,
                    'cash_drawer' => true,
                    'barcode' => ['CODE39', 'CODE128', 'QR', 'UPC', 'EAN'],
                    'fonts' => ['A', 'B', 'C'],
                    'charsets' => ['PC437', 'PC850', 'PC852', 'PC858']
                ]
            ],
            'star_tsp143' => [
                'name' => 'Star TSP143',
                'brand' => 'Star',
                'model' => 'TSP143',
                'paper_width' => '80',
                'init_command' => '\x1B\x40',
                'cut_command' => '\x1B\x64\x02',
                'capabilities' => [
                    'auto_cut' => true,
                    'cash_drawer' => true,
                    'barcode' => ['CODE39', 'CODE128', 'QR'],
                    'fonts' => ['A', 'B'],
                    'charsets' => ['PC437', 'PC850']
                ]
            ],
            'generic_80mm' => [
                'name' => 'Generic 80mm Thermal',
                'brand' => 'Generic',
                'model' => '80mm',
                'paper_width' => '80',
                'init_command' => '\x1B\x40\x1B\x32',
                'cut_command' => '\x1B\x69',
                'capabilities' => [
                    'auto_cut' => false,
                    'cash_drawer' => false,
                    'barcode' => ['CODE39', 'CODE128'],
                    'fonts' => ['A'],
                    'charsets' => ['PC437']
                ]
            ]
        ];
    }

    // Get default printer setting
    public static function getDefault()
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?: static::where('is_active', true)->first();
    }

    // Set sebagai default
    public function setAsDefault()
    {
        // Remove default from others
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
        
        return $this;
    }

    // Generate ESC command string
    public function generateInitCommand()
    {
        $commands = [];
        
        // Basic initialization
        $commands[] = '\x1B\x40'; // ESC @ - Initialize
        
        // Line spacing
        if ($this->line_spacing != 30) {
            $commands[] = '\x1B\x33' . chr($this->line_spacing); // ESC 3
        } else {
            $commands[] = '\x1B\x32'; // ESC 2 - Default spacing
        }
        
        // Character spacing
        if ($this->char_spacing > 0) {
            $commands[] = '\x1B\x20' . chr($this->char_spacing); // ESC SP
        }
        
        // Font selection
        if ($this->font_size == 'small') {
            $commands[] = '\x1B\x4D\x01'; // ESC M - Font B
        } else {
            $commands[] = '\x1B\x4D\x00'; // ESC M - Font A
        }
        
        // Print density (if supported)
        if ($this->print_density != 3) {
            $commands[] = '\x1D\x7C\x00' . chr($this->print_density);
        }
        
        return implode('', $commands);
    }

    // Generate cut command
    public function generateCutCommand()
    {
        if (!$this->auto_cut) {
            return '';
        }
        
        // Feed beberapa lines sebelum cut
        $commands = [];
        $commands[] = '\x0A\x0A\x0A'; // 3 line feeds
        
        // Cut command based on brand
        switch (strtolower($this->brand)) {
            case 'star':
                $commands[] = '\x1B\x64\x02'; // Star cut
                break;
            case 'citizen':
                $commands[] = '\x1B\x6D'; // Citizen cut
                break;
            default:
                $commands[] = $this->cut_command ?: '\x1B\x69'; // Generic/Epson cut
        }
        
        return implode('', $commands);
    }

    // Generate cash drawer command
    public function generateCashDrawerCommand()
    {
        if (!$this->cash_drawer_command) {
            return '';
        }
        
        return $this->cash_drawer_command;
    }

    // Test printer connection
    public function testConnection()
    {
        switch ($this->connection_type) {
            case 'ethernet':
            case 'wifi':
                return $this->testNetworkConnection();
                
            case 'usb':
                return $this->testUSBConnection();
                
            case 'serial':
                return $this->testSerialConnection();
                
            case 'bluetooth':
                return $this->testBluetoothConnection();
                
            default:
                return ['status' => 'unknown', 'message' => 'Connection type not supported'];
        }
    }

    private function testNetworkConnection()
    {
        if (!$this->ip_address) {
            return ['status' => 'error', 'message' => 'IP address not configured'];
        }
        
        $connection = @fsockopen($this->ip_address, $this->port, $errno, $errstr, 5);
        
        if ($connection) {
            fclose($connection);
            return ['status' => 'success', 'message' => 'Network connection successful'];
        } else {
            return ['status' => 'error', 'message' => "Connection failed: $errstr ($errno)"];
        }
    }

    private function testUSBConnection()
    {
        // Untuk USB, kita cek apakah printer driver terinstall
        // Ini platform specific
        if (PHP_OS_FAMILY === 'Windows') {
            // Use PowerShell Get-Printer (more reliable than deprecated wmic)
            $name = $this->name ?? '';
            $escapedName = str_replace('"', '\\"', $name);

            if (!function_exists('shell_exec') || !config('printer.allow_system_commands', true)) {
                return ['status' => 'warning', 'message' => 'Cannot verify USB connection automatically: system command execution disabled'];
            }

            $cmd = 'powershell -NoProfile -Command "Get-Printer -Name \"' . $escapedName . '\" -ErrorAction SilentlyContinue | Select-Object Name,PrinterStatus | ConvertTo-Json"';
            $output = shell_exec($cmd);
            $output = $output !== null ? trim($output) : '';

            if ($output !== '') {
                $data = json_decode($output, true);
                if ($data) {
                    // Normalize data to array of printers
                    $printers = [];
                    if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) {
                        $printers = $data;
                    } else {
                        $printers = [$data];
                    }

                    // Try to match by exact, case-insensitive, or partial match against Name/Display
                    $needleCandidates = array_filter([$this->name ?? '', $this->brand ?? '', $this->model ?? '']);
                    $needleCandidates = array_map('strtolower', $needleCandidates);

                    foreach ($printers as $printer) {
                        $printerName = strtolower($printer['Name'] ?? ($printer['Name'] ?? ''));
                        foreach ($needleCandidates as $needle) {
                            if ($needle === '') {
                                continue;
                            }

                            if ($printerName === $needle || strpos($printerName, $needle) !== false || strpos($needle, $printerName) !== false) {
                                return ['status' => 'success', 'message' => 'USB printer detected', 'printer' => $printer];
                            }
                        }
                    }
                }
            }
        } else {
            // On Unix-like systems, try lpstat as a basic check
            if (!empty($this->name)) {
                if (!function_exists('shell_exec') || !config('printer.allow_system_commands', true)) {
                    return ['status' => 'warning', 'message' => 'Cannot verify USB connection automatically: system command execution disabled'];
                }

                // Try listing all printers and perform a fuzzy match
                $output = shell_exec('lpstat -a 2>&1');
                $output = $output !== null ? trim($output) : '';
                if ($output !== '') {
                    $lines = explode("\n", $output);
                    $needleCandidates = array_filter([$this->name ?? '', $this->brand ?? '', $this->model ?? '']);
                    foreach ($lines as $line) {
                        $lineLower = strtolower($line);
                        foreach ($needleCandidates as $needle) {
                            $needle = strtolower($needle);
                            if ($needle === '') continue;
                            if (strpos($lineLower, $needle) !== false) {
                                return ['status' => 'success', 'message' => 'USB printer detected'];
                            }
                        }
                    }
                }
            }
        }

        return ['status' => 'warning', 'message' => 'Cannot verify USB connection automatically'];
    }

    private function testSerialConnection()
    {
        if (!$this->serial_port) {
            return ['status' => 'error', 'message' => 'Serial port not configured'];
        }
        
        // Platform specific serial test
        if (PHP_OS_FAMILY === 'Windows') {
            $handle = @fopen($this->serial_port, 'r+b');
        } else {
            $handle = @fopen($this->serial_port, 'r+');
        }
        
        if ($handle) {
            fclose($handle);
            return ['status' => 'success', 'message' => 'Serial port accessible'];
        } else {
            return ['status' => 'error', 'message' => 'Cannot access serial port'];
        }
    }

    private function testBluetoothConnection()
    {
        if (!$this->bluetooth_address) {
            return ['status' => 'error', 'message' => 'Bluetooth address not configured'];
        }
        
        return ['status' => 'warning', 'message' => 'Bluetooth connection test not implemented'];
    }
}