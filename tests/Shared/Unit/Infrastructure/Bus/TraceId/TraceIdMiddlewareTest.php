<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Bus\TraceId;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Bus\TraceId\TraceIdMiddleware;
use App\Shared\Infrastructure\Bus\TraceId\TraceIdStamp;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TraceIdMiddlewareTest extends TestCase
{
    /**
     * @throws InvalidTraceIdException
     * @throws ExceptionInterface
     */
    public function testItSetsTraceIdContextFromStamp(): void
    {
        $traceIdContext = $this->createMock(TraceIdContextInterface::class);
        $middleware = new TraceIdMiddleware($traceIdContext);

        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');
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

    /**
     * @throws InvalidTraceIdException
     * @throws ExceptionInterface
     */
    public function testItAddsTraceIdStampToEnvelopeFromContext(): void
    {
        $traceIdContext = $this->createMock(TraceIdContextInterface::class);
        $middleware = new TraceIdMiddleware($traceIdContext);

        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

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
