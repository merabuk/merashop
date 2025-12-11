<?php

namespace App\Shared\Domain\Exception;

class StringMaxLengthException extends InvalidStringException
{
    public static function becauseValueIsToLong(int $maxLength): self
    {
        return new self(sprintf('Value longer than %d characters', $maxLength));
    }
}
