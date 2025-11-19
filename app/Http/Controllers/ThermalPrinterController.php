<?php

namespace App\Http\Controllers;

use App\Models\ThermalPrinterSetting;
use App\Services\ThermalPrinterFixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThermalPrinterController extends Controller
{
    public function index()
    {
        $printers = ThermalPrinterSetting::orderBy('is_default', 'desc')
                                       ->orderBy('name')
                                       ->get();
        
        return view('thermal-printer.index', compact('printers'));
    }

    public function create()
    {
        $presets = ThermalPrinterSetting::getPresets();
        
        return view('thermal-printer.create', compact('presets'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|between:1,65535',
            'paper_width' => 'required|in:58,80,112',
            'print_speed' => 'required|in:1,2,3,4,5',
            'print_density' => 'required|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $printerSetting = ThermalPrinterSetting::create($request->all());

        // Set as default if it's the first printer or explicitly requested
        if ($request->is_default || ThermalPrinterSetting::count() === 1) {
            $printerSetting->setAsDefault();
        }

        return redirect()->route('thermal-printer.index')
                        ->with('success', 'Printer setting created successfully.');
    }

    public function show(ThermalPrinterSetting $thermalPrinter)
    {
        return view('thermal-printer.show', compact('thermalPrinter'));
    }

    public function edit(ThermalPrinterSetting $thermalPrinter)
    {
        $presets = ThermalPrinterSetting::getPresets();
        
        return view('thermal-printer.edit', compact('thermalPrinter', 'presets'));
    }

    public function update(Request $request, ThermalPrinterSetting $thermalPrinter)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|between:1,65535',
            'paper_width' => 'required|in:58,80,112',
            'print_speed' => 'required|in:1,2,3,4,5',
            'print_density' => 'required|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $thermalPrinter->update($request->all());

        // Set as default if requested
        if ($request->is_default) {
            $thermalPrinter->setAsDefault();
        }

        return redirect()->route('thermal-printer.index')
                        ->with('success', 'Printer setting updated successfully.');
    }

    public function destroy(ThermalPrinterSetting $thermalPrinter)
    {
        // Don't allow deletion of default printer if it's the only one
        if ($thermalPrinter->is_default && ThermalPrinterSetting::where('is_active', true)->count() === 1) {
            return redirect()->route('thermal-printer.index')
                           ->with('error', 'Cannot delete the only active printer setting.');
        }

        // If deleting default printer, set another as default
        if ($thermalPrinter->is_default) {
            $nextDefault = ThermalPrinterSetting::where('id', '!=', $thermalPrinter->id)
                                              ->where('is_active', true)
                                              ->first();
            if ($nextDefault) {
                $nextDefault->setAsDefault();
            }
        }

        $thermalPrinter->delete();

        return redirect()->route('thermal-printer.index')
                        ->with('success', 'Printer setting deleted successfully.');
    }

    public function setDefault(ThermalPrinterSetting $thermalPrinter)
    {
        $thermalPrinter->setAsDefault();

        return redirect()->route('thermal-printer.index')
                        ->with('success', 'Default printer updated successfully.');
    }

    public function testConnection(ThermalPrinterSetting $thermalPrinter)
    {
        $result = $thermalPrinter->testConnection();

        return response()->json($result);
    }

    public function loadPreset(Request $request)
    {
        $presetKey = $request->input('preset');
        $presets = ThermalPrinterSetting::getPresets();
        
        if (!isset($presets[$presetKey])) {
            return response()->json(['error' => 'Preset not found'], 404);
        }
        
        return response()->json($presets[$presetKey]);
    }

    public function printTest(ThermalPrinterSetting $thermalPrinter)
    {
        try {
            // Generate test content
            $testContent = $this->generateTestContent($thermalPrinter);
            
            // Send to printer based on connection type
            $result = $this->sendToPrinter($thermalPrinter, $testContent);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Test print sent successfully',
                'details' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test print failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateTestContent(ThermalPrinterSetting $printer)
    {
        $content = [];
        
        // Initialize printer
        $content[] = $printer->generateInitCommand();
        
        // Header
        $content[] = $printer->header_text ?: "THERMAL PRINTER TEST\n";
        $content[] = "========================\n";
        
        // Printer info
        $content[] = "Printer: " . $printer->name . "\n";
        $content[] = "Brand: " . ($printer->brand ?: 'Generic') . "\n";
        $content[] = "Model: " . ($printer->model ?: 'Unknown') . "\n";
        $content[] = "Paper: " . $printer->paper_width . "mm\n";
        $content[] = "Connection: " . ucfirst($printer->connection_type) . "\n";
        $content[] = "========================\n";
        
        // Font test
        $content[] = "Font Test:\n";
        $content[] = "Normal: ABCDEFGHIJKLMNOP\n";
        $content[] = "Numbers: 0123456789\n";
        $content[] = "Symbols: !@#$%^&*()_+-=\n";
        $content[] = "========================\n";
        
        // Width test
        $content[] = "Width Test (" . $printer->paper_width . "mm):\n";
        if ($printer->paper_width == '80') {
            $content[] = "12345678901234567890123456789012345678901234567890\n";
        } elseif ($printer->paper_width == '58') {
            $content[] = "1234567890123456789012345678901234567890\n";
        } else {
            $content[] = "123456789012345678901234567890123456789012345678901234567890\n";
        }
        $content[] = "========================\n";
        
        // Settings
        $content[] = "Settings:\n";
        $content[] = "Speed: Level " . $printer->print_speed . "\n";
        $content[] = "Density: Level " . $printer->print_density . "\n";
        $content[] = "Auto Cut: " . ($printer->auto_cut ? 'ON' : 'OFF') . "\n";
        $content[] = "Buzzer: " . ($printer->buzzer_enabled ? 'ON' : 'OFF') . "\n";
        $content[] = "========================\n";
        
        // Footer
        $content[] = $printer->footer_text ?: "Test completed successfully!\n";
        $content[] = "Time: " . date('Y-m-d H:i:s') . "\n";
        
        // Cut command
        $content[] = $printer->generateCutCommand();
        
        return implode('', $content);
    }

    private function sendToPrinter(ThermalPrinterSetting $printer, $content)
    {
        switch ($printer->connection_type) {
            case 'ethernet':
            case 'wifi':
                return $this->sendToNetworkPrinter($printer, $content);
                
            case 'usb':
                return $this->sendToUSBPrinter($printer, $content);
                
            case 'serial':
                return $this->sendToSerialPrinter($printer, $content);
                
            default:
                throw new \Exception('Connection type not supported for direct printing');
        }
    }

    private function sendToNetworkPrinter(ThermalPrinterSetting $printer, $content)
    {
        $socket = fsockopen($printer->ip_address, $printer->port, $errno, $errstr, 10);
        
        if (!$socket) {
            throw new \Exception("Cannot connect to printer: $errstr ($errno)");
        }
        
        $bytes = fwrite($socket, $content);
        fclose($socket);
        
        return ['bytes_sent' => $bytes, 'method' => 'network'];
    }

    private function sendToUSBPrinter(ThermalPrinterSetting $printer, $content)
    {
        // For USB printers, we typically need to send to a device file or use a printer driver
        if (PHP_OS_FAMILY === 'Windows') {
            if (!config('printer.allow_system_commands', true) || !function_exists('exec')) {
                throw new \Exception('Cannot print: system command execution disabled on this host');
            }

            // Windows - send to printer via print command
            $tempFile = tempnam(sys_get_temp_dir(), 'thermal_test_');
            file_put_contents($tempFile, $content);

            $printerName = $printer->name ?? '';
            if ($printerName === '') {
                unlink($tempFile);
                throw new \Exception('Printer name not configured for Windows printing');
            }

            $command = "print /d:\"" . $printerName . "\" \"$tempFile\"";
            exec($command . ' 2>&1', $output, $return_var);

            unlink($tempFile);

            if ($return_var === 0) {
                return ['method' => 'windows_print', 'output' => $output];
            } else {
                throw new \Exception('Print command failed: ' . implode('; ', $output));
            }
        } else {
            // Linux/Unix - typically /dev/usb/lp0 or similar (configurable)
            $device = config('printer.usb_device_path', '/dev/usb/lp0');

            if (!file_exists($device)) {
                throw new \Exception('USB device not found: ' . $device);
            }

            if (!is_writable($device)) {
                throw new \Exception('USB device exists but is not writable: ' . $device);
            }

            $bytes = file_put_contents($device, $content);
            return ['bytes_sent' => $bytes, 'method' => 'device_file'];
        }
    }

    private function sendToSerialPrinter(ThermalPrinterSetting $printer, $content)
    {
        if (!$printer->serial_port) {
            throw new \Exception('Serial port not configured');
        }
        
        // Configure serial port (platform specific)
        if (PHP_OS_FAMILY === 'Windows') {
            $handle = fopen($printer->serial_port, 'r+b');
        } else {
            $handle = fopen($printer->serial_port, 'r+');
        }
        
        if (!$handle) {
            throw new \Exception('Cannot open serial port');
        }
        
        $bytes = fwrite($handle, $content);
        fclose($handle);
        
        return ['bytes_sent' => $bytes, 'method' => 'serial'];
    }

    public function exportSettings()
    {
        $printers = ThermalPrinterSetting::all();
        
        $filename = 'thermal_printer_settings_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($printers->toArray())
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function importSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_file' => 'required|file|mimes:json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator);
        }

        try {
            $fileContent = file_get_contents($request->file('settings_file')->getRealPath());
            $settings = json_decode($fileContent, true);
            
            if (!is_array($settings)) {
                throw new \Exception('Invalid file format');
            }
            
            $imported = 0;
            foreach ($settings as $setting) {
                // Remove ID to avoid conflicts
                unset($setting['id']);
                unset($setting['created_at']);
                unset($setting['updated_at']);
                
                // Ensure unique name
                $originalName = $setting['name'];
                $counter = 1;
                while (ThermalPrinterSetting::where('name', $setting['name'])->exists()) {
                    $setting['name'] = $originalName . ' (Imported ' . $counter . ')';
                    $counter++;
                }
                
                ThermalPrinterSetting::create($setting);
                $imported++;
            }
            
            return redirect()->route('thermal-printer.index')
                           ->with('success', "Successfully imported $imported printer settings.");
                           
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Emergency stop untuk menghentikan kertas roll yang terus berjalan
     */
    public function emergencyStop(Request $request)
    {
        try {
            $printerIP = $request->input('printer_ip');
            $port = $request->input('port', 9100);
            
            $result = ThermalPrinterFixService::sendEmergencyStop($printerIP, $port);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Emergency stop command sent successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Emergency stop failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix printer settings untuk mengatasi infinite rolling
     */
    public function fixSettings()
    {
        try {
            // Update printer settings dengan commands yang benar
            $printerSetting = ThermalPrinterFixService::updatePrinterSettings();
            
            return redirect()->route('thermal-printer.index')
                           ->with('success', 'Printer settings fixed! ESC commands updated to prevent infinite rolling.');
                           
        } catch (\Exception $e) {
            return redirect()->route('thermal-printer.index')
                           ->with('error', 'Fix failed: ' . $e->getMessage());
        }
    }

    /**
     * Send test print dengan fixed commands
     */
    public function testFixedPrint(Request $request)
    {
        try {
            $printerIP = $request->input('printer_ip');
            $port = $request->input('port', 9100);
            
            if (!$printerIP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Printer IP address required'
                ], 400);
            }
            
            // Generate test print content dengan fixed commands
            $testContent = ThermalPrinterFixService::generateTestPrint();
            
            // Send ke printer
            $socket = fsockopen($printerIP, $port, $errno, $errstr, 10);
            
            if (!$socket) {
                return response()->json([
                    'success' => false,
                    'message' => "Connection failed: $errstr ($errno)"
                ], 400);
            }
            
            $bytes = fwrite($socket, $testContent);
            fclose($socket);
            
            return response()->json([
                'success' => true,
                'message' => 'Fixed test print sent successfully!',
                'bytes_sent' => $bytes
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test print failed: ' . $e->getMessage()
            ], 500);
        }
    }
}