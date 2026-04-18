<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Utility;

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

    public static function after(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return mb_substr($subject, $pos + mb_strlen($search));
    }
}
