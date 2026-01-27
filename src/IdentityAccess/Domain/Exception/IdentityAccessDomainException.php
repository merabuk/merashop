<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class IdentityAccessDomainException extends ServerException implements ThrowableIdentityAccessException
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_DOMAIN_ERROR';
    }
}
