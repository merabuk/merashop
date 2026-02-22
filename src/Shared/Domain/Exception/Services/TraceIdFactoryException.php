<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services;

use App\Shared\Domain\Exception\LogicException;

class TraceIdFactoryException extends LogicException
{
    public static function becauseCanNotGenerateTraceId(): self
    {
        return new self('Can not generate trace id');
    }

    public static function becauseCanNotCreateTraceIdFromString(): self
    {
        return new self('Can not create trace id from giving string');
    }

    public function getErrorCode(): string
    {
        return 'TRACE_ID_FACTORY_ERROR';
    }
}
