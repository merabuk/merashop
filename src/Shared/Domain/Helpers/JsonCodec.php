<?php

declare(strict_types=1);

namespace App\Shared\Domain\Helpers;

use JsonException;

class JsonCodec
{
    /**
     * @param array<array-key, mixed> $array
     */
    public static function jsonEncode(array $array, string $default = ''): string
    {
        try {
            return json_encode(value: $array, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $default;
        }
    }
}
