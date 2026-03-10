<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\Identity\TraceId;

interface TraceIdContextInterface
{
    public function get(): TraceId;

    public function set(TraceId $traceId): void;
}
