<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ProductRepositoryInterface
 *
 * This interface defines the contract for our product repository.
 * It abstracts the database logic from the service layer.
 */
interface ProductRepositoryInterface
{
    /**
     * Get a paginated list of products for the admin panel.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedForPanel(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a paginated and filtered list of products for the public (client) API.
     *
     * @param array $filters (e.g., ['category_slug' => 'mdf', 'search' => 'oak'])
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a single product by its ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function find(int $id): ?Product;

    /**
     * Find a single published product by its slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool;
}
