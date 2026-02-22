<?php

namespace App\Shared\Domain\Exception\Services;

use App\Shared\Domain\Exception\InvalidStringException;

final class StringMinLengthException extends InvalidStringException
{
    public static function becauseValueIsToShort(int $minLength): self
    {
        return new self(sprintf('Value shorter than %d characters', $minLength));
    }
}
