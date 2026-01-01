<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Casts\JalaliDateTime;
use Hekmatinasser\Verta\Verta;

// --- ADD THESE ---
use Illuminate\Database\Eloquent\Relations\HasOne;
// --- END ADD ---

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mobile',
        'password',
        'first_name',
        'last_name',
        'profile_picture',
        'role_id',
        'creator_id',
        'email',
        'company_name',
        'country',
        'province',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => JalaliDateTime::class,
            'updated_at' => JalaliDateTime::class,
        ];
    }

    protected $appends = [
        'created_at_day',
        'created_at_month',
        'created_at_year',
        'updated_at_day',
        'updated_at_month',
        'updated_at_year',
    ];

    /**
     * Convert the model instance to an array.
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Convert timestamps to Jalali format
        if (isset($array['created_at'])) {
            $array['created_at'] = (new Verta($array['created_at']))->format('Y/m/d');
        }
        if (isset($array['updated_at'])) {
            $array['updated_at'] = (new Verta($array['updated_at']))->format('Y/m/d');
        }

        return $array;
    }

    // --- Virtual Jalali columns for created_at ---
    public function getCreatedAtDayAttribute()
    {
        if (!$this->created_at) return null;
        $v = new Verta($this->created_at);
        return $v->formatWord('l'); // Persian day name
    }
    public function getCreatedAtMonthAttribute()
    {
        if (!$this->created_at) return null;
        $v = new Verta($this->created_at);
        return $v->formatWord('F'); // Persian month name
    }
    public function getCreatedAtYearAttribute()
    {
        if (!$this->created_at) return null;
        $v = new Verta($this->created_at);
        return $v->format('Y'); // Jalali year
    }
    // --- Virtual Jalali columns for updated_at ---
    public function getUpdatedAtDayAttribute()
    {
        if (!$this->updated_at) return null;
        $v = new Verta($this->updated_at);
        return $v->formatWord('l');
    }
    public function getUpdatedAtMonthAttribute()
    {
        if (!$this->updated_at) return null;
        $v = new Verta($this->updated_at);
        return $v->formatWord('F');
    }
    public function getUpdatedAtYearAttribute()
    {
        if (!$this->updated_at) return null;
        $v = new Verta($this->updated_at);
        return $v->format('Y');
    }

    /**
     * Get the role that belongs to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the posts written by this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get the comments written by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    /**
     * Get the media uploaded by this user.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'uploader_id');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user who created this user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'creator_id');
    }

    // --- ADD THIS BLOCK ---

    /**
     * Get the products created by this user.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'author_id');
    }

    /**
     * Get the user's active shopping cart.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get all orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    // --- END ADD ---
}

