<?php

namespace App\Services;

use App\Models\ThermalPrinterSetting;

class PrinterDriverFactory
{
    /**
     * Create appropriate driver instance
     */
    public static function create(ThermalPrinterSetting $printer): PrinterDriverInterface
    {
        return match ($printer->connection_type) {
            'network' => new NetworkPrinterDriver($printer),
            'usb' => new USBPrinterDriver($printer),
            'serial' => new SerialPrinterDriver($printer),
            'bluetooth' => new BluetoothPrinterDriver($printer),
            'windows' => new WindowsPrinterDriver($printer),
            default => throw new \Exception("Unsupported connection type: {$printer->connection_type}"),
        };
    }
}

interface PrinterDriverInterface
{
    public function testConnection();
    public function print($content, $options = []);
}

class NetworkPrinterDriver implements PrinterDriverInterface
{
    protected $printer;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
    }

    public function testConnection()
    {
        $host = $this->printer->connection_address;
        $port = $this->printer->connection_port ?? 9100;

        if (@fsockopen($host, $port, $errno, $errstr, 2)) {
            return true;
        }

        throw new \Exception("Cannot connect to $host:$port");
    }

    public function print($content, $options = [])
    {
        $host = $this->printer->connection_address;
        $port = $this->printer->connection_port ?? 9100;

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        
        if (!$socket) {
            throw new \Exception("Connection failed: $errstr ($errno)");
        }

        fwrite($socket, $content);
        fclose($socket);
    }
}

class USBPrinterDriver implements PrinterDriverInterface
{
    protected $printer;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
    }

    public function testConnection()
    {
        // Test USB connection by checking device existence
        $device = $this->printer->connection_address;
        
        if (PHP_OS_FAMILY === 'Windows') {
            return true; // Windows USB detection is complex
        } else {
            return file_exists($device);
        }
    }

    public function print($content, $options = [])
    {
        $device = $this->printer->connection_address;

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: use network printer fallback
            throw new \Exception("Use network printer for Windows USB devices");
        } else {
            // Linux: write directly to device
            $handle = @fopen($device, 'w');
            if (!$handle) {
                throw new \Exception("Cannot open USB device: $device");
            }
            fwrite($handle, $content);
            fclose($handle);
        }
    }
}

class SerialPrinterDriver implements PrinterDriverInterface
{
    protected $printer;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
    }

    public function testConnection()
    {
        $port = $this->printer->connection_address;
        
        if (PHP_OS_FAMILY === 'Windows') {
            return true;
        } else {
            return file_exists($port);
        }
    }

    public function print($content, $options = [])
    {
        // Serial printing requires phpseclib3 or similar library
        throw new \Exception("Serial printer support requires additional library installation");
    }
}

class BluetoothPrinterDriver implements PrinterDriverInterface
{
    protected $printer;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
    }

    public function testConnection()
    {
        // Bluetooth connection test
        return true;
    }

    public function print($content, $options = [])
    {
        // Bluetooth printing (mobile device)
        throw new \Exception("Bluetooth printing requires mobile app implementation");
    }
}

class WindowsPrinterDriver implements PrinterDriverInterface
{
    protected $printer;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
    }

    public function testConnection()
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new \Exception("Windows printer driver only works on Windows");
        }
        return true;
    }

    public function print($content, $options = [])
    {
        $printerName = $this->printer->connection_address;

        // Write to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'print_');
        file_put_contents($tempFile, $content);

        // Send to printer using Windows print command
        exec("print /D:$printerName $tempFile");
        
        // Clean up
        unlink($tempFile);
    }
}
