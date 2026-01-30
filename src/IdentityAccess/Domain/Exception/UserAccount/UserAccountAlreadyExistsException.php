<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\UserAccount;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessConflictException;

class UserAccountAlreadyExistsException extends InvalidIdentityAccessConflictException
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::UserAccountAlreadyExists->value;
    }
}
