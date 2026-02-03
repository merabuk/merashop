<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Exception;

use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;
use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\UnauthorizedExceptionInterface;

class InvalidCredentialsException extends IdentityAccessDomainException implements UnauthorizedExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::Unauthorized->value;
    }
}
