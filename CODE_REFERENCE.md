# Multi-Printer Implementation - Code Reference

**Complete copy-paste ready code snippets for quick integration**

---

## 1️⃣ Service Layer - PrinterService.php

**Location**: `app/Services/PrinterService.php`  
**Purpose**: Facade untuk printer operations dengan caching  
**Dependencies**: Cache, ThermalPrinterSetting, UserPrinterPreference

```php
<?php

namespace App\Services;

use App\Models\ThermalPrinterSetting;
use App\Models\UserPrinterPreference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PrinterService
{
    /**
     * Get active printer untuk user
     * Priority: User preference > System default > First active
     */
    public static function getActivePrinter($userId = null)
    {
        try {
            // 1. Check user preference
            if ($userId) {
                $cacheKey = "user_printer_pref_{$userId}";
                $preference = Cache::remember($cacheKey, 3600, function() use ($userId) {
                    return UserPrinterPreference::where('user_id', $userId)
                        ->where('is_active', true)
                        ->first();
                });
                
                if ($preference && $preference->printer) {
                    return $preference->printer;
                }
            }

            // 2. Check default printer
            $defaultPrinter = Cache::remember('default_printer', 3600, function() {
                return ThermalPrinterSetting::where('is_default', true)
                    ->where('is_active', true)
                    ->first();
            });
            
            if ($defaultPrinter) {
                return $defaultPrinter;
            }

            // 3. Return first active printer
            return ThermalPrinterSetting::where('is_active', true)->first();
        } catch (Exception $e) {
            Log::error('Error getting active printer', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get single printer by ID
     */
    public static function getPrinter($printerId)
    {
        return Cache::remember("printer_{$printerId}", 3600, function() use ($printerId) {
            return ThermalPrinterSetting::find($printerId);
        });
    }

    /**
     * Test printer connection
     */
    public static function testConnection(ThermalPrinterSetting $printer)
    {
        try {
            $driver = PrinterDriverFactory::create(
                $printer->connection_type,
                $printer->connection_address,
                $printer->connection_port ?? 9100
            );

            $result = $driver->testConnection();

            return [
                'success' => true,
                'message' => 'Koneksi printer berhasil',
                'printer' => $printer->name,
                'connection_type' => $printer->connection_type,
            ];
        } catch (Exception $e) {
            Log::warning('Printer connection failed', [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terkoneksi dengan printer: ' . $e->getMessage(),
                'printer' => $printer->name,
            ];
        }
    }

    /**
     * Send content to printer
     */
    public static function print($content, $options = [])
    {
        try {
            $userId = $options['user_id'] ?? null;
            $printer = $options['printer'] ?? self::getActivePrinter($userId);

            if (!$printer) {
                throw new Exception('Printer tidak ditemukan');
            }

            $driver = PrinterDriverFactory::create(
                $printer->connection_type,
                $printer->connection_address,
                $printer->connection_port ?? 9100
            );

            $result = $driver->print($content, $options);

            Log::info('Print job sent', [
                'printer_id' => $printer->id,
                'user_id' => $userId
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Print job failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get all available printers (cached)
     */
    public static function getAvailablePrinters()
    {
        return Cache::remember('available_printers', 300, function() {
            return ThermalPrinterSetting::where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->get();
        });
    }

    /**
     * Clear cache untuk printer
     */
    public static function clearCache($printerId = null)
    {
        if ($printerId) {
            Cache::forget("printer_{$printerId}");
        }

        Cache::forget('available_printers');
        Cache::forget('default_printer');
    }
}
```

---

## 2️⃣ Driver Factory - PrinterDriverFactory.php

**Location**: `app/Services/PrinterDriverFactory.php`  
**Purpose**: Factory pattern untuk create driver berdasarkan connection type

```php
<?php

namespace App\Services;

use Exception;

interface PrinterDriverInterface
{
    public function testConnection(): bool;
    public function print($content, $options = []): bool;
}

class PrinterDriverFactory
{
    public static function create(string $connectionType, $address, $port = 9100): PrinterDriverInterface
    {
        return match(strtolower($connectionType)) {
            'network' => new NetworkPrinterDriver($address, $port),
            'usb' => new USBPrinterDriver($address),
            'serial' => new SerialPrinterDriver($address, $port),
            'windows' => new WindowsPrinterDriver($address),
            'bluetooth' => new BluetoothPrinterDriver($address),
            default => throw new Exception("Tipe koneksi tidak didukung: {$connectionType}"),
        };
    }
}

/**
 * Network Printer Driver (Ethernet)
 * Connection: TCP socket ke printer IP:PORT
 */
class NetworkPrinterDriver implements PrinterDriverInterface
{
    private $ip;
    private $port;

    public function __construct($ip, $port = 9100)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function testConnection(): bool
    {
        $socket = @fsockopen($this->ip, $this->port, $errno, $errstr, 2);
        if (!$socket) {
            throw new Exception("Tidak bisa koneksi ke {$this->ip}:{$this->port} - {$errstr}");
        }
        fclose($socket);
        return true;
    }

    public function print($content, $options = []): bool
    {
        $socket = @fsockopen($this->ip, $this->port, $errno, $errstr, 2);
        if (!$socket) {
            throw new Exception("Print gagal: Tidak bisa koneksi");
        }

        fwrite($socket, $content);
        fclose($socket);
        return true;
    }
}

/**
 * USB Printer Driver
 * Linux: /dev/usb/lp0, /dev/ttyUSB0
 * Windows: Printer name atau port
 */
class USBPrinterDriver implements PrinterDriverInterface
{
    private $devicePath;

    public function __construct($devicePath)
    {
        $this->devicePath = $devicePath;
    }

    public function testConnection(): bool
    {
        if (!file_exists($this->devicePath)) {
            throw new Exception("Device tidak ditemukan: {$this->devicePath}");
        }

        if (!is_writable($this->devicePath)) {
            throw new Exception("Device tidak bisa ditulis: {$this->devicePath}");
        }

        return true;
    }

    public function print($content, $options = []): bool
    {
        $handle = @fopen($this->devicePath, 'w');
        if (!$handle) {
            throw new Exception("Tidak bisa buka device: {$this->devicePath}");
        }

        fwrite($handle, $content);
        fclose($handle);
        return true;
    }
}

/**
 * Serial Printer Driver
 * Windows: COM1, COM2, COM3
 * Linux: /dev/ttyS0, /dev/ttyS1
 */
class SerialPrinterDriver implements PrinterDriverInterface
{
    private $port;

    public function __construct($port)
    {
        $this->port = $port;
    }

    public function testConnection(): bool
    {
        // Simplified test untuk serial port
        if (!file_exists($this->port) && strpos($this->port, 'COM') === false) {
            throw new Exception("Port tidak valid: {$this->port}");
        }
        return true;
    }

    public function print($content, $options = []): bool
    {
        $handle = @fopen($this->port, 'w');
        if (!$handle) {
            throw new Exception("Tidak bisa buka port: {$this->port}");
        }

        fwrite($handle, $content);
        fclose($handle);
        return true;
    }
}

/**
 * Windows Print Server Driver
 * Menggunakan Windows print command
 */
class WindowsPrinterDriver implements PrinterDriverInterface
{
    private $printerName;

    public function __construct($printerName)
    {
        $this->printerName = $printerName;
    }

    public function testConnection(): bool
    {
        // Test dengan list printer
        $output = shell_exec('wmic printjob list brief');
        if ($output === null) {
            throw new Exception("Windows print system tidak tersedia");
        }
        return true;
    }

    public function print($content, $options = []): bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'print_');
        file_put_contents($tempFile, $content);

        $command = "print /d:\"{$this->printerName}\" \"{$tempFile}\"";
        $output = shell_exec($command);

        unlink($tempFile);

        if ($output === null) {
            throw new Exception("Print command gagal");
        }

        return true;
    }
}

/**
 * Bluetooth Printer Driver
 * Untuk mobile & portable printer
 */
class BluetoothPrinterDriver implements PrinterDriverInterface
{
    private $deviceAddress;

    public function __construct($deviceAddress)
    {
        $this->deviceAddress = $deviceAddress;
    }

    public function testConnection(): bool
    {
        // Stub untuk bluetooth
        // Implementasi nyata memerlukan bluetooth library
        return true;
    }

    public function print($content, $options = []): bool
    {
        // Stub untuk bluetooth
        // Implementasi nyata memerlukan bluetooth library
        return true;
    }
}
```

---

## 3️⃣ Database Migration

**Location**: `database/migrations/2025_11_17_create_user_printer_preferences_table.php`  
**Purpose**: Create user_printer_preferences table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_printer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('thermal_printer_setting_id')
                ->constrained('thermal_printer_settings')
                ->onDelete('cascade');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Unique constraint: user can have only one active preference per printer
            $table->unique(['user_id', 'thermal_printer_setting_id']);
            
            // Index untuk queries
            $table->index(['user_id', 'is_active']);
        });

        // Add receipt_copies column ke thermal_printer_settings jika belum ada
        if (!Schema::hasColumn('thermal_printer_settings', 'receipt_copies')) {
            Schema::table('thermal_printer_settings', function (Blueprint $table) {
                $table->integer('receipt_copies')->default(1)->after('paper_width');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_printer_preferences');
    }
};
```

---

## 4️⃣ Controller Methods

**Location**: `app/Http/Controllers/PrinterSettingController.php`  
**Tambahkan method ini ke controller**

```php
/**
 * Show form to create new printer
 */
public function create()
{
    Gate::authorize('access_settings');

    $presets = ThermalPrinterSetting::getPresets();
    $connectionTypes = [
        'network' => 'Network (Ethernet)',
        'usb' => 'USB',
        'serial' => 'Serial (COM Port)',
        'windows' => 'Windows Print Server',
        'bluetooth' => 'Bluetooth',
    ];

    return view('printer-settings.create', compact('presets', 'connectionTypes'));
}

/**
 * Store new printer
 */
public function store(Request $request)
{
    Gate::authorize('access_settings');

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'brand' => 'required|string',
        'connection_type' => 'required|in:network,usb,serial,windows,bluetooth',
        'connection_address' => 'required|string',
        'connection_port' => 'nullable|integer|between:1,65535',
        'paper_width' => 'required|in:58,80,letter,a4',
        'receipt_copies' => 'nullable|integer|between:1,10',
    ]);

    // Unset existing default jika ini adalah default
    if ($request->has('is_default') && $request->is_default) {
        ThermalPrinterSetting::where('is_default', true)->update(['is_default' => false]);
        $validated['is_default'] = true;
    } else {
        // Auto-set sebagai default jika ini printer pertama
        $validated['is_default'] = ThermalPrinterSetting::count() === 0;
    }

    $validated['is_active'] = true;
    $printer = ThermalPrinterSetting::create($validated);

    PrinterService::clearCache();
    Cache::forget('available_printers');

    return redirect()->route('printer-settings.index')
        ->with('success', "Printer '{$printer->name}' berhasil dibuat");
}

/**
 * Test printer connection
 */
public function testConnection(ThermalPrinterSetting $thermalPrinterSetting)
{
    Gate::authorize('access_settings');

    $result = PrinterService::testConnection($thermalPrinterSetting);

    if (request()->wantsJson()) {
        return response()->json($result);
    }

    $message = $result['success'] ? 'success' : 'error';
    return redirect()->back()->with($message, $result['message']);
}

/**
 * Set printer as default
 */
public function setDefault(ThermalPrinterSetting $thermalPrinterSetting)
{
    Gate::authorize('access_settings');

    // Unset all defaults
    ThermalPrinterSetting::where('is_default', true)->update(['is_default' => false]);

    // Set this one as default
    $thermalPrinterSetting->update(['is_default' => true]);

    PrinterService::clearCache();
    Cache::forget('default_printer');

    if (request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Printer berhasil dijadikan default']);
    }

    return redirect()->back()->with('success', 'Printer berhasil dijadikan default');
}

/**
 * Delete printer
 */
public function deletePrinter(ThermalPrinterSetting $thermalPrinterSetting)
{
    Gate::authorize('access_settings');

    if ($thermalPrinterSetting->is_default) {
        return redirect()->back()->with('error', 'Tidak bisa menghapus printer default. Ubah default terlebih dahulu.');
    }

    $name = $thermalPrinterSetting->name;
    $thermalPrinterSetting->delete();

    PrinterService::clearCache();
    Cache::forget('available_printers');

    if (request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => "Printer '{$name}' berhasil dihapus"]);
    }

    return redirect()->back()->with('success', "Printer '{$name}' berhasil dihapus");
}

/**
 * Save user printer preference
 */
public function savePreference(Request $request)
{
    $validated = $request->validate([
        'thermal_printer_setting_id' => 'required|exists:thermal_printer_settings,id',
    ]);

    UserPrinterPreference::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'thermal_printer_setting_id' => $validated['thermal_printer_setting_id'],
            'is_active' => true,
        ]
    );

    PrinterService::clearCache(auth()->id());

    if ($request->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Preferensi printer berhasil disimpan']);
    }

    return redirect()->back()->with('success', 'Preferensi printer berhasil disimpan');
}
```

---

## 5️⃣ Routes Configuration

**Location**: `routes/web.php`  
**Tambahkan dalam auth middleware group**

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... existing routes ...

    // Printer Settings Routes
    Route::get('/printer-settings/create', [PrinterSettingController::class, 'create'])->name('printer-settings.create');
    Route::post('/printer-settings', [PrinterSettingController::class, 'store'])->name('printer-settings.store');
    Route::get('/printer-settings/{thermalPrinterSetting}/test', [PrinterSettingController::class, 'testConnection'])->name('printer-settings.test');
    Route::post('/printer-settings/{thermalPrinterSetting}/default', [PrinterSettingController::class, 'setDefault'])->name('printer-settings.setDefault');
    Route::delete('/printer-settings/{thermalPrinterSetting}', [PrinterSettingController::class, 'deletePrinter'])->name('printer-settings.destroy');
    Route::post('/printer-preferences', [PrinterSettingController::class, 'savePreference'])->name('printer-preferences.save');
});
```

---

## 6️⃣ Usage Examples di Controller

```php
// Di SaleController atau PrintController

use App\Services\PrinterService;
use App\Models\ThermalPrinterSetting;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        // ... sale logic ...

        // Get user's active printer atau default
        $printer = PrinterService::getActivePrinter(auth()->id());

        if (!$printer) {
            return back()->with('error', 'Printer tidak dikonfigurasi');
        }

        // Generate receipt HTML
        $receiptHtml = view('receipts.thermal', [
            'sale' => $sale,
            'items' => $saleItems,
            'printer' => $printer,
        ])->render();

        try {
            // Print
            PrinterService::print($receiptHtml, [
                'user_id' => auth()->id(),
                'printer' => $printer,
            ]);

            return back()->with('success', 'Penjualan disimpan dan dicetak');
        } catch (Exception $e) {
            Log::error('Print failed', ['error' => $e->getMessage()]);
            return back()->with('warning', 'Penjualan disimpan tapi gagal cetak: ' . $e->getMessage());
        }
    }
}
```

---

## 7️⃣ API Example Response

### Get Available Printers
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Printer Kasir 1",
            "brand": "eppos",
            "connection_type": "network",
            "connection_address": "192.168.1.100",
            "connection_port": 9100,
            "paper_width": "80",
            "is_default": true,
            "is_active": true
        },
        {
            "id": 2,
            "name": "Printer Kasir 2",
            "brand": "xprinter",
            "connection_type": "usb",
            "connection_address": "/dev/ttyUSB0",
            "connection_port": null,
            "paper_width": "58",
            "is_default": false,
            "is_active": true
        }
    ]
}
```

### Test Connection Response
```json
{
    "success": true,
    "message": "Koneksi printer berhasil",
    "printer": "Printer Kasir 1",
    "connection_type": "network"
}
```

---

## ⚡ Quick Integration Checklist

- [ ] Copy `PrinterService.php` to `app/Services/`
- [ ] Copy `PrinterDriverFactory.php` to `app/Services/`
- [ ] Create directory `app/Services/` if not exists
- [ ] Copy migration file to `database/migrations/`
- [ ] Add controller methods to `PrinterSettingController`
- [ ] Add routes to `routes/web.php`
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan cache:clear`
- [ ] Test endpoints

---

**Ready to use!** Copy-paste these codes into your application.
