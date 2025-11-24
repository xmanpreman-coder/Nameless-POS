<?php

namespace Modules\Upload\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Modules\Upload\Entities\Upload;

class UploadController extends Controller
{

    public function filepondUpload(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:png,jpeg,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $uploaded_file = $request->file('image');
            $filename = now()->timestamp . '.' . $uploaded_file->getClientOriginalExtension();
            $folder = uniqid() . '-' . now()->timestamp;

            // Ensure temp directory exists
            if (!Storage::exists('temp')) {
                Storage::makeDirectory('temp');
            }
            
            if (!Storage::exists('temp/' . $folder)) {
                Storage::makeDirectory('temp/' . $folder);
            }

            // Process image with Intervention
            $file = Image::make($uploaded_file)
                ->resize(500, 500, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode($uploaded_file->getClientOriginalExtension(), 90);

            Storage::put('temp/' . $folder . '/' . $filename, $file);

            Upload::create([
                'folder'   => $folder,
                'filename' => $filename
            ]);

            \Log::info('FilePond Upload: File uploaded successfully', [
                'folder' => $folder,
                'filename' => $filename,
                'path' => storage_path('app/temp/' . $folder . '/' . $filename)
            ]);

            return response($folder, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }


    public function filepondDelete(Request $request) {
        $upload = Upload::where('folder', $request->getContent())->first();

        Storage::deleteDirectory('temp/' . $upload->folder);
        $upload->delete();

        return response(null);
    }


    public function dropzoneUpload(Request $request) {
        $file = $request->file('file');

        $filename = now()->timestamp . '.' . trim($file->getClientOriginalExtension());

        Storage::putFileAs('temp/dropzone/', $file, $filename);

        return response()->json([
            'name'          => $filename,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    public function dropzoneDelete(Request $request) {
        Storage::delete('temp/dropzone/' . $request->file_name);

        return response()->json($request->file_name, 200);
    }
}
