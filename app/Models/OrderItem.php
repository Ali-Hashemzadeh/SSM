<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'title',
        'company_name',
        'dimensions',
        'material',
        'chrome_plating',
    ];

    /**
     * Get the order that this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with this order item.
     * We use withDefault() so the app doesn't crash if the product was deleted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'title' => '[Deleted Product]',
        ]);
    }
}
