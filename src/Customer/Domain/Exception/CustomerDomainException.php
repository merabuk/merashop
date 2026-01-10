<?php

declare(strict_types=1);

namespace App\Customer\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class CustomerDomainException extends ServerException implements ThrowableCustomerException
{
}
