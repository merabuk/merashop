<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\Services\StringEmptyException;
use App\Shared\Domain\Exception\Services\StringMaxLengthException;
use App\Shared\Domain\Exception\Services\StringMinLengthException;

final class StringValidator
{
    /**
     * @throws InvalidStringException
     */
    public static function validate(
        string $rawValue,
        int $maxLength,
        int $minLength = 0,
        bool $normalize = true,
    ): string {
        $value = mb_trim($rawValue);

        if (empty($value)) {
            throw StringEmptyException::becauseValueIsEmpty();
        }

        if ($normalize) {
            $value = self::normalize($value);
        }

        $length = mb_strlen($value);

        if ($length > $maxLength) {
            throw StringMaxLengthException::becauseValueIsToLong($maxLength);
        }

        if ($length < $minLength) {
            throw StringMinLengthException::becauseValueIsToShort($minLength);
        }

        return $value;
    }

    private static function normalize(string $value): string
    {
        return preg_replace([
            '/ +/',
            '/ *(\r?\n) */',
            '/(?:\r?\n){2,}/',
        ], [
            ' ',
            '$1',
            PHP_EOL.PHP_EOL,
        ], $value);
    }
}
