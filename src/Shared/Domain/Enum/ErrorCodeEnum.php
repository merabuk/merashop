<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum ErrorCodeEnum: string
{
    case UnexpectedError = 'UNEXPECTED_ERROR';
    case ValidationFailed = 'VALIDATION_FAILED';
    case AccessDenied = 'ACCESS_DENIED';
    case Unauthorized = 'UNAUTHORIZED';
    case NotFound = 'NOT_FOUND';
    case BadRequest = 'BAD_REQUEST';
    case UnsupportedMediaType = 'UNSUPPORTED_MEDIA_TYPE';
}
