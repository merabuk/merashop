<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\LogicException;

abstract class IdentityAccessDomainException extends LogicException implements IdentityAccessExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::IdentityAccessDomainError->value;
    }

    public function getTranslationDomain(): string
    {
        return 'identity_access_exceptions';
    }
}
