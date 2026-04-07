<?php

namespace App\Shared\Domain\Exception\Services\Identity;

use App\Shared\Domain\Exception\InvalidArgumentException;

final class InvalidUlidException extends InvalidArgumentException
{
    public static function becauseItIsNotAValidUlid(string $invalidValue): self
    {
        return new self(sprintf('The string "%s" is not a valid ULID', $invalidValue));
    }
}
