<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;

abstract class InvalidIdentityAccessValueObjectException extends IdentityAccessDomainException implements ValueObjectExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_VALUE_OBJECT_ERROR';
    }
}
