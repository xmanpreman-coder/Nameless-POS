<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPrinterPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrinterController extends Controller
{
    public function getSystemSettings()
    {
        $settings = app('settings');
        
        return response()->json([
            'receipt_paper_size' => $settings->receipt_paper_size ?? '80mm',
            'auto_print_receipt' => $settings->auto_print_receipt ?? false,
            'default_receipt_printer' => $settings->default_receipt_printer
        ]);
    }

    public function getUserPreferences()
    {
        $user = Auth::user();
        $preference = UserPrinterPreference::where('user_id', $user->id)->first();
        
        if (!$preference) {
            // Return system defaults if no user preference exists
            $settings = app('settings');
            return response()->json([
                'receipt_paper_size' => $settings->receipt_paper_size ?? '80mm',
                'auto_print_receipt' => $settings->auto_print_receipt ?? false,
                'receipt_printer_name' => $settings->default_receipt_printer,
                'print_customer_copy' => false
            ]);
        }

        return response()->json([
            'receipt_paper_size' => $preference->receipt_paper_size,
            'auto_print_receipt' => $preference->auto_print_receipt,
            'receipt_printer_name' => $preference->receipt_printer_name,
            'print_customer_copy' => $preference->print_customer_copy,
            'printer_settings' => $preference->printer_settings
        ]);
    }

    public function saveUserPreferences(Request $request)
    {
        $request->validate([
            'receipt_printer_name' => 'nullable|string|max:255',
            'receipt_paper_size' => 'string|in:58mm,80mm,letter,a4',
            'auto_print_receipt' => 'boolean',
            'print_customer_copy' => 'boolean'
        ]);

        $user = Auth::user();
        
        UserPrinterPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'receipt_printer_name' => $request->receipt_printer_name,
                'receipt_paper_size' => $request->receipt_paper_size ?? '80mm',
                'auto_print_receipt' => $request->auto_print_receipt ?? false,
                'print_customer_copy' => $request->print_customer_copy ?? false,
                'printer_settings' => $request->printer_settings
            ]
        );

        return response()->json(['message' => 'Printer preferences saved successfully']);
    }

    public function getPrinterProfiles()
    {
        // Return common printer profiles for different printer types
        return response()->json([
            'thermal' => [
                'paper_sizes' => ['58mm', '80mm'],
                'default_settings' => [
                    'font_family' => 'Courier New',
                    'font_size' => '12px',
                    'margins' => '0',
                    'line_spacing' => 'normal'
                ]
            ],
            'dot_matrix' => [
                'paper_sizes' => ['letter', 'a4'],
                'default_settings' => [
                    'font_family' => 'Courier New',
                    'font_size' => '10px',
                    'margins' => '0.5in',
                    'line_spacing' => 'condensed'
                ]
            ],
            'laser' => [
                'paper_sizes' => ['letter', 'a4'],
                'default_settings' => [
                    'font_family' => 'Arial',
                    'font_size' => '12px',
                    'margins' => '0.5in',
                    'line_spacing' => 'normal'
                ]
            ]
        ]);
    }
}