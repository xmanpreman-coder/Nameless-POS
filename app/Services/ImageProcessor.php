<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * ImageProcessor Service
 * 
 * Handles image upload, validation, resizing, and compression.
 * Ensures all images are optimized for web with maximum quality while minimizing file size.
 * 
 * Usage:
 *   $processor = new ImageProcessor();
 *   $path = $processor->processImage(
 *       uploadedFile: $request->file('avatar'),
 *       folder: 'avatars',
 *       width: 200,
 *       height: 200,
 *       maxSize: 100 // KB
 *   );
 */
class ImageProcessor
{
    // Default configuration
    const DEFAULT_MAX_FILE_SIZE_KB = 2048; // 2MB
    const DEFAULT_OUTPUT_SIZE_KB = 150; // Target 150KB output
    const DEFAULT_QUALITY = 85; // Compression quality (1-100)
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * Process image: validate, resize, compress, and store
     * 
     * @param UploadedFile $file
     * @param string $folder Storage folder (e.g., 'avatars', 'products')
     * @param int|null $width Target width (null = no resize)
     * @param int|null $height Target height (null = no resize)
     * @param int $maxSizeKb Maximum file size in KB before processing
     * @param int $targetQuality JPEG quality (1-100)
     * @param string $disk Storage disk ('public' by default)
     * 
     * @return string Stored file path (relative to disk)
     * @throws \Exception
     */
    public function processImage(
        UploadedFile $file,
        string $folder = 'uploads',
        ?int $width = null,
        ?int $height = null,
        int $maxSizeKb = self::DEFAULT_MAX_FILE_SIZE_KB,
        int $targetQuality = self::DEFAULT_QUALITY,
        string $disk = 'public'
    ): string {
        try {
            // 1. VALIDATE
            $this->validateImage($file, $maxSizeKb);

            // 2. CREATE DIRECTORY
            if (!Storage::disk($disk)->exists($folder)) {
                Storage::disk($disk)->makeDirectory($folder);
            }

            // 3. GENERATE FILENAME
            $filename = $this->generateFilename($file);

            // 4. LOAD IMAGE
            $image = Image::make($file->getRealPath());

            // 5. RESIZE IF NEEDED
            if ($width && $height) {
                $image->fit($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // 6. COMPRESS & ENCODE
            $encodedImage = $image->encode($this->getOutputFormat($file), $targetQuality);

            // 7. STORE
            $path = "$folder/$filename";
            Storage::disk($disk)->put($path, $encodedImage->stream());

            // 8. VERIFY & LOG
            $storedSize = Storage::disk($disk)->size($path);
            Log::info('Image processed and stored', [
                'path' => $path,
                'original_size' => $file->getSize(),
                'stored_size' => $storedSize,
                'stored_size_kb' => round($storedSize / 1024, 2),
                'disk' => $disk,
                'dimensions' => $width ? "{$width}x{$height}" : 'original'
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate image file
     */
    private function validateImage(UploadedFile $file, int $maxSizeKb): void
    {
        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception(
                "Invalid file type: .{$extension}. Allowed: " . implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \Exception("Invalid MIME type: {$mimeType}");
        }

        // Check file size
        $fileSizeKb = $file->getSize() / 1024;
        if ($fileSizeKb > $maxSizeKb) {
            throw new \Exception(
                "File too large: " . round($fileSizeKb, 2) . "KB. Maximum: {$maxSizeKb}KB"
            );
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->timestamp;
        $random = random_int(1000, 9999);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get output format for encoding
     */
    private function getOutputFormat(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'jpeg'
        };
    }

    /**
     * Delete image file
     */
    public function deleteImage(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                Log::info('Image deleted', ['path' => $path]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Image deletion failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get image info
     */
    public function getImageInfo(string $path, string $disk = 'public'): ?array
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return null;
            }

            $fullPath = Storage::disk($disk)->path($path);
            $size = Storage::disk($disk)->size($path);
            
            try {
                $image = Image::make($fullPath);
                return [
                    'path' => $path,
                    'url' => Storage::disk($disk)->url($path),
                    'size_bytes' => $size,
                    'size_kb' => round($size / 1024, 2),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'mime_type' => Storage::disk($disk)->mimeType($path)
                ];
            } catch (\Exception $e) {
                // Return basic info if image loading fails
                return [
                    'path' => $path,
                    'url' => Storage::disk($disk)->url($path),
                    'size_bytes' => $size,
                    'size_kb' => round($size / 1024, 2),
                    'width' => null,
                    'height' => null,
                    'mime_type' => Storage::disk($disk)->mimeType($path)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get image info', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
