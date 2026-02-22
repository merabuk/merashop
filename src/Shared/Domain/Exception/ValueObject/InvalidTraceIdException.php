<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\Services\InvalidUuidException;

final class InvalidTraceIdException extends InvalidValueObjectExceptionInterface
{
    public static function fromBase(InvalidUuidException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
