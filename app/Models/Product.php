<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // --- UPDATED ---
    // These columns were removed as they are now in the translation table
    protected $fillable = [
        'author_id',
        // 'title', // Removed
        // 'slug', // Removed (see migration)
        // 'description', // Removed
        // 'company_name', // Removed
        'dimensions',   // This one stays
        // 'material', // Removed
        // 'chrome_plating', // Removed
        'status',
    ];

    /**
     * Get the user (author) that created the product.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get all categories for this product.
     */
    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_product');
    }

    /**
     * Get all media (images) for this product.
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    // --- NEW RELATIONSHIPS ---

    /**
     * Get all translations for the product.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /**
     * Get the translation for the current locale.
     * This is the method your repository is trying to load.
     */
    public function translation(): HasOne
    {
        $lang = app()->getLocale() ?: 'en'; // Default to 'en'

        return $this->hasOne(ProductTranslation::class)
            ->where('lang', $lang)
            ->withDefault(function (ProductTranslation $translation, Product $product) use ($lang) {
                // If the current lang isn't found, try to fallback to 'en'
                if ($lang !== 'en') {
                    $enTranslation = $product->translations()->where('lang', 'en')->first();
                    if ($enTranslation) {
                        return $enTranslation;
                    }
                }
                // If no translation exists at all, return an empty object
                return new ProductTranslation();
            });
    }
}
