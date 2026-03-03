<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\ValueObject\TraceId;

trait TraceIdHelperTrait
{
    protected function getTraceId(?string $traceId = null): TraceId
    {
        return TraceId::fromString($traceId ?? '01952796-03f3-793a-867c-d6159f8a329f');
    }
}
