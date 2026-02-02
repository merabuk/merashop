<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Scheduler;

use App\EmailSender\Application\Scheduler\OutboxEmailRelayMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('email_sender')]
final class EmailSenderScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return new Schedule()
            ->add(
                RecurringMessage::every('1 minute', new OutboxEmailRelayMessage())
            );
    }
}
