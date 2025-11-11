<?php

namespace Modules\Scanner\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Scanner\Entities\ScannerSetting;
use Modules\Product\Entities\Product;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner::index');
    }

    public function settings()
    {
        $settings = ScannerSetting::getSettings();
        return view('scanner::settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'scanner_type' => 'required|in:camera,usb,bluetooth',
            'beep_sound' => 'boolean',
            'vibration' => 'boolean', 
            'scan_mode' => 'required|in:auto,manual',
            'scan_timeout' => 'required|integer|min:5|max:120',
            'auto_focus' => 'boolean',
            'preferred_camera' => 'required|in:back,front'
        ]);

        $settings = ScannerSetting::getSettings();
        $settings->update([
            'scanner_type' => $request->scanner_type,
            'beep_sound' => $request->has('beep_sound'),
            'vibration' => $request->has('vibration'),
            'scan_mode' => $request->scan_mode,
            'scan_timeout' => $request->scan_timeout,
            'auto_focus' => $request->has('auto_focus'),
            'preferred_camera' => $request->preferred_camera,
            'camera_settings' => $request->camera_settings ?? [],
            'usb_settings' => $request->usb_settings ?? [],
            'bluetooth_settings' => $request->bluetooth_settings ?? []
        ]);

        return redirect()->back()->with('success', 'Scanner settings updated successfully!');
    }

    public function scan()
    {
        $settings = ScannerSetting::getSettings();
        return view('scanner::scan', compact('settings'));
    }

    public function searchProduct(Request $request)
    {
        $barcode = $request->barcode;
        
        $product = Product::where('product_barcode_symbology', $barcode)
                         ->orWhere('product_code', $barcode)
                         ->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'code' => $product->product_code,
                    'barcode' => $product->product_barcode_symbology,
                    'price' => $product->product_price,
                    'stock' => $product->product_quantity,
                    'image' => $product->product_image ? asset('storage/' . $product->product_image) : null
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found for barcode: ' . $barcode
        ]);
    }

    public function testCamera()
    {
        return view('scanner::test-camera');
    }
}