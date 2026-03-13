<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Customer\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\LogicException;

abstract class CustomerDomainException extends LogicException implements CustomerExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CustomerDomainError->value;
    }

    public function getTranslationDomain(): string
    {
        return 'customer_exceptions';
    }
}
