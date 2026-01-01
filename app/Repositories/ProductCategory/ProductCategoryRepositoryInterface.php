<?php

namespace App\Repositories\ProductCategory;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ProductCategoryRepositoryInterface
 *
 * This interface defines the contract for our product category repository.
 */
interface ProductCategoryRepositoryInterface
{
    /**
     * Get all product categories.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find a single category by its ID.
     *
     * @param int $id
     * @return ProductCategory|null
     */
    public function find(int $id): ?ProductCategory;

    /**
     * Create a new product category.
     *
     * @param array $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory;

    /**
     * Update an existing product category.
     *
     * @param ProductCategory $category
     * @param array $data
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory;

    /**
     * Delete a product category.
     *
     * @param ProductCategory $category
     * @return bool
     */
    public function delete(ProductCategory $category): bool;
}
