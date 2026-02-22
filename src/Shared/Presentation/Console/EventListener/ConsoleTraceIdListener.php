<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Console\EventListener;

use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsoleCommandEvent::class)]
final readonly class ConsoleTraceIdListener
{
    public function __construct(
        private TraceIdContextInterface $context,
        private TraceIdFactoryInterface $traceIdGenerator,
    ) {
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->context->set($this->traceIdGenerator->createNew());
    }
}
