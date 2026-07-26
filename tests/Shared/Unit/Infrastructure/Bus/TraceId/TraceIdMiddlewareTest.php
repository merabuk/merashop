<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Bus\TraceId;

use App\Shared\Domain\Service\Tracing\TraceIdContextInterface;
use App\Shared\Infrastructure\Bus\TraceId\TraceIdMiddleware;
use App\Shared\Infrastructure\Bus\TraceId\TraceIdStamp;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class TraceIdMiddlewareTest extends BaseUnitTest
{
    use TraceIdHelperTrait;

    public function testItSetsTraceIdContextFromStamp(): void
    {
        $traceIdContext = $this->createMock(TraceIdContextInterface::class);
        $middleware = new TraceIdMiddleware($traceIdContext);

        $traceId = $this->getTraceId();
        $envelope = new Envelope(new stdClass(), [new TraceIdStamp($traceId)]);

        $traceIdContext->expects(self::once())
            ->method('set')
            ->with($traceId);

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        $nextMiddleware->method('handle')->willReturnArgument(0);

        $middleware->handle($envelope, $stack);
    }

    public function testItAddsTraceIdStampToEnvelopeFromContext(): void
    {
        $traceIdContext = $this->createMock(TraceIdContextInterface::class);
        $middleware = new TraceIdMiddleware($traceIdContext);

        $traceId = $this->getTraceId();

        $traceIdContext->method('get')->willReturn($traceId);

        $envelope = new Envelope(new stdClass());

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        $nextMiddleware->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (Envelope $envelope) use ($traceId) {
                $stamp = $envelope->last(TraceIdStamp::class);

                return $stamp instanceof TraceIdStamp && $stamp->traceId->equals($traceId);
            }))
            ->willReturnArgument(0);

        $middleware->handle($envelope, $stack);
    }
}
