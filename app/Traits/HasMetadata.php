<?php

namespace App\Traits;

use App\Helpers\Meta\BaseCastableMeta;
use Illuminate\Database\Eloquent\{Builder, Collection};

/**
 * @property object $meta
 */
trait HasMetadata
{
  public static function byMeta(array $conditions, ?Builder $builder = null): Collection
  {
    return self::queryMeta($conditions, $builder)->get();
  }

  /**
   * Applies $attrs as constraints to meta field and returns query Builder.
   *
   * $attrs is a two dimensions associative array with string indexes that represent propery path of JSON data (initial meta-> is not required).
   * Example: [ ['active' => true, 'duration' => 90], ['score' => 10]] => "(active = true AND duration = 90) OR (score = 10)
   *
   * prefix # to use whereJsonLength instead of where: ['# courses' => 2]
   * prefix @ to use whereJsonContains instead of where: ['@ courses' => ['id' => 3]]
   * prefix !@ to use whereJsonDoesntContain instead of where: ['!@ courses' => ['id' => 3]]
   * prefix @[] to use whereJsonContainsKey instead of where: ['@[] courses' => ['id' => 3]]
   * prefix !@[] to use whereJsonDoesntContainKey instead of where: ['!@[] courses' => ['id' => 3]]
   * prefix [] to use whereIn instead of where: []column: ['[] score' => [10, 20, 30]]
   * prefix ![] to use whereNotIn instead of where: ['![] score' => [1, 2, 3]]
   * prefix ! to use whereNot instead of where: ['! score' => 20]
   * prefix > or >= or < or <= to replace where operator: ['> score' => 20]
   */
  public static function queryMeta(array $conditions, ?Builder $builder = null): Builder
  {
    $builder = $builder ?: static::query();
    foreach ($conditions as $idx => $props) {
      if (! is_array($props)) {
        continue;
      }

      if ($idx == 0) {
        $builder->where(function($query) use ($props) {
          foreach ($props as $key => $value) {
            self::applyConstraint($query, $key, $value);
          }
        });

        continue;
      }

      $builder->orWhere(function($query) use ($props) {
        foreach ($props as $key => $value) {
          self::applyConstraint($query, $key, $value);
        }
      });
    }

    return $builder;
  }

  private static function applyConstraint(Builder $builder, string $key, mixed $value): Builder
  {
    $segments = explode(' ', trim($key));
    if (count($segments) == 1) {
      return $builder->where(self::normalizeKey($key), $value);
    }
    if (count($segments) != 2) {
      return $builder;
    }

    $key = self::normalizeKey($segments[1]);

    return match ($segments[0]) {
      '[]'   => $builder->whereIn($key, $value),
      '![]'  => $builder->whereNotIn($key, $value),
      '#'    => $builder->whereJsonLength($key, $value),
      '@'    => $builder->whereJsonContains($key, $value),
      '!@'   => $builder->whereJsonDoesntContain($key, $value),
      '@[]'  => $builder->whereJsonContainsKey($key, $value ?? 'and'),
      '!@[]' => $builder->whereJsonDoesntContainKey($key, $value ?? 'and'),
      '>', '>=', '<', '<=', '!=' => $builder->where($key, $segments[0], $value),
      '#>', '#>=', '#<', '#<=', '#!=' => $builder->whereJsonLength($key, substr($segments[0], 1), $value),
      default => $builder
    };
  }

  private static function normalizeKey($key)
  {
    return startsWith($key, 'meta->') ? $key : "meta->{$key}";
  }

  public function initializeHasMetadata()
  {
    if (! in_array('meta', $this->fillable)) {
      $this->fillable[] = 'meta';
    }
    if (! isset($this->casts['meta'])) {
      $this->casts['meta'] = BaseCastableMeta::class;
    }
  }
}
