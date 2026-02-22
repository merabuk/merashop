<?php

declare(strict_types=1);

use App\Shared\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::UnexpectedError->value => 'An unexpected error occurred',
    ErrorCodeEnum::ValidationFailed->value => 'Validation failed',
    ErrorCodeEnum::AccessDenied->value => 'Access denied',
    ErrorCodeEnum::Unauthorized->value => 'Unauthorized access (invalid or expired token)',
    ErrorCodeEnum::ConcurrencyError->value => 'The {entityName} has been already modified. Please refresh the page and try again',
    ErrorCodeEnum::InvalidRequestHeaderValue->value => 'Invalid value for header "{headerName}"',
];
