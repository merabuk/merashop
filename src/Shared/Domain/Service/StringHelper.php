<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

final class StringHelper
{
    public static function limit(string $string, int $limit = 100, ?string $ending = null): string
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }

        $substring = mb_substr($string, 0, $limit);

        return null !== $ending ? $substring.$ending : $substring;
    }
}
