<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\AdminAccount;

use App\IdentityAccess\Domain\Enum\ErrorCodeEnum;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessConflictException;

class AdminAccountAlreadyExistsException extends InvalidIdentityAccessConflictException
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AdminAccountAlreadyExists->value;
    }
}
