<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    /**
     * Upload a file to the given disk and directory, disallowing SVG files.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return string The stored file path
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $directory = 'uploads', string $disk = 'direct_public'): string
    {
        // Disallow SVG files
        if ($file->getClientOriginalExtension() === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            throw new \Exception('SVG files are not allowed.');
        }

        // Store the file using the specified disk
        $path = Storage::disk($disk)->putFile($directory, $file);

        // Log the successful upload for debugging purposes
        Log::info("File uploaded successfully to: " . $path . " on disk: " . $disk);

        return $path;
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = 'direct_public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }
}
