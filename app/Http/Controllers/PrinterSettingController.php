<?php

namespace App\Http\Controllers;

use App\Models\PrinterSetting;
use App\Models\ThermalPrinterSetting;
use App\Models\UserPrinterPreference;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PrinterSettingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('access_settings'), 403);
        
        $printerSettings = PrinterSetting::getInstance();
        $printers = ThermalPrinterSetting::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
        $defaultPrinter = ThermalPrinterSetting::where('is_default', true)->first();
        $userPreference = auth()->user()->printerPreference;
        
        return view('printer-settings.index', compact(
            'printerSettings',
            'printers',
            'defaultPrinter',
            'userPreference'
        ));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('edit_settings'), 403);
        
        $request->validate([
            'receipt_paper_size' => 'required|string|in:58mm,80mm,letter,a4',
            'auto_print_receipt' => 'boolean',
            'default_receipt_printer' => 'nullable|string|max:255',
            'print_customer_copy' => 'boolean',
            'receipt_copies' => 'integer|min:1|max:5',
            'thermal_printer_commands' => 'nullable|string'
        ]);

        $printerSettings = PrinterSetting::getInstance();
        $printerSettings->update([
            'receipt_paper_size' => $request->receipt_paper_size,
            'auto_print_receipt' => $request->boolean('auto_print_receipt'),
            'default_receipt_printer' => $request->default_receipt_printer,
            'print_customer_copy' => $request->boolean('print_customer_copy'),
            'receipt_copies' => $request->receipt_copies ?? 1,
            'thermal_printer_commands' => $request->thermal_printer_commands
        ]);

        return redirect()->route('printer-settings.index')
            ->with('success', 'Printer settings updated successfully');
    }

    /**
     * Create new thermal printer
     */
    public function create()
    {
        abort_if(Gate::denies('access_settings'), 403);
        
        $presets = ThermalPrinterSetting::getPresets();
        $connectionTypes = ['network', 'usb', 'serial', 'windows'];
        
        return view('printer-settings.create', compact('presets', 'connectionTypes'));
    }

    /**
     * Store new thermal printer
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('access_settings'), 403);
        
        $validated = $request->validate([
            'name' => 'required|string|unique:thermal_printer_settings|max:100',
            'brand' => 'required|string|max:50',
            'connection_type' => 'required|in:network,usb,serial,windows',
            'connection_address' => 'required|string|max:255',
            'connection_port' => 'nullable|integer|min:1|max:65535',
            'paper_width' => 'required|in:58,80,letter,a4',
            'receipt_copies' => 'required|integer|min:1|max:10',
            'auto_cut' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $printer = ThermalPrinterSetting::create($validated);
        
        if (ThermalPrinterSetting::count() === 1) {
            $printer->update(['is_default' => true]);
        }

        PrinterService::clearCache();

        return redirect()->route('printer-settings.index')
            ->with('success', 'Printer berhasil ditambahkan');
    }

    /**
     * Test printer connection
     */
    public function testConnection(ThermalPrinterSetting $printer)
    {
        return response()->json(PrinterService::testConnection($printer));
    }

    /**
     * Set printer as default
     */
    public function setDefault(ThermalPrinterSetting $printer)
    {
        ThermalPrinterSetting::where('is_default', true)->update(['is_default' => false]);
        $printer->update(['is_default' => true]);
        PrinterService::clearCache();

        return response()->json(['success' => true, 'message' => 'Default printer updated']);
    }

    /**
     * Delete thermal printer
     */
    public function deletePrinter(ThermalPrinterSetting $printer)
    {
        if ($printer->is_default) {
            return back()->with('error', 'Tidak bisa menghapus printer default');
        }

        $printer->delete();
        PrinterService::clearCache();

        return back()->with('success', 'Printer berhasil dihapus');
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
            $validated
        );

        PrinterService::clearCache();

        return response()->json(['success' => true, 'message' => 'Preferensi printer disimpan']);
    }
}
