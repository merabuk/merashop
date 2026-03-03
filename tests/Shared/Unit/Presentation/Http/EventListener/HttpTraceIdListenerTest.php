<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\EventListener;

use App\Shared\Domain\Exception\Request\InvalidRequestHeaderValueException;
use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Presentation\Http\EventListener\HttpTraceIdListener;
use App\Tests\Shared\Support\Traits\AppListenerTrait;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class HttpTraceIdListenerTest extends TestCase
{
    use AppListenerTrait;
    use TraceIdHelperTrait;

    public function testItSetsTraceIdFromHeader(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new HttpTraceIdListener(traceIdContext: $context, traceIdFactory: $factory);

        $traceIdValue = '01952796-03f3-793a-867c-d6159f8a329f';
        $traceId = $this->getTraceId($traceIdValue);

        $factory->expects(self::once())
            ->method('createFromString')
            ->with($traceIdValue)
            ->willReturn($traceId);

        $context->expects(self::once())->method('set')->with($traceId);

        $request = new Request();
        $request->headers->set(HttpTraceIdListener::TRACE_ID_HEADER, $traceIdValue);

        $listener->onKernelRequest($this->makeRequestEvent(request: $request));

        self::assertSame($traceId, $request->attributes->get('trace_id'));
    }

    public function testItSetsTraceIdWithoutHeader(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new HttpTraceIdListener(traceIdContext: $context, traceIdFactory: $factory);

        $traceIdValue = '01952796-03f3-793a-867c-d6159f8a329f';
        $traceId = $this->getTraceId($traceIdValue);

        $factory->expects(self::once())
            ->method('createNew')
            ->willReturn($traceId);

        $context->expects(self::once())->method('set')->with($traceId);

        $listener->onKernelRequest($this->makeRequestEvent());
    }

    public function testItDoesNothingOnSubRequest(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new HttpTraceIdListener($context, $factory);

        $factory->expects(self::never())->method('createNew');

        $listener->onKernelRequest($this->makeRequestEvent(requestType: HttpKernelInterface::SUB_REQUEST));
    }

    public function testThrowsExceptionOnInvalidTraceId(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new HttpTraceIdListener(traceIdContext: $context, traceIdFactory: $factory);

        $invalidTraceId = 'invalid-trace-id';
        $factory->expects(self::once())
            ->method('createFromString')
            ->with($invalidTraceId)
            ->willThrowException(new TraceIdFactoryException('Invalid trace ID format'));

        $request = new Request();
        $request->headers->set(HttpTraceIdListener::TRACE_ID_HEADER, $invalidTraceId);

        $this->expectException(InvalidRequestHeaderValueException::class);
        $listener->onKernelRequest($this->makeRequestEvent(request: $request));
    }

    public function testItAddsResponseHeader(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new HttpTraceIdListener($context, $factory);

        $traceIdValue = '01952796-03f3-793a-867c-d6159f8a329f';
        $traceId = $this->getTraceId($traceIdValue);

        $context->expects(self::once())->method('get')->willReturn($traceId);

        $event = $this->makeResponseEvent();

        $listener->onKernelResponse($event);

        self::assertSame($traceIdValue, $event->getResponse()->headers->get(HttpTraceIdListener::TRACE_ID_HEADER));
    }
}
