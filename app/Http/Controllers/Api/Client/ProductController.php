<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public-facing controller for products.
 * This is for your Vue.js frontend.
 */
class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get a paginated and filtered list of products.
     *
     * Your Vue app can call this like:
     * /api/client/products?category_slug=mdf&material=oak&search=large
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // We pass all query parameters (category_slug, search, etc.)
        // to our service, which passes them to the repository.
        $products = $this->productService->getFilteredProducts($request->all());

        return ProductResource::collection($products);
    }

    /**
     * Get a single product by its slug.
     *
     * @param string $slug
     * @return ProductResource
     */
    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], Response::HTTP_NOT_FOUND);
        }

        return new ProductResource($product);
    }
}
