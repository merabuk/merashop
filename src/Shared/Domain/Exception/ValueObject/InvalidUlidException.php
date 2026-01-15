<?php

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\InvalidUlidException as BaseInvalidUlidException;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;

final class InvalidUlidException extends LogicException implements ThrowableValueObjectException
{
    public static function fromBase(BaseInvalidUlidException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
