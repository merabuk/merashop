<?php

namespace App\Shared\Domain\Exception;

class InvalidUlidException extends InvalidArgumentException
{
    public static function becauseItIsNotAValidUlid(string $invalidValue): self
    {
        return new self(sprintf('The string "%s" is not a valid ULID', $invalidValue));
    }
}
