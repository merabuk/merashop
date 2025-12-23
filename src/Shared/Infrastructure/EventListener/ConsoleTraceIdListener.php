<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdGeneratorInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsoleCommandEvent::class)]
final readonly class ConsoleTraceIdListener
{
    public function __construct(
        private TraceIdContextInterface $context,
        private TraceIdGeneratorInterface $traceIdGenerator,
    ) {
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $this->context->set($this->traceIdGenerator->generate());
    }
}
