<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Bus\Middleware;

use App\Shared\Domain\Bus\AsyncMessageInterface;
use App\Shared\Infrastructure\Bus\Middleware\EventRoutingKeyMiddleware;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class EventRoutingKeyMiddlewareTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testItAddsAmqpStampForAsyncMessages(): void
    {
        $middleware = new EventRoutingKeyMiddleware();

        $message = new class implements AsyncMessageInterface {
            public function getRoutingKey(): string
            {
                return 'test.routing.key';
            }
        };

        $envelope = new Envelope($message);

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->method('next')->willReturn($nextMiddleware);

        $nextMiddleware->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (Envelope $envelope) {
                $stamp = $envelope->last(AmqpStamp::class);

                return $stamp instanceof AmqpStamp && $stamp->getRoutingKey() === 'test.routing.key';
            }))
            ->willReturnArgument(0);

        $middleware->handle($envelope, $stack);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testItDoesNotAddStampForRegularMessages(): void
    {
        $middleware = new EventRoutingKeyMiddleware();

        $envelope = new Envelope(new stdClass());

        $stack = $this->createMock(StackInterface::class);
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $stack->method('next')->willReturn($nextMiddleware);

        $nextMiddleware->expects(self::once())
            ->method('handle')
            ->with(self::callback(function (Envelope $envelope) {
                return $envelope->last(AmqpStamp::class) === null;
            }))
            ->willReturnArgument(0);

        $middleware->handle($envelope, $stack);
    }
}
