<?php

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\InvalidUlidException as BaseInvalidUlidException;

final class InvalidUlidException extends InvalidValueObjectException
{
    public static function fromBase(BaseInvalidUlidException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
