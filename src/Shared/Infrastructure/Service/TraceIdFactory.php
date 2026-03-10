<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\Identity\TraceId;

final class TraceIdFactory implements TraceIdFactoryInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator,
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
