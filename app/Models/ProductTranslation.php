<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    // Ensure this is false so 'lang' can be part of the primary key if needed,
    // or just to match the PageTranslation pattern.
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'lang',
        'title',
        'slug',
        'description',
        'company_name',
        'material',
        'chrome_plating',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
