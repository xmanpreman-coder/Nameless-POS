# 🔧 Implementation Code Examples - Multiple Printer Support

**Purpose**: Practical code examples untuk implementasi multiple printer support  
**Based on**: Triangle POS / Nameless POS  
**Language**: PHP (Laravel 10+)

---

## 📑 Daftar Isi

1. [Model Layer](#model-layer)
2. [Controller Layer](#controller-layer)
3. [Service Layer](#service-layer)
4. [API Routes](#api-routes)
5. [Blade Views](#blade-views)
6. [JavaScript Integration](#javascript-integration)

---

## 📦 Model Layer

### 1. **ThermalPrinterSetting Model**

```php
<?php
// app/Models/ThermalPrinterSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThermalPrinterSetting extends Model
{
    use HasFactory;

    protected $table = 'thermal_printer_settings';

    protected $fillable = [
        'name', 'brand', 'model',
        'connection_type', 'ip_address', 'port',
        'bluetooth_address', 'serial_port', 'baud_rate',
        'paper_width', 'paper_length', 'paper_type',
        'print_speed', 'print_density', 'character_set',
        'font_size', 'auto_cut', 'buzzer_enabled',
        'esc_commands', 'init_command', 'cut_command',
        'cash_drawer_command', 'margin_left', 'margin_right',
        'margin_top', 'margin_bottom', 'line_spacing', 'char_spacing',
        'print_logo', 'header_text', 'footer_text',
        'print_barcode', 'barcode_position',
        'is_active', 'is_default', 'capabilities', 'notes'
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

    // ============ SCOPES ============

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByConnectionType($query, $type)
    {
        return $query->where('connection_type', $type);
    }

    // ============ RELATIONSHIPS ============

    public function userPreferences()
    {
        return $this->hasMany(UserPrinterPreference::class, 'receipt_printer_name', 'name');
    }

    // ============ ACCESSORS & MUTATORS ============

    public function getConnectionDisplayAttribute()
    {
        return match($this->connection_type) {
            'ethernet' => "{$this->ip_address}:{$this->port}",
            'serial' => "{$this->serial_port} @ {$this->baud_rate} baud",
            'bluetooth' => $this->bluetooth_address,
            'wifi' => "{$this->ip_address}:{$this->port}",
            'usb' => $this->name,
            default => 'Unknown'
        };
    }

    // ============ STATIC METHODS ============

    /**
     * Get all available presets
     */
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
                ]
            ],
        ];
    }

    /**
     * Get default printer
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first() ?? static::where('is_active', true)->first();
    }

    /**
     * Select printer untuk user
     */
    public static function selectForUser($user)
    {
        // 1. User preference
        if ($user->printerPreference?->receipt_printer_name) {
            $printer = static::where('name', $user->printerPreference->receipt_printer_name)
                ->where('is_active', true)
                ->first();
            
            if ($printer) return $printer;
        }

        // 2. System default
        return static::getDefault();
    }

    // ============ INSTANCE METHODS ============

    /**
     * Set as default printer
     */
    public function setAsDefault()
    {
        DB::transaction(function () {
            // Remove default dari yang lain
            static::where('is_default', true)->update(['is_default' => false]);
            
            // Set ini sebagai default
            $this->update(['is_default' => true]);
        });

        return $this;
    }

    /**
     * Test connection based on connection type
     */
    public function testConnection()
    {
        return match($this->connection_type) {
            'ethernet', 'wifi' => $this->testNetworkConnection(),
            'usb' => $this->testUSBConnection(),
            'serial' => $this->testSerialConnection(),
            'bluetooth' => $this->testBluetoothConnection(),
            default => ['status' => 'unknown', 'message' => 'Unsupported connection type']
        };
    }

    /**
     * Test network connection (Ethernet/WiFi)
     */
    private function testNetworkConnection()
    {
        if (!$this->ip_address) {
            return ['status' => 'error', 'message' => 'IP address not configured'];
        }

        $connection = @fsockopen($this->ip_address, $this->port ?? 9100, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);
            return [
                'status' => 'success',
                'message' => 'Network connection successful',
                'ip' => $this->ip_address,
                'port' => $this->port
            ];
        }

        return [
            'status' => 'error',
            'message' => "Connection failed: $errstr ($errno)"
        ];
    }

    /**
     * Test USB connection
     */
    private function testUSBConnection()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->testUSBConnectionWindows();
        } else {
            return $this->testUSBConnectionUnix();
        }
    }

    private function testUSBConnectionWindows()
    {
        if (!config('printer.allow_system_commands')) {
            return ['status' => 'warning', 'message' => 'System commands disabled'];
        }

        if (!function_exists('shell_exec')) {
            return ['status' => 'warning', 'message' => 'shell_exec not available'];
        }

        try {
            $name = $this->name;
            $escapedName = str_replace('"', '\\"', $name);
            
            $cmd = 'powershell -NoProfile -Command "Get-Printer -Name \\"' . $escapedName . '\\" -ErrorAction SilentlyContinue | ConvertTo-Json"';
            $output = shell_exec($cmd);

            if ($output && json_decode($output)) {
                return ['status' => 'success', 'message' => 'USB printer detected'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'warning', 'message' => 'Cannot verify USB connection automatically'];
    }

    private function testUSBConnectionUnix()
    {
        if (!config('printer.allow_system_commands') || !function_exists('shell_exec')) {
            return ['status' => 'warning', 'message' => 'System commands disabled'];
        }

        try {
            $output = shell_exec('lpstat -a 2>&1');
            
            if ($output && strpos($output, $this->name) !== false) {
                return ['status' => 'success', 'message' => 'USB printer detected'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'warning', 'message' => 'Cannot verify USB connection automatically'];
    }

    /**
     * Test serial connection
     */
    private function testSerialConnection()
    {
        if (!$this->serial_port) {
            return ['status' => 'error', 'message' => 'Serial port not configured'];
        }

        $handle = @fopen($this->serial_port, PHP_OS_FAMILY === 'Windows' ? 'r+b' : 'r+');

        if ($handle) {
            fclose($handle);
            return ['status' => 'success', 'message' => 'Serial port accessible'];
        }

        return ['status' => 'error', 'message' => 'Cannot access serial port'];
    }

    /**
     * Test Bluetooth connection
     */
    private function testBluetoothConnection()
    {
        if (!$this->bluetooth_address) {
            return ['status' => 'error', 'message' => 'Bluetooth address not configured'];
        }

        return ['status' => 'warning', 'message' => 'Bluetooth test not implemented'];
    }

    /**
     * Generate init ESC commands
     */
    public function generateInitCommand()
    {
        $commands = [];

        // Basic initialization
        $commands[] = '\x1B\x40'; // ESC @ - Initialize

        // Line spacing
        if ($this->line_spacing != 30) {
            $commands[] = '\x1B\x33' . chr($this->line_spacing);
        } else {
            $commands[] = '\x1B\x32'; // ESC 2 - Default
        }

        // Character spacing
        if ($this->char_spacing > 0) {
            $commands[] = '\x1B\x20' . chr($this->char_spacing);
        }

        // Font selection
        $commands[] = $this->font_size === 'small' ? '\x1B\x4D\x01' : '\x1B\x4D\x00';

        return implode('', $commands);
    }

    /**
     * Generate cut ESC commands
     */
    public function generateCutCommand()
    {
        if (!$this->auto_cut) {
            return '';
        }

        $commands = [];
        $commands[] = '\x0A\x0A\x0A'; // Feed lines

        $command = match(strtolower($this->brand)) {
            'star' => '\x1B\x64\x02',
            'citizen' => '\x1B\x6D',
            'xprinter' => '\x1B\x6D',
            default => $this->cut_command ?? '\x1B\x69'
        };

        $commands[] = $command;

        return implode('', $commands);
    }

    /**
     * Generate cash drawer command
     */
    public function generateCashDrawerCommand()
    {
        return $this->cash_drawer_command ?? '';
    }
}
```

### 2. **UserPrinterPreference Model**

```php
<?php
// app/Models/UserPrinterPreference.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrinterPreference extends Model
{
    protected $fillable = [
        'user_id',
        'receipt_printer_name',
        'receipt_paper_size',
        'auto_print_receipt',
        'print_customer_copy',
        'printer_settings'
    ];

    protected $casts = [
        'auto_print_receipt' => 'boolean',
        'print_customer_copy' => 'boolean',
        'printer_settings' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preference untuk user
     */
    public static function forUser($user)
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            ['receipt_paper_size' => '80mm']
        );
    }
}
```

### 3. **Add Relationship ke User Model**

```php
<?php
// app/Models/User.php

public function printerPreference()
{
    return $this->hasOne(UserPrinterPreference::class);
}

public function getPrinterForUse()
{
    // Get user's preferred printer or system default
    if ($this->printerPreference?->receipt_printer_name) {
        $printer = ThermalPrinterSetting::where('name', $this->printerPreference->receipt_printer_name)
            ->where('is_active', true)
            ->first();
        
        if ($printer) return $printer;
    }

    return ThermalPrinterSetting::getDefault();
}
```

---

## 🎮 Controller Layer

### 1. **ThermalPrinterController**

```php
<?php
// app/Http/Controllers/ThermalPrinterController.php

namespace App\Http\Controllers;

use App\Models\ThermalPrinterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ThermalPrinterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * List semua printer
     */
    public function index()
    {
        $printers = ThermalPrinterSetting::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('thermal-printer.index', compact('printers'));
    }

    /**
     * Form create printer
     */
    public function create()
    {
        $presets = ThermalPrinterSetting::getPresets();
        return view('thermal-printer.create', compact('presets'));
    }

    /**
     * Store printer baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'connection_type' => 'required|in:usb,ethernet,bluetooth,serial,wifi',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|between:1,65535',
            'bluetooth_address' => 'nullable|string',
            'serial_port' => 'nullable|string',
            'baud_rate' => 'nullable|integer',
            'paper_width' => 'required|in:58,80,112',
            'paper_type' => 'required|in:thermal,impact',
            'print_speed' => 'required|in:1,2,3,4,5',
            'print_density' => 'required|in:1,2,3,4,5',
            'font_size' => 'required|in:small,normal,large',
            'auto_cut' => 'boolean',
            'buzzer_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                $printer = ThermalPrinterSetting::create($request->all());

                // Set as default jika ini printer pertama atau diminta
                if ($request->is_default || ThermalPrinterSetting::count() === 1) {
                    $printer->setAsDefault();
                }
            });

            return redirect()->route('thermal-printer.index')
                ->with('success', 'Printer berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Form edit printer
     */
    public function edit(ThermalPrinterSetting $thermalPrinter)
    {
        $presets = ThermalPrinterSetting::getPresets();
        return view('thermal-printer.edit', compact('thermalPrinter', 'presets'));
    }

    /**
     * Update printer settings
     */
    public function update(Request $request, ThermalPrinterSetting $thermalPrinter)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'paper_width' => 'required|in:58,80,112',
            'print_speed' => 'required|in:1,2,3,4,5',
            'print_density' => 'required|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $thermalPrinter->update($request->all());

            if ($request->is_default) {
                $thermalPrinter->setAsDefault();
            }

            return redirect()->route('thermal-printer.index')
                ->with('success', 'Printer berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete printer
     */
    public function destroy(ThermalPrinterSetting $thermalPrinter)
    {
        // Jangan hapus default printer jika itu satu-satunya
        if ($thermalPrinter->is_default && ThermalPrinterSetting::where('is_active', true)->count() === 1) {
            return redirect()->route('thermal-printer.index')
                ->with('error', 'Tidak bisa menghapus satu-satunya printer aktif.');
        }

        // Jika delete default printer, set yang lain sebagai default
        if ($thermalPrinter->is_default) {
            $next = ThermalPrinterSetting::where('id', '!=', $thermalPrinter->id)
                ->where('is_active', true)
                ->first();

            if ($next) $next->setAsDefault();
        }

        $thermalPrinter->delete();

        return redirect()->route('thermal-printer.index')
            ->with('success', 'Printer berhasil dihapus.');
    }

    /**
     * Set sebagai default
     */
    public function setDefault(ThermalPrinterSetting $thermalPrinter)
    {
        $thermalPrinter->setAsDefault();

        return redirect()->route('thermal-printer.index')
            ->with('success', 'Default printer berhasil diupdate.');
    }

    /**
     * Test connection
     */
    public function testConnection(ThermalPrinterSetting $thermalPrinter)
    {
        $result = $thermalPrinter->testConnection();
        return response()->json($result);
    }

    /**
     * Print test page
     */
    public function printTest(ThermalPrinterSetting $thermalPrinter)
    {
        try {
            $service = new \App\Services\ThermalPrinterService($thermalPrinter);

            $testContent = "=== TEST PRINT ===\n";
            $testContent .= "Printer: {$thermalPrinter->name}\n";
            $testContent .= "Brand: {$thermalPrinter->brand}\n";
            $testContent .= "Model: {$thermalPrinter->model}\n";
            $testContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $testContent .= "===================\n";

            $service->print($testContent);

            return response()->json([
                'status' => 'success',
                'message' => 'Test print sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

### 2. **PrinterSettingController**

```php
<?php
// app/Http/Controllers/PrinterSettingController.php

namespace App\Http\Controllers;

use App\Models\PrinterSetting;
use App\Models\ThermalPrinterSetting;
use Illuminate\Http\Request;

class PrinterSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show printer settings form
     */
    public function index()
    {
        $printerSettings = PrinterSetting::first() ?? new PrinterSetting();
        $activePrinters = ThermalPrinterSetting::where('is_active', true)->get();

        return view('printer-settings.index', compact('printerSettings', 'activePrinters'));
    }

    /**
     * Update printer settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'receipt_paper_size' => 'required|in:58mm,80mm,letter,a4',
            'default_receipt_printer' => 'nullable|exists:thermal_printer_settings,id',
            'receipt_copies' => 'required|integer|min:1|max:5',
            'auto_print_receipt' => 'boolean',
            'print_customer_copy' => 'boolean',
        ]);

        $setting = PrinterSetting::first() ?? new PrinterSetting();

        $setting->fill($request->all());
        $setting->save();

        return redirect()->route('printer-settings.index')
            ->with('success', 'Printer settings updated successfully.');
    }
}
```

---

## 🔌 Service Layer

### **ThermalPrinterService**

```php
<?php
// app/Services/ThermalPrinterService.php

namespace App\Services;

use App\Models\ThermalPrinterSetting;
use Exception;

class ThermalPrinterService
{
    private $printer;
    private $driver;

    public function __construct(ThermalPrinterSetting $printer)
    {
        $this->printer = $printer;
        $this->selectDriver();
    }

    /**
     * Select driver based on preference
     */
    private function selectDriver()
    {
        $preferred = config('printer.preferred_driver', 'native');

        if ($preferred === 'mike42' && $this->canUseMike42()) {
            $this->driver = new \Mike42\Escpos\Printer(
                new \Mike42\Escpos\PrintConnectors\FilePrintConnector('/dev/usb/lp0')
            );
        } else {
            $this->driver = new NativePrinterDriver($this->printer);
        }
    }

    /**
     * Check if mike42 library is available
     */
    private function canUseMike42()
    {
        return class_exists('\Mike42\Escpos\Printer');
    }

    /**
     * Print content
     */
    public function print($content, $options = [])
    {
        try {
            // Initialize
            $this->initialize();

            // Write content
            $this->write($content);

            // Auto cut
            if ($options['auto_cut'] ?? $this->printer->auto_cut) {
                $this->cut();
            }

            // Cash drawer
            if ($options['cash_drawer'] ?? false) {
                $this->openCashDrawer();
            }

            // Buzzer
            if ($this->printer->buzzer_enabled) {
                $this->buzzer();
            }

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initialize printer
     */
    private function initialize()
    {
        $commands = $this->printer->generateInitCommand();
        $this->sendRawCommand($commands);
    }

    /**
     * Write content to printer
     */
    private function write($content)
    {
        $this->sendRawCommand($content);
    }

    /**
     * Cut paper
     */
    private function cut()
    {
        $commands = $this->printer->generateCutCommand();
        $this->sendRawCommand($commands);
    }

    /**
     * Open cash drawer
     */
    private function openCashDrawer()
    {
        $commands = $this->printer->generateCashDrawerCommand();
        if ($commands) {
            $this->sendRawCommand($commands);
        }
    }

    /**
     * Buzzer
     */
    private function buzzer()
    {
        $this->sendRawCommand('\x1B\x42\x09\x09'); // ESC B n m
    }

    /**
     * Send raw ESC command
     */
    private function sendRawCommand($command)
    {
        if ($this->printer->connection_type === 'usb') {
            $this->sendUSB($command);
        } elseif ($this->printer->connection_type === 'ethernet') {
            $this->sendNetwork($command);
        } elseif ($this->printer->connection_type === 'serial') {
            $this->sendSerial($command);
        }
    }

    /**
     * Send via USB
     */
    private function sendUSB($command)
    {
        // Platform specific
        if (PHP_OS_FAMILY === 'Windows') {
            $this->sendWindowsPrint($command);
        } else {
            $this->sendUnixPrint($command);
        }
    }

    /**
     * Send via Network
     */
    private function sendNetwork($command)
    {
        $socket = @fsockopen(
            $this->printer->ip_address,
            $this->printer->port ?? 9100,
            $errno,
            $errstr,
            5
        );

        if (!$socket) {
            throw new Exception("Cannot connect to printer: $errstr");
        }

        fwrite($socket, $command);
        fclose($socket);
    }

    /**
     * Send via Serial
     */
    private function sendSerial($command)
    {
        $handle = fopen($this->printer->serial_port, 'w+b');

        if (!$handle) {
            throw new Exception('Cannot open serial port');
        }

        fwrite($handle, $command);
        fclose($handle);
    }

    /**
     * Windows printer submission
     */
    private function sendWindowsPrint($command)
    {
        if (!config('printer.allow_system_commands')) {
            throw new Exception('System commands disabled');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'PRT');
        file_put_contents($tempFile, $command);

        $printerName = $this->printer->name;
        shell_exec("print /D:\"{$printerName}\" \"$tempFile\"");

        unlink($tempFile);
    }

    /**
     * Unix printer submission
     */
    private function sendUnixPrint($command)
    {
        if (!config('printer.allow_system_commands')) {
            throw new Exception('System commands disabled');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'PRT');
        file_put_contents($tempFile, $command);

        shell_exec("lpr -P {$this->printer->name} $tempFile");

        unlink($tempFile);
    }

    /**
     * Get printer connection display
     */
    public function getConnectionDisplay()
    {
        return $this->printer->connection_display;
    }
}
```

---

## 🛣️ API Routes

```php
<?php
// routes/api.php

Route::middleware(['auth:api'])->group(function () {
    
    // Printer Preferences (User)
    Route::prefix('printer')->group(function () {
        Route::get('/system-settings', [PrinterController::class, 'getSystemSettings']);
        Route::get('/user-preferences', [PrinterController::class, 'getUserPreferences']);
        Route::post('/user-preferences', [PrinterController::class, 'saveUserPreferences']);
        Route::get('/profiles', [PrinterController::class, 'getPrinterProfiles']);
    });

    // Thermal Printer Management (Admin)
    Route::prefix('thermal-printer')->middleware('admin')->group(function () {
        Route::get('/', [Api\ThermalPrinterController::class, 'index']);
        Route::post('/', [Api\ThermalPrinterController::class, 'store']);
        Route::get('{printer}', [Api\ThermalPrinterController::class, 'show']);
        Route::put('{printer}', [Api\ThermalPrinterController::class, 'update']);
        Route::delete('{printer}', [Api\ThermalPrinterController::class, 'destroy']);
        Route::post('{printer}/set-default', [Api\ThermalPrinterController::class, 'setDefault']);
        Route::get('{printer}/test-connection', [Api\ThermalPrinterController::class, 'testConnection']);
        Route::post('{printer}/print-test', [Api\ThermalPrinterController::class, 'printTest']);
    });
});
```

---

## 🖼️ Blade Views

### **Printer Settings Index**

```blade
@extends('layouts.app')

@section('title', 'Printer Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-printer"></i> Global Printer Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('printer-settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Receipt Paper Size *</label>
                                    <select name="receipt_paper_size" class="form-control" required>
                                        <option value="58mm" {{ $printerSettings->receipt_paper_size === '58mm' ? 'selected' : '' }}>
                                            58mm (Small Thermal)
                                        </option>
                                        <option value="80mm" {{ $printerSettings->receipt_paper_size === '80mm' ? 'selected' : '' }}>
                                            80mm (Standard Thermal)
                                        </option>
                                        <option value="letter" {{ $printerSettings->receipt_paper_size === 'letter' ? 'selected' : '' }}>
                                            Letter (8.5" x 11")
                                        </option>
                                        <option value="a4" {{ $printerSettings->receipt_paper_size === 'a4' ? 'selected' : '' }}>
                                            A4 (210mm x 297mm)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Default Receipt Printer</label>
                                    <select name="default_receipt_printer" class="form-control">
                                        <option value="">System Default</option>
                                        @foreach($activePrinters as $printer)
                                            <option value="{{ $printer->id }}"
                                                {{ $printerSettings->default_receipt_printer == $printer->id ? 'selected' : '' }}>
                                                {{ $printer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Number of Copies</label>
                                    <select name="receipt_copies" class="form-control">
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ $printerSettings->receipt_copies == $i ? 'selected' : '' }}>
                                                {{ $i }} {{ $i == 1 ? 'Copy' : 'Copies' }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="auto_print" name="auto_print_receipt" 
                                               class="form-check-input" value="1"
                                               {{ $printerSettings->auto_print_receipt ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_print">
                                            Auto Print Receipt After Sale
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="customer_copy" name="print_customer_copy"
                                               class="form-check-input" value="1"
                                               {{ $printerSettings->print_customer_copy ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customer_copy">
                                            Print Customer Copy
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## ⚡ JavaScript Integration

### **Printer Detection & Auto-selection**

```javascript
// public/js/printer-detection.js

class PrinterManager {
    constructor() {
        this.availablePrinters = [];
        this.userPreferences = null;
        this.systemSettings = null;
        this.defaultPrinter = null;
    }

    async init() {
        await this.loadSystemSettings();
        await this.loadUserPreferences();
        await this.detectPrinters();
        this.selectBestPrinter();
        this.setupPrintHandlers();
    }

    async loadSystemSettings() {
        const response = await fetch('/api/printer/system-settings');
        this.systemSettings = await response.json();
    }

    async loadUserPreferences() {
        const response = await fetch('/api/printer/user-preferences');
        this.userPreferences = await response.json();
    }

    async detectPrinters() {
        // Detect available printers via system
        const response = await fetch('/api/thermal-printer');
        this.availablePrinters = await response.json();
    }

    selectBestPrinter() {
        // 1. User preference
        if (this.userPreferences?.receipt_printer_name) {
            this.defaultPrinter = this.availablePrinters.find(p =>
                p.name === this.userPreferences.receipt_printer_name
            );
        }

        // 2. System default
        if (!this.defaultPrinter && this.systemSettings?.default_receipt_printer) {
            this.defaultPrinter = this.availablePrinters.find(p =>
                p.id == this.systemSettings.default_receipt_printer
            );
        }

        // 3. First active printer
        if (!this.defaultPrinter && this.availablePrinters.length > 0) {
            this.defaultPrinter = this.availablePrinters[0];
        }
    }

    setupPrintHandlers() {
        document.addEventListener('DOMContentLoaded', () => {
            const printBtn = document.getElementById('print-receipt');
            if (printBtn) {
                printBtn.addEventListener('click', () => this.printReceipt());
            }
        });
    }

    async printReceipt() {
        if (!this.defaultPrinter) {
            alert('No printer configured');
            return;
        }

        const receiptContent = document.getElementById('receipt-preview').innerHTML;

        try {
            const response = await fetch(`/api/thermal-printer/${this.defaultPrinter.id}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: receiptContent,
                    copies: this.systemSettings.receipt_copies
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('Print job sent!');
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            alert('Print failed: ' + error.message);
        }
    }
}

// Initialize
const printerManager = new PrinterManager();
printerManager.init();
```

---

**Document Version**: 1.0  
**Updated**: November 17, 2025  
**Status**: Ready for Implementation ✓
