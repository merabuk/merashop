<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\LogicException;

abstract class UserDomainException extends LogicException implements UsersExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'USER_DOMAIN_ERROR';
    }
}
