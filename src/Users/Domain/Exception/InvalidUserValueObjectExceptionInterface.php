<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ValueObjectExceptionInterface;

abstract class InvalidUserValueObjectExceptionInterface extends UserDomainException implements ValueObjectExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'USERS_VALUE_OBJECT_ERROR';
    }
}
