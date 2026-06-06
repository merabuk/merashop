<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum ErrorCodeEnum: string
{
    case UnexpectedError = 'unexpected_error';
    case ValidationFailed = 'validation_failed';
    case AccessDenied = 'access_denied';
    case Unauthorized = 'unauthorized';
    case NotFound = 'not_found';
    case MethodNotAllowed = 'method_not_allowed';
    case BadRequest = 'bad_request';
    case UnsupportedMediaType = 'unsupported_media_type';
    case Conflict = 'conflict';
    case ConcurrencyError = 'concurrency_error';
    case InvalidRequestHeaderValue = 'invalid_request_header_value';
}
