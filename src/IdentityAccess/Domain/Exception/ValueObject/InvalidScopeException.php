<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ValueObject;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

class InvalidScopeException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Scope cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Scope cannot be longer than %d characters', $maxLength));
    }

    public static function becauseFormatIsInvalid(string $got): self
    {
        return new self(sprintf('Scope must have valid format, got "%s"', $got));
    }
}
