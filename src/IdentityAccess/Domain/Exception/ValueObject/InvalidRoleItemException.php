<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ValueObject;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;

class InvalidRoleItemException extends InvalidIdentityAccessValueObjectException
{
    public static function fromBase(InvalidAbstractCollectionItemException $e): self
    {
        return new self(message: $e->getMessage(), previous: $e);
    }
}
