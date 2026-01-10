<?php

namespace App\Shared\Domain\Exception;

final class IntegerIsNotUnsignedException extends InvalidArgumentException
{
    public static function becauseValueIsNotUnsigned(): self
    {
        return new self('Value must be a positive integer');
    }
}
