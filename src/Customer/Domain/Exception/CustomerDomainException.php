<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Shared\Domain\Exception\LogicException;

abstract class CustomerDomainException extends LogicException implements CustomerExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'CUSTOMER_DOMAIN_ERROR';
    }
}
