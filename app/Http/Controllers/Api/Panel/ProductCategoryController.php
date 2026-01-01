<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreProductCategoryRequest;
use App\Http\Requests\Panel\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @group Panel | Product Categories
 *
 * APIs for managing product categories.
 */
class ProductCategoryController extends Controller
{
    /**
     * ProductCategoryController constructor.
     * We use PHP 8 constructor property promotion for a cleaner controller.
     * The ProductService is injected by Laravel's service container.
     *
     * --- THIS IS THE FIX ---
     * The old __construct method that called $this->middleware() is removed.
     * Middleware is now correctly handled in the routes/api/panel.php file.
     */
    public function __construct(protected ProductCategoryService $productCategoryService)
    {
    }

    /**
     * Get all categories.
     *
     * Retrieves a list of all product categories.
     *
     * @return JsonResource
     */
    public function index(): JsonResource
    {
        // We don't paginate categories as there are usually not many.
        $categories = $this->productCategoryService->getAllCategories();
        return ProductCategoryResource::collection($categories);
    }

    /**
     * Create new category.
     *
     * Creates a new product category from the given data.
     *
     * @param StoreProductCategoryRequest $request
     * @return JsonResponse|JsonResource
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse|JsonResource
    {
        try {
            $category = $this->productCategoryService->createCategory($request->validated());
            return new ProductCategoryResource($category);
        } catch (Exception $e) {
            Log::error('Failed to create product category', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Get single category.
     *
     * Retrieves a single product category by its ID.
     *
     * @param ProductCategory $productCategory
     * @return JsonResource
     */
    public function show(ProductCategory $productCategory): JsonResource
    {
        return new ProductCategoryResource($productCategory);
    }

    /**
     * Update category.
     *
     * Updates an existing product category.
     *
     * @param UpdateProductCategoryRequest $request
     * @param ProductCategory $productCategory
     * @return JsonResource|JsonResponse
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): JsonResource|JsonResponse
    {
        try {
            $category = $this->productCategoryService->updateCategory($productCategory, $request->validated());
            return new ProductCategoryResource($category);
        } catch (Exception $e) {
            Log::error('Failed to update product category', [
                'id' => $productCategory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Delete category.
     *
     * Deletes a product category.
     *
     * @param ProductCategory $productCategory
     * @return JsonResponse
     */
    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        try {
            $this->productCategoryService->deleteCategory($productCategory);
            return response()->json(['success' => true, 'message' => 'Category deleted.']);
        } catch (Exception $e) {
            Log::error('Failed to delete product category', [
                'id' => $productCategory->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}

