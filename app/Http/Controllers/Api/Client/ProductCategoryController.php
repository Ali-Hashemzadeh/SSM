<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\ProductCategoryService;
use App\Http\Resources\ProductCategoryResource;

/**
 * Public-facing controller to get the list of categories for filtering.
 */
class ProductCategoryController extends Controller
{
    protected $categoryService;

    public function __construct(ProductCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all product categories.
     * This is for your Vue app to build its filter menu.
     */
    public function index()
    {
        $categories = $this->categoryService->getAll();
        return ProductCategoryResource::collection($categories);
    }
}
