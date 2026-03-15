<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class UserAccountAlreadyExistsException extends InvalidIdentityAccessConflictException implements ClientFacingExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::UserAccountAlreadyExists->value;
    }
}
