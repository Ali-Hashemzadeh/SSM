<?php

namespace App\Traits;

trait Enumerable
{
  public static function keys(): array
  {
    return array_map(fn($v) => $v->name, static::cases());
  }

  public static function values(): array
  {
    return array_map(fn($v) => $v->value, static::cases());
  }

  public static function pairs(): array
  {
    return array_combine(static::keys(), static::values());
  }

  public static function getValue(?string $key): ?string
  {
    return static::pairs()[$key] ?? null;
  }

  public static function getName(?string $value): ?string
  {
    foreach (static::pairs() as $k => $v) {
      if ($v === $value) {
        return $k;
      }
    }

    return null;
  }
}
