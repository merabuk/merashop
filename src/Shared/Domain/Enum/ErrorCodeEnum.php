<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum ErrorCodeEnum: string
{
    case UnexpectedError = 'UNEXPECTED_ERROR';
    case ValidationFailed = 'VALIDATION_FAILED';
}
