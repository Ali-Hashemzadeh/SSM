<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductCategory\ProductCategoryRepositoryInterface; // Make sure this is imported
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProductService
{
    /**
     * ProductService constructor.
     *
     * This constructor uses PHP 8's "constructor property promotion".
     * It automatically declares and initializes the properties.
     * This is what was missing, causing the "null" error.
     */
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected ProductCategoryRepositoryInterface $productCategoryRepository
    ) {
    }

    /**
     * Get filtered and paginated products for the client-side.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredProducts(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        // Clean filters
        $allowedFilters = [
            'category_slug' => $filters['category'] ?? null, // Renamed to match repo
            'company_name'  => $filters['company_name'] ?? null,
            'material'      => $filters['material'] ?? null,
            'search'        => $filters['search'] ?? null,
            'dimensions'    => $filters['dimensions'] ?? null,
            'chrome_plating' => $filters['chrome_plating'] ?? null,
        ];

        $allowedFilters['status'] = 'published';

        // This line will now work because $this->productRepository is set
        return $this->productRepository->getFiltered($allowedFilters, $perPage);
    }

    /**
     * Get all products for the admin panel, paginated.
     *
     * @param array $filters Filters (like 'per_page')
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllProductsPaginated(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        // We pass an empty filter array because the panel index doesn't need filtering by default
        return $this->productRepository->getFiltered([], $perPage);
    }

    /**
     * Create a new product.
     *
     * @param array $data (Validated data from StoreRequest)
     * @return Product
     * @throws Exception
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // 1. Separate relationship data and translations
            $categoryIds  = $data['categories'] ?? [];
            $mediaIds     = $data['media_ids'] ?? []; // Using 'media_ids' from your request
            $translations = $data['translations'];

            // 2. Create the product with ONLY non-translated data
            $productData = [
                'status'     => $data['status'],
                'dimensions' => $data['dimensions'] ?? null,
                'author_id'  => Auth::id(), // Set author
            ];
            $product = $this->productRepository->create($productData);

            // 3. Create the translations
            foreach ($translations as $lang => $transData) {
                $product->translations()->create(array_merge($transData, ['lang' => $lang]));
            }

            // 4. Sync relationships
            $product->productCategories()->sync($categoryIds);
            $product->media()->sync($mediaIds); // Your model uses media() relation

            return $product->load('translations');
        });
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data (Validated data from UpdateRequest)
     * @return Product
     * @throws Exception
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // 1. Separate relationship data and translations
            $categoryIds  = $data['categories'] ?? null;
            $mediaIds     = $data['media_ids'] ?? null; // Using 'media_ids' from your request
            $translations = $data['translations'];

            // 2. Update the product with ONLY non-translated data
            $productData = [
                'status'     => $data['status'],
                'dimensions' => $data['dimensions'] ?? null,
            ];
            $product = $this->productRepository->update($product, $productData);

            // 3. Update or Create the translations
            foreach ($translations as $lang => $transData) {
                $product->translations()->updateOrCreate(
                    ['lang' => $lang], // Find by lang
                    $transData         // Update with this data
                );
            }

            // 4. Sync relationships (if provided)
            if ($categoryIds !== null) {
                $product->productCategories()->sync($categoryIds);
            }
            if ($mediaIds !== null) {
                $product->media()->sync($mediaIds);
            }

            return $product->load('translations');
        });
    }

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return void
     * @throws Exception
     */
    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            // Detach relationships
            $product->productCategories()->detach();
            $product->media()->detach();

            // Delete the product
            $this->productRepository->delete($product); // Use the object, not ID
        });
    }
}
