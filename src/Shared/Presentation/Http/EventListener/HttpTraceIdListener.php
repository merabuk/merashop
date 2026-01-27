<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventListener;

use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final readonly class HttpTraceIdListener
{
    public const string TRACE_ID_HEADER = 'Merashop-Trace-Id';

    public function __construct(
        private TraceIdContextInterface $context,
        private TraceIdFactoryInterface $traceIdFactory,
    ) {
    }

    #[AsEventListener(event: RequestEvent::class, priority: 255)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $headerValue = $request->headers->get(self::TRACE_ID_HEADER);

        $traceId = $headerValue
            ? $this->traceIdFactory->createFromString($headerValue)
            : $this->traceIdFactory->createNew();

        $this->context->set($traceId);

        $request->attributes->set('trace_id', $traceId);
    }

    #[AsEventListener(event: ResponseEvent::class)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set(self::TRACE_ID_HEADER, $this->context->get()->value());
    }
}
