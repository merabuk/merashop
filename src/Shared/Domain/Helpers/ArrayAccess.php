<?php

declare(strict_types=1);

namespace App\Shared\Domain\Helpers;

class ArrayAccess
{
    /**
     * Retrieve a value from an array using a key (can use dot notation for nested keys).
     *
     * @param array<string, mixed> $data
     */
    public static function getByKey(array $data, string $key, mixed $default = null, bool $useDot = true): mixed
    {
        if ($useDot && str_contains($key, '.')) {
            return self::getByDotKey(data: $data, key: $key, default: $default);
        }

        return $data[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function getByDotKey(array $data, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $current = $data;

        foreach ($keys as $k) {
            if (!is_array($current) || !isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }
}
