<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Stream media file from public disk
     * 
     * @param int $mediaId
     * @param string $filename
     * @return StreamedResponse
     */
    public function show($mediaId, $filename)
    {
        $path = $mediaId . '/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }
}
