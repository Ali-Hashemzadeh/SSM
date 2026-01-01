<?php

namespace App\Services;

use App\Repositories\Medias\MediaRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// Correct Facade for Intervention Image v3 Laravel package
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    protected MediaRepositoryInterface $mediaRepository;
    protected FileUploadService $fileUploadService;

    public function __construct(MediaRepositoryInterface $mediaRepository, FileUploadService $fileUploadService)
    {
        $this->mediaRepository = $mediaRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function createMedia(UploadedFile $file, array $data)
    {
        return DB::transaction(function () use ($file, $data) {
            $createdMedia = [];
            $tempFilesToDelete = []; // Keep track of temporary resized files

            $sizeInfo = $data['size'] ?? [];
            $isImage = Str::startsWith($file->getMimeType(), 'image/');

            // --- Handle Original Creation if requested ---
            if ($isImage && isset($sizeInfo['original']['width']) && isset($sizeInfo['original']['height'])) {
                $resizedOriginal = $this->resizeImage($file, $sizeInfo['original'], 'original_');
                if ($resizedOriginal) {
                    $originalPath = $this->fileUploadService->upload($resizedOriginal['file'], 'media');
                    $tempFilesToDelete[] = $resizedOriginal['temp_path'];

                    $originalMediaData = [
                        'file_path' => $originalPath,
                        'file_type' => $file->getMimeType(),
                        'alt_text' => $data['alt_text'] ?? null,
                        'caption' => $data['caption'] ?? null,
                        'uploader_id' => Auth::id(),
                        'type' => 'original',
                    ];
                    $createdMedia['original'] = $this->mediaRepository->create($originalMediaData);
                }
            }

            // --- Handle Thumbnail Creation if requested ---
            if ($isImage && isset($sizeInfo['thumbnail']['width']) && isset($sizeInfo['thumbnail']['height'])) {
                $resizedThumbnail = $this->resizeImage($file, $sizeInfo['thumbnail'], 'thumb_');
                if ($resizedThumbnail) {
                    $thumbnailPath = $this->fileUploadService->upload($resizedThumbnail['file'], 'media/thumbnails');
                    $tempFilesToDelete[] = $resizedThumbnail['temp_path'];

                    if ($thumbnailPath) {
                        $thumbnailMediaData = [
                            'file_path' => $thumbnailPath,
                            'file_type' => $file->getMimeType(),
                            'alt_text' => $data['alt_text'] ?? null,
                            'caption' => $data['caption'] ?? '(thumbnail)',
                            'uploader_id' => Auth::id(),
                            'type' => 'thumbnail',
                        ];
                        $createdMedia['thumbnail'] = $this->mediaRepository->create($thumbnailMediaData);
                    }
                }
            }

            // --- Handle default case: No specific sizes requested, or not an image ---
            // If no media has been created yet, save the original file as is.
            if (empty($createdMedia)) {
                $originalPath = $this->fileUploadService->upload($file, 'media');
                $originalMediaData = [
                    'file_path' => $originalPath,
                    'file_type' => $file->getMimeType(),
                    'alt_text' => $data['alt_text'] ?? null,
                    'caption' => $data['caption'] ?? null,
                    'uploader_id' => Auth::id(),
                    'type' => 'original',
                ];
                $createdMedia['original'] = $this->mediaRepository->create($originalMediaData);
            }

            // --- Cleanup all temporary files ---
            foreach ($tempFilesToDelete as $path) {
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            
            // --- Determine what to return ---
            // If only one media item was created, return it directly.
            if (count($createdMedia) === 1) {
                return reset($createdMedia); // reset() gets the first element of an array
            }
            
            // Otherwise, return the associative array of created media.
            return $createdMedia;
        });
    }

    /**
     * Resizes an image and prepares it for upload.
     *
     * @param UploadedFile $sourceFile The original file from the request.
     * @param array $dimensions The target dimensions ['width' => x, 'height' => y].
     * @param string $prefix A prefix for the new filename (e.g., 'thumb_').
     * @return array|null An array containing the new UploadedFile instance and its temporary path.
     */
    private function resizeImage(UploadedFile $sourceFile, array $dimensions, string $prefix = ''): ?array
    {
        try {
            $width = $dimensions['width'];
            $height = $dimensions['height'];

            $extension = $sourceFile->getClientOriginalExtension();
            $filename = pathinfo($sourceFile->hashName(), PATHINFO_FILENAME);
            $newFilename = "{$prefix}{$filename}.{$extension}";

            $image = Image::read($sourceFile->getRealPath());

            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $newFilename;
            $image->save($tempPath);

            $tempFile = new UploadedFile(
                $tempPath,
                $newFilename,
                $sourceFile->getMimeType(),
                null,
                true
            );

            return [
                'file' => $tempFile,
                'temp_path' => $tempPath,
            ];
        } catch (\Exception $e) {
            Log::error('Image resizing failed: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteMedia($id)
    {
        return DB::transaction(function () use ($id) {
            $media = $this->mediaRepository->find($id);
            if (!$media) {
                return false;
            }
            $this->fileUploadService->delete($media->file_path);
            
            return $this->mediaRepository->delete($id);
        });
    }
}