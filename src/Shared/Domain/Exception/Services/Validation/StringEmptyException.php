<?php

namespace App\Shared\Domain\Exception\Services\Validation;

final class StringEmptyException extends InvalidStringException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('Value cannot be empty');
    }
}
