<?php

namespace App\Shared\Domain\Exception;

class StringEmptyException extends InvalidStringException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('Value cannot be empty');
    }
}
