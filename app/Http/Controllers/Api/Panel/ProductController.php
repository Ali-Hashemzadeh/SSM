<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreProductRequest;
use App\Http\Requests\Panel\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @group Panel | Products
 *
 * APIs for managing products.
 */
class ProductController extends Controller
{
    /**
     * ProductController constructor.
     * We use PHP 8 constructor property promotion.
     *
     * --- THIS IS THE FIX ---
     * The old __construct method that called $this->middleware() is removed.
     * Middleware is now correctly handled in the routes/api/panel.php file.
     */
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * Get all products (Panel).
     *
     * Retrieves a paginated list of products for the admin panel.
     *
     * @param Request $request
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        // In the panel, we just need a simple paginated list.
        $products = $this->productService->getAllProductsPaginated($request->all());
        return ProductResource::collection($products);
    }

    /**
     * Create new product.
     *
     * Creates a new product and syncs its categories and media.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse|JsonResource
     */
    public function store(StoreProductRequest $request): JsonResponse|JsonResource
    {
        try {
            $product = $this->productService->createProduct($request->validated());
            // Load relationships for the resource
            $product->load('author', 'productCategories', 'media');
            return new ProductResource($product);
        } catch (Exception $e) {
            Log::error('Failed to create product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Get single product (Panel).
     *
     * Retrieves a single product by its ID, including all relationships.
     *
     * @param Product $product
     * @return JsonResource
     */
    public function show(Product $product): JsonResource
    {
        // Load all relationships needed for the admin view
        $product->load('author', 'productCategories', 'media');
        return new ProductResource($product);
    }

    /**
     * Update product.
     *
     * Updates an existing product and re-syncs its categories and media.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResource|JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResource|JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($product, $request->validated());
            $product->load('author', 'productCategories', 'media');
            return new ProductResource($product);
        } catch (Exception $e) {
            Log::error('Failed to update product', [
                'id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Delete product.
     *
     * Deletes a product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->deleteProduct($product);
            return response()->json(['success' => true, 'message' => 'Product deleted.']);
        } catch (Exception $e) {
            Log::error('Failed to delete product', [
                'id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}

