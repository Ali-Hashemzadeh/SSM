<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use App\Enums\CommentStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'status',
        'post_id',
        'author_id',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    /**
     * Get the post that this comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the author of the comment.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent comment (for threaded replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function getStatusAttribute(): ?string
    {
        return CommentStatuses::pairs()[$this->attributes['status']];
    }
} 