<?php

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\Services\Identity\InvalidUlidException as BaseInvalidUlidException;

final class InvalidUlidException extends InvalidValueObjectExceptionInterface
{
    public static function fromBase(BaseInvalidUlidException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
