<?php

declare(strict_types=1);

use App\Customer\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::CustomerDomainError->value => 'Something went wrong in the customer domain. Please try again later',
    ErrorCodeEnum::CustomerProfileNotFound->value => 'Customer profile not found',
];
