<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EloquentProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getPaginatedForPanel(int $perPage = 15): LengthAwarePaginator
    {
        // Load with the default translation for the panel
        return $this->model->with('translation')->latest()->paginate($perPage);
    }

    public function getFiltered(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $lang = app()->getLocale() ?: 'en'; // Use current locale or fallback to 'en'

        // Start with a base query
        $query = $this->model->newQuery()->where('status', 'published');

        // --- NEW FILTERING LOGIC ---
        // We will filter *inside* the 'translations' relationship
        // This avoids all manual joins and 'select' issues.
        $query->whereHas('translations', function ($q) use ($filters, $lang) {
            // --- THE FIX ---
            // The column is named 'lang', not 'locale'.
            $q->where('lang', $lang); // Filter by the correct language

            // Apply translated fields filters *inside* this closure
            if (!empty($filters['company_name'])) {
                $q->where('company_name', $filters['company_name']);
            }
            if (!empty($filters['material'])) {
                $q->where('material', $filters['material']);
            }
            if (!empty($filters['chrome_plating'])) {
                $q->where('chrome_plating', $filters['chrome_plating']);
            }

            // Apply search term filter for translated fields
            if (!empty($filters['search'])) {
                $term = $filters['search'];
                $q->where(function ($subQ) use ($term) {
                    $subQ->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('company_name', 'like', "%{$term}%");
                });
            }
        });

        // --- CATEGORY SLUG FILTER ---
        // This filters products that have a category where the category's translation has the matching slug.
        // We use the correct relationship name: 'productCategories'
        if (!empty($filters['category_slug'])) {
            $query->whereHas('productCategories.translations', function ($q) use ($filters) {
                $q->where('product_category_translations.slug', $filters['category_slug']);
            });
        }

        // --- NON-TRANSLATED FILTERS ---
        // Apply dimensions filter (This is on the main 'products' table)
        if (!empty($filters['dimensions'])) {
            $query->where('dimensions', '=', $filters['dimensions']);
        }

        // Apply search term for non-translated fields (like dimensions)
        // We use orWhere here to add to the search results
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->orWhere(function($q) use ($term) {
                $q->where('dimensions', 'like', "%{$term}%");
            });
        }

        // --- EAGER LOADING ---
        // Now we just load the translations using the accessors we defined in the Models.
        // No more joins or 'select('products.*')' needed.
        return $query->with([
            'media',
            'translations', // Loads the *current* translation for the product
            'productCategories.translations' // Loads the *current* translation for the categories
        ])
            ->latest() // No need to specify table name
            ->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        // Load with all translations
        return $this->model->with('translations')->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        $lang = app()->getLocale() ?: 'en';

        // --- REFACTORED findBySlug ---
        // Use whereHas to find the product safely, without manual joins
        return $this->model
            ->where('status', 'published')
            ->whereHas('translations', function ($q) use ($slug, $lang) {
                // --- THE FIX ---
                // The column is named 'lang', not 'locale'.
                $q->where('slug', $slug)
                    ->where('lang', $lang);
            })
            ->with([
                'media',
                'translations', // Load all translations
                'productCategories.translations' // Load all category translations
            ])
            ->first();
    }

    /**
     * Create a new product.
     *
     * @param array $data (Now contains non-translated data ONLY)
     * @return Product
     */
    public function create(array $data): Product
    {
        // --- SYNTAX FIX ---
        // Fixed a stray '}' bracket
        if (Auth::check()) {
            $data['author_id'] = Auth::id();
        } else {
            // Handle case where user might not be auth'd (e.g., seeder)
            // You might want to pass in author_id or get first admin
            $data['author_id'] = $data['author_id'] ?? 1; // Default to 1 if not passed
        }

        // Slug logic is REMOVED from here.
        // It's now handled by the Form Request and Service.

        return $this->model->create($data);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data (Non-translated data)
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        // Slug logic is REMOVED from here.
        $product->update($data);
        return $product;
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
