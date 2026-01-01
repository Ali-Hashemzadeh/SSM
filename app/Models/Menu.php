<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'parent_id',
        'order',
        'lang'
    ];

    protected $casts = [
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    /**
     * Parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Children menus (submenus).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }
} 