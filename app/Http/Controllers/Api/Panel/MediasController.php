<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreRequest;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Repositories\Medias\MediaRepositoryInterface;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MediasController extends Controller
{
    protected MediaRepositoryInterface $mediaRepository;
    protected MediaService $mediaService;
    protected FileUploadService $fileUploadService;

    public function __construct(MediaRepositoryInterface $mediaRepository, MediaService $mediaService, FileUploadService $fileUploadService)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediaService = $mediaService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of user's media files.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $media = $this->mediaRepository->all($perPage, $request->only(['s', 'status', 'date_from', 'date_to', 'uploader_id']));
        return response()->json([
            'success' => true,
            'data' => $media
        ]);
    }

    /**
     * Display a listing of all media files.
     */
    public function all(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $media = $this->mediaRepository->all($perPage, $request->only(['s', 'date_from', 'date_to', 'uploader_id']));
        return response()->json([
            'success' => true,
            'data' => $media
        ]);
    }

    /**
     * Store a newly uploaded media file.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            // The service now returns an array if a thumbnail is created
            $media = $this->mediaService->createMedia($file, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $media,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Media store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود فایل'
            ], 500);
        }
    }

    /**
     * Display a user's own media file.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $media = $this->mediaRepository->find($id);
            if ($media->uploader_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما دسترسی به این رسانه را ندارید'
                ], 403);
            }
            return response()->json([
                'success' => true,
                'data' => $media
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'رسانه مورد نظر یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Media show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'media_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت رسانه'
            ], 500);
        }
    }

    /**
     * Display any media file (admin access).
     */
    public function showAny(string $id): JsonResponse
    {
        try {
            $media = $this->mediaRepository->find($id);
            return response()->json([
                'success' => true,
                'data' => $media
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'رسانه مورد نظر یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Media showAny failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'media_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت رسانه'
            ], 500);
        }
    }

    /**
     * Remove the specified media (user's own).
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $media = $this->mediaRepository->find($id);
            if ($media->uploader_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما اجازه حذف این رسانه را ندارید'
                ], 403);
            }
            $this->mediaService->deleteMedia($id);
            return response()->json([
                'success' => true,
                'message' => 'رسانه با موفقیت حذف شد'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'رسانه مورد نظر یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Media delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'media_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف رسانه'
            ], 500);
        }
    }
}