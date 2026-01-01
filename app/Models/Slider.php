<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta',
        'lang',
        'order',
        'status',
    ];

    protected $casts = [
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }
} 