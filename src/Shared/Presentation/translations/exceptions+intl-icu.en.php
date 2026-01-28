<?php

declare(strict_types=1);

use App\Shared\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::UnexpectedError->value => 'An unexpected error occurred',
    ErrorCodeEnum::ValidationFailed->value => 'Validation failed',
];
