<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

class InvalidUserUlidException extends InvalidUserValueObjectException
{
    public static function becauseItIsNotAValidUlid(string $invalidValue): self
    {
        return new self(sprintf('The string "%s" is not a valid User ID (ULID)', $invalidValue));
    }
}
