<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class BarcodeController extends Controller
{

    public function printBarcode() {
        abort_if(Gate::denies('print_barcodes'), 403);

        return view('product::barcode.index');
    }
    
    public function printBarcodeView() {
        abort_if(Gate::denies('print_barcodes'), 403);
        
        // Coba ambil dari session dengan put (persistent) atau flash (temporary)
        $barcodeData = session('barcode_data', []);
        
        // Jika masih kosong, coba ambil dari flash
        if (empty($barcodeData)) {
            $barcodeData = session()->get('barcode_data', []);
        }
        
        if (empty($barcodeData)) {
            // Jika masih kosong, return error message dengan JavaScript untuk close window
            return response('<script>alert("Tidak ada barcode untuk dicetak!"); if(window.opener){window.close()}</script>')
                ->header('Content-Type', 'text/html');
        }
        
        return view('product::barcode.print-view', [
            'barcodeData' => $barcodeData
        ]);
    }

}
