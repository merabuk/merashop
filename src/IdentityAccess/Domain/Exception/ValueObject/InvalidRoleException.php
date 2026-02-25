<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ValueObject;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidRoleException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Role cannot be empty');
    }

    public static function becausePrefixIsMissing(string $prefix, string $got): self
    {
        return new self(sprintf('Role must start with %s, got "%s"', $prefix, $got));
    }
}
