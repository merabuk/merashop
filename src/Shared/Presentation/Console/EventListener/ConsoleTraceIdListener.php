<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Console\EventListener;

use App\Shared\Domain\Exception\Services\Tracing\TraceIdFactoryException;
use App\Shared\Domain\Service\Tracing\TraceIdContextInterface;
use App\Shared\Domain\Service\Tracing\TraceIdFactoryInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsoleCommandEvent::class)]
final readonly class ConsoleTraceIdListener
{
    public function __construct(
        private TraceIdContextInterface $traceIdContext,
        private TraceIdFactoryInterface $traceIdFactory,
    ) {
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->traceIdContext->set($this->traceIdFactory->createNew());
    }
}
