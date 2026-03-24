<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service\Tracing;

use App\Shared\Domain\Exception\Services\Tracing\TraceIdFactoryException;
use App\Shared\Domain\Service\Tracing\TraceIdContextInterface;
use App\Shared\Domain\Service\Tracing\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\Tracing\TraceId;
use Symfony\Contracts\Service\ResetInterface;

final class TraceIdContext implements TraceIdContextInterface, ResetInterface
{
    private ?TraceId $current = null;

    public function __construct(
        private readonly TraceIdFactoryInterface $traceIdFactory,
    ) {
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function get(): TraceId
    {
        return $this->current ??= $this->traceIdFactory->createNew();
    }

    public function set(TraceId $traceId): void
    {
        $this->current = $traceId;
    }

    public function reset(): void
    {
        $this->current = null;
    }
}
