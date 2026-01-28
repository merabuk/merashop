<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\LogicException;

abstract class IdentityAccessDomainException extends LogicException implements IdentityAccessExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'IDENTITY_ACCESS_DOMAIN_ERROR';
    }
}
