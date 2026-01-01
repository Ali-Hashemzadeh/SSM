<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'creator_id',
    ];

    protected $casts = [
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    /**
     * Get the posts that have this tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tags', 'tag_id', 'post_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
} 