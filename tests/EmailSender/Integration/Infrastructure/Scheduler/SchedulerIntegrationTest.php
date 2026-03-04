<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Integration\Infrastructure\Scheduler;

use App\EmailSender\Infrastructure\Scheduler\EmailSenderScheduleProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

final class SchedulerIntegrationTest extends KernelTestCase
{
    public function testProviderIsRegisteredInContainer(): void
    {
        self::bootKernel();

        $provider = self::getContainer()->get(EmailSenderScheduleProvider::class);

        self::assertInstanceOf(ScheduleProviderInterface::class, $provider);
        self::assertInstanceOf(EmailSenderScheduleProvider::class, $provider);
    }

    public function testMessengerTransportExists(): void
    {
        self::bootKernel();

        $receiverLocator = self::getContainer()->get('messenger.receiver_locator');

        self::assertTrue(
            $receiverLocator->has('scheduler_email_sender'),
            'Messenger receiver "scheduler_email_sender" should be registered'
        );
    }
}
