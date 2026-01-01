<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use App\Enums\PostStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'status',
        'published_at',
        'author_id',
        'post_type_id',
        'seen',
        'meta',
        'lang',
    ];

    protected $casts = [
        'published_at' => JalaliDateTime::class,
        'meta' => 'array',
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Get the categories for the post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories', 'post_id', 'category_id');
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    }

    /**
     * Get the media for the post.
     */
    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    /**
     * Get the post type for the post.
     */
    public function postType(): BelongsTo
    {
        return $this->belongsTo(PostType::class, 'post_type_id');
    }

    public function getStatusAttribute(): ?string
    {
        return PostStatuses::pairs()[$this->attributes['status']];
    }

    /**
     * Increment the seen count for the post.
     */
    public function incrementSeen(): void
    {
        $this->increment('seen');
    }
} 