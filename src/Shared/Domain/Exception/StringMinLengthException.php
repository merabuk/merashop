<?php

namespace App\Shared\Domain\Exception;

class StringMinLengthException extends InvalidStringException
{
    public static function becauseValueIsToShort(int $minLength): self
    {
        return new self(sprintf('Value shorter than %d characters', $minLength));
    }
}
