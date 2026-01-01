<?php

namespace App\Casts;

use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class JalaliDateTime implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return new Verta($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        // If the value is already a Verta instance, convert it to datetime
        if ($value instanceof Verta) {
            return $value->datetime();
        }

        // If it's a string or other format, let Laravel handle it
        return $value;
    }
} 