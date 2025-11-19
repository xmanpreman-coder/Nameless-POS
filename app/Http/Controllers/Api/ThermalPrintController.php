<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ThermalPrinterService;
use App\Models\ThermalPrinterSetting;
use Modules\Sale\Entities\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThermalPrintController extends Controller
{
    protected $thermalService;

    public function __construct(ThermalPrinterService $thermalService)
    {
        $this->thermalService = $thermalService;
    }

    /**
     * Print sale receipt
     */
    public function printSale(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sale_id' => 'required|exists:sales,id',
                'printer_id' => 'nullable|exists:thermal_printer_settings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $sale = Sale::findOrFail($request->sale_id);
            
            // Use specific printer if provided
            if ($request->printer_id) {
                $printer = ThermalPrinterSetting::findOrFail($request->printer_id);
                $this->thermalService->setPrinter($printer);
            }

            $result = $this->thermalService->printSaleReceipt($sale);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'printer_name' => $this->thermalService->getPrinter()->name,
                'printer_type' => $this->thermalService->getPrinter()->connection_type,
                'details' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error('Thermal print API error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Print failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print test page
     */
    public function printTest(Request $request, $printerId = null)
    {
        try {
            // Use specific printer if provided
            if ($printerId) {
                $printer = ThermalPrinterSetting::findOrFail($printerId);
                $this->thermalService->setPrinter($printer);
            }

            $result = $this->thermalService->printTestPage();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'printer_name' => $this->thermalService->getPrinter()->name,
                'printer_type' => $this->thermalService->getPrinter()->connection_type,
                'details' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error('Thermal test print API error: ' . $e->getMessage(), [
                'printer_id' => $printerId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test print failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open cash drawer
     */
    public function openCashDrawer(Request $request, $printerId = null)
    {
        try {
            // Use specific printer if provided
            if ($printerId) {
                $printer = ThermalPrinterSetting::findOrFail($printerId);
                $this->thermalService->setPrinter($printer);
            }

            $result = $this->thermalService->openCashDrawer();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'printer_name' => $this->thermalService->getPrinter()->name,
                'printer_type' => $this->thermalService->getPrinter()->connection_type,
                'details' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error('Cash drawer API error: ' . $e->getMessage(), [
                'printer_id' => $printerId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cash drawer operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available thermal printers
     */
    public function getAvailablePrinters()
    {
        try {
            $printers = ThermalPrinterSetting::where('is_active', true)
                                           ->orderBy('is_default', 'desc')
                                           ->orderBy('name')
                                           ->get(['id', 'name', 'brand', 'model', 'connection_type', 'is_default']);

            return response()->json([
                'success' => true,
                'data' => $printers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get printers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get printer status
     */
    public function getPrinterStatus($printerId)
    {
        try {
            $printer = ThermalPrinterSetting::findOrFail($printerId);
            $status = $printer->testConnection();

            return response()->json([
                'success' => true,
                'printer_name' => $printer->name,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}