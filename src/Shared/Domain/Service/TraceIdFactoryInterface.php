<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\ValueObject\Identity\TraceId;

interface TraceIdFactoryInterface
{
    /**
     * @throws TraceIdFactoryException
     */
    public function createNew(): TraceId;

    /**
     * @throws TraceIdFactoryException
     */
    public function createFromString(string $value): TraceId;
}
