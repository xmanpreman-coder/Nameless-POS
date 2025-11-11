<?php

namespace App\Http\Controllers;

use App\Models\PrinterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PrinterSettingController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('access_settings'), 403);
        
        $printerSettings = PrinterSetting::getInstance();
        
        return view('printer-settings.index', compact('printerSettings'));
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
}
