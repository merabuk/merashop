<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\ModuleAccount;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class ModuleAccountAlreadyExistsException extends InvalidIdentityAccessConflictException implements ClientFacingExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AdminAccountAlreadyExists->value;
    }
}
