<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Exception\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdGeneratorInterface;
use App\Shared\Domain\ValueObject\TraceId;

final class TraceIdContext implements TraceIdContextInterface
{
    private ?TraceId $current = null;

    public function __construct(
        private readonly TraceIdGeneratorInterface $traceIdGenerator,
    ) {
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function get(): TraceId
    {
        return $this->current ??= $this->makeTraceId($this->traceIdGenerator->generate());
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function set(string $traceId): void
    {
        $this->current = $this->makeTraceId($traceId);
    }

    /**
     * @throws InvalidTraceIdException
     */
    private function makeTraceId(string $traceId): TraceId
    {
        return TraceId::fromString($traceId);
    }
}
