<?php

namespace App\Services;

use App\Models\ThermalPrinterSetting;
use App\Models\UserPrinterPreference;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PrinterService
{
    /**
     * Get active printer for user
     */
    public static function getActivePrinter($userId = null)
    {
        // Get user preference if exists
        if ($userId) {
            $preference = Cache::remember(
                "user_printer_pref_{$userId}",
                3600,
                function () use ($userId) {
                    return UserPrinterPreference::where('user_id', $userId)
                        ->where('is_active', true)
                        ->first();
                }
            );

            if ($preference && $preference->printer) {
                return $preference->printer;
            }
        }

        // Get default printer
        $defaultPrinter = Cache::remember('default_printer', 3600, function () {
            return ThermalPrinterSetting::where('is_default', true)
                ->where('is_active', true)
                ->first();
        });

        if ($defaultPrinter) {
            return $defaultPrinter;
        }

        // Get first active printer as fallback
        return ThermalPrinterSetting::where('is_active', true)->first();
    }

    /**
     * Get printer by ID
     */
    public static function getPrinter($printerId)
    {
        return Cache::remember("printer_{$printerId}", 3600, function () use ($printerId) {
            return ThermalPrinterSetting::find($printerId);
        });
    }

    /**
     * Test printer connection
     */
    public static function testConnection(ThermalPrinterSetting $printer)
    {
        try {
            $driver = PrinterDriverFactory::create($printer);
            $result = $driver->testConnection();

            Log::info("Printer test connection: {$printer->name}", ['result' => $result]);
            
            return [
                'success' => true,
                'message' => 'Koneksi berhasil',
                'printer' => $printer->name,
                'connection_type' => $printer->connection_type,
            ];
        } catch (\Exception $e) {
            Log::error("Printer test connection failed: {$printer->name}", ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
                'printer' => $printer->name,
            ];
        }
    }

    /**
     * Print content
     */
    public static function print($content, $options = [])
    {
        $userId = $options['user_id'] ?? auth()->id();
        $printer = $options['printer'] ?? self::getActivePrinter($userId);

        if (!$printer) {
            throw new \Exception('Tidak ada printer yang dikonfigurasi');
        }

        try {
            $driver = PrinterDriverFactory::create($printer);
            $driver->print($content, $options);

            Log::info("Print job sent to {$printer->name} for user {$userId}");
            
            return ['success' => true, 'printer' => $printer->name];
        } catch (\Exception $e) {
            Log::error("Print failed on {$printer->name}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get available printers (cached)
     */
    public static function getAvailablePrinters()
    {
        return Cache::remember('available_printers', 300, function () {
            return ThermalPrinterSetting::where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear printer cache
     */
    public static function clearCache($printerId = null)
    {
        if ($printerId) {
            Cache::forget("printer_{$printerId}");
        } else {
            Cache::forget('available_printers');
            Cache::forget('default_printer');
        }
    }
}
