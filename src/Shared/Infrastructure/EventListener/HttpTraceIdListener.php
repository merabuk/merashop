<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdGeneratorInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final readonly class HttpTraceIdListener
{
    public function __construct(
        private TraceIdContextInterface $context,
        private TraceIdGeneratorInterface $traceIdGenerator,
    ) {
    }

    #[AsEventListener(event: RequestEvent::class, priority: 255)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) return;

        $request = $event->getRequest();
        $traceId = $request->headers->get('X-Trace-Id') ?? $this->traceIdGenerator->generate();

        $this->context->set($traceId);

        $request->attributes->set('trace_id', $traceId);
    }

    #[AsEventListener(event: ResponseEvent::class)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Trace-Id', $this->context->get()->value());
    }
}
