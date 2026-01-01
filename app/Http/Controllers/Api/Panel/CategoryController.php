<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Categories\StoreRequest;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    protected CategoryRepositoryInterface $categoryRepository;
    protected CategoryService $categoryService;

    public function __construct(CategoryRepositoryInterface $categoryRepository, CategoryService $categoryService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $categories = $this->categoryRepository->all($perPage, $request->only(['s', 'status', 'date_from', 'date_to', 'post_type_id']));
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $category = $this->categoryService->createCategory($data);
            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'دسته‌بندی با موفقیت ایجاد شد.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Category store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد دسته‌بندی'
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id);
            return response()->json([
                'success' => true,
                'message' => 'دسته‌بندی با موفقیت حذف شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'دسته‌بندی یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Category delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف دسته‌بندی'
            ], 500);
        }
    }
}
