<?php

namespace App\Customer\Domain\Exception\CustomerProfile;

use App\Customer\Domain\Enum\ErrorCodeEnum;
use App\Customer\Domain\Exception\CustomerDomainException;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class CustomerProfileNotFoundException extends CustomerDomainException implements NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CustomerProfileNotFound->value;
    }
}
