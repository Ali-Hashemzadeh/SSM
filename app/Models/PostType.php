<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'title', 'fields'];

    protected $casts = [
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
        'fields' => 'array',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'post_type_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'post_type_id');
    }
}
