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
    public static function validate(string $value, int $maxLength, int $minLength = 0): string
    {
        $trimmedValue = mb_trim($value);

        if (empty($trimmedValue)) {
            throw StringEmptyException::becauseValueIsEmpty();
        }

        $length = mb_strlen($trimmedValue);

        if ($length > $maxLength) {
            throw StringMaxLengthException::becauseValueIsToLong($maxLength);
        }

        if ($length < $minLength) {
            throw StringMinLengthException::becauseValueIsToShort($minLength);
        }

        return $trimmedValue;
    }
}
