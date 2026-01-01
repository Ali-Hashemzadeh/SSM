<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Repositories\ProductCategory\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class ProductCategoryService
 *
 * This service handles the business logic for product categories.
 * It uses the repository interface to abstract database operations.
 */
class ProductCategoryService
{
    /**
     * @var ProductCategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * ProductCategoryService constructor.
     *
     * @param ProductCategoryRepositoryInterface $categoryRepository
     */
    public function __construct(ProductCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all product categories.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->categoryRepository->all();
    }

    /**
     * Get a single category by ID.
     *
     * @param int $id
     * @return ProductCategory|null
     */
    public function find(int $id): ?ProductCategory
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * Create a new product category.
     *
     * @param array $data (Validated data from the controller)
     * @return ProductCategory
     */
    public function createCategory(array $data): ProductCategory
    {
        // Business logic (like logging) could go here
        // We'll log the default (e.g., 'fa') language name
        Log::info('Creating new product category', ['name' => $data['translations']['fa']['name'] ?? 'N/A']);
        return $this->categoryRepository->create($data);
    }

    /**
     * Update an existing product category.
     *
     * @param ProductCategory $category
     * @param array $data (Validated data)
     * @return ProductCategory
     */
    public function updateCategory(ProductCategory $category, array $data): ProductCategory
    {
        Log::info('Updating product category', ['id' => $category->id, 'name' => $data['translations']['fa']['name'] ?? 'N/A']);
        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a product category.
     *
     * @param ProductCategory $category
     * @return bool
     */
    public function deleteCategory(ProductCategory $category): bool
    {
        // We could add complex business logic here, e.g.,
        // "check if category has products before deleting".
        // For now, we'll just delete.
        Log::info('Deleting product category', ['id' => $category->id]);
        return $this->categoryRepository->delete($category);
    }
}
