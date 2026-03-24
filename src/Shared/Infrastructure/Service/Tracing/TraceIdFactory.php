<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service\Tracing;

use App\Shared\Domain\Exception\Services\Tracing\TraceIdFactoryException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\Identity\UuidGeneratorInterface;
use App\Shared\Domain\Service\Tracing\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\Tracing\TraceId;

final readonly class TraceIdFactory implements TraceIdFactoryInterface
{
    public function __construct(
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function createNew(): TraceId
    {
        try {
            return $this->makeTraceId($this->uuidGenerator->nextV7());
        } catch (InvalidTraceIdException) {
            throw TraceIdFactoryException::becauseCanNotGenerateTraceId();
        }
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function createFromString(string $value): TraceId
    {
        try {
            return $this->makeTraceId($value);
        } catch (InvalidTraceIdException) {
            throw TraceIdFactoryException::becauseCanNotCreateTraceIdFromString();
        }
    }

    /**
     * @throws InvalidTraceIdException
     */
    private function makeTraceId(string $traceId): TraceId
    {
        return TraceId::fromString($traceId);
    }
}
