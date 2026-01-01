<?php

namespace App\Repositories\ProductCategory;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the ProductCategoryRepositoryInterface.
 */
class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    protected $model;

    public function __construct(ProductCategory $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['translations', 'media'])->get();
    }

    public function find(int $id): ?ProductCategory
    {
        return $this->model->find($id);
    }

    /**
     * Create a new product category.
     *
     * @param array $data (This will now be the 'translations' array)
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory
    {
        // 1. The main model has no fillable data, so just create it.
        $category = $this->model->create();

        // 2. Create the translations
        foreach ($data['translations'] as $lang => $transData) {
            $category->translations()->create(array_merge($transData, ['lang' => $lang]));
        }

        return $category;
    }

    /**
     * Update an existing product category.
     *
     * @param ProductCategory $category
     * @param array $data (This will be the 'translations' array)
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory
    {
        // 1. Update or Create the translations
        foreach ($data['translations'] as $lang => $transData) {
            $category->translations()->updateOrCreate(
                ['lang' => $lang], // Find by language
                $transData         // Update with this data
            );
        }

        // 2. Main model doesn't have data to update, so just return it.
        return $category;
    }

    public function delete(ProductCategory $category): bool
    {
        return $category->delete();
    }
}
