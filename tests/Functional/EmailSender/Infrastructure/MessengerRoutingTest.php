<?php

declare(strict_types=1);

namespace App\Tests\Functional\EmailSender\Infrastructure;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Enum\EmailSenderQueueEnum;
use App\Shared\Infrastructure\Bus\Middleware\EventRoutingKeyMiddleware;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
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

        $this->assertNotNull($stamp);
        $this->assertEquals(
            expected: EmailSenderQueueEnum::EmailProcessor->value,
            actual: $stamp->getRoutingKey()
        );
    }

    private function createMockStack(): StackInterface
    {
        $stack = $this->createMock(StackInterface::class);
        $stack->method('next')->willReturn($stack);

        return $stack;
    }
}
