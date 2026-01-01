<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany; // <-- 1. Import this
use App\Models\Media; // <-- 2. Import this

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [];

    /**
     * Get the translations for the product category.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductCategoryTranslation::class);
    }

    /**
     * The products that belong to the category.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category_product');
    }

    /**
     * 3. Add the media relationship (same as Product/Page)
     * Get all media associated with this category.
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    /**
     * 4. Add a helper accessor to get the *first* media item.
     * This makes it act like a single-image relationship.
     *
     * @return Media|null
     */
    public function getImageAttribute(): ?Media
    {
        return $this->media->first();
    }
}
