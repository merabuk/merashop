<?php

namespace App\Shared\Domain\Exception;

class IntegerIsNotUnsignedException extends InvalidArgumentException
{
    public static function becauseValueIsNotUnsigned(): self
    {
        return new self('Value must be a positive integer');
    }
}
