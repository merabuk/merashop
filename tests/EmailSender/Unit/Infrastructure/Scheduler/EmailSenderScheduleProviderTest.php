<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Scheduler;

use App\EmailSender\Application\Scheduler\OutboxEmailRelayMessage;
use App\EmailSender\Infrastructure\Scheduler\EmailSenderScheduleProvider;
use App\Tests\Shared\BaseUnitTest;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

final class EmailSenderScheduleProviderTest extends BaseUnitTest
{
    public function testScheduleDefinition(): void
    {
        $clock = new MockClock();
        $provider = new EmailSenderScheduleProvider();
        $schedule = $provider->getSchedule();

        $messages = $schedule->getRecurringMessages();

        self::assertCount(1, $messages);

        $recurringMessage = $messages[0];

        $triggerMock = $this->createMock(TriggerInterface::class);
        $context = new MessageContext(
            name: 'email_sender',
            id: 'default',
            trigger: $triggerMock,
            triggeredAt: $clock->now()
        );

        self::assertInstanceOf(
            OutboxEmailRelayMessage::class,
            $recurringMessage->getMessages($context)[0]
        );

        self::assertStringContainsString('every 1 minute', (string) $recurringMessage->getTrigger());
    }
}
