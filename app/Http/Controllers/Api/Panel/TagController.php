<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Repositories\Tags\TagRepositoryInterface;
use App\Services\TagService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TagController extends Controller
{
    protected TagRepositoryInterface $tagRepository;
    protected TagService $tagService;

    public function __construct(TagRepositoryInterface $tagRepository, TagService $tagService)
    {
        $this->tagRepository = $tagRepository;
        $this->tagService = $tagService;
    }

    /**
     * Display a listing of the tags.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $tags = $this->tagRepository->all($perPage, $request->only(['s', 'status', 'date_from', 'date_to']));
        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }

    /**
     * Store a newly created tag in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tags,slug',
        ]);

        try {
            $tag = $this->tagService->createTag($validated);
            return response()->json([
                'success' => true,
                'data' => $tag,
                'message' => 'برچسب با موفقیت ایجاد شد.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Tag store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد برچسب'
            ], 500);
        }
    }

    /**
     * Remove the specified tag from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->tagService->deleteTag($id);
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'برچسب یافت نشد.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'برچسب با موفقیت حذف شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'برچسب یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Tag delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tag_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف برچسب'
            ], 500);
        }
    }
}
