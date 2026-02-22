<?php

namespace App\Shared\Domain\Exception\Services;

use App\Shared\Domain\Exception\InvalidStringException;

final class StringMaxLengthException extends InvalidStringException
{
    public static function becauseValueIsToLong(int $maxLength): self
    {
        return new self(sprintf('Value longer than %d characters', $maxLength));
    }
}
