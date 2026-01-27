<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class UserDomainException extends ServerException implements ThrowableUsersException
{
    public function getErrorCode(): string
    {
        return 'USER_DOMAIN_ERROR';
    }
}
