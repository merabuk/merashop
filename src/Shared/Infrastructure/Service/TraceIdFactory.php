<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Exception\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\TraceId;

final class TraceIdFactory implements TraceIdFactoryInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function createNew(): TraceId
    {
        return $this->makeTraceId($this->uuidGenerator->nextV7());
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function createFromString(string $value): TraceId
    {
        return $this->makeTraceId($value);
    }

    /**
     * @throws InvalidTraceIdException
     */
    private function makeTraceId(string $traceId): TraceId
    {
        return TraceId::fromString($traceId);
    }
}
