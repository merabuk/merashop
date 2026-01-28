<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\ConflictExceptionInterface;

abstract class InvalidIdentityAccessConflictException extends IdentityAccessDomainException implements ConflictExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_CONFLICT_ERROR';
    }
}
