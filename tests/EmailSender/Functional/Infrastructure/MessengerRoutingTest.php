<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Functional\Infrastructure;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Enum\EmailSenderEventNameEnum;
use App\Shared\Infrastructure\Bus\Middleware\EventRoutingKeyMiddleware;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class MessengerRoutingTest extends KernelTestCase
{
    public function testCommandHasCorrectRoutingStamp(): void
    {
        self::bootKernel();
        $fakeId = 123;

        $middleware = self::getContainer()->get(EventRoutingKeyMiddleware::class);

        $command = new SendOutboxEmailCommand($fakeId);
        $envelope = new Envelope($command);

        $envelope = $middleware->handle($envelope, $this->createMockStack());

        /** @var ?AmqpStamp $stamp */
        $stamp = $envelope->last(AmqpStamp::class);

        self::assertNotNull($stamp);
        self::assertEquals(
            expected: EmailSenderEventNameEnum::EmailProcessor->value,
            actual: $stamp->getRoutingKey()
        );
    }

    private function createMockStack(): StackInterface
    {
        $nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $nextMiddleware->method('handle')
            ->willReturnCallback(fn (Envelope $envelope) => $envelope);

        $stack = $this->createMock(StackInterface::class);
        $stack->method('next')->willReturn($nextMiddleware);

        return $stack;
    }
}
