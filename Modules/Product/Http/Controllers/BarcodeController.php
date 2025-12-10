<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use ZipArchive;

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

    /**
     * Convert provided barcode SVGs to PNG and return a ZIP download.
     * Expects JSON payload: { barcodeData: [ { barcode: '<svg...>', name: '...', barcode_value: '...' }, ... ] }
     */
    public function downloadPngZip(Request $request) {
        abort_if(Gate::denies('print_barcodes'), 403);

        $barcodeData = $request->input('barcodeData', []);
        if (empty($barcodeData) || !is_array($barcodeData)) {
            return response()->json(['error' => 'No barcode data provided'], 400);
        }

        if (!extension_loaded('imagick')) {
            return response()->json(['error' => 'Imagick extension is required on server to convert SVG to PNG'], 500);
        }

        $tmpDir = sys_get_temp_dir();
        $zipPath = tempnam($tmpDir, 'barcodes_') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return response()->json(['error' => 'Unable to create ZIP file'], 500);
        }

        $index = 0;
        foreach ($barcodeData as $item) {
            $index++;
            $svg = $item['barcode'] ?? '';
            $name = $item['name'] ?? 'barcode';
            $value = $item['barcode_value'] ?? ($item['sku'] ?? $index);

            if (empty($svg)) continue;

            try {
                // Use dynamic class names to avoid static analysis issues when Imagick is not present
                if (!class_exists('Imagick')) {
                    throw new \Exception('Imagick class not available on server');
                }
                $imClass = 'Imagick';
                $pixClass = 'ImagickPixel';

                $imagick = new $imClass();
                if (class_exists($pixClass)) {
                    $imagick->setBackgroundColor(new $pixClass('white'));
                }
                // Ensure the SVG is treated as svg
                $imagick->readImageBlob($svg);
                $imagick->setImageFormat('png32');
                // Optional resize for consistent size (width 600 auto height)
                $filterConst = defined("{$imClass}::FILTER_LANCZOS") ? constant("{$imClass}::FILTER_LANCZOS") : 1;
                $imagick->resizeImage(600, 0, $filterConst, 1);
                $png = $imagick->getImagesBlob();
                $imagick->clear();
                $imagick->destroy();

                $cleanName = preg_replace('/[^a-z0-9_\-\.]/i', '_', $name);
                $fileName = sprintf('%s_%s.png', $cleanName, preg_replace('/[^a-z0-9\-]/i', '_', (string)$value));
                $zip->addFromString($fileName, $png);
            } catch (\Exception $e) {
                // Skip problematic item but continue; include error note in ZIP
                $zip->addFromString("error_{$index}.txt", "Failed to convert item: " . $e->getMessage());
            }
        }

        $zip->close();

        return response()->download($zipPath, 'barcodes_png_' . time() . '.zip')->deleteFileAfterSend(true);
    }

}
