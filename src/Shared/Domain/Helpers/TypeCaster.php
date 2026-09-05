<?php

declare(strict_types=1);

namespace App\Shared\Domain\Helpers;

use App\Shared\Domain\Exception\InvalidArgumentException;

class TypeCaster
{
    public static function castToInt(mixed $value, int $default = 0): int
    {
        if (null === $value) {
            return $default;
        }

        return match (true) {
            is_integer($value),
            is_float($value),
            is_numeric($value),
            is_string($value) => (int) $value,
            default => throw self::makeException(sprintf('Cast to int is not supported for %s', get_debug_type($value))),
        };
    }

    public static function castToFloat(mixed $value, float $default = 0.0): float
    {
        if (null === $value || 0 === $value || '' === $value) {
            return $default;
        }

        return match (true) {
            is_float($value),
            is_integer($value),
            is_numeric($value),
            is_string($value) => (float) $value,
            default => throw self::makeException(sprintf('Cast to float is not supported for %s', get_debug_type($value))),
        };
    }

    public static function castToString(mixed $value, string $default = ''): string
    {
        if (null === $value || '' === $value) {
            return $default;
        }

        return match (true) {
            is_string($value) => $value,
            is_integer($value),
            is_float($value) => (string) $value,
            is_array($value) => JsonCodec::jsonEncode(array: $value, default: $default),
            is_object($value) => serialize($value),
            default => throw self::makeException(sprintf('Cast to string is not supported for %s', get_debug_type($value))),
        };
    }

    public static function castToNullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return self::castToString(value: $value);
    }

    /**
     * @return non-empty-string
     */
    public static function castToNonEmptyString(
        string $string,
        string $message = 'Given string must be non empty',
    ): string {
        if ('' === $string) {
            throw self::makeException($message);
        }

        return $string;
    }

    /**
     * @param string[] $default
     *
     * @return string[]
     */
    public static function castToArrayOfStrings(mixed $value, array $default = []): array
    {
        if (null === $value || [] === $value || '' === $value) {
            return $default;
        }

        return match (true) {
            is_array($value) => array_map(fn (mixed $item) => self::castToString(value: $item), $value),
            default => throw self::makeException(sprintf('Cast to array of strings is not supported for %s', get_debug_type($value))),
        };
    }

    /**
     * @param array<string, string> $default
     *
     * @return array<string, string>
     */
    public static function castToStringMap(mixed $value, array $default = []): array
    {
        if (null === $value || [] === $value || '' === $value) {
            return $default;
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[(string) $key] = self::castToString(value: $item);
            }

            return $result;
        }

        throw self::makeException(sprintf('Cast to string map is not supported for %s', get_debug_type($value)));
    }

    private static function makeException(string $message): InvalidArgumentException
    {
        return new InvalidArgumentException(message: $message);
    }
}
