<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'is_published',
        'published_at',
        'status',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at'   => JalaliDateTime::class,
        'updated_at'   => JalaliDateTime::class,
    ];

    /**
     * Author relationship.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the media associated with the page.
     */
    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable')->withPivot('display_order');
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusAttribute(): ?string
    {
        return \App\Enums\PageStatuses::pairs()[$this->attributes['status']] ?? null;
    }

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }
} 