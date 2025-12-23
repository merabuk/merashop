<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\TraceId;

interface TraceIdContextInterface
{
    public function get(): TraceId;

    public function set(string $traceId): void;
}
