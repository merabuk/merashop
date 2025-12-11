<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\StringEmptyException;
use App\Shared\Domain\Exception\StringMaxLengthException;
use App\Shared\Domain\Exception\StringMinLengthException;

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
