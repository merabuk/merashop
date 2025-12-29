<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\TraceId;

interface TraceIdFactoryInterface
{
    public function createNew(): TraceId;

    public function createFromString(string $value): TraceId;
}
